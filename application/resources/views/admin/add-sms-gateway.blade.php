@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Add SMS Gateway')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Add SMS Gateway')}}</h3>
                        </div>
                        <div class="panel-body">

                            <form role="form" method="post" action="{{url('sms/post-new-sms-gateway')}}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{language_data('Gateway Name')}}</label>
                                            <input type="text" class="form-control" required name="gateway_name">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Gateway API Link')}}</label>
                                            <input type="text" class="form-control" required name="gateway_link">
                                            <span class="help">{{language_data('Api link execute like')}}: http://example.com?Parameter=Value&Parameter=Value</span>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Status')}}</label>
                                            <select class="selectpicker form-control" name="status">
                                                <option value="Active">{{language_data('Active')}}</option>
                                                <option value="Inactive">{{language_data('Inactive')}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Schedule SMS')}}</label>
                                            <select class="selectpicker form-control" name="schedule">
                                                <option value="Yes">{{language_data('Yes')}}</option>
                                                <option value="No">{{language_data('No')}}</option>
                                            </select>
                                        </div>


                                        <div class="form-group">
                                            <label>{{language_data('Two way')}}</label>
                                            <select class="selectpicker form-control" name="two_way">
                                                <option value="Yes">{{language_data('Yes')}}</option>
                                                <option value="No">{{language_data('No')}}</option>
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
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" required name="username_param" class="form-control"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" required name="username_value" class="form-control"></td>
                                                <td></td>
                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Password')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="password_param" class="form-control"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" name="password_value" class="form-control"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="password_status">
                                                        <option value="no">{{language_data('Set Blank')}}</option>
                                                        <option value="yes">{{language_data('Add on parameter')}}</option>
                                                    </select>
                                                </td>

                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Action')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="action_param" class="form-control"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" name="action_value" class="form-control"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="action_status">
                                                        <option value="yes">{{language_data('Add on parameter')}}</option>
                                                        <option value="no">{{language_data('Set Blank')}}</option>
                                                    </select>
                                                </td>

                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Source')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="source_param" class="form-control"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" name="source_value" class="form-control"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="source_status">
                                                        <option value="yes">{{language_data('Add on parameter')}}</option>
                                                        <option value="no">{{language_data('Set Blank')}}</option>
                                                    </select>
                                                </td>

                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Destination')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" required name="destination_param" class="form-control"></td>
                                                <td></td>
                                                <td></td>

                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Message')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" required name="message_param" class="form-control"></td>
                                                <td></td>
                                                <td></td>

                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Unicode')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="unicode_param" class="form-control"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" name="unicode_value" class="form-control"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="unicode_status">
                                                        <option value="no">{{language_data('Set Blank')}}</option>
                                                        <option value="yes">{{language_data('Add on parameter')}}</option>
                                                    </select>
                                                </td>

                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Type_Route')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="route_param" class="form-control"></td>
                                                <td data-label="{{language_data('Value')}}"><input route="text" autocomplete="off" name="route_value" class="form-control"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="route_status">
                                                        <option value="no">{{language_data('Set Blank')}}</option>
                                                        <option value="yes">{{language_data('Add on parameter')}}</option>
                                                    </select>
                                                </td>

                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Language')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="language_param" class="form-control"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" name="language_value" class="form-control"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="language_status">
                                                        <option value="no">{{language_data('Set Blank')}}</option>
                                                        <option value="yes">{{language_data('Add on parameter')}}</option>
                                                    </select>
                                                </td>

                                            <tr class="item-row info">
                                                <td>{{language_data('Custom Value 1')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="custom_one_param" class="form-control"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" name="custom_one_value" class="form-control"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="custom_one_status">
                                                        <option value="no">{{language_data('Set Blank')}}</option>
                                                        <option value="yes">{{language_data('Add on parameter')}}</option>
                                                    </select>
                                                </td>

                                            <tr class="item-row info">
                                                <td>{{language_data('Custom Value 2')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="custom_two_param" class="form-control"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" name="custom_two_value" class="form-control"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="custom_two_status">
                                                        <option value="no">{{language_data('Set Blank')}}</option>
                                                        <option value="yes">{{language_data('Add on parameter')}}</option>
                                                    </select>
                                                </td>

                                            <tr class="item-row info">
                                                <td>{{language_data('Custom Value 3')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="custom_three_param" class="form-control"></td>
                                                <td data-label="{{language_data('Value')}}"><input type="text" autocomplete="off" name="custom_three_value" class="form-control"></td>
                                                <td data-label="{{language_data('Add On URL')}}">
                                                    <select class="selectpicker form-control" name="custom_three_status">
                                                        <option value="no">{{language_data('Set Blank')}}</option>
                                                        <option value="yes">{{language_data('Add on parameter')}}</option>
                                                    </select>
                                                </td>

                                            </tr>


                                            </tbody>
                                        </table>

                                        <div class="text-right">
                                            <button class="btn btn-success" type="submit"><i class="fa fa-plus"></i> {{language_data('Add')}}</button>
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