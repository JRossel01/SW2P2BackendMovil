<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MedicalRecordPdfMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $nombre;
    public string $pdfPath;

    public function __construct(string $nombre)
    {
        $this->nombre = $nombre;
    }

    public function build()
    {
        return $this->subject("Historial Médico")
            ->view('emails.medical_record')
            ->attach(
                storage_path('app/public/' . $this->pdfPath),
                [
                    'as' => 'historial_medico.pdf',
                    'mime' => 'application/pdf',
                ]
            );
    }


    public function withAttachment(string $pdfPath): self
    {
        $this->pdfPath = $pdfPath;
        return $this;
    }
}
