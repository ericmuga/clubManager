<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingLine extends Model
{
    use HasFactory;
    public function meeting()
    {
        return $this->belongsTo(Meeting::class,'meeting_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    protected $guarded =['id'];
}


