<?php

namespace App\Mcp\Tools;

use App\Services\AkauntingClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create a category for income, expense or items (the equivalent of a chart-of-accounts line for reporting).')]
class CreateCategoryTool extends AkauntingTool
{
    public function __construct(private readonly AkauntingClient $client) {}

    public function schema(JsonSchema $schema): array
    {
        return [
            'name'      => $schema->string()->description('Category name, e.g. "Hosting costs".')->required(),
            'type'      => $schema->string()->description('"income", "expense" or "item".')->required(),
            'color'     => $schema->string()->description('Hex colour.')->default('#6da252'),
            'parent_id' => $schema->integer()->description('Parent category ID for a sub-category.'),
        ];
    }

    protected function execute(Request $request): Response
    {
        $data = [
            'name'    => $request->get('name'),
            'type'    => $request->get('type'),
            'color'   => $request->get('color', '#6da252'),
            'enabled' => 1,
        ];

        if ($request->has('parent_id')) {
            $data['parent_id'] = $request->get('parent_id');
        }

        return Response::text(json_encode($this->client->post('categories', $data)));
    }
}
