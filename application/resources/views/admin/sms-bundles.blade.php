@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Price Bundles')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">

            @include('notification.notify')


            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-body">
                            <form class="" role="form" action="{{url('sms/post-sms-bundles')}}" method="post">
                                <div class="panel-heading">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('Save')}}</button>
                                </div>

                                <table class="table task-items table-ultra-responsive">

                                    <thead>
                                    <tr>
                                        <th width="20%">{{language_data('Unit From')}}</th>
                                        <th width="20%">{{language_data('Unit To')}}</th>
                                        <th width="20%">{{language_data('Price')}}</th>
                                        <th width="20">{{language_data('Transaction Fee')}} (%)</th>
                                        <th width="20%"></th>
                                    </tr>
                                    </thead>

                                    <tbody>

                                    @if(count($bundles)>0)

                                        @foreach($bundles as $tr)

                                            <tr class="item-row">
                                                <td data-label="{{language_data('Unit From')}}"><input type="text" name="unit_from[]" class="form-control description salary_from" value="{{$tr->unit_from}}"></td>
                                                <td data-label="{{language_data('Unit To')}}"><input type="text" name="unit_to[]" class="form-control description" value="{{$tr->unit_to}}"></td>
                                                <td data-label="{{language_data('Price')}}"><input type="text" name="price[]" class="form-control description" value="{{$tr->price}}"></td>
                                                <td data-label="{{language_data('Transaction Fee')}}"><input type="text" name="trans_fee[]" class="form-control description" value="{{$tr->trans_fee}}"></td>

                                                <td><button class="btn btn-danger btn-sm ExitRemoveITEM" type="button"><i class="fa fa-trash-o"></i> {{language_data('Delete')}}</button></td>
                                            </tr>
                                        @endforeach
                                        <tr class="item-row">

                                            <td data-label="{{language_data('Unit From')}}"><input type="text" name="unit_from[]" class="form-control description"></td>
                                            <td data-label="{{language_data('Unit To')}}"><input type="text" name="unit_to[]" class="form-control description"></td>
                                            <td data-label="{{language_data('Price')}}"><input type="text" name="price[]" class="form-control description"></td>
                                            <td data-label="{{language_data('Transaction Fee')}}"><input type="text" name="trans_fee[]" class="form-control description"></td>
                                            <td><button class="btn btn-success btn-sm item-add"><i class="fa fa-plus"></i> {{language_data('Add More')}}</button></td>
                                        </tr>
                                    @else
                                        <tr class="item-row">
                                            <td data-label="{{language_data('Unit From')}}"><input type="text" name="unit_from[]" class="form-control description"></td>
                                            <td data-label="{{language_data('Unit To')}}"><input type="text" name="unit_to[]" class="form-control description"></td>
                                            <td data-label="{{language_data('Price')}}"><input type="text" name="price[]" class="form-control description"></td>
                                            <td data-label="{{language_data('Transaction Fee')}}"><input type="text" name="trans_fee[]" class="form-control description"></td>
                                            <td><button class="btn btn-success btn-sm item-add"><i class="fa fa-plus"></i> {{language_data('Add More')}}</button></td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>

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
    {!! Html::script("assets/js/sms-bundles.js")!!}

    <script>
        $('.ExitRemoveITEM').on("click", function () {
            $(this).parents(".item-row").remove();
        });
    </script>

@endsection