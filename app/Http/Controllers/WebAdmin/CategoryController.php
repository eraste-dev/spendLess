<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $authenticatedUser = auth()->user();
        $search = $request->cat_search ? strtolower($request->cat_search) : null;
        $query = CategoryRepository::query()
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            });

        // Apply organization condition
        if ($authenticatedUser->is_org == 1 || $authenticatedUser->organization) {
            $orgId = $authenticatedUser->organization?->id;
            $query->where('organization_id', $orgId);
        } elseif ($authenticatedUser->hasRole('instructor') && $authenticatedUser->instructor && $authenticatedUser->instructor->organization) {
            $orgId = $authenticatedUser->instructor->organization?->id;
            $query->where('organization_id', $orgId);
        } else {
            $query->when($authenticatedUser->hasRole('instructor'), function ($query) use ($authenticatedUser) {
                $query->where('organization_id', $authenticatedUser->instructor?->organization_id);
            })->whereNull('organization_id');
        }

        $categories = $query
            ->withTrashed()
            ->orderBy('display_order')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('category.index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return view('category.create');
    }

    public function store(CategoryStoreRequest $request)
    {
        CategoryRepository::storeByRequest($request);
        return to_route('category.index')->with('success', 'Category created');
    }

    public function edit(Category $category)
    {
        return view('category.edit', [
            'category' => $category,
        ]);
    }

    public function update(CategoryUpdateRequest $request, Category $category)
    {
        CategoryRepository::updateByRequest($request, $category);

        return to_route('category.index')->withSuccess('Category updated');
    }

    public function delete(Category $category)
    {
        $category?->delete();

        return redirect()->route('category.index')->withSuccess('Category deleted');
    }

    public function restore(int $id)
    {
        CategoryRepository::query()->onlyTrashed()->find($id)->restore();

        return redirect()->route('category.index')->withSuccess('Category restored');
    }


    public function sort(Request $request)
    {
        $sortedCategories = $request->input('sortedCategories');

        foreach ($sortedCategories as $categoryData) {
            Category::where('id', $categoryData['id'])
                ->update(['display_order' => $categoryData['display_order']]);
        }

        return response()->json(['success' => true]);
    }
}
