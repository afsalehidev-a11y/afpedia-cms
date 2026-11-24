<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MenuItem extends Model { protected $fillable = ['menu_id','parent_id','label','url','content_translation_id','position']; }
