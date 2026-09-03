<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklSupervisor extends Model
{
    use HasFactory;

    protected $fillable = ['supervisor_name', 'company_name', 'company_address', 'contact_phone'];
}
