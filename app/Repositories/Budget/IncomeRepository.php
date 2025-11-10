<?php

namespace App\Repositories\Budget;

use App\Models\Budget\Income;
use App\Repositories\BaseRepository;

class IncomeRepository extends BaseRepository
{
    public function __construct(Income $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all incomes for a specific user.
     */
    public function getAllForUser(int $userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->ordered()
            ->get();
    }

    /**
     * Get only active incomes for a specific user.
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
     * Create a new income for a user.
     */
    public function createForUser(int $userId, array $data)
    {
        $data['user_id'] = $userId;
        return $this->create($data);
    }

    /**
     * Update income order for a user.
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
     * Toggle income active status.
     */
    public function toggleActive(int $id, int $userId)
    {
        $income = $this->model
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($income) {
            $income->is_active = !$income->is_active;
            $income->save();
            return $income;
        }

        return null;
    }
}
