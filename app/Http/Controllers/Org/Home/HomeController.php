<?php

namespace App\Http\Controllers\Org\Home;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\InstructorRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\TestimonialRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $topStudents = UserRepository::query()
            ->where('is_admin', false)
            ->whereDoesntHave('instructor')
            ->where('student_organization_id', auth()->user()->organization_id)
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(5)
            ->get();

        $topInstructors = InstructorRepository::query()
            ->with(['user'])
            ->where('organization_id', auth()->user()->organization_id)
            ->withCount('courses')
            ->withCount([
                'courses as reviews_count' => function ($query) {
                    $query->join('reviews', 'reviews.course_id', '=', 'courses.id')
                        ->select(DB::raw('count(reviews.id)'));
                }
            ])
            ->withAvg([
                'courses as rating_avg' => function ($query) {
                    $query->join('reviews', 'reviews.course_id', '=', 'courses.id')
                        ->select(DB::raw('avg(reviews.rating)'));
                }
            ], 'rating')
            ->orderBy('courses_count', 'desc')
            ->limit(5)
            ->get();

        $instructors = UserRepository::query()->where('is_admin', false)
            ->whereHas('instructor', function ($query) {
                $query->where('organization_id', auth()->user()->organization_id);
            })->count();

        $topSaleCourses = CourseRepository::query()
            ->withTrashed()
            ->whereHas('organization', function ($query) {
                $query->where('organization_id', auth()->user()->organization_id);
            })
            ->whereHas('transactions', function ($query) {
                $query->where('is_paid', 1);
            })
            ->withSum(['transactions as total_payment' => function ($query) {
                $query->where('is_paid', 1);
            }], 'payment_amount')
            ->orderBy('total_payment', 'desc')
            ->limit(5)
            ->get();

        $courses = CourseRepository::query()->whereHas('organization', function ($query) {
            $query->where('organization_id', auth()->user()->organization_id);
        })->where('is_active', true)->count();

        $enrollments = EnrollmentRepository::query()->whereHas('course', function ($query) {
            $query->whereHas('organization', function ($query) {
                $query->where('id', auth()->user()->organization_id);
            });
        })->count();

        $students = UserRepository::query()->where('student_organization_id', auth()->user()->organization_id)->count();

        $transactions = Transaction::query()->whereHas('enrollment', function ($query) {
            $query->whereHas('course', function ($query) {
                $query->whereHas('organization', function ($query) {
                    $query->where('id', auth()->user()->organization_id);
                });
            });
        })->where('is_paid', 1)->sum('payment_amount');

        $transactionsCount = Transaction::query()->whereHas('enrollment', function ($query) {
            $query->whereHas('course', function ($query) {
                $query->whereHas('organization', function ($query) {
                    $query->where('id', auth()->user()->organization_id);
                });
            });
        })->count();

        $courseReview = ReviewRepository::query()->whereHas('course', function ($query) {
            $query->whereHas('organization', function ($query) {
                $query->where('id', auth()->user()->organization_id);
            });
        })->count();

        $clientFeedback = TestimonialRepository::query()->where('organization_id', auth()->user()->organization_id)->count();

        $popular_courses = CourseRepository::query()->whereHas('organization', function ($query) {
            $query->where('organization_id', auth()->user()->organization_id);
        })->where('is_active', true)->orderBy('view_count', 'desc')->limit(5)->get();

        return view('organization.home.index', [
            'total_courses' => $courses,
            'total_enrollments' => $enrollments,
            'total_students' => $students,
            'total_transactions' => $transactions,
            'topSaleCourses' => $topSaleCourses,
            'total_instructors' => $instructors,
            'total_transactions_count' => $transactionsCount,
            'total_course_review' => $courseReview,
            'total_client_feedback' => $clientFeedback,
            'popular_courses' => $popular_courses,
            'topStudents' => $topStudents,
            'topInstructors' => $topInstructors
        ]);
    }

    public function revenue()
    {
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate   = Carbon::now()->endOfMonth();

        $months = [];
        $courseData = [];
        $enrollmentData = [];
        $reviewData = [];

        $period = new \DatePeriod($startDate, new \DateInterval('P1M'), $endDate->addMonth());
        foreach ($period as $date) {
            $month = $date->format('Y-m'); // e.g. 2025-08
            $months[] = $date->format('M Y'); // e.g. Aug 2025

            // Courses created per month
            $courseData[] = Course::whereYear('created_at', $date->format('Y'))
                ->whereMonth('created_at', $date->format('m'))
                ->whereHas('organization', function ($query) {
                    $query->where('id', auth()->user()->organization_id);
                })
                ->count();

            // Enrollments per month
            $enrollmentData[] = Enrollment::whereYear('created_at', $date->format('Y'))
                ->whereMonth('created_at', $date->format('m'))
                ->whereHas('course', function ($query) {
                    $query->whereHas('organization', function ($query) {
                        $query->where('id', auth()->user()->organization_id);
                    });
                })
                ->count();

            // Reviews per month
            $reviewData[] = Review::whereYear('created_at', $date->format('Y'))
                ->whereMonth('created_at', $date->format('m'))
                ->whereHas('course', function ($query) {
                    $query->whereHas('organization', function ($query) {
                        $query->where('id', auth()->user()->organization_id);
                    });
                })
                ->count();
        }

        return response()->json([
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Courses Created',
                    'data' => $courseData,
                    'borderColor' => '#4e73df',
                    'backgroundColor' => 'rgba(78,115,223,0.4)',
                    'fill' => true,
                    'tension' => 0.4,
                    'borderWidth' => 3
                ],
                [
                    'label' => 'Enrollments',
                    'data' => $enrollmentData,
                    'borderColor' => '#1cc88a',
                    'backgroundColor' => 'rgba(28,200,138,0.4)',
                    'fill' => true,
                    'tension' => 0.4,
                    'borderWidth' => 3
                ],
                [
                    'label' => 'Reviews',
                    'data' => $reviewData,
                    'borderColor' => '#f6c23e',
                    'backgroundColor' => 'rgba(246,194,62,0.4)',
                    'fill' => true,
                    'tension' => 0.4,
                    'borderWidth' => 3
                ],
            ]
        ]);
    }

    public function profile(User $user)
    {
        return view('organization.profile.index', compact('user'));
    }
}
