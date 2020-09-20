<?php

namespace App\Console\Commands;

use App\InvoiceItems;
use App\Invoices;
use Illuminate\Console\Command;

class SendRecurringInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For Sending Recurring Invoice';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $current_date = date('Y-m-d');

        $recurring_invoice = Invoices::where('recurring', '!=', '0')->where('duedate', $current_date)->where('bill_created', 'no')->get();

        foreach ($recurring_invoice as $ri) {

            $rinvoice = Invoices::find($ri->id);

            $due_date = new \DateTime($ri->duedate);
            $due_date->modify($ri->recurring);
            $duedate = $due_date->format('Y-m-d');

            $newInvoice = $rinvoice->replicate();

            $newInvoice->duedate = $duedate;
            $newInvoice->status  = 'Unpaid';

            $rinvoice->bill_created = 'yes';
            $newInvoice->save();
            $rinvoice->save();

            $inv_items = InvoiceItems::where('inv_id', $ri->id)->where('cl_id', $ri->cl_id)->get();
            foreach ($inv_items as $ii) {
                $newItems = $ii->replicate();
                $newItems->save();
            }

        }

    }
}
