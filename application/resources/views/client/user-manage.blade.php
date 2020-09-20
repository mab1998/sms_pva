@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('View Profile',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-body p-t-20">
                            <div class="clearfix">
                                <div class="pull-left m-r-30">
                                    <div class="thumbnail m-b-none">
                                        @if($client->image!='')
                                            <img src="<?php echo asset('assets/client_pic/' . $client->image); ?>" alt="{{$client->fname}} {{$client->lname}}" height="150px" width="150px">
                                        @else
                                            <img src="<?php echo asset('assets/client_pic/profile.jpg'); ?>" alt="{{$client->fname}} {{$client->lname}}" height="150px" width="150px">
                                        @endif
                                    </div>
                                </div>
                                <div class="pull-left">
                                    <h3 class="bold font-color-1">{{$client->fname}} {{$client->lname}}</h3>
                                    <ul class="info-list">
                                        @if($client->email!='')
                                            <li>
                                                <span class="info-list-title">{{language_data('Email',Auth::guard('client')->user()->lan_id)}}</span><span class="info-list-des">{{$client->email}}</span>
                                            </li>
                                        @endif
                                        <li>
                                            <span class="info-list-title">{{language_data('Phone',Auth::guard('client')->user()->lan_id)}}</span><span class="info-list-des">{{$client->phone}}</span>
                                        </li>
                                        <li>
                                            <span class="info-list-title">{{language_data('Location',Auth::guard('client')->user()->lan_id)}}</span><span class="info-list-des">{{$client->address1}} {{$client->address2}} {{$client->state}} {{$client->city}} - {{$client->postcode}} {{$client->country}}</span>
                                        </li>
                                        <li>
                                            <span class="info-list-title">{{language_data('SMS Balance',Auth::guard('client')->user()->lan_id)}}</span><span class="info-list-des">{{$client->sms_limit}}</span>
                                        </li>
                                    </ul>

                                    <a href="#" data-toggle="modal" data-target=".modal_send_sms_{{$client->id}}" class="btn btn-success btn-sm"><i class="fa fa-mobile-phone"></i> {{language_data('Send SMS',Auth::guard('client')->user()->lan_id)}}</a>
                                    <a href="#" data-toggle="modal" data-target=".modal_update_limit_{{$client->id}}" class="btn btn-primary btn-sm"><i class="fa fa-exchange"></i> {{language_data('Update Limit',Auth::guard('client')->user()->lan_id)}}</a>
                                    <a href="#" data-toggle="modal" data-target=".modal_change_image_{{$client->id}}" class="btn btn-complete btn-sm change-image"><i class="fa fa-image"></i> {{language_data('Change Image',Auth::guard('client')->user()->lan_id)}}</a>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <div class="p-30 p-t-none p-b-none">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#edit-profile" aria-controls="home" role="tab" data-toggle="tab">{{language_data('Edit Profile'),Auth::guard('client')->user()->lan_id}}</a></li>
                        <li role="presentation"><a href="#tickets" aria-controls="tickets" role="tab" data-toggle="tab">{{language_data('Support Tickets',Auth::guard('client')->user()->lan_id)}}</a></li>
                        <li role="presentation"><a href="#invoices" aria-controls="invoices" role="tab" data-toggle="tab">{{language_data('Invoices',Auth::guard('client')->user()->lan_id)}}</a></li>
                        <li role="presentation"><a href="#sms-transaction" aria-controls="sms-transaction" role="tab" data-toggle="tab">{{language_data('SMS Transaction'),Auth::guard('client')->user()->lan_id}}</a></li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content panel p-20">
                        <div role="tabpanel" class="tab-pane active" id="edit-profile">
                            <form role="form" action="{{url('user/update-user-post')}}" method="post">
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-md-4">

                                        <div class="form-group">
                                            <label>{{language_data('First Name',Auth::guard('client')->user()->lan_id)}}</label>
                                            <input type="text" class="form-control" required="" name="first_name" value="{{$client->fname}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Last Name',Auth::guard('client')->user()->lan_id)}}</label>
                                            <input type="text" class="form-control" name="last_name"  value="{{$client->lname}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Company',Auth::guard('client')->user()->lan_id)}}</label>
                                            <input type="text" class="form-control" name="company" value="{{$client->company}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Website',Auth::guard('client')->user()->lan_id)}}</label>
                                            <input type="url" class="form-control" name="website" value="{{$client->website}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Client Group',Auth::guard('client')->user()->lan_id)}}</label>
                                            <select class="selectpicker form-control" name="client_group"  data-live-search="true">
                                                <option value="0" @if($client->groupid==0) selected @endif>{{language_data('None',Auth::guard('client')->user()->lan_id)}}</option>
                                                @foreach($clientGroups as $cg)
                                                    <option value="{{$cg->id}}"  @if($client->groupid==$cg->id) selected @endif>{{$cg->group_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <div class="form-group">
                                            <label>{{language_data('SMS Gateway',Auth::guard('client')->user()->lan_id)}}</label>
                                            <select class="selectpicker form-control" name="sms_gateway[]"  data-live-search="true" multiple>
                                                @if(isset($selected_gateways) && is_array($selected_gateways))
                                                    @foreach($sms_gateways as $sg)
                                                        <option value="{{$sg->id}}" @if(in_array_r($sg->id,$selected_gateways)) selected @endif>{{$sg->name}}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>


                                        @if(\Auth::guard('client')->user()->api_access=='Yes')
                                            <div class="form-group">
                                                <label>{{language_data('Api Access',Auth::guard('client')->user()->lan_id)}}</label>
                                                <select class="selectpicker form-control" name="api_access">
                                                    <option value="Yes" @if($client->api_access=='Yes') selected @endif>{{language_data('Yes',Auth::guard('client')->user()->lan_id)}}</option>
                                                    <option value="No" @if($client->api_access=='No') selected @endif>{{language_data('No',Auth::guard('client')->user()->lan_id)}}</option>
                                                </select>
                                            </div>
                                        @endif

                                    </div>
                                    <div class="col-md-4">

                                        <div class="form-group">
                                            <label>{{language_data('Address',Auth::guard('client')->user()->lan_id)}}</label>
                                            <input type="text" class="form-control" name="address" value="{{$client->address1}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('More Address',Auth::guard('client')->user()->lan_id)}}</label>
                                            <input type="text" class="form-control" name="more_address"  value="{{$client->address2}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('State',Auth::guard('client')->user()->lan_id)}}</label>
                                            <input type="text" class="form-control" name="state"  value="{{$client->state}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('City',Auth::guard('client')->user()->lan_id)}}</label>
                                            <input type="text" class="form-control" name="city"  value="{{$client->city}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Postcode',Auth::guard('client')->user()->lan_id)}}</label>
                                            <input type="text" class="form-control" name="postcode"  value="{{$client->postcode}}">
                                        </div>

                                        <div class="form-group">
                                            <label for="Country">{{language_data('Country',Auth::guard('client')->user()->lan_id)}}</label>
                                            <select name="country" class="form-control selectpicker" data-live-search="true">
                                                {!!countries($client->country)!!}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{language_data('Email',Auth::guard('client')->user()->lan_id)}}</label>
                                            <input type="email" class="form-control" required name="email" value="{{$client->email}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('User Name',Auth::guard('client')->user()->lan_id)}}</label>
                                            <input type="text" class="form-control" required name="user_name" value="{{$client->username}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Password',Auth::guard('client')->user()->lan_id)}}</label> <span class="help">{{language_data('Leave blank if you do not change',Auth::guard('client')->user()->lan_id)}}</span>
                                            <input type="password" class="form-control" name="password">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Phone',Auth::guard('client')->user()->lan_id)}}</label>
                                            <input type="text" class="form-control" required name="phone" value="{{$client->phone}}">
                                        </div>


                                        <div class="form-group">
                                            <label>{{language_data('Status',Auth::guard('client')->user()->lan_id)}}</label>
                                            <select class="selectpicker form-control" name="status">
                                                <option value="Active" @if($client->status=='Active') selected @endif>{{language_data('Active',Auth::guard('client')->user()->lan_id)}}</option>
                                                <option value="Inactive" @if($client->status=='Inactive') selected @endif>{{language_data('Inactive',Auth::guard('client')->user()->lan_id)}}</option>
                                                <option value="Closed" @if($client->status=='Closed') selected @endif>{{language_data('Closed',Auth::guard('client')->user()->lan_id)}}</option>
                                            </select>
                                        </div>

                                        @if(\Auth::guard('client')->user()->reseller_client=='Yes')

                                            <div class="form-group">
                                                <label>{{language_data('Reseller Panel',Auth::guard('client')->user()->lan_id)}}</label>
                                                <select class="selectpicker form-control" name="reseller_panel">
                                                    <option value="Yes" @if($client->reseller=='Yes') selected @endif>{{language_data('Yes',Auth::guard('client')->user()->lan_id)}}</option>
                                                    <option value="No" @if($client->reseller=='No') selected @endif>{{language_data('No',Auth::guard('client')->user()->lan_id)}}</option>
                                                </select>
                                            </div>

                                        @endif

                                    </div>

                                    <div class="col-md-12">
                                        <input type="hidden" value="{{$client->id}}" name="cmd">
                                        <input type="submit" value="{{language_data('Update',Auth::guard('client')->user()->lan_id)}}" class="btn btn-primary">
                                    </div>
                                </div>


                            </form>
                        </div>

                        <div role="tabpanel" class="tab-pane" id="tickets">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{language_data('Subject',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th>{{language_data('Date',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th>{{language_data('Status',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th class="text-right">{{language_data('Action',Auth::guard('client')->user()->lan_id)}}</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($tickets as $in)
                                    <tr>
                                        <td>{{$loop->iteration}} </td>
                                        <td>{{$in->subject}}</td>
                                        <td>{{get_date_format($in->date)}}</td>
                                        <td>
                                            @if($in->status=='Pending')
                                                <span class="label label-danger">{{language_data('Pending',Auth::guard('client')->user()->lan_id)}}</span>
                                            @elseif($in->status=='Answered')
                                                <span class="label label-success">{{language_data('Answered',Auth::guard('client')->user()->lan_id)}}</span>
                                            @elseif($in->status=='Customer Reply')
                                                <span class="label label-info">{{language_data('Customer Reply',Auth::guard('client')->user()->lan_id)}}</span>
                                            @else
                                                <span class="label label-primary">{{language_data('Closed',Auth::guard('client')->user()->lan_id)}}</span>
                                            @endif
                                        </td>

                                        <td class="text-right">
                                            <a href="{{url('user/tickets/view-ticket/'.$in->id)}}" class="btn btn-success btn-xs"><i class="fa fa-eye"></i> {{language_data('View',Auth::guard('client')->user()->lan_id)}}</a>
                                        </td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>


                        <div role="tabpanel" class="tab-pane" id="invoices">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 10%;">{{language_data('Amount',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 15%;">{{language_data('Invoice Date',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 15%;">{{language_data('Due Date',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 10%;">{{language_data('Status',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 15%;">{{language_data('Type',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 30%;">{{language_data('Manage',Auth::guard('client')->user()->lan_id)}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($invoices as $in)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{us_money_format($in->total)}}</td>
                                        <td>{{get_date_format($in->created)}}</td>
                                        <td>{{get_date_format($in->duedate)}}</td>
                                        <td>
                                            @if($in->status=='Unpaid')
                                                <span class="label label-warning">{{language_data('Unpaid',Auth::guard('client')->user()->lan_id)}}</span>
                                            @elseif($in->status=='Paid')
                                                <span class="label label-success">{{language_data('Paid',Auth::guard('client')->user()->lan_id)}}</span>
                                            @elseif($in->status=='Cancelled')
                                                <span class="label label-danger">{{language_data('Cancelled',Auth::guard('client')->user()->lan_id)}}</span>
                                            @else
                                                <span class="label label-info">{{language_data('Partially Paid',Auth::guard('client')->user()->lan_id)}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($in->recurring=='0')
                                                <span class="label label-success"> {{language_data('Onetime',Auth::guard('client')->user()->lan_id)}}</span>
                                            @else
                                                <span class="label label-info"> {{language_data('Recurring',Auth::guard('client')->user()->lan_id)}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{url('user/invoices/view/'.$in->id)}}" class="btn btn-success btn-xs"><i class="fa fa-eye"></i> {{language_data('View'),Auth::guard('client')->user()->lan_id}}</a>
                                            <a href="{{url('user/invoices/edit/'.$in->id)}}" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i> {{language_data('Edit'),Auth::guard('client')->user()->lan_id}}</a>
                                        </td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>


                        <div role="tabpanel" class="tab-pane" id="sms-transaction">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 20%;">#</th>
                                    <th style="width: 30%;">{{language_data('Amount',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 50%;">{{language_data('Date',Auth::guard('client')->user()->lan_id)}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($sms_transaction as $st)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{$st->amount}}</td>
                                        <td>{{get_date_format($st->updated_at)}}</td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade modal_send_sms_{{$client->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">

        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">{{language_data('Send SMS',Auth::guard('client')->user()->lan_id)}}</h4>
                </div>
                <form class="form-some-up form-block" role="form" action="{{url('user/send-sms')}}" method="post">

                    <div class="modal-body">
                        @if(app_config('sender_id_verification') == 1)
                            @if($sender_ids)
                                <div class="form-group">
                                    <label>{{language_data('Sender ID',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="selectpicker form-control sender_id" name="sender_id" data-live-search="true">
                                        @foreach($sender_ids as $si)
                                            <option value="{{$si}}">{{$si}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="form-group">
                                    <label>{{language_data('Sender ID',Auth::guard('client')->user()->lan_id)}}</label>
                                    <p><a href="{{url('user/sms/sender-id-management')}}" class="text-uppercase">{{language_data('Request New Sender ID',Auth::guard('client')->user()->lan_id)}}</a> </p>
                                </div>
                            @endif
                        @else
                            <div class="form-group">
                                <label>{{language_data('Sender ID',Auth::guard('client')->user()->lan_id)}}</label>
                                <input type="text" class="form-control sender_id" name="sender_id" value="{{old('sender_id')}}">
                            </div>
                        @endif


                        <div class="form-group">
                            <label>{{language_data('Message Type',Auth::guard('client')->user()->lan_id)}}</label>
                            <select class="selectpicker form-control message_type" name="message_type">
                                <option value="plain">{{language_data('Plain',Auth::guard('client')->user()->lan_id)}}</option>
                                <option value="unicode">{{language_data('Unicode',Auth::guard('client')->user()->lan_id)}}</option>
                                <option value="arabic">{{language_data('Arabic',Auth::guard('client')->user()->lan_id)}}</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>{{language_data('Message',Auth::guard('client')->user()->lan_id)}}</label>
                            <textarea class="form-control" name="message" rows="5" id="message"></textarea>
                            <span class="help text-uppercase" id="remaining">160 {{language_data('characters remaining',Auth::guard('client')->user()->lan_id)}}</span>
                            <span class="help text-success" id="messages">1 {{language_data('message',Auth::guard('client')->user()->lan_id)}}(s)</span>
                        </div>


                        <div class="form-group">
                            <label>{{language_data('SMS Gateway',Auth::guard('client')->user()->lan_id)}}</label>
                            <select class="selectpicker form-control" name="sms_gateway" data-live-search="true">
                                @foreach($sms_gateways as $gateway)
                                    <option value="{{$gateway->id}}">{{$gateway->name}}</option>
                                @endforeach
                            </select>
                        </div>



                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="hidden" name="cmd" value="{{$client->id}}">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{language_data('Close',Auth::guard('client')->user()->lan_id)}}</button>
                        <button type="submit" class="btn btn-primary">{{language_data('Send',Auth::guard('client')->user()->lan_id)}}</button>
                    </div>

                </form>
            </div>
        </div>

    </div>
    <div class="modal fade modal_update_limit_{{$client->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">{{language_data('Update Limit',Auth::guard('client')->user()->lan_id)}}</h4>
                </div>
                <form class="form-some-up form-block" role="form" action="{{url('user/update-limit')}}" method="post">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{language_data('SMS Balance',Auth::guard('client')->user()->lan_id)}} :</label>
                            <input type="number" class="form-control" required="" name="sms_amount">
                            <span class="help">{{language_data('Update with previous balance. Enter (-) amount for decrease limit',Auth::guard('client')->user()->lan_id)}}</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="hidden" name="cmd" value="{{$client->id}}">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{language_data('Close',Auth::guard('client')->user()->lan_id)}}</button>
                        <button type="submit" class="btn btn-primary">{{language_data('Add',Auth::guard('client')->user()->lan_id)}}</button>
                    </div>

                </form>
            </div>
        </div>

    </div>
    <div class="modal fade modal_change_image_{{$client->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">{{language_data('Update Image',Auth::guard('client')->user()->lan_id)}}</h4>
                </div>
                <form class="form-some-up form-block" role="form" action="{{url('user/update-image')}}" method="post" enctype="multipart/form-data">

                    <div class="modal-body">

                        <div class="form-group">
                            <label>{{language_data('Avatar',Auth::guard('client')->user()->lan_id)}}</label>
                            <div class="input-group input-group-file">
                                        <span class="input-group-btn">
                                            <span class="btn btn-primary btn-file">
                                                {{language_data('Browse',Auth::guard('client')->user()->lan_id)}} <input type="file" class="form-control" name="client_image" accept="image/*">
                                            </span>
                                        </span>
                                <input type="text" class="form-control" readonly="">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="hidden" name="cmd" value="{{$client->id}}">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{language_data('Close',Auth::guard('client')->user()->lan_id)}}</button>
                        <button type="submit" class="btn btn-primary">{{language_data('Update',Auth::guard('client')->user()->lan_id)}}</button>
                    </div>

                </form>
            </div>
        </div>

    </div>

@endsection

{{--External Style Section--}}
@section('script')
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
    {!! Html::script("assets/libs/data-table/datatables.min.js")!!}

    <script>
        $(document).ready(function(){

            $('.data-table').DataTable({
                language: {
                    url: '{!! url("assets/libs/data-table/i18n/".get_language_code(Auth::guard('client')->user()->lan_id)->language.".lang") !!}'
                },
                responsive: true
            })

            var $get_msg = $("#message"),
                $remaining = $('#remaining'),
                $messages = $remaining.next(),
                message_type = 'plain',
                maxCharInitial = 160,
                maxChar = 157,
                messages = 1;


            function get_character() {
                var totalChar = $get_msg[0].value.length;
                var remainingChar = maxCharInitial;

                if ( totalChar <= maxCharInitial ) {
                    remainingChar = maxCharInitial - totalChar;
                    messages = 1;
                } else {
                    totalChar = totalChar - maxCharInitial;
                    messages = Math.ceil( totalChar / maxChar );
                    remainingChar = messages * maxChar - totalChar;
                    messages = messages + 1;
                }

                $remaining.text(remainingChar + " {!! language_data('characters remaining',Auth::guard('client')->user()->lan_id) !!}");
                $messages.text(messages + " {!! language_data('message',Auth::guard('client')->user()->lan_id) !!}"+ '(s)');
            }

            $('.message_type').on('change', function () {
                message_type = $(this).val();

                if (message_type == 'unicode') {
                    maxCharInitial = 70;
                    maxChar = 67;
                    messages = 1;
                }

                if (message_type == 'plain') {
                    maxCharInitial = 160;
                    maxChar = 157;
                    messages = 1;
                }

                get_character();
            });

            $get_msg.keyup(get_character);

        });
    </script>
@endsection
