<?php

namespace App\Repositories\Assessment;

use App\Models\Assessment\Exam;
use App\Repositories\BaseRepository;

class ExamRepository extends BaseRepository
{
    public function __construct(Exam $model)
    {
        parent::__construct($model);
    }

    public function getWithQuestions($examId)
    {
        return $this->model->with('questions')->find($examId);
    }

    public function getByCourse($courseId)
    {
        return $this->model->where('course_id', $courseId)->get();
    }
}