<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ContentTranslation extends Model {
    protected $fillable = ['content_id','locale','title','slug','excerpt','body','status','published_at','seo_title','seo_description'];
    protected function casts(): array { return ['published_at' => 'datetime']; }
    public function content(): BelongsTo { return $this->belongsTo(Content::class); }
    public function revisions(): HasMany { return $this->hasMany(ContentRevision::class, 'translation_id'); }
}
