@extends('vendor.installer.layouts.master')

@section('title', 'Setup Application')
@section('style')
    <link href="{{ asset('installer/froiden-helper/helper.css') }}" rel="stylesheet"/>
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
    <form method="post" action="{{ route('LaravelInstaller::setupSave') }}" id="env-form">

        <div class="form-group">
            <label class="col-sm-2 control-label">System Email</label>
            <div class="col-sm-10">
                <input type="email" required name="system_email" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">Admin Password</label>
            <div class="col-sm-10">
                <input type="text" name="password" class="form-control"  required>
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
                url: "{!! route('LaravelInstaller::setupSave') !!}",
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
    <script>

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
@endsection