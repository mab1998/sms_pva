@component('mail::message')
Hi {{$name}},

Welcome to {{ app_config('AppName') }}! This message is an automated reply to your User Access request. Login to your User panel by using the details below:

@component('mail::panel')
Username: {{$username}} <br>
Password: {{$password}}
@endcomponent

@component('mail::button', ['url' => url('/')])
Login
@endcomponent

Thanks,<br>
{{ app_config('AppName') }}
@endcomponent
