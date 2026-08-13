<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealPlan extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'description'];
}
