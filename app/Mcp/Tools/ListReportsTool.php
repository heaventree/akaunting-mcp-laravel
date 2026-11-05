<?php

namespace App\Mcp\Tools;

use App\Services\AkauntingClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('List the reports available in Akaunting (profit and loss, income summary, expense summary, tax summary and so on) with their IDs.')]
class ListReportsTool extends AkauntingTool
{
    public function __construct(private readonly AkauntingClient $client) {}

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    protected function execute(Request $request): Response
    {
        return Response::text(json_encode($this->client->get('reports')));
    }
}
