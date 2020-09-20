<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserRegistration extends Mailable
{
    use Queueable, SerializesModels;

    protected $username;
    protected $password;
    protected $name;
    protected $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $username, $password, $url = null)
    {

        if ($url == null){
            $this->url = url('/');
        }else {
            $this->url = $url;
        }

        $this->username = $username;
        $this->password = $password;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Welcome to ' . app_config('AppName');

        return $this->from(['address' => app_config('Email'), 'name' => app_config('AppName')])
            ->subject($subject)
            ->markdown('emails.auth.registration')
            ->with([
                'name' => $this->name,
                'username' => $this->username,
                'password' => $this->password,
                'url' => $this->url,
            ]);
    }
}
