@extends('admin')


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('English To')}} {{$lan_name}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">

            @if($notify)
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                   {{$notify}}
                </div>
            @endif

            @include('notification.notify')
            <div class="row">
                <form method="post" action="{{url('settings/language-settings-translate-post')}}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="lan_id" value="{{$lid}}">

                    <div class="col-lg-12">
                        <div class="panel">
                            <div class="panel-heading">
                                <h3 class="panel-title">{{language_data('Translate')}} {{language_data('English To')}} {{$lan_name}}</h3>
                                <button class="btn btn-success btn-sm pull-right" type="submit"><i class="fa fa-save"></i> {{language_data('Save')}}</button>
                                <br>
                            </div>
                            <div class="panel-body p-none">
                                <table class="table table-hover table-ultra-responsive">
                                    <thead>
                                    <tr>
                                        <th style="width: 50%;" class="text-center">{{language_data('English')}}</th>
                                        <th style="width: 50%;" class="text-center">{{$lan_name}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($lan_data as $ld)
                                        <tr>
                                            <td data-label="{{language_data('English')}}"><p><input  type="hidden" name="english_data[]" value="{{$ld->lan_data}}"> {{$ld->lan_data}}</p></td>
                                            <td data-label="{{$lan_name}}"><p><input type="text" class="form-control" required="" name="translate_data[]" value="{{$ld->lan_value}}"></p></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </section>

@endsection

{{--External Style Section--}}
@section('script')
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
@endsection
