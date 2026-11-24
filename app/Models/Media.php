<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Media extends Model { protected $fillable = ['disk','path','original_name','mime_type','size','uploaded_by']; }
