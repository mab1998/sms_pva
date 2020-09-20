@extends('admin')

@section('style')
<style>
    .radio_label{
        text-transform: lowercase !important;
    }
</style>
@endsection

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Background Jobs')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">

            @if(!exec_enabled())
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    {{$get_message}}
                </div>
            @endif

            @include('notification.notify')
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Please specify the PHP executable path on your system')}}</h3>
                        </div>
                        <div class="panel-body">


                            <form class="" role="form" >

                                @foreach($paths as $p)
                                    <div class="form-group">
                                        <div class="coder-radiobox">
                                            <input type="radio" name="php_bin_path" value="{{$p}}" @if($p == $server_php_path) checked @endif>
                                            <span class="co-radio-ui"></span>
                                            <label class="radio_label">{{$p}}</label>
                                        </div>
                                    </div>
                                @endforeach

                            </form>

                            <hr>
                            <label class="text-bold">Insert the following line to your system's contab.
                                Please note, below timings for running the cron jobs are the recommended, you can change it if you want. </label>

                                <pre style="font-size: 16px;background:#f5f5f5">* * * * * <span class="current_path_value">{!! $server_php_path !!}</span> -d register_argc_argv=On {{ base_path() }}/artisan schedule:run >> /dev/null 2>&1    </pre>

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

    <script>
        $(document).ready(function() {

            $('input[name="php_bin_path"]:checked').trigger('change');

            // pickadate mask
            $(document).on('keyup change', 'input[name="php_bin_path"]', function() {
                var value = $(this).val();

                if(value !== '') {
                    $('.current_path_value').html(value);
                } else {
                    $('.current_path_value').html('{PHP_BIN_PATH}');
                }
            });
            $('input[name="php_bin_path_value"]').trigger('change');

        });
    </script>
@endsection