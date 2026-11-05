<?php

namespace App\Mcp\Tools;

use App\Services\AkauntingClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use App\Mcp\Tools\Concerns\NormalizesDates;

#[Description('Move money between two of your own accounts (e.g. Wise to Revolut, or a Stripe clearing account to the bank). Creates the matching expense and income legs.')]
class CreateTransferTool extends AkauntingTool
{
    use NormalizesDates;

    public function __construct(private readonly AkauntingClient $client) {}

    public function schema(JsonSchema $schema): array
    {
        return [
            'from_account_id' => $schema->integer()->description('Account the money leaves.')->required(),
            'to_account_id'   => $schema->integer()->description('Account the money arrives in.')->required(),
            'amount'          => $schema->number()->description('Amount, in the from-account currency.')->required(),
            'transferred_at'  => $schema->string()->description('Date (YYYY-MM-DD).')->required(),
            'payment_method'  => $schema->string()->description('Payment method code.')->default('offline-payments.bank_transfer.2'),
            'description'     => $schema->string()->description('Description.'),
            'reference'       => $schema->string()->description('Reference.'),
        ];
    }

    protected function execute(Request $request): Response
    {
        $data = [
            'from_account_id' => $request->get('from_account_id'),
            'to_account_id'   => $request->get('to_account_id'),
            'amount'          => $request->get('amount'),
            'transferred_at'  => $this->toDate($request->get('transferred_at')),
            'payment_method'  => $request->get('payment_method', 'offline-payments.bank_transfer.2'),
        ];

        foreach (['description', 'reference'] as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->get($field);
            }
        }

        return Response::text(json_encode($this->client->post('transfers', $data)));
    }
}
