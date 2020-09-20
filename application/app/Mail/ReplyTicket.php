<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReplyTicket extends Mailable
{
    use Queueable, SerializesModels;

    protected $name;
    protected $mail_subject;
    protected $message;
    protected $url;
    protected $sysName;
    protected $sysEmail;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $mail_subject, $message, $url, $sysName, $sysEmail)
    {
        $this->name = $name;
        $this->mail_subject = $mail_subject;
        $this->message = $message;
        $this->url = $url;
        $this->sysName = $sysName;
        $this->sysEmail = $sysEmail;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(['address' => $this->sysEmail, 'name' => $this->sysName])
            ->subject($this->mail_subject)
            ->markdown('emails.tickets.reply-ticket')
            ->with([
                'name' => $this->name,
                'message' => $this->message,
                'url' => $this->url
            ]);
    }
}
