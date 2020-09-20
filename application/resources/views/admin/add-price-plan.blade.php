@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Add SMS Price Plan')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Add SMS Price Plan')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('sms/post-new-price-plan')}}">
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
                                    <input type="text" class="form-control" required name="plan_name">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Price')}}</label>
                                    <input type="number" class="form-control" name="price" required>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Show in Client')}}</label>
                                    <select class="selectpicker form-control" name="show_in_client">
                                        <option value="Active">{{language_data('Yes')}}</option>
                                        <option value="Inactive">{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Mark Popular')}}</label>
                                    <select class="selectpicker form-control" name="popular">
                                        <option value="Yes">{{language_data('Yes')}}</option>
                                        <option value="No">{{language_data('No')}}</option>
                                    </select>
                                </div>


                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> {{language_data('Add Plan')}}</button>
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