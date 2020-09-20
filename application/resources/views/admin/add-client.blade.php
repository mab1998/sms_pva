@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Add New Client')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Add New Client')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('clients/post-new-client')}}" enctype="multipart/form-data">
                                {{ csrf_field() }}


                                <div class="form-group">
                                    <label>{{language_data('First Name')}}</label>
                                    <input type="text" class="form-control" required name="first_name" value="{{old('first_name')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Last Name')}}</label>
                                    <input type="text" class="form-control" name="last_name" value="{{old('last_name')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Company')}}</label>
                                    <input type="text" class="form-control" name="company" value="{{old('company')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Website')}}</label>
                                    <input type="url" class="form-control" name="website" value="{{old('website')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Email')}}</label>
                                    <input type="email" class="form-control" name="email" value="{{old('email')}}" required>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('User name')}}</label>
                                    <input type="text" class="form-control" required name="user_name" value="{{old('user_name')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Password')}}</label>
                                    <input type="password" class="form-control" required name="password">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Confirm Password')}}</label>
                                    <input type="password" class="form-control" required name="cpassword">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Phone')}}</label>
                                    <input type="text" class="form-control" required name="phone" value="{{old('phone')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Address')}}</label>
                                    <input type="text" class="form-control" name="address" value="{{old('address')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('More Address')}}</label>
                                    <input type="text" class="form-control" name="more_address" value="{{old('more_address')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('State')}}</label>
                                    <input type="text" class="form-control" name="state" value="{{old('state')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('City')}}</label>
                                    <input type="text" class="form-control" name="city" value="{{old('city')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Postcode')}}</label>
                                    <input type="text" class="form-control" name="postcode" value="{{old('postcode')}}">
                                </div>

                                <div class="form-group">
                                    <label for="Country">{{language_data('Country')}}</label>
                                    <select name="country" class="form-control selectpicker" data-live-search="true">
                                        {!!countries(app_config('Country'))!!}
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Reseller Panel')}}</label>
                                    <select class="selectpicker form-control" name="reseller_panel">
                                        <option value="Yes">{{language_data('Yes')}}</option>
                                        <option value="No">{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Api Access')}}</label>
                                    <select class="selectpicker form-control" name="api_access">
                                        <option value="Yes">{{language_data('Yes')}}</option>
                                        <option value="No">{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Client Group')}}</label>
                                    <select class="selectpicker form-control" name="client_group"  data-live-search="true">
                                        <option value="0">{{language_data('None')}}</option>
                                        @foreach($clientGroups as $cg)
                                            <option value="{{$cg->id}}">{{$cg->group_name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('SMS Gateway')}}</label>
                                    <select class="selectpicker form-control" name="sms_gateway[]"  data-live-search="true" multiple>
                                        @foreach($sms_gateways as $sg)
                                            <option value="{{$sg->id}}">{{$sg->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('SMS Limit')}}</label>
                                    <input type="text" class="form-control" name="sms_limit" required value="{{old('sms_limit')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Avatar')}}</label>
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
                                        <label>{{language_data('Notify Client with email')}}</label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> {{language_data('Add')}} </button>
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