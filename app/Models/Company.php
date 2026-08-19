<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = [
        'empresa_institucional' => 'Empresa institucional',
        'tienda_barrio' => 'Tienda de barrio',
    ];

    protected $fillable = [
        'company_type',
        'name',
        'nit',
        'email',
        'phone',
        'address',
        'google_maps_url',
        'city',
        'city_id',
        'owner_first_name',
        'owner_last_name_paterno',
        'owner_last_name_materno',
        'created_by',
    ];

    /**
     * Creador administrado por usuarios del sistema.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cityRelation(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Etiqueta amigable segun el tipo registrado.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->company_type] ?? 'Sin clasificar';
    }
}
