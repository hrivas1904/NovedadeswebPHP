<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $table = 'areas';

    protected $primaryKey = 'ID_AREA';

    public $timestamps = false;

    protected $fillable = [
        'NOMBRE',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class, 'area_id');
    }

    /*public function empleados()
    {
        return $this->hasMany(Empleado::class, 'area_id');
    }*/
}