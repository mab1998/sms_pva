@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('SMS Gateway Manage')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('SMS Gateway Manage')}}</h3>
                        </div>
                        <div class="panel-body">

                            <form role="form" method="post" action="{{url('sms/post-custom-sms-gateway')}}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{language_data('Gateway Name')}}</label>
                                            <input type="text" class="form-control" required name="gateway_name" value="{{$gateway->name}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Gateway API Link')}}</label>
                                            <input type="text" class="form-control" required name="gateway_link" value="{{$gateway->api_link}}">
                                            <span class="help">{{language_data('Api link execute like')}}: http://example.com?Parameter=Value&Parameter=Value</span>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Status')}}</label>
                                            <select class="selectpicker form-control" name="status">
                                                <option value="Active"  @if($gateway->status=='Active') selected @endif>{{language_data('Active')}}</option>
                                                <option value="Inactive"  @if($gateway->status=='Inactive') selected @endif>{{language_data('Inactive')}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Schedule SMS')}}</label>
                                            <select class="selectpicker form-control" name="schedule">
                                                <option value="Yes"  @if($gateway->schedule=='Yes') selected @endif>{{language_data('Yes')}}</option>
                                                <option value="No"  @if($gateway->schedule=='No') selected @endif>{{language_data('No')}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Two way')}}</label>
                                            <select class="selectpicker form-control" name="two_way">
                                                <option value="Yes"  @if($gateway->two_way=='Yes') selected @endif>{{language_data('Yes')}}</option>
                                                <option value="No"  @if($gateway->two_way=='No') selected @endif>{{language_data('No')}}</option>
                                            </select>
                                        </div>

                                    </div>

                                    <div class="col-lg-8">
                                        <table class="table table-hover table-ultra-responsive" id="gateway_items">
                                            <thead>
                                            <tr>
                                                <th width="20%"></th>
                                                <th width="25%">{{language_data('Parameter')}}</th>
                                                <th width="30%">{{language_data('Value')}}</th>
                                                <th width="25%">{{language_data('Add On URL')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>

                                            <tr class="item-row info">
                                                <td>{{language_data('Username_Key')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" required name="username_param" class="form-control" value="{{$gateway_info->username_param}}"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" required name="username_value" class="form-control"  value="{{$gateway_info->username_value}}"></td>
                                                <td></td>
                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Password')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="password_param" class="form-control"  value="{{$gateway_info->password_param}}"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" name="password_value" class="form-control"  value="{{$gateway_info->password_value}}"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="password_status">
                                                        <option value="no" @if($gateway_info->password_status=='no') selected @endif>{{language_data('Set Blank')}}</option>
                                                        <option value="yes" @if($gateway_info->password_status=='yes') selected @endif>{{language_data('Add on parameter')}}</option>
                                                    </select>
                                                </td>

                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Action')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="action_param" class="form-control"  value="{{$gateway_info->action_param}}"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" name="action_value" class="form-control"  value="{{$gateway_info->action_value}}"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="action_status">
                                                        <option value="no"  @if($gateway_info->action_status=='no') selected @endif>{{language_data('Set Blank')}}</option>
                                                        <option value="yes"  @if($gateway_info->action_status=='yes') selected @endif>{{language_data('Add on parameter')}}</option>
                                                    </select>
                                                </td>

                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Source')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="source_param" class="form-control"  value="{{$gateway_info->source_param}}"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" name="source_value" class="form-control"  value="{{$gateway_info->source_value}}"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="source_status">
                                                        <option value="no"  @if($gateway_info->source_status=='no') selected @endif>{{language_data('Set Blank')}}</option>
                                                        <option value="yes"  @if($gateway_info->source_status=='yes') selected @endif>{{language_data('Add on parameter')}}</option>
                                                    </select>
                                                </td>

                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Destination')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" required name="destination_param" class="form-control"  value="{{$gateway_info->destination_param}}"></td>
                                                <td></td>
                                                <td></td>

                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Message')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" required name="message_param" class="form-control"  value="{{$gateway_info->message_param}}"></td>
                                                <td></td>
                                                <td></td>

                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Unicode')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="unicode_param" class="form-control"  value="{{$gateway_info->unicode_param}}"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" name="unicode_value" class="form-control"  value="{{$gateway_info->unicode_value}}"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="unicode_status">
                                                        <option value="no"  @if($gateway_info->unicode_status=='no') selected @endif>{{language_data('Set Blank')}}</option>
                                                        <option value="yes"  @if($gateway_info->unicode_status=='yes') selected @endif>{{language_data('Add on parameter')}}</option>
                                                    </select>
                                                </td>

                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Type_Route')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="route_param" class="form-control"  value="{{$gateway_info->route_param}}"></td>
                                                <td data-label="{{language_data('Value')}}"><input route="text" autocomplete="off" name="route_value" class="form-control"  value="{{$gateway_info->route_value}}"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="route_status">
                                                        <option value="no"  @if($gateway_info->route_status=='no') selected @endif>{{language_data('Set Blank')}}</option>
                                                        <option value="yes"  @if($gateway_info->route_status=='yes') selected @endif>{{language_data('Add on parameter')}}</option>
                                                    </select>
                                                </td>

                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Language')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="language_param" class="form-control"  value="{{$gateway_info->language_param}}"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" name="language_value" class="form-control"  value="{{$gateway_info->language_value}}"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="language_status">
                                                        <option value="no"  @if($gateway_info->language_status=='no') selected @endif>{{language_data('Set Blank')}}</option>
                                                        <option value="yes"  @if($gateway_info->language_status=='yes') selected @endif>{{language_data('Add on parameter')}}</option>
                                                    </select>
                                                </td>

                                            <tr class="item-row info">
                                                <td>{{language_data('Custom Value 1')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="custom_one_param" class="form-control"  value="{{$gateway_info->custom_one_param}}"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" name="custom_one_value" class="form-control"  value="{{$gateway_info->custom_one_value}}"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="custom_one_status">
                                                        <option value="no"  @if($gateway_info->custom_one_status=='no') selected @endif>{{language_data('Set Blank')}}</option>
                                                        <option value="yes"  @if($gateway_info->custom_one_status=='yes') selected @endif>{{language_data('Add on parameter')}}</option>
                                                    </select>
                                                </td>

                                            <tr class="item-row info">
                                                <td>{{language_data('Custom Value 2')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="custom_two_param" class="form-control"  value="{{$gateway_info->custom_two_param}}"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" name="custom_two_value" class="form-control"  value="{{$gateway_info->custom_two_value}}"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="custom_two_status">
                                                        <option value="no"  @if($gateway_info->custom_two_status=='no') selected @endif>{{language_data('Set Blank')}}</option>
                                                        <option value="yes"  @if($gateway_info->custom_two_status=='yes') selected @endif>{{language_data('Add on parameter')}}</option>
                                                    </select>
                                                </td>

                                            <tr class="item-row info">
                                                <td>{{language_data('Custom Value 3')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="custom_three_param" class="form-control"  value="{{$gateway_info->custom_three_param}}"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" name="custom_three_value" class="form-control"  value="{{$gateway_info->custom_three_value}}"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="custom_three_status">
                                                        <option value="no"  @if($gateway_info->custom_three_status=='no') selected @endif>{{language_data('Set Blank')}}</option>
                                                        <option value="yes"  @if($gateway_info->custom_three_status=='yes') selected @endif>{{language_data('Add on parameter')}}</option>
                                                    </select>
                                                </td>

                                            </tr>


                                            </tbody>
                                        </table>

                                        <div class="text-right">
                                            <input type="hidden" value="{{$gateway->id}}" name="cmd">
                                            <button class="btn btn-success" type="submit"><i class="fa fa-save"></i> {{language_data('Save')}}</button>
                                        </div>

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

@endsection

{{--External Script Section--}}
@section('script')
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
@endsection