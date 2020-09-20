@component('mail::message')
Hi {{$name}},

Password Reset Successfully! This message is an automated reply to your password reset request. Click this link to reset your password:

@component('mail::button', ['url' => $url])
Reset Password
@endcomponent

Notes: Until your password has been changed, your current password will remain valid. The Forgot Password Link will be available for a limited time only.

Thanks,<br>
{{ app_config('AppName') }}
@endcomponent
