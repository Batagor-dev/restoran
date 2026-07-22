<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleCategory extends Model
{
    use HasFactory, HasSlug;

    protected $guarded = ['id'];

    protected string $slugFrom = 'name';

    public function article()
    {
        return $this->hasMany(Article::class, 'article_category_id');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
