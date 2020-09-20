@extends('vendor.installer.layouts.master')

@section('title', trans('installer_messages.environment.title'))
@section('style')
    <link href="{{ asset('installer/froiden-helper/helper.css') }}" rel="stylesheet"/>
    <link href="{{ asset('installer/froiden-helper/select2.css') }}" rel="stylesheet"/>
    <style>
        .form-control{
            height: 14px;
            width: 100%;
        }
        select{
            width: auto !important;
        }
        .has-error{
            color: red;
        }
        .has-error input{
            color: black;
            border:1px solid red;
        }
    </style>
@endsection
@section('container')
    <form method="post" action="{{ route('LaravelInstaller::environmentSave') }}" id="env-form">

        <div class="form-group">
            <label class="col-sm-2 control-label">Application URL</label>
            <div class="col-sm-10">
                <input type="text" name="app_url" class="form-control" value="{{url('/')}}">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">Database Hostname</label>
            <div class="col-sm-10">
                <input type="text" name="hostname" class="form-control" >
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">Database Username</label>
            <div class="col-sm-10">
                <input type="text" name="username" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label  class="col-sm-2 control-label">Database Password</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" name="password">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Database Name</label>
            <div class="col-sm-10">
                <input type="text" name="database" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">Timezone</label>
            <div class="col-sm-10">
                <select name="timezone" class="form-control selectpicker">
                    @foreach (timezoneList() as $value => $label)
                        <option value="{{$value}}" @if($value == 'Asia/Dhaka') selected @endif>{{$label}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <br>


        <div class="modal-footer">
            <div class="buttons">
                <button class="button" onclick="checkEnv();return false">
                    {{ trans('installer_messages.next') }}
                </button>
            </div>
        </div>
    </form>
    <script>
        function checkEnv() {
            $.easyAjax({
                url: "{!! route('LaravelInstaller::environmentSave') !!}",
                type: "GET",
                data: $("#env-form").serialize(),
                container: "#env-form",
                messagePosition: "inline"
            });
        }
    </script>
@stop
@section('scripts')
    <script src="{{ asset('installer/js/jQuery-2.2.0.min.js') }}"></script>
    <script src="{{ asset('installer/froiden-helper/helper.js')}}"></script>
    <script src="{{ asset('installer/froiden-helper/select2.js')}}"></script>
    <script>

        $('.selectpicker').select2();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
@endsection