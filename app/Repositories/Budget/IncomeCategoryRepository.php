<?php

namespace App\Repositories\Budget;

use App\Models\Budget\IncomeCategory;
use App\Repositories\BaseRepository;

class IncomeCategoryRepository extends BaseRepository
{
    public function __construct(IncomeCategory $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all income categories for a specific user.
     */
    public function getAllForUser(int $userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->ordered()
            ->get();
    }

    /**
     * Get only active income categories for a specific user.
     */
    public function getActiveForUser(int $userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Create a new income category for a user.
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
}
