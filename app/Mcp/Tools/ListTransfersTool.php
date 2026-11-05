<?php

namespace App\Mcp\Tools;

use App\Services\AkauntingClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('List transfers between accounts.')]
class ListTransfersTool extends AkauntingTool
{
    public function __construct(private readonly AkauntingClient $client) {}

    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer()->description('Max results to return.')->default(25),
        ];
    }

    protected function execute(Request $request): Response
    {
        return Response::text(json_encode($this->client->get('transfers', ['limit' => $request->get('limit', 25)])));
    }
}
