@component('mail::message')
Hi {{$name}},

Password Reset Successfully! This message is an automated reply to your password reset request. Login to your account to set up your all details by using the details below:

@component('mail::panel')
Username: {{$username}} <br>
Password: {{$password}}
@endcomponent

@component('mail::button', ['url' => $url])
Login
@endcomponent

Thanks,<br>
{{ app_config('AppName') }}
@endcomponent
