@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">Register WhatsApp Number</h2>
        </div>


        <div class="p-30 p-t-none p-b-none">

            <div class="row" id="registerSection">

                <div class="text-center" v-if="isRedirecting">

                    <div :class="[alertType, 'alert alert-dismissible']" role="alert">
                        @{{redirectMessage}}
                    </div>

                </div>

                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Register WhatsApp Number</h3>
                        </div>
                        <div class="panel-body" v-if="!isRedirecting">

                            <form class="" role="form" method="post" action="{{url('whatsapp/post-phone-register')}}" @submit.prevent="onSubmit($event)" @keyup="onKeyup($event)">

                                <input type="hidden" value="{{csrf_token()}}" name="_token">

                                <div class="form-group">
                                    <label for="el1">{{language_data('Phone Number')}}</label>
                                    <input type="text" class="form-control" name="phone_number" required v-model="phone_number">
                                    <span class="help text-danger" v-if="getError('phone_number')" v-text="getErrorMessage('phone_number')"></span>
                                </div>

                                <div class="form-group" v-if="isTokendField">
                                    <label for="el1">Token</label>
                                    <input id="sdfsdf" type="password" class="form-control" name="token" autofocus required v-model="token">
                                </div>


                                <input type="submit" class="btn btn-primary pull-right btn-sm" value="Verify" v-if="!isTokendField" :disabled="isDisable">
                                <input type="submit" class="btn btn-primary pull-right btn-sm" value="Register" v-if="isTokendField">

                                <br>

                                <div v-if="loading">Loading...</div>

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
    {!! Html::script("assets/libs/alertify/js/alertify.js") !!}
    {!! Html::script("assets/js/vue.js") !!}
    {!! Html::script("assets/js/register-number.js") !!}

@endsection