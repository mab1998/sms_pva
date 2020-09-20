@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/bootstrap3-wysihtml5-bower/bootstrap3-wysihtml5.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Send')}} {{language_data('Email')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">

            @include('notification.notify')
            <div class="row">

                <div class="col-lg-7">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Send')}} {{language_data('Email')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form method="POST" action="{{ url('clients/post-send-bulk-email') }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">


                                <div class="form-group">
                                    <label for="subject">{{language_data('Subject')}}</label>
                                    <input type="text" class="form-control" id="subject" name="subject">
                                </div>

                                <div class="form-group">
                                    <label for="message">{{language_data('Message')}}</label>
                                    <textarea class="textarea-wysihtml5 form-control" name="message"></textarea>
                                </div>


                                <button type="submit" name="add" class="btn btn-success"><i class="fa fa-send"></i> {{language_data('Send')}}</button>
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
    {!! Html::script("assets/libs/wysihtml5x/wysihtml5x-toolbar.min.js")!!}
    {!! Html::script("assets/libs/bootstrap3-wysihtml5-bower/bootstrap3-wysihtml5.min.js")!!}
@endsection
