@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Manage SMS Price Plan')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Manage SMS Price Plan')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('sms/post-manage-price-plan')}}">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label>Category</label>
                                    <select class="form-control" required name="category">
                                        <option selected="selected" value="Bulk 1">Bulk 1</option>
                                        <option value="Bulk 2">Bulk 2</option>
                                        <option value="Bulk 3">Bulk 3</option>
                                    </select>
                                    <!-- <input type="text" class="form-control" required name="category"> -->
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Plan Name')}}</label>
                                    <input type="text" class="form-control" required name="plan_name" value="{{$price_plan->plan_name}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Price')}}</label>
                                    <input type="number" class="form-control" name="price" required value="{{$price_plan->price}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Show In Client')}}</label>
                                    <select class="selectpicker form-control" name="show_in_client">
                                        <option value="Active" @if($price_plan->status=='Active') selected @endif>{{language_data('Yes')}}</option>
                                        <option value="Inactive"  @if($price_plan->status=='Inactive') selected @endif>{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Mark Popular')}}</label>
                                    <select class="selectpicker form-control" name="popular">
                                        <option value="Yes"  @if($price_plan->popular=='Yes') selected @endif>{{language_data('Yes')}}</option>
                                        <option value="No"  @if($price_plan->popular=='No') selected @endif>{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <input type="hidden" value="{{$price_plan->id}}" name="cmd">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('Update Plan')}}</button>
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