@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Administrators')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-4">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Add New Administrator')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('administrators/add-new')}}" enctype="multipart/form-data">

                                <div class="form-group">
                                    <label>{{language_data('First Name')}}</label>
                                    <input type="text" class="form-control" required name="first_name">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Last Name')}}</label>
                                    <input type="text" class="form-control" name="last_name">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('User Name')}}</label>
                                    <input type="text" class="form-control" required name="username">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Email')}}</label>
                                    <input type="email" class="form-control" required name="email">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Password')}}</label>
                                    <input type="password" class="form-control" required name="password">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Confirm Password')}}</label>
                                    <input type="password" class="form-control" required name="cpassword">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Role')}}</label>
                                    <select class="selectpicker form-control" name="role" data-live-search="true">
                                        @foreach($admin_roles as $ar)
                                            <option value="{{$ar->id}}">{{$ar->role_name}}</option>
                                        @endforeach
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


                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" checked="" value="yes" name="email_notify">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Notify Administrator with email')}}</label>
                                    </div>
                                </div>


                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> {{language_data('Add')}} </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Administrators')}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 5%;">{{language_data('SL')}}#</th>
                                    <th style="width: 18%;">{{language_data('Name')}}</th>
                                    <th style="width: 20%;">{{language_data('User Name')}}</th>
                                    <th style="width: 15%;">{{language_data('Role')}}</th>
                                    <th style="width: 5%;">{{language_data('Status')}}</th>
                                    <th style="width: 37%;">{{language_data('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($admin as $a)
                                    <tr>
                                        <td data-label="{{language_data('SL')}}">{{ $loop->iteration }}</td>
                                        <td data-label="{{language_data('Name')}}"><p>{{$a->fname}} {{$a->lname}}</p></td>
                                        <td data-label="{{language_data('User Name')}}"><p>{{$a->username}}</p></td>
                                        <td data-label="{{language_data('Role')}}"><p>{{$a->get_admin_role->role_name}}</p></td>
                                        @if($a->status=='Active')
                                            <td data-label="{{language_data('Status')}}"><p class="btn btn-success btn-xs">{{language_data('Active')}}</p></td>
                                        @else
                                            <td data-label="{{language_data('Status')}}"><p class="btn btn-warning btn-xs">{{language_data('Inactive')}}</p></td>
                                        @endif
                                        <td data-label="{{language_data('Action')}}">
                                            <a class="btn btn-success btn-xs" href="{{url('administrators/manage/'.$a->id)}}" ><i class="fa fa-edit"></i> {{language_data('Edit')}}</a>
                                            <a href="#" class="btn btn-danger btn-xs cdelete" id="{{$a->id}}"><i class="fa fa-trash"></i> {{language_data('Delete')}}</a>
                                        </td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
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
    {!! Html::script("assets/libs/data-table/datatables.min.js")!!}
    {!! Html::script("assets/js/bootbox.min.js")!!}

    <script>
        $(document).ready(function(){
          $('.data-table').DataTable({
            language: {
              url: '{!! url("assets/libs/data-table/i18n/".get_language_code()->language.".lang") !!}'
            },
            responsive: true
          });

            /*For Delete admin*/
            $( "body" ).delegate( ".cdelete", "click",function (e) {
                e.preventDefault();
                var id = this.id;
              bootbox.confirm("{!! language_data('Are you sure') !!}?", function (result) {
                    if (result) {
                        var _url = $("#_url").val();
                        window.location.href = _url + "/administrators/delete-admin/" + id;
                    }
                });
            });

        });
    </script>
@endsection