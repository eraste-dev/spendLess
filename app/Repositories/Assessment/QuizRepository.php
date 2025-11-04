<?php

namespace App\Repositories\Assessment;

use App\Models\Assessment\Quiz;
use App\Repositories\BaseRepository;

class QuizRepository extends BaseRepository
{
    public function __construct(Quiz $model)
    {
        parent::__construct($model);
    }

    public function getWithQuestions($quizId)
    {
        return $this->model->with('questions')->find($quizId);
    }

    public function getByCourse($courseId)
    {
        return $this->model->where('course_id', $courseId)->get();
    }
}