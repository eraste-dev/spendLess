<?php

namespace App\Repositories\Certificate;

use App\Models\Certificate\ManageCertificate;
use App\Repositories\BaseRepository;

class CertificateRepository extends BaseRepository
{
    public function __construct(ManageCertificate $model)
    {
        parent::__construct($model);
    }

    public function getUserCertificates($userId)
    {
        return $this->model->where('user_id', $userId)->get();
    }

    public function getByCourse($courseId)
    {
        return $this->model->where('course_id', $courseId)->get();
    }
}