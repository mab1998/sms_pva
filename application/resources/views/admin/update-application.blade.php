@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Update Application')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Update Application')}}</h3>
                        </div>
                        <div class="panel-body">

                            <h3>Your application version is: <span
                                        class="text-uppercase text-complete"> V {{app_config('SoftwareVersion')}} </span>
                            </h3>

                            @if(app_config('SoftwareVersion') == '2.8')
                                <h4><span class="text-uppercase text-complete"> Congratulation!!! </span> You are using
                                    latest version</h4>
                                <br>
                                <a href="{{url('admin/check-available-update')}}" class="btn btn-success">Check for
                                    Updates</a>
                            @else

                                <p class="text-complete">Please update your version with new one. Ultimate SMS Version
                                    2.8 already released</p>

                                <p>To update your application please visit this url: <a href="#">Update Version To
                                        v2.8</a></p>

                                <hr>

                                <form class="" role="form" method="post"
                                      action="{{url('admin/post-update-application')}}" enctype="multipart/form-data">
                                    {{ csrf_field() }}

                                    <div class="form-group">
                                        <label>{{language_data('Select File')}}</label>
                                        <div class="form-group input-group input-group-file">
                                        <span class="input-group-btn">
                                            <span class="btn btn-primary btn-file">
                                                {{language_data('Browse')}} <input type="file" class="form-control"
                                                                                   name="update_file"
                                                                                   accept="application/zip">
                                            </span>
                                        </span>
                                            <input type="text" class="form-control" readonly="">
                                        </div>
                                    </div>


                                    <button type="submit" class="btn btn-success btn-sm pull-right"><i
                                                class="fa fa-upload"></i> {{language_data('Update')}} </button>
                                </form>
                            @endif

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
