<?php

namespace App\Http\Controllers;

use App\Client;
use App\InvoiceItems;
use App\Invoices;
use App\PaymentGateways;
use Illuminate\Support\Facades\Auth;

class ClientInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('client');
    }

    //======================================================================
    // allInvoices Function Start Here
    //======================================================================
    public function allInvoices()
    {
        $invoices = Invoices::where('cl_id', Auth::guard('client')->user()->id)->orderBy('updated_at', 'DESC')->get();
        return view('client.all-invoices', compact('invoices'));
    }

    //======================================================================
    // recurringInvoices Function Start Here
    //======================================================================
    public function recurringInvoices()
    {

        $invoices = Invoices::where('cl_id', Auth::guard('client')->user()->id)->where('recurring', '!=', '0')->orderBy('updated_at', 'DESC')->get();
        return view('client.all-invoices', compact('invoices'));
    }


    //======================================================================
    // viewInvoice Function Start Here
    //======================================================================
    public function viewInvoice($id)
    {

        $inv = Invoices::where('cl_id', Auth::guard('client')->user()->id)->find($id);
        if ($inv) {
            $client           = Client::where('status', 'Active')->find($inv->cl_id);
            $inv_items        = InvoiceItems::where('inv_id', $id)->get();
            $tax_sum          = InvoiceItems::where('inv_id', $id)->sum('tax');
            $dis_sum          = InvoiceItems::where('inv_id', $id)->sum('discount');
            $payment_gateways = PaymentGateways::where('status', 'Active')->get();
            return view('client.view-invoice', compact('client', 'inv', 'inv_items', 'tax_sum', 'dis_sum', 'payment_gateways'));
        } else {
            return redirect('user/invoices/all')->with([
                'message' => language_data('Invoice not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // clientIView Function Start Here
    //======================================================================
    public function clientIView($id)
    {
        $inv = Invoices::where('cl_id', Auth::guard('client')->user()->id)->find($id);
        if ($inv) {
            $client    = Client::where('status', 'Active')->find($inv->cl_id);
            $inv_items = InvoiceItems::where('inv_id', $id)->get();
            $tax_sum   = InvoiceItems::where('inv_id', $id)->sum('tax');
            $dis_sum   = InvoiceItems::where('inv_id', $id)->sum('discount');
            return view('client.invoice-client-view', compact('client', 'inv', 'inv_items', 'tax_sum', 'dis_sum'));
        } else {
            return redirect('user/invoices/all')->with([
                'message' => language_data('Invoice not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // printView Function Start Here
    //======================================================================
    public function printView($id)
    {
        $inv = Invoices::where('cl_id', Auth::guard('client')->user()->id)->find($id);
        if ($inv) {
            $client    = Client::where('status', 'Active')->find($inv->cl_id);
            $inv_items = InvoiceItems::where('inv_id', $id)->get();
            $tax_sum   = InvoiceItems::where('inv_id', $id)->sum('tax');
            $dis_sum   = InvoiceItems::where('inv_id', $id)->sum('discount');
            return view('client.invoice-print-view', compact('client', 'inv', 'inv_items', 'tax_sum', 'dis_sum'));
        } else {
            return redirect('user/invoices/all')->with([
                'message' => language_data('Invoice not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // downloadPdf Function Start Here
    //======================================================================
    public function downloadPdf($id)
    {
        $inv = Invoices::where('cl_id', Auth::guard('client')->user()->id)->find($id);

        if ($inv) {

            $client    = Client::where('status', 'Active')->find($inv->cl_id);
            $inv_items = InvoiceItems::where('inv_id', $id)->get();
            $tax_sum   = InvoiceItems::where('inv_id', $id)->sum('tax');
            $dis_sum   = InvoiceItems::where('inv_id', $id)->sum('discount');
            $data      = view('client.invoice-pdf-view', compact('client', 'inv', 'inv_items', 'tax_sum', 'dis_sum'));
            $html      = $data->render();
            $pdf       = \App::make('snappy.pdf.wrapper');
            $pdf->loadHTML($html)->setPaper('a4')->setOption('margin-bottom', 0);
            return $pdf->download('invoice.pdf');
        } else {
            return redirect('user/invoices/all')->with([
                'message' => language_data('Invoice not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

    }


}
