<?php

namespace App\Repositories\Course;

use App\Models\Course\Course;
use App\Repositories\BaseRepository;

class CourseRepository extends BaseRepository
{
    public function __construct(Course $model)
    {
        parent::__construct($model);
    }

    public function getWithDetails($courseId)
    {
        return $this->model->with(['instructor', 'category', 'chapters.contents', 'reviews'])
                          ->find($courseId);
    }

    public function getByCategory($categoryId)
    {
        return $this->model->where('category_id', $categoryId)->get();
    }

    public function getPublishedCourses()
    {
        return $this->model->where('status', 'published')->get();
    }

    public function searchCourses($query)
    {
        return $this->model->where('title', 'like', "%{$query}%")
                          ->orWhere('description', 'like', "%{$query}%")
                          ->get();
    }
}