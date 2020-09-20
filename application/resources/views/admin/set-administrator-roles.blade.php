@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{$admin_roles->role_name}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">

            @include('notification.notify')
            <div class="row">

                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title"> {{language_data('Set Rules')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" action="{{url('administrators/update-admin-set-roles')}}" method="post">
                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" id="select_all"/>
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Check All')}}</label>
                                    </div>
                                </div>

                                <div class="hr-dotted"></div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,1)) checked @endif name="perms[]" value="1">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Dashboard')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,2)) checked @endif name="perms[]" value="2">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('All Clients')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,3)) checked @endif name="perms[]" value="3">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Add New Client')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,4)) checked @endif name="perms[]" value="4">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Manage')}} {{language_data('Client')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,5)) checked @endif name="perms[]" value="5">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Export and Import Clients')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,6)) checked @endif name="perms[]" value="6">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Client Group')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,7)) checked @endif name="perms[]" value="7">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Edit')}} {{language_data('Client Group')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,8)) checked @endif name="perms[]" value="8">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('All Invoices')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,9)) checked @endif name="perms[]" value="9">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Recurring')}} {{language_data('Invoices')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,10)) checked @endif name="perms[]" value="10">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Manage')}} {{language_data('Invoices')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,11)) checked @endif name="perms[]" value="11">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Add New Invoice')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,12)) checked @endif name="perms[]" value="12">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Send Bulk SMS')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,13)) checked @endif name="perms[]" value="13">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Send SMS From File')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,14)) checked @endif name="perms[]" value="14">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Send')}} {{language_data('Schedule SMS')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,15)) checked @endif name="perms[]" value="15">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Schedule SMS From File')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,16)) checked @endif name="perms[]" value="16">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('SMS History')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,17)) checked @endif name="perms[]" value="17">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('SMS Gateway')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,18)) checked @endif name="perms[]" value="18">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Add SMS Gateway')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,19)) checked @endif name="perms[]" value="19">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Manage')}} {{language_data('SMS Gateway')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,20)) checked @endif name="perms[]" value="20">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('SMS Price Plan')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,21)) checked @endif name="perms[]" value="21">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Add Price Plan')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,22)) checked @endif name="perms[]" value="22">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Coverage')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,23)) checked @endif name="perms[]" value="23">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Sender ID Management')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,24)) checked @endif name="perms[]" value="24">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('SMS Templates')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,25)) checked @endif name="perms[]" value="25">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('SMS API')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,26)) checked @endif name="perms[]" value="26">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Support Tickets')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,27)) checked @endif name="perms[]" value="27">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Create New Ticket')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,28)) checked @endif name="perms[]" value="28">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Manage')}} {{language_data('Support Tickets')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,29)) checked @endif name="perms[]" value="29">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Support Department')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,30)) checked @endif name="perms[]" value="30">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Administrators')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,31)) checked @endif name="perms[]" value="31">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Administrator Roles')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,32)) checked @endif name="perms[]" value="32">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('System Settings')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,33)) checked @endif name="perms[]" value="33">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Localization')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,34)) checked @endif name="perms[]" value="34">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Email Templates')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,35)) checked @endif name="perms[]" value="35">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Language Settings')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,36)) checked @endif name="perms[]" value="36">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Payment Gateways')}}</label>
                                    </div>
                                </div>

                                {{--Verson 1.1--}}
                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,37)) checked @endif name="perms[]" value="37">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Send Quick SMS')}}</label>
                                    </div>
                                </div>


                                {{--Verson 2.3--}}
                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,38)) checked @endif name="perms[]" value="38">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Price Bundles')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,39)) checked @endif name="perms[]" value="39">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Price Bundles')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,40)) checked @endif name="perms[]" value="40">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Import Contacts')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,41)) checked @endif name="perms[]" value="41">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Spam Words')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,42)) checked @endif name="perms[]" value="42">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Blacklist Contacts')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,43)) checked @endif name="perms[]" value="43">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Block Message')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,44)) checked @endif name="perms[]" value="44">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Recurring SMS')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,45)) checked @endif name="perms[]" value="45">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Send')}} {{language_data('Recurring SMS')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" @if(permission($admin_roles->id,46)) checked @endif name="perms[]" value="46">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Send Recurring SMS File')}}</label>
                                    </div>
                                </div>


                                <input type="hidden" value="{{$admin_roles->id}}" name="role_id">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('Update')}} </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

@endsection

{{--External Style Section--}}
@section('script')
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}

    <script>
        $("#select_all").click(function(){
            $('input:checkbox').not(this).prop('checked', this.checked);
        });
    </script>


@endsection
