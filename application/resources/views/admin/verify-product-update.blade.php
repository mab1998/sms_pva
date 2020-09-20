<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{app_config('AppName')}} {{language_data('User Registration')}}</title>
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300,500,700' rel='stylesheet' type='text/css'>
    {!! Html::style("assets/libs/bootstrap/css/bootstrap.min.css") !!}
    {!! Html::style("assets/libs/font-awesome/css/font-awesome.min.css") !!}
    {!! Html::style("assets/css/style.css") !!}
    {!! Html::style("assets/css/responsive.css") !!}
    {!! Html::style("assets/libs/bootstrap-select/css/bootstrap-select.min.css") !!}
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
                        <h3 class="panel-title text-center">Verify Your Product</h3>
                    </div>
                    <div class="panel-body">

                        @include('notification.notify')

                        <form class="" role="form" method="post" action="{{url('update/post-verify-product')}}" enctype="multipart/form-data">

                            <div class="form-group">
                                <label>Purchase Code</label>
                                <input type="text" class="form-control" required name="purchase_code" value="{{app_config('purchase_key')}}">
                            </div>

                            <div class="form-group">
                                <label>Application Url</label>
                                <input type="text" class="form-control" name="app_url" required value="{{url('/')}}">
                            </div>

                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="submit" class="btn btn-primary btn-block btn-lg" value="Verify">
                        </form>
                        <br>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
{!! Html::script("assets/libs/jquery-1.10.2.min.js") !!}
{!! Html::script("assets/libs/jquery.slimscroll.min.js") !!}
{!! Html::script("assets/libs/bootstrap/js/bootstrap.min.js") !!}
{!! Html::script("assets/libs/bootstrap-select/js/bootstrap-select.min.js") !!}
{!! Html::script("assets/js/scripts.js") !!}
</body>
</html>