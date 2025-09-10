<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostPriorities extends Model
{
    protected $fillable = ['wp_post_id','priority'];

}
