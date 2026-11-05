<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreateContactTool;
use App\Mcp\Tools\CreateDocumentTool;
use App\Mcp\Tools\CreateItemTool;
use App\Mcp\Tools\CancelDocumentTool;
use App\Mcp\Tools\CreateTransactionTool;
use App\Mcp\Tools\DeleteDocumentTool;
use App\Mcp\Tools\GetContactTool;
use App\Mcp\Tools\GetDocumentTool;
use App\Mcp\Tools\ListAccountsTool;
use App\Mcp\Tools\ListCategoriesTool;
use App\Mcp\Tools\ListCompaniesTool;
use App\Mcp\Tools\ListContactsTool;
use App\Mcp\Tools\ListCurrenciesTool;
use App\Mcp\Tools\ListDocumentsTool;
use App\Mcp\Tools\ListItemsTool;
use App\Mcp\Tools\ListTaxesTool;
use App\Mcp\Tools\ListTransactionsTool;
use App\Mcp\Tools\UpdateContactTool;
use App\Mcp\Tools\UpdateDocumentTool;
use App\Mcp\Tools\CreateAccountTool;
use App\Mcp\Tools\CreateCategoryTool;
use App\Mcp\Tools\CreateCurrencyTool;
use App\Mcp\Tools\CreateTransactionsBatchTool;
use App\Mcp\Tools\CreateTransferTool;
use App\Mcp\Tools\DeleteTransactionTool;
use App\Mcp\Tools\GetReportTool;
use App\Mcp\Tools\GetTransactionTool;
use App\Mcp\Tools\ListReportsTool;
use App\Mcp\Tools\ListTransfersTool;
use App\Mcp\Tools\RecordDocumentPaymentTool;
use App\Mcp\Tools\UpdateTransactionTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Akaunting')]
#[Version('1.1.0')]
#[Instructions('Manage accounting in Akaunting 3.1.21: customers and vendors (contacts), items, invoices and bills (documents), income and expenses (transactions), plus reference data such as accounts, categories, taxes and currencies. Akaunting is multi-company; use list_companies to discover company IDs. When creating records, fetch the relevant reference IDs first (list_accounts, list_categories, list_currencies). Documents (invoices and bills) cannot be cancelled through the API; if asked to cancel one, use the cancel_document tool, which explains why and points to the Akaunting web UI Cancel button or delete_document. To categorise a bank line, use update_transaction with a category_id. To mark an invoice or bill paid, use record_document_payment rather than create_transaction. Use create_transfer for money moving between your own accounts, never two transactions.')]
class AkauntingServer extends Server
{
    protected array $tools = [
        ListContactsTool::class,
        GetContactTool::class,
        CreateContactTool::class,
        UpdateContactTool::class,
        ListItemsTool::class,
        CreateItemTool::class,
        ListDocumentsTool::class,
        GetDocumentTool::class,
        CreateDocumentTool::class,
        UpdateDocumentTool::class,
        CancelDocumentTool::class,
        DeleteDocumentTool::class,
        ListTransactionsTool::class,
        CreateTransactionTool::class,
        CreateTransactionsBatchTool::class,
        GetTransactionTool::class,
        UpdateTransactionTool::class,
        DeleteTransactionTool::class,
        RecordDocumentPaymentTool::class,
        ListTransfersTool::class,
        CreateTransferTool::class,
        ListAccountsTool::class,
        CreateAccountTool::class,
        ListCategoriesTool::class,
        CreateCategoryTool::class,
        ListTaxesTool::class,
        ListCurrenciesTool::class,
        CreateCurrencyTool::class,
        ListReportsTool::class,
        GetReportTool::class,
        ListCompaniesTool::class,
    ];
}
