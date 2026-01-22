<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecordatorioNotificacion extends Notification
{
    use Queueable;
    protected $recordatorio;
    public $mensaje;
    
    public function __construct(array $mensaje)
    {
        $this->mensaje = $mensaje;
    }

    
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'pertenece'=>$this->mensaje['pertenece'],
            'id'=>$this->mensaje['id'],
            'descripcion'=>$this->mensaje['descripcion'],
            'fecha'=>$this->mensaje['fecha'],
            'hora'=>$this->mensaje['hora'],
            'activo'=>$this->mensaje['activo'],
            'es_asesor_activo'=>$this->mensaje['es_asesor_activo'],
            'es_criterio'=>$this->mensaje['es_criterio'],
        ];
    }
}
