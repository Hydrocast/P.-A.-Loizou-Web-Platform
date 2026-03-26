<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Represents a reusable graphical asset available in the design workspace.
 *
 * Clipart items have no relationships with other entities.
 * They are loaded into the workspace clipart browser when the design
 * workspace is initialised.
 *
 * @property int    $clipart_id
 * @property string $clipart_name
 * @property string $image_reference
 */
class Clipart extends Model
{
    use HasFactory;

    protected $table = 'clipart';
    protected $primaryKey = 'clipart_id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'clipart_name',
        'image_reference',
    ];
}