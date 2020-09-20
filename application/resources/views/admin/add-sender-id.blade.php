@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Add Sender ID')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Add Sender ID')}}</h3>
                        </div>
                        <div class="panel-body">

                            <form method="post" action="{{url('sms/post-new-sender-id')}}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{language_data('Client')}}</label>
                                            <select name="client_id[]" class="selectpicker form-control" multiple data-live-search="true">
                                                <option value="0">{{language_data('All')}}</option>
                                                @foreach($clients as $cl)
                                                    <option value="{{$cl->id}}">{{$cl->fname}} {{$cl->lname}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Status')}}</label>
                                            <select class="selectpicker form-control" name="status">
                                                <option value="block">{{language_data('Block')}}</option>
                                                <option value="unblock">{{language_data('Unblock')}}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-8">
                                        <table class="table table-hover table-ultra-responsive" id="sender_id_items">
                                            <thead>
                                            <tr>
                                                <th width="70%">{{language_data('Sender ID')}}</th>
                                                <th width="30%">{{language_data('Action')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr class="item-row info">
                                                <td data-label="{{language_data('Sender ID')}}"><input type="text" autocomplete="off" required name="sender_id[]" class="form-control sender_id"></td>
                                                <td data-label="{{language_data('Action')}}"><button class="btn btn-success btn-sm item-add"><i class="fa fa-plus"></i> {{language_data('Add More')}}</button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>

                                        <div class="text-right">
                                            <button class="btn btn-success" type="submit"><i class="fa fa-save"></i> {{language_data('Save')}}</button>
                                        </div>

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

{{--External Script Section--}}
@section('script')
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
    {!! Html::script("assets/js/sender-id-management.js")!!}
@endsection