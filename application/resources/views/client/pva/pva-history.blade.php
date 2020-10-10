@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title"> PVA History</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">PVA History</h3>
                        </div>
                    </div>

<div >
   {{-- <form method='POST' action="get_number">
    <select class="selectpicker form-control" name='country' id='country' data-live-search="true">
            <!-- <option value="audi">Audi TT</option> -->

            @foreach($countries as $country)
                <option value="{{$country->country_code}}"   >{{$country->country}}</option>
            @endforeach

      </select>

    <select class="selectpicker form-control" id='services' name="services" data-live-search="true">
            <!-- <option value="audi">Audi TT</option> -->
            @foreach($services as $service)
                <option value="{{$service->service_code}}"   >{{$service->service}}</option>
            @endforeach

      </select>
   


<button id="get_number" type="submit"  class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> get number </button>

</form> --}}

    {{-- <span id="price" style="color:blue;font-size: larger;">price</span>    <span style="color:blue;font-size: larger;">Point</span></br> --}}


    {{-- <span id="buy">buy</span> --}}

 {{-- <input type="hidden" id="auth" name="auth" value="{{Auth::guard('client')->user()->username}}"> --}}

</div>

{{-- $client->sms_limit --}}

    {{-- <button id="get_number">get number</button> --}}
    {{-- <button id="get_number"  class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> refresh </button> --}}

        {{-- <button id="refresh">refresh</button> --}}

<div class="panel-body p-none">
    <table class="table data-table table-hover" >
  <thead>
    <tr>
        <th scope="col">#</th>
        {{-- <th scope="col">id</th> --}}
        <th scope="col">country</th>
        <th scope="col">service</th>
        <th scope="col">price</th>
        <th scope="col">country code</th>
        <th scope="col">Phone number</th>
      <th scope="col">Activation code</th>
    </tr>
  </thead>
  <tbody id="tb">
  @foreach($history as $h)
  <tr id="{{$h->id}}" >
  <td >{{$loop->iteration}}</td>
    <td >{{country($h->country)}}</td>
        <td >{{service($h->service)}}</td>
                <td >{{$h->price}}</td>
                <td >+{{country_code_number($h->country)}}</td>
        <td >{{$h->Phone_Number}}</td>
        <td >{{$h->activation_code}}</td>
</tr>

  @endforeach
   

  </tbody>
</table>
</div>

{{-- <span >Don't refresh webpage when you are working with numbers! Open website in new tab, if you need it.</span> --}}



                </div>
            </div>
        </div>
    </section>


@endsection


@section('script')
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
    {!! Html::script("assets/libs/data-table/datatables.min.js")!!}
    {!! Html::script("assets/js/bootbox.min.js")!!}


@endsection

