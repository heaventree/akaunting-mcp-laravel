<?php

namespace App\Mcp\Tools;

use App\Services\AkauntingClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Delete a transaction by ID. This cannot be undone.')]
class DeleteTransactionTool extends AkauntingTool
{
    public function __construct(private readonly AkauntingClient $client) {}

    public function schema(JsonSchema $schema): array
    {
        return [
            'transaction_id' => $schema->integer()->description('Transaction ID to delete.')->required(),
        ];
    }

    protected function execute(Request $request): Response
    {
        $id = $request->get('transaction_id');
        $this->client->delete("transactions/{$id}");

        return Response::text(json_encode(['deleted' => true, 'transaction_id' => $id]));
    }
}
