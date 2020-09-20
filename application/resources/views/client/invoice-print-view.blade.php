<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>

    <title>Invoice -{{$inv->id}}</title>
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">
    <style>

        * {
            margin: 0;
            padding: 0;
        }

        body {
            font: 14px/1.4 Helvetica, Arial, sans-serif;
        }

        #page-wrap {
            width: 800px;
            margin: 0 auto;
        }

        textarea {
            border: 0;
            font: 14px Helvetica, Arial, sans-serif;
            overflow: hidden;
            resize: none;
        }

        table {
            border-collapse: collapse;
        }

        table td, table th {
            border: 1px solid black;
            padding: 5px;
        }

        #header {
            height: 15px;
            width: 100%;
            margin: 20px 0;
            background: #222;
            text-align: center;
            color: white;
            font: bold 15px Helvetica, Sans-Serif;
            text-decoration: uppercase;
            letter-spacing: 20px;
            padding: 8px 0px;
        }

        #address {
            width: 250px;
            height: 150px;
            float: left;
        }

        #customer {
            overflow: hidden;
        }

        #logo {
            text-align: right;
            float: right;
            position: relative;
            margin-top: 25px;
            border: 1px solid #fff;
            max-width: 540px;
            overflow: hidden;
        }

        #customer-title {
            font-size: 20px;
            font-weight: bold;
            float: left;
        }

        #meta {
            margin-top: 1px;
            width: 100%;
            float: right;
        }

        #meta td {
            text-align: right;
        }

        #meta td.meta-head {
            text-align: left;
            background: #eee;
        }

        #meta td textarea {
            width: 100%;
            height: 20px;
            text-align: right;
        }

        #items {
            clear: both;
            width: 100%;
            margin: 30px 0 0 0;
            border: 1px solid black;
        }

        #items th {
            background: #eee;
        }

        #items textarea {
            width: 80px;
            height: 50px;
        }

        #items tr.item-row td {
            vertical-align: top;
        }

        #items td.description {
            width: 300px;
        }

        #items td.item-name {
            width: 175px;
        }

        #items td.description textarea, #items td.item-name textarea {
            width: 100%;
        }

        #items td.total-line {
            border-right: 0;
            text-align: right;
        }

        #items td.total-value {
            border-left: 0;
            padding: 10px;
        }

        #items td.total-value textarea {
            height: 20px;
            background: none;
        }

        #items td.balance {
            background: #eee;
        }

        #items td.blank {
            border: 0;
        }

        #terms {
            text-align: center;
            margin: 20px 0 0 0;
        }

        #terms h5 {
            text-transform: uppercase;
            font: 13px Helvetica, Sans-Serif;
            letter-spacing: 10px;
            border-bottom: 1px solid black;
            padding: 0 0 8px 0;
            margin: 0 0 8px 0;
        }

        #terms textarea {
            width: 100%;
            text-align: center;
        }

        .delete-wpr {
            position: relative;
        }

        .delete {
            display: block;
            color: #000;
            text-decoration: none;
            position: absolute;
            background: #EEEEEE;
            font-weight: bold;
            padding: 0px 3px;
            border: 1px solid;
            top: -6px;
            left: -22px;
            font-family: Verdana;
            font-size: 12px;
        }
        .text-center{
            text-align: center;
        }
        .text-success{
            color:#30ddbc ;
        }
    </style>

</head>

<body>

<div id="page-wrap">

    <table width="100%">
        <tr>
            <td style="border: 0;  text-align: left" width="62%">
                <span style="font-size: 18px; color: #2f4f4f"><strong>{{language_data('Invoice',Auth::guard('client')->user()->lan_id)}} # {{$inv->id}}</strong></span>
            </td>
            <td style="border: 0;  text-align: right" width="62%">
                <div id="logo">
                    <h3>{{app_config('AppName')}}</h3><br>
                    {!!app_config('Address')!!}
                </div>
            </td>
        </tr>

    </table>

    <div style="clear:both"></div>

    <div id="customer">

        <table id="meta">
            <tr>
                <td rowspan="5" style="border: 1px solid white; border-right: 1px solid black; text-align: left" width="62%"> {{$inv->cname}}
                    <br>
                    {{$client->address1}} <br>
                    {{$client->address2}} <br>
                    {{$client->state}}, {{$client->city}} - {{$client->postcode}}, {{$client->country}}
                    <br>
                    @if($client->phone!='')
                        {{$client->phone}}
                        <br>
                    @endif
                    @if($client->email!='')
                        {{$client->email}}
                    @endif
                </td>
                <td class="meta-head">{{language_data('Invoice',Auth::guard('client')->user()->lan_id)}} #</td>
                <td>{{$inv->id}}</td>
            </tr>
            <tr>

                <td class="meta-head">{{language_data('Status',Auth::guard('client')->user()->lan_id)}}</td>
                <td>{{$inv->status}}</td>
            </tr>
            <tr>

                <td class="meta-head">{{language_data('Invoice Date',Auth::guard('client')->user()->lan_id)}}</td>
                <td>{{get_date_format($inv->created)}}</td>
            </tr>
            <tr>

                <td class="meta-head">{{language_data('Due Date',Auth::guard('client')->user()->lan_id)}}</td>
                <td>{{get_date_format($inv->duedate)}}</td>
            </tr>

            <tr>

                <td class="meta-head">{{language_data('Amount Due',Auth::guard('client')->user()->lan_id)}}</td>
                <td>
                    <div class="due">{{$inv->total}}</div>
                </td>
            </tr>

        </table>

    </div>

    <table id="items">

        <tr>
            <th width="65%">{{language_data('Item',Auth::guard('client')->user()->lan_id)}}</th>
            <th align="right">{{language_data('Price',Auth::guard('client')->user()->lan_id)}}</th>
            <th align="right">{{language_data('Quantity',Auth::guard('client')->user()->lan_id)}}</th>
            <th align="right">{{language_data('Total',Auth::guard('client')->user()->lan_id)}}</th>

        </tr>


        @foreach($inv_items as $it)
            <tr class="item-row">
                <td class="description">{{$it->item}}</td>
                <td align="right">{{app_config('CurrencyCode')}} {{$it->price}}</td>
                <td align="right">{{$it->qty}}</td>
                <td align="right">{{app_config('CurrencyCode')}} {{$it->subtotal}}</td>
            </tr>
        @endforeach

        @if($tax_sum!='0.00' OR $tax_sum!='')
            <tr>
                <td class="blank"></td>
                <td colspan="2" class="total-line">{{language_data('Subtotal',Auth::guard('client')->user()->lan_id)}}</td>
                <td class="total-value">
                    <div id="subtotal">{{app_config('CurrencyCode')}} {{$inv->subtotal}}</div>
                </td>
            </tr>
            <tr>

                <td class="blank text-center text-success">@if($inv->status=='Paid') <h1>Paid</h1> @endif</td>
                <td colspan="2" class="total-line">{{language_data('Tax',Auth::guard('client')->user()->lan_id)}}</td>
                <td class="total-value">
                    <div id="total">{{app_config('CurrencyCode')}} {{$tax_sum}}</div>
                </td>
            </tr>
        @endif

        @if($dis_sum!='0.00' OR $dis_sum!='')
            <tr>
                <td class="blank"></td>
                <td colspan="2" class="total-line">{{language_data('Discount',Auth::guard('client')->user()->lan_id)}}</td>
                <td class="total-value">
                    <div id="total">{{app_config('CurrencyCode')}} {{$dis_sum}}</div>
                </td>
            </tr>
        @endif

        <tr>
            <td class="blank"></td>
            <td colspan="2" class="total-line balance">{{language_data('Grand Total',Auth::guard('client')->user()->lan_id)}}</td>
            <td class="total-value balance">
                <div class="due">{{app_config('CurrencyCode')}} {{$inv->total}}</div>
            </td>
        </tr>

    </table>

    @if($inv->note!='')
        <div id="terms">
            <h5>{{language_data('Invoice Note',Auth::guard('client')->user()->lan_id)}}</h5>
            {{$inv->note}}
        </div>
    @endif


</div>

</body>

</html>
