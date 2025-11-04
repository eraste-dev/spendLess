<?php

namespace App\Repositories\User;

use App\Models\User\Instructor;
use App\Repositories\BaseRepository;

class InstructorRepository extends BaseRepository
{
    public function __construct(Instructor $model)
    {
        parent::__construct($model);
    }

    public function getWithCourses($instructorId)
    {
        return $this->model->with('courses')->find($instructorId);
    }

    public function getTopInstructors($limit = 10)
    {
        return $this->model->with('courses')
                          ->withCount('courses')
                          ->orderBy('courses_count', 'desc')
                          ->limit($limit)
                          ->get();
    }
}