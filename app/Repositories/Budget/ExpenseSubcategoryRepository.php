<?php

namespace App\Repositories\Budget;

use App\Models\Budget\ExpenseSubcategory;
use App\Repositories\BaseRepository;

class ExpenseSubcategoryRepository extends BaseRepository
{
    public function __construct(ExpenseSubcategory $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all subcategories for a specific category.
     */
    public function getAllForCategory(int $categoryId)
    {
        return $this->model
            ->where('expense_category_id', $categoryId)
            ->ordered()
            ->get();
    }

    /**
     * Get only active subcategories for a specific category.
     */
    public function getActiveForCategory(int $categoryId)
    {
        return $this->model
            ->where('expense_category_id', $categoryId)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Create a new subcategory for a category.
     */
    public function createForCategory(int $categoryId, array $data)
    {
        $data['expense_category_id'] = $categoryId;
        return $this->create($data);
    }

    /**
     * Update subcategory order for a category.
     */
    public function updateOrder(int $categoryId, array $orderData)
    {
        foreach ($orderData as $item) {
            $this->model
                ->where('id', $item['id'])
                ->where('expense_category_id', $categoryId)
                ->update(['order' => $item['order']]);
        }
        return true;
    }

    /**
     * Toggle subcategory active status.
     */
    public function toggleActive(int $id, int $categoryId)
    {
        $subcategory = $this->model
            ->where('id', $id)
            ->where('expense_category_id', $categoryId)
            ->first();

        if ($subcategory) {
            $subcategory->is_active = !$subcategory->is_active;
            $subcategory->save();
            return $subcategory;
        }

        return null;
    }

    /**
     * Get subcategory with its category.
     */
    public function findWithCategory(int $id)
    {
        return $this->model
            ->with('category')
            ->find($id);
    }
}
