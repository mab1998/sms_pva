<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BlockMessage extends Mailable
{
    use Queueable, SerializesModels;


    protected $name;
    protected $email;
    protected $message;
    protected $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, $message, $url)
    {
        $this->name = $name;
        $this->email = $email;
        $this->message = $message;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Get spam message from '.app_config('AppName');

        return $this->from(['address' => $this->email, 'name' => $this->name])
            ->subject($subject)
            ->markdown('emails.sms.block-message')
            ->with([
                'client_name' => $this->name,
                'message' => $this->message,
                'url' => $this->url
            ]);
    }
}
