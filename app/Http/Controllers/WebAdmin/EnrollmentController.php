<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Repositories\EnrollmentRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->cat_search ? strtolower($request->cat_search) : null;

        $enrollmentsQuery = EnrollmentRepository::query();

        if (auth()->user()->hasRole('admin') || auth()->user()->is_admin) {
            // No additional constraints for admin
        } elseif (auth()->user()->hasRole('organization') || auth()->user()->is_org) {
            $enrollmentsQuery->whereHas('course', function ($query) {
                $query->whereHas('organization', function ($query) {
                    $query->whereHas('user', function ($query) {
                        $query->where('organization_id', auth()->user()->organization?->id);
                    });
                });
            });
        } else {
            // Instructor case
            $enrollmentsQuery->whereHas('course', function ($query) {
                $query->whereHas('instructor', function ($query) {
                    $query->whereHas('user', function ($query) {
                        $query->where('user_id', auth()->user()->id);
                    });
                });
            });
        }

        // Shared search + pagination logic
        $enrollments = $enrollmentsQuery
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('course', function ($q) use ($search) {
                        $q->where('title', 'like', '%' . $search . '%');
                    })->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
                });
            })
            ->withTrashed()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('enrollment.index', [
            'enrollments' => $enrollments,
        ]);
    }

    public function delete(Enrollment $enrollment)
    {
        $enrollment->course_progress = 0.00;
        $enrollment->save();
        $enrollment->delete();
        return redirect()->route('enrollment.index')->withSuccess('Enrollment Canceled');
    }
    public function suspended(Enrollment $enrollment)
    {
        $enrollment->delete();
        return redirect()->route('enrollment.index')->withSuccess('Enrollment Successfully Suspended');
    }

    public function restore(int $id)
    {
        EnrollmentRepository::query()->onlyTrashed()->find($id)->restore();

        return redirect()->route('enrollment.index')->withSuccess('Enrollment restored');
    }

    public function nameUpdate(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'student_name' => 'required|string|max:255',
        ]);

        $users = EnrollmentRepository::query()
            ->where('user_id', $enrollment->user_id)->get();

        foreach ($users as $user) {
            $user->update([
                'certificate_user_name' => $request->student_name,
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('enrollment.index')->withSuccess('Student Certificate Name updated');
    }

    public function generatePdf(Request $request)
    {
        $page_num = $request->query('page', 1);

        if (auth()->user()->hasRole('admin') || auth()->user()->is_admin) {
            $enrollments = EnrollmentRepository::query()
                ->withTrashed()
                ->latest('id')
                ->paginate(15, ['*'], 'page', $page_num);
        } else {
            $enrollments = EnrollmentRepository::query()
                ->withTrashed()
                ->latest('id')
                ->paginate(15, ['*'], 'page', $page_num);
        }

        $reportsQuery = $enrollments;


        $pdf = Pdf::loadView('pdf.enrollment', [
            'enrollments' => $reportsQuery->items(),
            'page_num' => $page_num,
        ]);

        return $pdf->stream("report-{$page_num}" . ".pdf");
    }

    public function exportCSV(Request $request)
    {
        $page_num = $request->query('page', 1);

        if (auth()->user()->hasRole('admin') || auth()->user()->is_admin) {
            $enrollments = EnrollmentRepository::query()
                ->withTrashed()
                ->latest('id')
                ->paginate(15, ['*'], 'page', $page_num);
        } else {
            $enrollments = EnrollmentRepository::query()
                ->withTrashed()
                ->latest('id')
                ->paginate(15, ['*'], 'page', $page_num);
        }


        // Generate CSV content
        $csvContent = "SL,Enroll ID,Student,Course Title,Progress\n";
        foreach ($enrollments as $index => $enrollment) {
            $csvContent .= implode(',', [
                $index + 1,
                $enrollment->id,
                $enrollment->user?->name ?? 'N/A',
                $enrollment->course?->title ?? 'N/A',
                $enrollment->course_progress
            ]) . "\n";
        }

        // Send response as a CSV file
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="enrollments-report.csv"');
    }
}
