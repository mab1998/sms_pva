@extends('client')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">SMS API SDK</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-sm-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">SMS API SDK</h3>
                        </div>
                        <div class="panel-body">

                                    The Ulitmate SMS PHP SDK makes it easy to interact with the Ultimate SMS API from
                                    your PHP application. The most recent version of the Ultimate SMS PHP SDK can be
                                    found on <a href="https://github.com/akasham67/ultimate-sms-api" target="_blank">Github</a>
                                    The Ultimate SMS PHP SDK requires PHP version 5.6 or higher. If you are interested
                                    in migrating to your php application, check out this <a
                                            href="https://ultimatesms.coderpixel.com/ultimate-sms-api-documentation/"
                                            target="_blank">Guide</a> .

                                    <br>
                                    <br>
                                    <a href="https://github.com/akasham67/ultimate-sms-api/archive/master.zip"
                                       class="btn btn-success btn-lg"><i class="fa fa-download"></i> Download SDK</a>

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