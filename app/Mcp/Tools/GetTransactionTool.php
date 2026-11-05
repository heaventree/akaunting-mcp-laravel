<?php

namespace App\Mcp\Tools;

use App\Services\AkauntingClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Get full details for a single transaction (income or expense payment) by ID.')]
class GetTransactionTool extends AkauntingTool
{
    public function __construct(private readonly AkauntingClient $client) {}

    public function schema(JsonSchema $schema): array
    {
        return [
            'transaction_id' => $schema->integer()->description('Transaction ID.')->required(),
        ];
    }

    protected function execute(Request $request): Response
    {
        return Response::text(json_encode($this->client->get('transactions/'.$request->get('transaction_id'))));
    }
}
