@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Manage Plan Feature')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Manage Plan Feature')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('sms/post-manage-plan-feature')}}">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label>{{language_data('Feature Name')}}</label>
                                    <input type="text" class="form-control" required name="feature_name" value="{{$plan_feature->feature_name}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Feature Value')}}</label>
                                    <input type="text" class="form-control" required name="feature_value" value="{{$plan_feature->feature_value}}">
                                </div>


                                <div class="form-group">
                                    <label>{{language_data('Show In Client')}}</label>
                                    <select class="selectpicker form-control" name="show_in_client">
                                        <option value="Active" @if($plan_feature->status=='Active') selected @endif>{{language_data('Yes')}}</option>
                                        <option value="Inactive"  @if($plan_feature->status=='Inactive') selected @endif>{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <input type="hidden" value="{{$plan_feature->id}}" name="cmd">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('Update Feature')}}</button>
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