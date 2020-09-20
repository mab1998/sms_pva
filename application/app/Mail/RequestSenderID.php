<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RequestSenderID extends Mailable
{
    use Queueable, SerializesModels;

    protected $name;
    protected $email;
    protected $sender_id;
    protected $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, $sender_id, $url)
    {
        $this->name = $name;
        $this->email = $email;
        $this->sender_id = $sender_id;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = app_config('AppName') . ' New Sender ID Request';
        return $this->from(['address' => $this->email, 'name' => $this->name])
            ->subject($subject)
            ->markdown('emails.senderid.request-senderid')
            ->with([
                'client_name' => $this->name,
                'sender_id' => $this->sender_id,
                'url' => $this->url
            ]);
    }
}
