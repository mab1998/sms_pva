@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title"> PVA</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">PVA</h3>
                        </div>
                    </div>

<div >
   <form method='POST' action="get_number">
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

</form>

    <span id="price" style="color:blue;font-size: larger;">price</span>    <span style="color:blue;font-size: larger;">Point</span></br>


    {{-- <span id="buy">buy</span> --}}

 <input type="hidden" id="auth" name="auth" value="{{Auth::guard('client')->user()->username}}">

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

<span >Don't refresh webpage when you are working with numbers! Open website in new tab, if you need it.</span>



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


    <script>
    var ee;
    window.setInterval(function(){
        $.ajax({
            type: 'GET',
            url: "get_unchecked",
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            // data: {value: "myJsVar"},
            success: function (response) {
                

                for(var k in response) {
                    // if(e[k] instanceof Object) {
                    console.log(response[k]);
                    let element =response[k];
                    if (element.status=='Failed'){
                    document.getElementById(element.id).lastElementChild.innerHTML=element.status;
                    }else if(element.status=='Successful'){
                        document.getElementById(element.id).lastElementChild.innerHTML=element.activation_code;
                    }
                }
                
                ee=response;
                console.log(response)
            }
        });
}, 20000);


            

            let user =document.getElementById("auth").value;


            // Pusher.logToConsole = true;

            // var pusher = new Pusher('44b1363ca1cdddbc1558', {
            // cluster: 'eu'
            // });

            // var channel = pusher.subscribe('activation_code');
            // channel.bind(user, function(data) {
            //     console.log(data.id_real,"======",data.status)
            //     let tr=document.getElementById(data.id_real).lastChild.innerText=data.status
            //     console.log(tr)
            //     // =data.status
            //     // tr
            //     // alert(JSON.stringify(data));
            // });

            


            // $.get("http://127.0.0.1:8000/get_history", {
            //     user: 'mab1998'
            // }, function(data, status) {


            //     // let td = document.createElement("td");

            //     for (i = 0; i < data.length; i++) {
            //         let input_id =document.createElement("input");
            //         input_id.id="id"
            //         input_id.value=data[i][6]
            //         input_id.type="hidden"

            //         let tb=document.getElementById("tb");
            //         let tr=document.createElement("tr");

            //         let th_id = document.createElement("th");
            //         let td_country =document.createElement("td");
            //         let td_service =document.createElement("td");
            //         let td_price =document.createElement("td");
            //         let td_phone_number =document.createElement("td");
            //         let td_activatio_code =document.createElement("td");

            //         th_id.innerText =data[i][0]
            //         th_id.id=data[i][6]

            //         td_country.innerText =data[i][1]
            //         td_country.innerText ="country"

            //         td_service.innerText =data[i][2]
            //         td_country.innerText ="service"

            //         td_price.innerText =data[i][3]
            //         td_phone_number.innerText=data[i][4]

            //         td_activatio_code.innerText =data[i][5]
            //         td_activatio_code.id ="act_code"

            //         tr.id=data[i][6]


            //         tr.appendChild(th_id)
            //         tr.appendChild(td_country)
            //         tr.appendChild(td_service)
            //         tr.appendChild(td_price)
            //         tr.appendChild(td_phone_number)
            //         tr.appendChild(input_id)
            //         tr.appendChild(td_activatio_code)
                    
            //         tb.appendChild(tr)
                    

            //     }


            // });

        // $("#get_number").click(function() {
        //         let country = document.getElementById("country").value;
        //         let service = document.getElementById("services").value;
        //         let user =document.getElementById("auth").value;


        //         $.get("get_number", {
        //             user:user,
        //             country: country,
        //             service: service

        //         }, function(data, status) {
        //             // let e = document.getElementById("buy");
        //             // alert(data.number);
        //             // e.innerText = data.number;

        //             window.location.reload(true);


        //         });



        //     })
            // $("button").click(function() {



        // });
        $("#country").change(function() {
            let country = document.getElementById("country").value;
            let service = document.getElementById("services").value;


            $.get("get_info", {
                country: country,
                service: service
            }, function(data, status) {
                let e = document.getElementById("price");
                e.innerText = data;

            });



        })


        $("#services").change(function() {
            let country = document.getElementById("country").value;
            let service = document.getElementById("services").value;


            $.get("get_info", {
                country: country,
                service: service
            }, function(data, status) {
                let e = document.getElementById("price");
                e.innerText = data;

            });

        })

        // $.get("http://127.0.0.1:8000/get_countries", function(data, status) {
        //     // alert("Data: " + data + "\nStatus: " + status);
        //     var obj = data;
        //     var x = document.getElementById("country");


        //     for (i = 0; i < obj.length; i++) {
        //         //     //     alert("Data: " + data + "\nStatus: " + status);
        //         var option = document.createElement("option");

        //         // console.log(obj[i][0])
        //         option.text = obj[i][0];
        //         option.value = obj[i][1];
        //         x.add(option);
        //         // x.add(option);

        //         // $.("#country").appendChild(btn);

        //     }
        // });

        // $.get("http://127.0.0.1:8000/get_services", function(data, status) {
        //     // alert("Data: " + data + "\nStatus: " + status);
        //     var obj = data;
        //     var x = document.getElementById("services");


        //     for (i = 0; i < obj.length; i++) {
        //         //     //     alert("Data: " + data + "\nStatus: " + status);
        //         var option = document.createElement("option");

        //         // console.log(obj[i][0])
        //         option.text = obj[i][0];
        //         option.value = obj[i][1];
        //         x.add(option);
        //         // x.add(option);

        //         // $.("#country").appendChild(btn);

        //     }
        // });


// $("#refresh").click(function() {
//             let tb=document.getElementById("tb")
//             for (let index = 0; index < tb.length; index++) {
//                 let element = tb[index];
//                 let country =element.getElementById("country").innerText;
//                 let service =element.getElementById("service").innerText; 
//                 let id =element.getElementById("service").value; 
                
//                 if(element.getElementById("act_code").innerText!="ERROR"){

                          
//                                 $.get("http://127.0.0.1:8000/check", {
//                                     id:id,
//                                     user:user,
//                                     country: country,
//                                     service: service

//                                 }, function(data, status) {
//                                     // let e = document.getElementById("buy");
//                                     // e.innerText = data.number;

//                                 });




//                 }
                
//             }
//             })

            let country = document.getElementById("country").value;
            let service = document.getElementById("services").value;


            $.get("get_info", {
                country: country,
                service: service
            }, function(data, status) {
                let e = document.getElementById("price");
                e.innerText = data;

            });
    </script>

@endsection

