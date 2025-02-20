<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\SPK;

class SPKNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $spk;

    public function __construct(SPK $spk)
    {
        $this->spk = $spk;
    }

    public function build()
    {
        return $this->subject('Permintaan Approval SPK untuk Pekerjaan Jasa')
                    ->view('emails.spk_notification');
    }
}
