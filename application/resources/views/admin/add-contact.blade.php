@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Add New Contact')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Add New Contact')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('sms/post-new-contact')}}">

                                <div class="form-group">
                                    <label>Country Code</label>
                                    <select class="selectpicker form-control" name="country_code" data-live-search="true">
                                        <option value="0">Exist on phone number</option>
                                        @foreach($country_code as $code)
                                            <option value="{{$code->country_code}}" @if(app_config('Country') == $code->country_name) selected @endif >{{$code->country_name}} ({{$code->country_code}})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Phone Number')}}</label>
                                    <input type="text" class="form-control" required name="number" value="{{old('number')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('First Name')}}</label>
                                    <input type="text" class="form-control" name="first_name" value="{{old('first_name')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Last Name')}}</label>
                                    <input type="text" class="form-control" name="last_name" value="{{old('last_name')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Email')}}</label>
                                    <input type="email" class="form-control" name="email" value="{{old('email')}}">
                                </div>


                                <div class="form-group">
                                    <label>{{language_data('Company')}}</label>
                                    <input type="text" class="form-control" name="company" value="{{old('company')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('User name')}}</label>
                                    <input type="text" class="form-control" name="username" value="{{old('username')}}">
                                </div>



                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="cmd" value="{{$id}}">
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