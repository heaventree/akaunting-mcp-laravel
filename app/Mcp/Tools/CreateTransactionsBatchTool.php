<?php

namespace App\Mcp\Tools;

use App\Services\AkauntingClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use App\Exceptions\AkauntingApiException;
use App\Mcp\Tools\Concerns\NormalizesDates;

#[Description('Record many transactions in one call, e.g. lines from a bank statement or a migration. Each line is created independently; the result lists which lines succeeded and the error for each that failed. Max 100 lines.')]
class CreateTransactionsBatchTool extends AkauntingTool
{
    use NormalizesDates;

    private const MAX = 100;

    public function __construct(private readonly AkauntingClient $client) {}

    public function schema(JsonSchema $schema): array
    {
        return [
            'transactions' => $schema->array()
                ->description('Lines to create. Each needs type ("income"/"expense"), account_id, amount, paid_at, currency_code, category_id; optional number, payment_method, contact_id, document_id, description, reference.')
                ->items($schema->object())
                ->required(),
            'payment_method' => $schema->string()->description('Default payment method for lines that omit one.')->default('offline-payments.bank_transfer.2'),
        ];
    }

    protected function execute(Request $request): Response
    {
        $lines = (array) $request->get('transactions', []);

        if (count($lines) > self::MAX) {
            return Response::error('Too many lines: send at most '.self::MAX.' per call.');
        }

        $defaultMethod = $request->get('payment_method', 'offline-payments.bank_transfer.2');
        $results = [];

        foreach (array_values($lines) as $i => $line) {
            $line = (array) $line;

            $data = array_merge($line, [
                'number'         => $line['number'] ?? 'TXN-'.date('YmdHis').'-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'paid_at'        => $this->toDateTime((string) ($line['paid_at'] ?? '')),
                'currency_rate'  => $line['currency_rate'] ?? 1,
                'payment_method' => $line['payment_method'] ?? $defaultMethod,
            ]);

            try {
                $created = $this->unwrap($this->client->post('transactions', $data));
                $results[] = ['line' => $i, 'ok' => true, 'id' => $created['id'] ?? null];
            } catch (AkauntingApiException $e) {
                $results[] = ['line' => $i, 'ok' => false, 'error' => $e->forAi()];
            }
        }

        $ok = count(array_filter($results, fn ($r) => $r['ok']));

        return Response::text(json_encode([
            'created' => $ok,
            'failed'  => count($results) - $ok,
            'results' => $results,
        ]));
    }
}
