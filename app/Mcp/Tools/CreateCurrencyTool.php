<?php

namespace App\Mcp\Tools;

use App\Services\AkauntingClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Add a currency to the company, optionally making it the default (use this to set the company to EUR).')]
class CreateCurrencyTool extends AkauntingTool
{
    public function __construct(private readonly AkauntingClient $client) {}

    public function schema(JsonSchema $schema): array
    {
        return [
            'code'             => $schema->string()->description('ISO code, e.g. "EUR".')->required(),
            'name'             => $schema->string()->description('Name, e.g. "Euro".')->required(),
            'rate'             => $schema->number()->description('Rate against the default currency (1 for the default).')->default(1),
            'default_currency' => $schema->boolean()->description('Make this the company default currency.')->default(false),
        ];
    }

    protected function execute(Request $request): Response
    {
        $data = [
            'code'             => strtoupper($request->get('code')),
            'name'             => $request->get('name'),
            'rate'             => $request->get('rate', 1),
            'enabled'          => 1,
            'default_currency' => $request->get('default_currency', false) ? 1 : 0,
        ];

        return Response::text(json_encode($this->client->post('currencies', $data)));
    }
}
