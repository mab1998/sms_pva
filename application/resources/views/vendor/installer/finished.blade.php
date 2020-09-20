@extends('vendor.installer.layouts.master')

@section('title', trans('installer_messages.final.title'))
@section('container')
    <p class="paragraph" style="text-align: center;">{{ session('message')['message'] }}</p>

    <div class="bg-primary">
        To send bulk sms you must have to run cron job on your system. <br><br>
        To view Cron job details, login with your admin portal using url <br> {{url('/admin')}} <br>
        Then Go <span class="success"> Settings -> Background Jobs </span> Menu.
    </div>
    <br>

    <div class="bg-primary">
        <p>User Name: admin</p>
        <p>Password:</p>
    </div>
    <div class="buttons">
        <a href="{{ url('/admin') }}" class="button">{{ trans('installer_messages.final.exit') }}</a>
    </div>
@stop
