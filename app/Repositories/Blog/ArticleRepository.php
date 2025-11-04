<?php

namespace App\Repositories\Blog;

use App\Models\Blog\Article;
use App\Repositories\BaseRepository;

class ArticleRepository extends BaseRepository
{
    public function __construct(Article $model)
    {
        parent::__construct($model);
    }

    public function getPublishedArticles()
    {
        return $this->model->where('status', 'published')->get();
    }

    public function getByCategory($categoryId)
    {
        return $this->model->where('category_id', $categoryId)->get();
    }

    public function searchArticles($query)
    {
        return $this->model->where('title', 'like', "%{$query}%")
                          ->orWhere('content', 'like', "%{$query}%")
                          ->get();
    }
}