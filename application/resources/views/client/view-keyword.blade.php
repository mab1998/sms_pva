@extends('client')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Manage Keyword',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Manage Keyword',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('user/keywords/post-manage-keyword')}}" enctype="multipart/form-data">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label>{{language_data('Title',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" readonly value="{{$keyword->title}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Keyword Name',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" readonly value="{{$keyword->keyword_name}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Reply Text For Recipient',Auth::guard('client')->user()->lan_id)}}</label>
                                    <textarea class="form-control" rows="5" name="reply_text" >{{$keyword->reply_text}}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Reply Voice For Recipient',Auth::guard('client')->user()->lan_id)}}</label>
                                    <textarea class="form-control" rows="5" name="reply_voice" >{{$keyword->reply_voice}}</textarea>
                                </div>

                                @if($keyword->reply_mms)
                                <div class="form-group">
                                    <label>{{language_data('MMS File',Auth::guard('client')->user()->lan_id)}}</label>
                                    <p><a href="{{$keyword->reply_mms}}" target="_blank">{{$keyword->reply_mms}}</a> </p>
                                </div>
                                @endif

                                <div class="form-group">
                                    <label>{{language_data('Reply MMS For Recipient',Auth::guard('client')->user()->lan_id)}}</label>
                                    <div class="form-group input-group input-group-file">
                                        <span class="input-group-btn">
                                            <span class="btn btn-primary btn-file">
                                                {{language_data('Browse',Auth::guard('client')->user()->lan_id)}} <input type="file" class="form-control" name="reply_mms" accept="image/*">
                                            </span>
                                        </span>
                                        <input type="text" class="form-control" readonly="">
                                    </div>
                                </div>

                                <input type="hidden" value="{{$keyword->id}}" name="keyword_id">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('Update',Auth::guard('client')->user()->lan_id)}} </button>
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
