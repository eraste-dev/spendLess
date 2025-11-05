<?php

namespace App\Repositories\Budget;

use App\Models\Budget\ExpenseCategory;
use App\Repositories\BaseRepository;

class ExpenseCategoryRepository extends BaseRepository
{
    public function __construct(ExpenseCategory $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all expense categories with subcategories for a specific user.
     */
    public function getAllForUser(int $userId, bool $withSubcategories = false)
    {
        $query = $this->model
            ->where('user_id', $userId)
            ->ordered();

        if ($withSubcategories) {
            $query->with(['subcategories' => function ($q) {
                $q->active()->ordered();
            }]);
        }

        return $query->get();
    }

    /**
     * Get only active expense categories for a specific user.
     */
    public function getActiveForUser(int $userId, bool $withSubcategories = false)
    {
        $query = $this->model
            ->where('user_id', $userId)
            ->active()
            ->ordered();

        if ($withSubcategories) {
            $query->with(['subcategories' => function ($q) {
                $q->active()->ordered();
            }]);
        }

        return $query->get();
    }

    /**
     * Get recurring categories for a specific user.
     */
    public function getRecurringForUser(int $userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->recurring()
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Create a new expense category for a user.
     */
    public function createForUser(int $userId, array $data)
    {
        $data['user_id'] = $userId;
        return $this->create($data);
    }

    /**
     * Update category order for a user.
     */
    public function updateOrder(int $userId, array $orderData)
    {
        foreach ($orderData as $item) {
            $this->model
                ->where('id', $item['id'])
                ->where('user_id', $userId)
                ->update(['order' => $item['order']]);
        }
        return true;
    }

    /**
     * Toggle category active status.
     */
    public function toggleActive(int $id, int $userId)
    {
        $category = $this->model
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($category) {
            $category->is_active = !$category->is_active;
            $category->save();
            return $category;
        }

        return null;
    }

    /**
     * Find a category with subcategories for a user.
     */
    public function findWithSubcategoriesForUser(int $id, int $userId)
    {
        return $this->model
            ->with(['subcategories' => function ($q) {
                $q->ordered();
            }])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }
}
