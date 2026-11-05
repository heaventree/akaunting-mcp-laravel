<?php

namespace App\Mcp\Tools;

use App\Services\AkauntingClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create a financial account (a bank account, card or clearing account such as Stripe or GoCardless).')]
class CreateAccountTool extends AkauntingTool
{
    public function __construct(private readonly AkauntingClient $client) {}

    public function schema(JsonSchema $schema): array
    {
        return [
            'name'            => $schema->string()->description('Account name, e.g. "Wise EUR".')->required(),
            'number'          => $schema->string()->description('Account number or IBAN (any unique label is fine).')->required(),
            'currency_code'   => $schema->string()->description('Currency code, e.g. "EUR".')->required(),
            'type'            => $schema->string()->description('"bank" or "credit_card".')->default('bank'),
            'opening_balance' => $schema->number()->description('Opening balance.')->default(0),
            'bank_name'       => $schema->string()->description('Bank name, e.g. "Wise".'),
        ];
    }

    protected function execute(Request $request): Response
    {
        $data = [
            'type'            => $request->get('type', 'bank'),
            'name'            => $request->get('name'),
            'number'          => $request->get('number'),
            'currency_code'   => $request->get('currency_code'),
            'opening_balance' => $request->get('opening_balance', 0),
            'enabled'         => 1,
        ];

        if ($request->has('bank_name')) {
            $data['bank_name'] = $request->get('bank_name');
        }

        return Response::text(json_encode($this->client->post('accounts', $data)));
    }
}
