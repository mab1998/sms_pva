@component('mail::message')
Hi {{app_config('AppName')}}

Spam word detected. Here is the message and client details:

@component('mail::panel')
Username: {{$client_name}}
Message: {{$message}}
@endcomponent

@component('mail::button', ['url' => $url])
View Client
@endcomponent

Thanks,<br>
{{ app_config('AppName') }}
@endcomponent
