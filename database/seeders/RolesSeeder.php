<?php

namespace Database\Seeders;

use App\Models\Auth\Role;

/**
 * Создание ролей
 */
class RolesSeeder extends AbstractSeeder
{

    private $roles = [
        // полные права
        ['admin' => 'Администратор (по всему контексту)'],

        // права для работы со справочниками (управление справочниками принтеров, расходных материалов)
        ['editor-dictionary' => 'Редактор справочников (принтеров, расходных материалов)'],

        // права на добавление принтеров на местах
        ['editor-printer-workplace' => 'Редактор принтеров на местах'],

        // права на добавление расходных материалов (количества)
        ['add-consumables' => 'Добавление расходных материалов'],

        // права на вычитание расходного материала (количества)
        ['subtract-consumable' => 'Вычитание расходных материалов'],

        // права на согласование заказов
        ['order-approver' => 'Согласование заказов'],
    ];

    /**
     * Run the database seeds.
     * @return void
     */
    public function run(): void
    {
        $inserts = [];
        foreach ($this->roles as $role => $description) {
            $inserts[] = [
                'name' => $role,
                'description'=> $description,
                'created_at' => $this->getDbNowDate(), 
                'updated_at' => $this->getDbNowDate(),
            ];
        }

        Role::query()->insert($inserts);
    }
}
