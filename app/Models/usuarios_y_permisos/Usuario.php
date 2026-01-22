<?php

namespace App\Models\usuarios_y_permisos;

use App\Models\usuarios_y_permisos\Permiso;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{
    use HasFactory;
    protected $connection = 'mysql4';
    //Asociacion con la tabla usuarios de la BD
    protected $table = 'usuarios';
    use Notifiable;
    //Campos de la tabla usuario
    protected $fillable = [
        'name',
        'username',
        'password',
        'admin',
        'telefono_laboral',
        'telefono_interno',
        'fecha_nac',
        'email_interno',
        'email_externo'
    ];


    // Relación con la tabla permisos
    public function permisos()
    {
        return $this->hasMany(Permiso::class, 'usuario_id', 'id');
    }

    //Relacion con la tabla propiedad
    /* public function propiedadesModificadas()
    {
        return $this->hasMany(Propiedad::class, 'last_modified_by');
    }

    public function padrones()
    {
        return $this->hasMany(Padron::class, 'last_modified_by', 'id');
    }
    public function padron_telefonos()
    {
        return $this->hasMany(Padron_telefonos::class, 'last_modified_by', 'id');
    }
    public function propiedades()
    {
        return $this->hasMany(Propiedad::class, 'last_modified_by', 'id');
    }

    public function propiedades_padron()
    {
        return $this->hasMany(Propiedades_padron::class, 'last_modified_by', 'id');
    }

    public function fotos()
    {
        return $this->hasMany(Foto::class, 'last_modified_by', 'id');
    }

    public function observaciones_propiedades()
    {
        return $this->hasMany(Observaciones_propiedades::class, 'last_modified_by', 'id');
    }

    public function documentacion()
    {
        return $this->hasMany(Documentacion::class, 'last_modified_by', 'id');
    } */

    // Métodos requeridos por JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Sobrescribir método para devolver contraseña en texto plano
    public function getAuthPassword()
    {
        return $this->password;
    }
}
