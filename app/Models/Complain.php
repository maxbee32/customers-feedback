<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complain extends Model
{
    use HasFactory;

    protected $guarded=[];
    // protected $fillable = [
    //     'user_id',
    //     'comment',
    //     'phone_number',
    // ];


    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
