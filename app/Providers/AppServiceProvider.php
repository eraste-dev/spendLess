<?php

namespace App\Providers;

use App\Models\Assessment\Exam;
use App\Models\Assessment\Quiz;
use App\Models\Blog\Article;
use App\Models\Budget\ExpenseCategory;
use App\Models\Budget\ExpenseSubcategory;
use App\Models\Budget\Income;
use App\Models\Budget\IncomeCategory;
use App\Models\User\UserSetting;
use App\Models\Certificate\ManageCertificate;
use App\Models\Course\Category;
use App\Models\Course\Course;
use App\Models\User\Instructor;
use App\Models\User\User;
use App\Repositories\Assessment\ExamRepository;
use App\Repositories\Assessment\QuizRepository;
use App\Repositories\Blog\ArticleRepository;
use App\Repositories\Budget\ExpenseCategoryRepository;
use App\Repositories\Budget\ExpenseSubcategoryRepository;
use App\Repositories\Budget\IncomeCategoryRepository;
use App\Repositories\Budget\IncomeRepository;
use App\Repositories\User\UserSettingRepository;
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

        // Budget Module Repositories
        $this->app->bind(IncomeCategoryRepository::class, function ($app) {
            return new IncomeCategoryRepository(new IncomeCategory());
        });

        $this->app->bind(IncomeRepository::class, function ($app) {
            return new IncomeRepository(new Income());
        });

        $this->app->bind(ExpenseCategoryRepository::class, function ($app) {
            return new ExpenseCategoryRepository(new ExpenseCategory());
        });

        $this->app->bind(ExpenseSubcategoryRepository::class, function ($app) {
            return new ExpenseSubcategoryRepository(new ExpenseSubcategory());
        });

        // User Settings Repository
        $this->app->bind(UserSettingRepository::class, function ($app) {
            return new UserSettingRepository(new UserSetting());
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
