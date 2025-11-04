<?php

namespace App\Providers;

use App\Models\Assessment\Exam;
use App\Models\Assessment\Quiz;
use App\Models\Blog\Article;
use App\Models\Certificate\ManageCertificate;
use App\Models\Course\Category;
use App\Models\Course\Course;
use App\Models\User\Instructor;
use App\Models\User\User;
use App\Repositories\Assessment\ExamRepository;
use App\Repositories\Assessment\QuizRepository;
use App\Repositories\Blog\ArticleRepository;
use App\Repositories\Certificate\CertificateRepository;
use App\Repositories\Course\CategoryRepository;
use App\Repositories\Course\CourseRepository;
use App\Repositories\User\InstructorRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // User Module Repositories
        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository(new User());
        });

        $this->app->bind(InstructorRepository::class, function ($app) {
            return new InstructorRepository(new Instructor());
        });

        // Course Module Repositories
        $this->app->bind(CourseRepository::class, function ($app) {
            return new CourseRepository(new Course());
        });

        $this->app->bind(CategoryRepository::class, function ($app) {
            return new CategoryRepository(new Category());
        });

        // Assessment Module Repositories
        $this->app->bind(ExamRepository::class, function ($app) {
            return new ExamRepository(new Exam());
        });

        $this->app->bind(QuizRepository::class, function ($app) {
            return new QuizRepository(new Quiz());
        });

        // Certificate Module Repositories
        $this->app->bind(CertificateRepository::class, function ($app) {
            return new CertificateRepository(new ManageCertificate());
        });

        // Blog Module Repositories
        $this->app->bind(ArticleRepository::class, function ($app) {
            return new ArticleRepository(new Article());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
