<?php

namespace App\Mcp\Tools;

use App\Services\AkauntingClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use App\Mcp\Tools\Concerns\NormalizesDates;

#[Description('Update an existing transaction, typically to categorise it: set its category, contact, description or reference, or link it to an invoice/bill. Only the fields you pass are changed.')]
class UpdateTransactionTool extends AkauntingTool
{
    use NormalizesDates;

    private const FIELDS = [
        'category_id', 'contact_id', 'document_id', 'description', 'reference',
        'account_id', 'amount', 'paid_at', 'payment_method', 'number',
    ];

    public function __construct(private readonly AkauntingClient $client) {}

    public function schema(JsonSchema $schema): array
    {
        return [
            'transaction_id' => $schema->integer()->description('Transaction ID to update.')->required(),
            'category_id'    => $schema->integer()->description('New category ID (see list_categories; must match the transaction type).'),
            'contact_id'     => $schema->integer()->description('Customer (income) or vendor (expense) ID.'),
            'document_id'    => $schema->integer()->description('Invoice/bill ID this transaction settles.'),
            'description'    => $schema->string()->description('Description.'),
            'reference'      => $schema->string()->description('Reference, e.g. a bank statement reference.'),
            'account_id'     => $schema->integer()->description('Move the transaction to another account.'),
            'amount'         => $schema->number()->description('Corrected amount.'),
            'paid_at'        => $schema->string()->description('Corrected date (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS).'),
            'payment_method' => $schema->string()->description('Payment method code, e.g. "offline-payments.bank_transfer.2".'),
            'number'         => $schema->string()->description('Transaction number.'),
        ];
    }

    protected function execute(Request $request): Response
    {
        $id = $request->get('transaction_id');

        // Akaunting PUT is a full replace validated like a create, so start
        // from the current record and overlay the requested changes.
        $current = $this->unwrap($this->client->get("transactions/{$id}"));

        $data = [
            'type'           => $current['type'] ?? null,
            'number'         => $current['number'] ?? null,
            'account_id'     => $current['account_id'] ?? null,
            'paid_at'        => $this->toDateTime((string) ($current['paid_at'] ?? '')),
            'amount'         => $current['amount'] ?? null,
            'currency_code'  => $current['currency_code'] ?? null,
            'currency_rate'  => $current['currency_rate'] ?? 1,
            'category_id'    => $current['category_id'] ?? null,
            'payment_method' => $current['payment_method'] ?? null,
            'contact_id'     => $current['contact_id'] ?? null,
            'document_id'    => $current['document_id'] ?? null,
            'description'    => $current['description'] ?? null,
            'reference'      => $current['reference'] ?? null,
        ];

        foreach (self::FIELDS as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->get($field);
            }
        }

        $data['paid_at'] = $this->toDateTime((string) $data['paid_at']);

        return Response::text(json_encode($this->client->put("transactions/{$id}", array_filter($data, fn ($v) => $v !== null))));
    }
}
