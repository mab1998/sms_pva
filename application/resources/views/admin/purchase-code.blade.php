@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Purchase Code')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">

            @include('notification.notify')

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                        </div>
                        <div class="panel-body">

                            <h3>Your current license</h3>

                            <hr>
                            <p>Thank you for purchasing Ultimate SMS! Below is your license key, also known as Purchase Code. Your license type is <strong class="text-primary"> {{app_config('license_type')}}</strong></p>
                            <h4>{{app_config('purchase_key')}}</h4>
                            <hr>
                            <h4>License types</h4>
                            <p>When you purchase Ultimate SMS from Envato website, you are actually purchasing a license to use the product.
                                There are 2 types of license that are issued</p>

                            <h4>Regular License</h4>
                            <p>All features are available, for a single end product which end users are NOT charged for</p>

                            <h4>Extended License</h4>
                            <p>All features are available, for a single end product which end users can be charged for (software as a service)</p>


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