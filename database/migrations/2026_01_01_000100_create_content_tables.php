<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('contents', function (Blueprint $t): void { $t->id(); $t->string('type',32); $t->foreignId('author_id')->constrained('users')->restrictOnDelete(); $t->timestamps(); });
  Schema::create('content_translations', function (Blueprint $t): void {
   $t->id(); $t->foreignId('content_id')->constrained()->cascadeOnDelete(); $t->string('locale',12); $t->string('title'); $t->string('slug'); $t->text('excerpt')->nullable(); $t->longText('body')->nullable(); $t->string('status',24)->default('draft'); $t->timestamp('published_at')->nullable(); $t->string('seo_title')->nullable(); $t->text('seo_description')->nullable(); $t->timestamps(); $t->unique(['content_id','locale']); $t->unique(['locale','slug']); $t->index(['locale','status','published_at']);
  });
  Schema::create('content_revisions', function (Blueprint $t): void { $t->id(); $t->foreignId('translation_id')->constrained('content_translations')->cascadeOnDelete(); $t->foreignId('editor_id')->constrained('users')->restrictOnDelete(); $t->json('snapshot'); $t->timestamp('created_at')->useCurrent(); });
  Schema::create('media', function (Blueprint $t): void { $t->id(); $t->string('disk',32)->default('public'); $t->string('path'); $t->string('original_name'); $t->string('mime_type',127); $t->unsignedBigInteger('size'); $t->foreignId('uploaded_by')->constrained('users')->restrictOnDelete(); $t->timestamps(); $t->unique(['disk','path']); });
  Schema::create('content_media', function (Blueprint $t): void { $t->foreignId('content_id')->constrained()->cascadeOnDelete(); $t->foreignId('media_id')->constrained()->restrictOnDelete(); $t->string('role',32)->default('attachment'); $t->unsignedInteger('position')->default(0); $t->primary(['content_id','media_id','role']); });
  Schema::create('menus', function (Blueprint $t): void { $t->id(); $t->string('name'); $t->string('locale',12); $t->timestamps(); $t->unique(['name','locale']); });
  Schema::create('menu_items', function (Blueprint $t): void { $t->id(); $t->foreignId('menu_id')->constrained()->cascadeOnDelete(); $t->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete(); $t->foreignId('content_translation_id')->nullable()->constrained('content_translations')->nullOnDelete(); $t->string('label'); $t->string('url',2048)->nullable(); $t->unsignedInteger('position')->default(0); $t->timestamps(); $t->index(['menu_id','parent_id','position']); });
  Schema::create('settings', function (Blueprint $t): void { $t->string('key')->primary(); $t->json('value'); $t->timestamps(); });
 }
 public function down(): void { foreach (['settings','menu_items','menus','content_media','media','content_revisions','content_translations','contents'] as $name) Schema::dropIfExists($name); }
};
