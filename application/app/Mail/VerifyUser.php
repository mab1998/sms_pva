<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyUser extends Mailable
{
    use Queueable, SerializesModels;

    protected $name;
    protected $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $url)
    {
        $this->name = $name;
        $this->url  = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = app_config('AppName') . ' Registration Code';

        return $this->from(['address' => app_config('Email'), 'name' => app_config('AppName')])
            ->subject($subject)
            ->markdown('emails.auth.verify-user')
            ->with([
                'name' => $this->name,
                'url'  => $this->url
            ]);
    }
}
