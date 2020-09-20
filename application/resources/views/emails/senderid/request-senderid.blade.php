@component('mail::message')
Hi {{app_config('AppName')}}

You got new sender id request from {{$client_name}} Please verify this login with admin portal.

@component('mail::button', ['url' => $url])
Verify
@endcomponent

Thanks,<br>
{{ $client_name }}
@endcomponent
