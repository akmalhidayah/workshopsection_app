<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Abnormal;

class AbnormalitasNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $abnormal;

    public function __construct(Abnormal $abnormal)
    {
        $this->abnormal = $abnormal;
    }

    public function build()
    {
        return $this->subject('Permintaan Approval Pekerjaan Jasa Fabrikasi Konstruksi dan Pengerjaan Mesin')
                    ->view('emails.abnormalitas_notification');
    }
}


