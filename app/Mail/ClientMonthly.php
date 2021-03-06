<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientMonthly extends Mailable
{
    use Queueable, SerializesModels;

    public $body;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($body)
    {
        $this->body = $body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->body['month'] = date('m')-1;
        if($this->body['type']=='delegation') {
            return $this->from('qc@optimaretail.es')
                        ->subject(__('Monthly Summary'))
                        ->view('emails.delegations');
        } else {
            return $this->from('qc@optimaretail.es')
                    ->subject(__('Monthly Summary'))
                    ->view('emails.clients');
        }
        /**/
    }
}
