<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Models\Invoice;
use Botble\JobBoard\Supports\InvoiceHelper;
use Botble\JobBoard\Tables\InvoiceTable;
use Exception;
use Illuminate\Http\Request;

class InvoiceController extends BaseController
{
    public function index(InvoiceTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::invoice.name'));

        return $table->renderTable();
    }

    public function edit(Invoice $invoice, Request $request)
    {
        event(new BeforeEditContentEvent($request, $invoice));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $invoice->code]));

        Assets::addStylesDirectly('vendor/core/plugins/job-board/css/invoice.css');

        return view('plugins/job-board::invoice.edit', ['invoice' => $invoice]);
    }

    public function destroy(Invoice $invoice, Request $request, BaseHttpResponse $response)
    {
        try {
            $invoice->delete();

            event(new DeletedContentEvent(INVOICE_MODULE_SCREEN_NAME, $request, $invoice));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function deletes(Request $request, BaseHttpResponse $response)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        foreach ($ids as $id) {
            $invoice = Invoice::query()->findOrFail($id);
            $invoice->delete();
            event(new DeletedContentEvent(INVOICE_MODULE_SCREEN_NAME, $request, $invoice));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }

    public function getGenerateInvoice(int $invoiceId, Request $request, InvoiceHelper $invoiceHelper)
    {
        $invoice = Invoice::query()->findOrFail($invoiceId);

        if ($request->input('type') === 'print') {
            return $invoiceHelper->streamInvoice($invoice);
        }

        return $invoiceHelper->downloadInvoice($invoice);
    }
}
