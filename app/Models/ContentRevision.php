<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ContentRevision extends Model {
    public $timestamps = false;
    protected $fillable = ['translation_id','editor_id','snapshot','created_at'];
    protected function casts(): array { return ['snapshot' => 'array', 'created_at' => 'datetime']; }
}
