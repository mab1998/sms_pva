@component('mail::message')
Hi {{$name}}

{{$message}}

@component('mail::button', ['url' => $attachment])
Download Invoice
@endcomponent

Thanks,<br>
{{ app_config('AppName') }}
@endcomponent
