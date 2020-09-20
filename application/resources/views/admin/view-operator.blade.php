@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('View Operator')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('View Operator')}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 5%;">{{language_data('SL')}}</th>
                                    <th style="width: 20%;">{{language_data('Operator name')}}</th>
                                    <th style="width: 10%;">{{language_data('Operator code')}}</th>
                                    <th style="width: 10%;">{{language_data('Plain')}} {{language_data('Price')}}</th>
                                    <th style="width: 10%;">{{language_data('Voice')}} {{language_data('Price')}}</th>
                                    <th style="width: 10%;">{{language_data('MMS')}} {{language_data('Price')}}</th>
                                    <th style="width: 10%;">{{language_data('Status')}}</th>
                                    <th style="width: 25%;">{{language_data('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($operators as $op)
                                    <tr>
                                        <td data-label="SL">{{ $loop->iteration }}</td>
                                        <td data-label="Operator Name"><p>{{$op->operator_name}}</p></td>
                                        <td data-label="Operator Code"><p>{{$op->operator_code}}</p></td>
                                        <td data-label="Plain Price"><p>{{$op->plain_price}}</p></td>
                                        <td data-label="Voice Price"><p>{{$op->voice_price}}</p></td>
                                        <td data-label="MMS Price"><p>{{$op->mms_price}}</p></td>
                                        @if($op->status=='active')
                                            <td data-label="Status"><p class="label label-success">{{language_data('Active')}}</p></td>
                                        @else
                                            <td data-label="Status"><p class="label label-danger">{{language_data('Inactive')}}</p>
                                            </td>
                                        @endif
                                        <td data-label="Actions">
                                            <a class="btn btn-success btn-xs"
                                               href="{{url('sms/manage-operator/'.$op->id)}}"><i
                                                        class="fa fa-edit"></i> {{language_data('Manage')}}</a>
                                            <a href="#" class="btn btn-danger btn-xs cdelete" id="{{$op->id}}"><i
                                                        class="fa fa-trash"></i> {{language_data('Delete')}}</a>

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
      $(document).ready(function () {

        $('.data-table').DataTable({
          language: {
            url: '{!! url("assets/libs/data-table/i18n/".get_language_code()->language.".lang") !!}'
          },
          responsive: true
        });

        /*For Delete operator*/
        $('body').delegate('.cdelete', 'click', function (e) {
          e.preventDefault();
          var id = this.id;
          bootbox.confirm("{!! language_data('Are you sure') !!}?", function (result) {
            if (result) {
              var _url = $('#_url').val();
              window.location.href = _url + '/sms/delete-operator/' + id
            }
          })
        })

      })
    </script>
@endsection
