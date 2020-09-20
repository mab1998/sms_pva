@extends('admin')


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('View Profile')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')

            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-body p-t-20">
                            <div class="clearfix">
                                <div class="pull-left m-r-30">
                                    <div class="thumbnail m-b-none">

                                        @if($admin->image!='')
                                            <img src="<?php echo asset('assets/admin_pic/'.$admin->image); ?>" alt="Profile Page" width="200px" height="200px">
                                        @else
                                            <img src="<?php echo asset('assets/admin_pic/user.png');?>" alt="Profile Page" width="200px" height="200px">
                                        @endif
                                    </div>
                                </div>
                                <div class="pull-left">
                                    <h3 class="bold font-color-1">{{$admin->fname}} {{$admin->lname}}</h3>
                                    <ul class="info-list">
                                        @if($admin->email!='')
                                            <li><span class="info-list-title">{{language_data('Email')}}</span><span class="info-list-des">{{$admin->email}}</span></li>
                                        @endif

                                        @if($admin->username!='')
                                                <li><span class="info-list-title">{{language_data('User Name')}}</span><span class="info-list-des">{{$admin->username}}</span></li>
                                        @endif

                                         <li>
                                             <span class="info-list-title">{{language_data('Access Role')}}</span><span class="info-list-des">
                                                 @if($admin->roleid==0) {{language_data('Super Admin')}} @else {{$admin->get_admin_role->roleid}} @endif
                                             </span>
                                         </li>

                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <div class="p-30 p-t-none p-b-none">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#personal_details" aria-controls="home" role="tab" data-toggle="tab">{{language_data('Personal Details')}}</a></li>

                        <li role="presentation"><a href="#change-picture" aria-controls="settings" role="tab" data-toggle="tab">{{language_data('Change Image')}}</a></li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content panel p-20">


                        {{--Personal Details--}}

                        <div role="tabpanel" class="tab-pane active" id="personal_details">
                            <form role="form" method="post" action="{{url('admin/post-personal-info')}}">

                                <div class="row">
                                    <div class="col-md-6">

                                        <div class="form-group">
                                            <label>{{language_data('First Name')}}</label>
                                            <input type="text" class="form-control" required="" value="{{$admin->fname}}" name="fname">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Last Name')}}</label>
                                            <input type="text" class="form-control" value="{{$admin->lname}}" name="lname">
                                        </div>


                                        <div class="form-group">
                                            <label>{{language_data('Email')}}</label>
                                            <span class="help">e.g. "coderpixel@gmail.com" ({{language_data('Unique For every User')}})</span>
                                            <input type="email" class="form-control" required name="email" value="{{$admin->email}}">
                                        </div>


                                    </div>

                                    <div class="col-md-12">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" value="{{language_data('Update')}}" class="btn btn-success">

                                    </div>
                                </div>


                            </form>

                        </div>


                        <div role="tabpanel" class="tab-pane" id="change-picture">
                            <form role="form" action="{{url('admin/update-avatar')}}" method="post" enctype="multipart/form-data">

                                <div class="row">
                                    <div class="col-md-4">

                                        <div class="form-group input-group input-group-file">
                                                <span class="input-group-btn">
                                                    <span class="btn btn-primary btn-file">
                                                        {{language_data('Browse')}} <input type="file" class="form-control" name="image" accept="image/*">
                                                    </span>
                                                </span>
                                            <input type="text" class="form-control" readonly="" >
                                        </div>

                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" value="{{language_data('Update')}}" class="btn btn-primary">

                                    </div>

                                </div>

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
@endsection
