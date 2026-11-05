<?php

namespace App\Mcp\Tools;

use App\Services\AkauntingClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use App\Mcp\Tools\Concerns\NormalizesDates;

#[Description('Record a payment against an invoice (money received) or a bill (money paid). Akaunting marks the document partial or paid automatically.')]
class RecordDocumentPaymentTool extends AkauntingTool
{
    use NormalizesDates;

    public function __construct(private readonly AkauntingClient $client) {}

    public function schema(JsonSchema $schema): array
    {
        return [
            'document_id'    => $schema->integer()->description('Invoice or bill ID.')->required(),
            'document_type'  => $schema->string()->description('"invoice" or "bill".')->required(),
            'account_id'     => $schema->integer()->description('Account the money moved through (see list_accounts).')->required(),
            'amount'         => $schema->number()->description('Amount paid. Defaults to the full document amount.'),
            'paid_at'        => $schema->string()->description('Payment date (YYYY-MM-DD). Defaults to today.'),
            'payment_method' => $schema->string()->description('Payment method code.')->default('offline-payments.bank_transfer.2'),
            'number'         => $schema->string()->description('Unique transaction number. Auto-generated if omitted.'),
            'description'    => $schema->string()->description('Description.'),
            'reference'      => $schema->string()->description('Reference, e.g. Stripe or GoCardless payout id.'),
        ];
    }

    protected function execute(Request $request): Response
    {
        $id = $request->get('document_id');
        $docType = $request->get('document_type');

        if (! in_array($docType, ['invoice', 'bill'], true)) {
            return Response::error('document_type must be "invoice" or "bill".');
        }

        $query = ['search' => 'type:'.$docType];
        $doc = $this->unwrap($this->client->get("documents/{$id}", $query));

        $data = [
            'type'           => $docType === 'invoice' ? 'income' : 'expense',
            'number'         => $request->get('number') ?: 'PAY-'.$id.'-'.date('YmdHis'),
            'account_id'     => $request->get('account_id'),
            'amount'         => $request->get('amount', $doc['amount'] ?? null),
            'paid_at'        => $this->toDateTime($request->get('paid_at', date('Y-m-d'))),
            'currency_code'  => $doc['currency_code'] ?? null,
            'currency_rate'  => $doc['currency_rate'] ?? 1,
            'category_id'    => $doc['category_id'] ?? null,
            'contact_id'     => $doc['contact_id'] ?? null,
            'document_id'    => $id,
            'payment_method' => $request->get('payment_method', 'offline-payments.bank_transfer.2'),
        ];

        foreach (['description', 'reference'] as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->get($field);
            }
        }

        return Response::text(json_encode($this->client->post("documents/{$id}/transactions", $data, $query)));
    }
}
