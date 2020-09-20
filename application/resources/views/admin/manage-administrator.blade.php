@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Manage Administrator')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Manage Administrator')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('administrators/post-update-admin')}}" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label>{{language_data('First Name')}}</label>
                                    <input type="text" class="form-control" required name="first_name" value="{{$admin->fname}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Last Name')}}</label>
                                    <input type="text" class="form-control" name="last_name" value="{{$admin->lname}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('User Name')}}</label>
                                    <input type="text" class="form-control" required name="username"  value="{{$admin->username}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Email')}}</label>
                                    <input type="email" class="form-control" required name="email"  value="{{$admin->email}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Password')}}</label>
                                    <input type="password" class="form-control"  name="password">
                                    <span class="help">{{language_data('Leave blank if you do not change')}}</span>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Confirm Password')}}</label>
                                    <input type="text" class="form-control"  name="cpassword">
                                    <span class="help">{{language_data('Leave blank if you do not change')}}</span>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Role')}}</label>
                                    <select class="selectpicker form-control" name="role" data-live-search="true">
                                        @foreach($admin_roles as $ar)
                                            <option value="{{$ar->id}}" @if($ar->id == $admin->roleid) selected @endif>{{$ar->role_name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Status')}}</label>
                                    <select class="selectpicker form-control" name="status">
                                        <option value="Active" @if($admin->status=='Active') selected @endif>{{language_data('Active')}}</option>
                                        <option value="Inactive" @if($admin->status=='Inactive') selected @endif>{{language_data('Inactive')}}</option>
                                    </select>
                                </div>


                                <div class="form-group">
                                    <label>{{language_data('Avatar')}}</label>
                                    <div class="form-group input-group input-group-file">
                                        <span class="input-group-btn">
                                            <span class="btn btn-primary btn-file">
                                                {{language_data('Browse')}} <input type="file" class="form-control" name="image" accept="image/*">
                                            </span>
                                        </span>
                                        <input type="text" class="form-control" readonly="">
                                    </div>
                                </div>

                                <input type="hidden" name="cmd" value="{{$admin->id}}">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('Update')}} </button>
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