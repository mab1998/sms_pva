@extends('client')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Add New Client',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Add New Client',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('user/post-new-user')}}" enctype="multipart/form-data">
                                {{ csrf_field() }}



                                <div class="form-group">
                                    <label>{{language_data('First Name',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" required name="first_name" value="{{old('first_name')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Last Name',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" name="last_name" value="{{old('last_name')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Company',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" name="company" value="{{old('company')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Website',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="url" class="form-control" name="website" value="{{old('website')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Email',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="email" class="form-control" name="email" value="{{old('email')}}" required>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('User name',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" required name="user_name" value="{{old('user_name')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Password',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="password" class="form-control" required name="password" value="{{old('password')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Confirm Password',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="password" class="form-control" required name="cpassword" value="{{old('cpassword')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Phone',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" required name="phone" value="{{old('phone')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Address',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" name="address" value="{{old('address')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('More Address',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" name="more_address" value="{{old('more_address')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('State',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" name="state" value="{{old('state')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('City',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" name="city" value="{{old('city')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Postcode',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" name="postcode" value="{{old('postcode')}}">
                                </div>

                                <div class="form-group">
                                    <label for="Country">{{language_data('Country',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select name="country" class="form-control selectpicker" data-live-search="true">
                                        {!!countries(app_config('Country'))!!}
                                    </select>
                                </div>

                                @if(\Auth::guard('client')->user()->reseller=='Yes')

                                    <div class="form-group">
                                        <label>{{language_data('Reseller Panel',Auth::guard('client')->user()->lan_id)}}</label>
                                        <select class="selectpicker form-control" name="reseller_panel">
                                            <option value="Yes">{{language_data('Yes',Auth::guard('client')->user()->lan_id)}}</option>
                                            <option value="No">{{language_data('No',Auth::guard('client')->user()->lan_id)}}</option>
                                        </select>
                                    </div>

                                @endif

                                @if(\Auth::guard('client')->user()->api_access=='Yes')
                                    <div class="form-group">
                                        <label>{{language_data('Api Access',Auth::guard('client')->user()->lan_id)}}</label>
                                        <select class="selectpicker form-control" name="api_access">
                                            <option value="Yes">{{language_data('Yes',Auth::guard('client')->user()->lan_id)}}</option>
                                            <option value="No">{{language_data('No',Auth::guard('client')->user()->lan_id)}}</option>
                                        </select>
                                    </div>
                                @endif

                                <div class="form-group">
                                    <label>{{language_data('Client Group',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="selectpicker form-control" name="client_group"  data-live-search="true">
                                        <option value="0">{{language_data('None',Auth::guard('client')->user()->lan_id)}}</option>
                                        @foreach($clientGroups as $cg)
                                            <option value="{{$cg->id}}">{{$cg->group_name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('SMS Gateway',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="selectpicker form-control" name="sms_gateway[]"  data-live-search="true" multiple>
                                        @foreach($sms_gateways as $sg)
                                            <option value="{{$sg->id}}">{{$sg->name}}</option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="form-group">
                                    <label>{{language_data('SMS Limit',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" name="sms_limit" required value="{{old('sms_limit')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Avatar',Auth::guard('client')->user()->lan_id)}}</label>
                                    <div class="form-group input-group input-group-file">
                                        <span class="input-group-btn">
                                            <span class="btn btn-primary btn-file">
                                                {{language_data('Browse')}} <input type="file" class="form-control" name="image" accept="image/*">
                                            </span>
                                        </span>
                                        <input type="text" class="form-control" readonly="">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" checked="" value="yes" name="email_notify">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Notify Client with email',Auth::guard('client')->user()->lan_id)}}</label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> {{language_data('Add',Auth::guard('client')->user()->lan_id)}} </button>
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
@endsection
