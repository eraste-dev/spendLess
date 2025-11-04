<?php

namespace App\Repositories\Course;

use App\Models\Course\Category;
use App\Repositories\BaseRepository;

class CategoryRepository extends BaseRepository
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function getWithCourses()
    {
        return $this->model->with('courses')->get();
    }

    public function getActiveCategories()
    {
        return $this->model->where('is_active', true)->get();
    }
}