<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{app_config('AppName')}} {{language_data('Admin')}} {{language_data('Login')}}</title>
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300,500,700' rel='stylesheet' type='text/css'>
    {!! Html::style("assets/libs/bootstrap/css/bootstrap.min.css") !!}
    {!! Html::style("assets/libs/font-awesome/css/font-awesome.min.css") !!}
    {!! Html::style("assets/css/style.css") !!}
    {!! Html::style("assets/css/responsive.css") !!}
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <style>
        .app-logo-inner * {
            max-width: 242px;
            height: auto;
        }
    </style>

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
                        <h3 class="panel-title text-center">{{language_data('Sign to your account')}}</h3>
                    </div>
                    <div class="panel-body">
                        <form class="" role="form" method="post" action="{{url('admin/get-login')}}">
                            <div class="form-group form-group-default required">
                                <label for="user name">{{language_data('User Name')}}</label>
                                <input type="text" class="form-control" required name="username">
                            </div>
                            <div class="form-group form-group-default required">
                                <label for="password">{{language_data('Password')}}</label>
                                <input type="password" class="form-control" required name="password">
                            </div>

                            @if(app_config('captcha_in_admin')=='1')
                            <div id="g-recaptcha" class="g-recaptcha" data-sitekey="{{app_config('captcha_site_key')}}" data-expired-callback="recaptchaCallback"></div>

                            <noscript>
                                <div style="width: 302px; height: 352px;margin-bottom:20px;margin-left:100px;">
                                    <div style="width: 302px; height: 352px; position: relative;">
                                        <div style="width: 302px; height: 352px; position: absolute;">
                                            <!-- change YOUR_SITE_KEY with your google recaptcha key -->
                                            <iframe src="https://www.google.com/recaptcha/api/fallback?k={{app_config('captcha_site_key')}}" style="width: 302px; height:352px; border-style: none;">
                                            </iframe>
                                        </div>
                                        <div style="width: 250px; height: 80px; position: absolute; border-style: none; bottom: 21px; left: 25px; margin: 0px; padding: 0px; right: 25px;">
                                            <textarea id="g-recaptcha-response" name="g-recaptcha-response" class="g-recaptcha-response" style="width: 250px; height: 80px; border: 1px solid #c1c1c1; margin: 0px; padding: 0px; resize: none;"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </noscript>
                            @endif


                            <div class="form-group m-t-20 m-b-20">
                                <div class="coder-checkbox">
                                    <input type="checkbox" checked name="remember">
                                    <span class="co-check-ui"></span>
                                    <label>{{language_data('Remember Me')}}</label>
                                </div>
                            </div>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="submit" class="btn btn-primary btn-block btn-lg" value="{{language_data('Login')}}">
                        </form>
                        <br>
                        @if(app_config('AppStage') == 'Demo')
                            <div class="alert alert-info alert-dismissible  text-uppercase text-center text-danger" role="alert">
                                Demo Resets Every 1 Hour
                            </div>
                        @endif
                        @include('notification.notify')
                    </div>
                </div>
                <div class="panel-other-acction">
                    <div class="text-sm text-center">
                        <a href="{{url('admin/forgot-password')}}">{{language_data('Forget Password')}}?</a>
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
