<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdModAccount extends Model
{
    //
	protected $table='admod_accounts';
	protected $primaryKey='id';

	protected $fillable =  ['admod_pub_id', 'access_token', 'admod_name', 'note','g_client_id','g_secret','error'];
	public $timestamps = true;
}
