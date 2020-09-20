@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Add Plan Feature')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Add Plan Feature')}}</h3>
                        </div>
                        <div class="panel-body">

                            <form method="post" action="{{url('sms/post-new-plan-feature')}}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{language_data('Client')}}</label>
                                            <input type="text" disabled class="form-control" value="{{$price_plan->plan_name}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Show In Client')}}</label>
                                            <select class="selectpicker form-control" name="show_in_client">
                                                <option value="Active">{{language_data('Yes')}}</option>
                                                <option value="Inactive">{{language_data('No')}}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-8">
                                        <table class="table table-hover table-ultra-responsive" id="plan-feature-items">
                                            <thead>
                                            <tr>
                                                <th width="40%">{{language_data('Feature Name')}}</th>
                                                <th width="30%">{{language_data('Feature Value')}}</th>
                                                <th width="30%">{{language_data('Action')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr class="item-row info">
                                                <td data-label="Feature Name"><input type="text" autocomplete="off" required name="feature_name[]" class="form-control feature_name"></td>
                                                <td data-label="Feature Value"><input type="text" autocomplete="off" required name="feature_value[]" class="form-control feature_value"></td>
                                                <td data-label="Action"><button class="btn btn-success btn-sm item-add"><i class="fa fa-plus"></i>{{language_data('Add More')}}</button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>

                                        <div class="text-right">
                                            <input type="hidden" value="{{$price_plan->id}}" name="cmd">
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
    {!! Html::script("assets/js/plan-feature.js")!!}
@endsection