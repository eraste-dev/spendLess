<?php

namespace App\Http\Controllers\WebAdmin;

use App\Enum\NotificationTypeEnum;
use App\Events\NotifyEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExamStoreRequest;
use App\Http\Requests\ExamUpdateRequest;
use App\Models\Course;
use App\Models\Exam;
use App\Repositories\CourseRepository;
use App\Repositories\ExamRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    public function selectCourse(Request $request)
    {
        $search = $request->cat_search ? strtolower($request->cat_search) : null;
        $user = auth()->user();
        $query = CourseRepository::query();

        // Search filter (applies for all)
        $query->when($search, function ($q) use ($search, $user) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhereHas('organization.user', fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->orWhereHas('instructor.user', fn($q) => $q->where('name', 'like', "%{$search}%"));
        });

        // Role/organization-specific filtering
        if ($user->is_org == 1 || $user->organization) {
            $query->where('organization_id', $user->organization?->id);
        } elseif ($user->hasRole('instructor') && $user->instructor) {
            $query->where('instructor_id', $user->instructor->id);
        } else {
            $query->whereNull('organization_id');
        }

        // Finalize query
        $courses = $query->latest('id')->paginate(10)->withQueryString();

        return view('exam.select_course', [
            'courses' => $courses,
        ]);
    }

    public function index(Request $request, Course $course)
    {
        $search = $request->cat_search ? strtolower($request->cat_search) : null;

        $exams = ExamRepository::query()->when($search, function ($query) use ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        })
            ->where('course_id', '=', $course->id)
            ->latest('id')
            ->paginate(8)->withQueryString();

        return view('exam.index', [
            'exams' => $exams,
            'course' => $course
        ]);
    }

    public function create(Course $course)
    {
        $user = auth()->user();
        $courses = CourseRepository::query()
            ->when($user->hasRole('instructor'), function ($query) use ($user) {
                $query->where('instructor_id', $user->instructor?->id);
            })
            ->latest('id')
            ->get();

        return view('exam.create', [
            'selectedCourse' => $course,
            'courses' => $courses,
        ]);
    }

    public function store(ExamStoreRequest $request)
    {

        $exam = ExamRepository::storeByRequest($request);

        NotifyEvent::dispatch(NotificationTypeEnum::NewExamFromCourse->value, $exam->course_id, [
            'course' => $exam->course,
        ]);

        return to_route('exam.index', ['course' => $exam->course_id])->with('success', 'Exam created');
    }

    public function edit(Exam $exam)
    {

        $user = auth()->user();
        $courses = CourseRepository::query()
            ->when($user->hasRole('instructor'), function ($query) use ($user) {
                $query->where('instructor_id', $user->instructor?->id);
            })
            ->latest('id')
            ->get();


        return view('exam.edit', [
            'exam' => $exam,
            'courses' => $courses,
        ]);
    }

    public function update(ExamUpdateRequest $request, Exam $exam)
    {
        ExamRepository::updateByRequest($request, $exam);

        return to_route('exam.index', ['course' => $exam->course_id])->withSuccess('Exam updated');
    }

    public function delete(Exam $exam)
    {
        $courseId = $exam->course_id;
        $exam->delete();

        return redirect()->route('exam.index', ['course' => $courseId])->withSuccess('Exam deleted');
    }
}
