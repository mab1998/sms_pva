@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('SMS Price Plan',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('SMS Price Plan',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 10%;">{{language_data('SL',Auth::guard('client')->user()->lan_id)}}#</th>
                                    <th style="width: 35%;">{{language_data('Plan Name',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 15%;">{{language_data('Price',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 10%;">{{language_data('Popular',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 30%;">{{language_data('Action',Auth::guard('client')->user()->lan_id)}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($price_plan as $pp)
                                    <tr>
                                        <td data-label="SL">{{ $loop->iteration }}</td>
                                        <td data-label="Plan Name"><p>{{$pp->plan_name}}</p></td>
                                        <td data-label="Price"><p>{{us_money_format($pp->price)}}</p></td>
                                        @if($pp->popular=='Yes')
                                            <td data-label="Popular"><p class="label label-success label-xs">{{language_data('Yes',Auth::guard('client')->user()->lan_id)}}</p></td>
                                        @else
                                            <td data-label="Popular"><p class="label label-primary label-xs">{{language_data('No',Auth::guard('client')->user()->lan_id)}}</p></td>
                                        @endif
                                        <td data-label="Actions">
                                            <a class="btn btn-success btn-xs" href="{{url('user/sms/sms-plan-feature/'.$pp->id)}}" ><i class="fa fa-eye"></i> {{language_data('View Features',Auth::guard('client')->user()->lan_id)}}</a>
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
              url: '{!! url("assets/libs/data-table/i18n/".get_language_code(Auth::guard('client')->user()->lan_id)->language.".lang") !!}'
            },
            responsive: true
          })

            /*For Delete Price Plan*/
            $( "body" ).delegate( ".cdelete", "click",function (e) {
                e.preventDefault();
                var id = this.id;
              bootbox.confirm("{!! language_data('Are you sure',Auth::guard('client')->user()->lan_id) !!} ?", function (result) {
                    if (result) {
                        var _url = $("#_url").val();
                        window.location.href = _url + "/sms/delete-price-plan/" + id;
                    }
                });
            });

        });
    </script>
@endsection