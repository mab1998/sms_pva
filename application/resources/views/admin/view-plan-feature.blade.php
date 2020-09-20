@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('SMS Plan Features')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('SMS Plan Features')}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 5%;">{{language_data('SL')}}#</th>
                                    <th style="width: 30%;">{{language_data('Feature Name')}}</th>
                                    <th style="width: 30%;">{{language_data('Feature Value')}}</th>
                                    <th style="width: 10%;">{{language_data('Show')}}</th>
                                    <th style="width: 25%;">{{language_data('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($features as $f)
                                    <tr>
                                        <td data-label="SL">{{ $loop->iteration }}</td>
                                        <td data-label="Feature Name"><p>{{$f->feature_name}}</p></td>
                                        <td data-label="Feature Value"><p>{{$f->feature_value}}</p></td>
                                        @if($f->status=='Active')
                                            <td data-label="Status"><p
                                                        class="label label-success label-xs">{{language_data('Show')}}</p>
                                            </td>
                                        @else
                                            <td data-label="Status"><p
                                                        class="label label-warning label-xs">{{language_data('Hide')}}</p>
                                            </td>
                                        @endif
                                        <td data-label="Actions">
                                            <a class="btn btn-complete btn-xs"
                                               href="{{url('sms/manage-plan-feature/'.$f->id)}}"><i
                                                        class="fa fa-edit"></i> {{language_data('Manage')}}</a>
                                            <a href="#" class="btn btn-danger btn-xs cdelete" id="{{$f->id}}"><i
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
        })

        /*For Delete Plan Feature*/
        $('body').delegate('.cdelete', 'click', function (e) {
          e.preventDefault()
          var id = this.id
          bootbox.confirm("{!! language_data('Are you sure') !!}?", function (result) {
            if (result) {
              var _url = $('#_url').val()
              window.location.href = _url + '/sms/delete-plan-feature/' + id
            }
          })
        })

      })
    </script>
@endsection