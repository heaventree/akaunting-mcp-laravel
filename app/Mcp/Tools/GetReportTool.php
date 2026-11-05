<?php

namespace App\Mcp\Tools;

use App\Services\AkauntingClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Run a report by ID (see list_reports) and return its figures.')]
class GetReportTool extends AkauntingTool
{
    public function __construct(private readonly AkauntingClient $client) {}

    public function schema(JsonSchema $schema): array
    {
        return [
            'report_id' => $schema->integer()->description('Report ID.')->required(),
            'year'      => $schema->integer()->description('Financial year to report on.'),
        ];
    }

    protected function execute(Request $request): Response
    {
        $params = $request->has('year') ? ['year' => $request->get('year')] : [];

        return Response::text(json_encode($this->client->get('reports/'.$request->get('report_id'), $params)));
    }
}
