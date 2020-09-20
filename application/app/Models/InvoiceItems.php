<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceItems extends Model
{
    protected $table='sys_invoice_items';
    protected $fillable =  ['inv_id', 'cl_id', 'item', 'price', 'qty', 'subtotal', 'tax', 'discount', 'total'];
}
