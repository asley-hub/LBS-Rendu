<?php

namespace lbs\auth\models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
   protected $table = 'user';

   protected $primaryKey = 'id';
   public $incrementing = true;

   protected $fillable = array(
      'id', 'email', 'username', 'password', 'refresh_token', 'level', 'created_at', 'updated_at'
   );
}
