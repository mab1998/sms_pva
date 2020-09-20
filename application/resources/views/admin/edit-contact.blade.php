@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Edit Contact')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Edit Contact')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form role="form" action="{{url('sms/update-single-contact')}}" method="post">

                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label>{{language_data('Phone Number')}}</label>
                                    <span class="help">({{language_data('Remain country code at the beginning of the number')}})</span>
                                    <input type="text" class="form-control" required name="number" value="{{$cl->phone_number}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('First Name')}}</label>
                                    <input type="text" class="form-control" name="first_name" value="{{$cl->first_name}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Last Name')}}</label>
                                    <input type="text" class="form-control" name="last_name" value="{{$cl->last_name}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Email')}}</label>
                                    <input type="email" class="form-control" name="email"  value="{{$cl->email_address}}">
                                </div>


                                <div class="form-group">
                                    <label>{{language_data('Company')}}</label>
                                    <input type="text" class="form-control" name="company"  value="{{$cl->company}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('User name')}}</label>
                                    <input type="text" class="form-control" name="username"  value="{{$cl->user_name}}">
                                </div>

                                <input type="hidden" name="cmd" value="{{$cl->id}}">
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
@endsection