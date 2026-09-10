<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostalService extends Model
{
    protected $fillable = ['code', 'name', 'modality', 'category', 'profile'];
}
