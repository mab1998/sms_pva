@component('mail::message')
Hi {{$name}}

Registration Successfully! This message is an automated reply to your active registration request. Click this link to active your account:

@component('mail::button', ['url' => $url])
Verify
@endcomponent

Thanks,<br>
{{ app_config('AppName') }}
@endcomponent
