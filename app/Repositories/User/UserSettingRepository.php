<?php

namespace App\Repositories\User;

use App\Models\User\UserSetting;
use App\Repositories\BaseRepository;

class UserSettingRepository extends BaseRepository
{
    public function __construct(UserSetting $model)
    {
        parent::__construct($model);
    }

    /**
     * Récupérer ou créer les paramètres d'un utilisateur
     */
    public function getOrCreateForUser(int $userId): mixed
    {
        return $this->model->firstOrCreate(
            ['user_id' => $userId],
            [
                'currency' => 'XOF',
                'currency_symbol' => 'FCFA',
                'date_format' => 'd/m/Y',
                'locale' => 'fr_FR',
            ]
        );
    }

    /**
     * Mettre à jour les paramètres d'un utilisateur
     */
    public function updateForUser(int $userId, array $data): mixed
    {
        $settings = $this->getOrCreateForUser($userId);
        $settings->update($data);
        return $settings->fresh();
    }

    /**
     * Récupérer les devises disponibles
     */
    public function getAvailableCurrencies(): array
    {
        return UserSetting::availableCurrencies();
    }
}
