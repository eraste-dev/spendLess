<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->cat_search ? strtolower($request->cat_search) : null;
        $reviewQuery = ReviewRepository::query();

        if (auth()->user()->hasRole('instructor') || auth()->user()->instructor) {
            // Instructor case
            $reviewQuery->whereHas('course', function ($query) {
                $query->whereHas('instructor', function ($query) {
                    $query->whereHas('user', function ($query) {
                        $query->where('user_id', auth()->user()->id);
                    });
                });
            });
        } else if (auth()->user()->organization || auth()->user()->hasRole('organization') || auth()->user()->is_org) {
            // Organization case
            $reviewQuery->whereHas('course', function ($query) {
                $query->whereHas('organization', function ($query) {
                    $query->whereHas('user', function ($query) {
                        $query->where('user_id', auth()->user()->id);
                    });
                });
            });
        }

        $reviews = $reviewQuery->when($search, function ($query) use ($search) {
            $query->where('comment', 'like', '%' . $search . '%')
                ->OrwhereHas('course', function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%');
                })
                ->OrwhereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                });
        })
            ->withTrashed()
            ->latest('id')
            ->paginate(20)->withQueryString();

        return view('review.index', [
            'reviews' => $reviews,
        ]);
    }

    public function delete(Review $review)
    {
        $review->delete();
        return redirect()->route('review.index')->withSuccess('Review removed');
    }
}
