@component('mail::message')
Hi {{$name}}

{{$message}}

@component('mail::button', ['url' => $url])
View Ticket
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
