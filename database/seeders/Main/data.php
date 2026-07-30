<?php

$roles = [
    'admin' => 'Администратор (по всему контексту)',
    'editor-dictionary' => 'Редактор справочников (принтеров, расходных материалов)',
    'editor-printer-workplace' => 'Редактор принтеров на местах',
    'add-consumables' => 'Добавление расходных материалов',
    'subtract-consumable' => 'Вычитание расходных материалов',
    'order-approver' => 'Согласование заказов',
];

$users = [
    [
        'email' => 'admin@example.com', 'name' => 'admin', 'fio' => 'Администратор',
        'department' => 'Отдел ИТ', 'post' => 'Ведущий специалист', 'telephone' => '8(999)99-99-999',
        'roles' => ['admin'],
    ],
];

$manufacturers = [
    'HP',
    'Canon',
    'Epson',
    'Brother',
    'Kyocera',
    'Xerox',
    'Samsung',
    'Pantum',
    'LG',
    'Ricoh',
    'Lexmark',
];

return [
    'roles' => $roles,
    'users' => $users,
    'manufacturers' => $manufacturers,
];
