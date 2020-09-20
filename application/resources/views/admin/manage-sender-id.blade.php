@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Manage')}} {{language_data('Sender ID')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Manage')}} {{language_data('Sender ID')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('sms/post-update-sender-id')}}">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label>{{language_data('Sender ID')}}</label>
                                    <input type="text" class="form-control" required name="sender_id" value="{{$senderId->sender_id}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Client')}}</label>
                                    <select class="form-control selectpicker" multiple data-live-search="true" name="client_id[]">
                                        <option value="0" @if($selected_all==true) selected @endif>{{language_data('All')}}</option>
                                        @foreach($clients as $e)
                                            <option value="{{$e->id}}" @if(in_array_r($e->id,$sender_id_clients)) selected @endif>{{$e->fname}} {{$e->lname}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Status')}}</label>
                                    <select class="selectpicker form-control" name="status">
                                        <option value="block" @if($senderId->status=='block') selected @endif>{{language_data('Block')}}</option>
                                        <option value="unblock"  @if($senderId->status=='unblock') selected @endif>{{language_data('Unblock')}}</option>
                                    </select>
                                </div>

                                <input value="{{$senderId->id}}" name="cmd" type="hidden">
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