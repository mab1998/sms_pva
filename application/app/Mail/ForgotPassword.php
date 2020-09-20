<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    protected $url;
    protected $name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($url,$name)
    {
        $this->url = $url;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = app_config('AppName'). ' password change request';

        return $this->from(['address' => app_config('Email'), 'name' => app_config('AppName')])
            ->subject($subject)
            ->markdown('emails.auth.forgot-password')
            ->with([
                'name' => $this->name,
                'url' => $this->url,
            ]);
    }
}
