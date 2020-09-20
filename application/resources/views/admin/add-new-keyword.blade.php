@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Add New Keyword')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Add New Keyword')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('keywords/post-new-keyword')}}" enctype="multipart/form-data">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label> {{language_data('Title')}}</label>
                                    <input type="text" class="form-control" required name="title" value="{{old('title')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Keyword Name')}}</label>
                                    <input type="text" class="form-control" name="keyword_name" required value="{{old('keyword_name')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Reply Text For Recipient')}}</label>
                                    <textarea class="form-control" rows="5" name="reply_text" ></textarea>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Reply Voice For Recipient')}}</label>
                                    <textarea class="form-control" rows="5" name="reply_voice" ></textarea>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Reply MMS For Recipient')}}</label>
                                    <div class="form-group input-group input-group-file">
                                        <span class="input-group-btn">
                                            <span class="btn btn-primary btn-file">
                                                {{language_data('Browse')}} <input type="file" class="form-control" name="reply_mms" accept="image/*">
                                            </span>
                                        </span>
                                        <input type="text" class="form-control" readonly="">
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label>{{language_data('Status')}}</label>
                                    <select class="selectpicker form-control" name="status">
                                        <option value="available">{{language_data('Available')}}</option>
                                        <option value="assigned">{{language_data('Assigned')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Select Client</label>
                                    <select class="selectpicker form-control" name="client"  data-live-search="true">
                                        <option value="0">{{language_data('None')}}</option>
                                        @foreach($clients as $cl)
                                            <option value="{{$cl->id}}">{{$cl->username}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Price')}}</label>
                                    <input type="text" class="form-control" required name="price">
                                </div>


                                <div class="form-group">
                                    <label>{{language_data('Validity')}}</label>
                                    <select class="selectpicker form-control" name="validity">
                                        <option value="0">{{language_data('Unlimited')}}</option>
                                        <option value="month1">{{language_data('Month')}}</option>
                                        <option value="months2">{{language_data('2 Months')}}</option>
                                        <option value="months3">{{language_data('3 Months')}}</option>
                                        <option value="months6">{{language_data('6 Months')}}</option>
                                        <option value="year1">{{language_data('Year')}}</option>
                                        <option value="years2">{{language_data('2 Years')}}</option>
                                        <option value="years3">{{language_data('3 Years')}}</option>
                                    </select>
                                </div>


                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> {{language_data('Add')}} </button>
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
