<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{app_config('AppName')}} - {{language_data('User Registration Verification')}}</title>
    <link href='http://fonts.googleapis.com/css?family=Roboto:400,300,500,700' rel='stylesheet' type='text/css'>
    {!! Html::style("assets/libs/bootstrap/css/bootstrap.min.css") !!}
    {!! Html::style("assets/libs/font-awesome/css/font-awesome.min.css") !!}
    {!! Html::style("assets/css/style.css") !!}
    {!! Html::style("assets/css/responsive.css") !!}

</head>
<body>

<main id="wrapper" class="wrapper">
    <div class="container jumbo-container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="app-logo-inner text-center">
                    <img src="<?php echo asset(app_config('AppLogo')); ?>" alt="logo" class="bar-logo">
                </div>

                <div class="panel panel-30">
                    <div class="panel-heading">
                        <h3 class="panel-title text-center">{{language_data('Verify Your Account')}}</h3>
                    </div>
                    <div class="panel-body">
                        <form class="" role="form" action="{{url('user/post-verification-token')}}" method="post">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" value="{{Auth::guard('client')->user()->id}}" name="cmd">
                            <button type="submit" class="btn btn-primary btn-block btn-lg">{{language_data('Send Verification Email')}}</button>
                        </form>
                        <br>
                        @include('notification.notify')
                    </div>
                </div>
                <div class="panel-other-acction">
                    <div class="text-sm text-center">
                        <a href="{{url('/')}}">{{language_data('Back To Sign in')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

{!! Html::script("assets/libs/jquery-1.10.2.min.js") !!}
{!! Html::script("assets/libs/jquery.slimscroll.min.js") !!}
{!! Html::script("assets/libs/bootstrap/js/bootstrap.min.js") !!}
{!! Html::script("assets/js/scripts.js") !!}

</body>
</html>
