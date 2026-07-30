<?php

use App\Models\Consumable\ConsumableTypesEnum;

$serialNumber = static fn(int $length = 16) => Str::upper(Str::random($length));
$location = static fn() => random_int(100, 999);
$randomArrayKey = static fn(array $array, string $key) => Arr::random($array)[$key];
$randomArrayItems = static fn(callable $item, int $max) => array_map($item, range(1, max(1, $max)));
$regionCode = config('app.region_code', '00');


$organizations = [
    "{$regionCode}00" => ['code' => "{$regionCode}00", 'name' => 'Управление', 'parent' => null],
];

$organizationsCall = function (array $organizations, string $regionCode, int $max) {
    for ($i = 1; $i <= $max; $i++) {
        $parent = null;
        if (random_int(0, 2) === 1) {
            $parent = \Arr::random(array_filter($organizations, static fn ($item) => $item['parent'] === null))['code'];
        }
        $code = \sprintf("%s%02d", $regionCode, $i);
        $organizations[$code] = [
            'code' => $code,
            'parent' => $parent,
            'name' => "Инспекция № {$i}",
        ];
    }

    return $organizations;
};

$organizations = $organizationsCall($organizations, $regionCode, 15);

$users = (function () use ($organizations) {
    $users = [
        [
            'email' => 'admin@example.com', 'name' => 'admin', 'fio' => 'Администратор',
            'department' => 'Отдел ИТ', 'post' => 'Ведущий специалист', 'telephone' => '8(999)99-99-999',
            'roles' => ['admin'],
        ],
        [
            'email' => 'user@example.com', 'name' => 'user', 'fio' => 'Пушкин Александр Сергеевич',
            'department' => 'Отдел ИТ', 'post' => 'Специалист', 'telephone' => '8(999)99-99-999',
            'roles' => [],
        ],
        [
            'email' => 'user2@example.com', 'name' => 'user2', 'fio' => 'Иванов Иван Иванович',
            'department' => 'Бухгалтерия', 'post' => 'Начальник', 'telephone' => '8(999)99-99-999',
            'roles' => ['editor-dictionary', 'editor-printer-workplace', 'add-consumables'],
        ],
        [
            'email' => 'user3@example.com', 'name' => 'user3', 'fio' => 'Петров Петр Петрович',
            'department' => 'Общий отдел', 'post' => 'Начальник', 'telephone' => '8(999)99-99-999',
            'roles' => ['subtract-consumable'],
        ],
    ];

    foreach ($users as &$user) {
        $organization = \Arr::random($organizations);
        $user['org_code'] = $organization['code'];
        $user['company'] = $organization['name'];
    }

    return $users;
})();

$printers = [
    'HP LaserJet M111w' => [
        'vendor' => 'HP', 'model' => 'LaserJet M111w', 'is_color_print' => false,
        'consumables' => [
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'HP 150A (W1500A)', 'color' => 'black', 'description' => null],
            ['type' => ConsumableTypesEnum::other->name, 'name' => 'CET341029', 'color' => null, 'description' => 'Ролики захвата бумаги, в сборе',]
        ],
    ],
    'Brother HL-L2300D' => [
        'vendor' => 'Brother', 'model' => 'HL-L2300D', 'is_color_print' => false,
        'consumables' => [
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'TN-2335', 'color' => 'black', 'description' => null],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'TN-2375', 'color' => 'black', 'description' => null],
            ['type' => ConsumableTypesEnum::drum->name, 'name' => 'DR-2335', 'color' => null, 'description' => null],
            ['type' => ConsumableTypesEnum::other->name, 'name' => 'LY2144001', 'color' => null, 'description' => 'Электромагнитная муфта',],
        ],
    ],
    'Pantum P2207' => [
        'vendor' => 'Pantum', 'model' => 'P2207', 'is_color_print' => false,
        'consumables' => [
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'PC-211EV', 'color' => 'black', 'description' => null],
            ['type' => ConsumableTypesEnum::other->name, 'name' => 'PC-211RB', 'color' => 'black', 'description' => 'Тонер'],
        ],
    ],
    'Epson L121' => [
        'vendor' => 'Epson', 'model' => 'L121', 'is_color_print' => true,
        'consumables' => [
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Epson 664 Black', 'color' => 'black', 'description' => 'C13T66414A'],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Epson 664 Cyan', 'color' => 'blue', 'description' => 'C13T66424A'],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Epson 664 Magenta', 'color' => 'magenta', 'description' => 'C13T66434A'],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Epson 664 Yellow', 'color' => 'yellow', 'description' => 'C13T66444A'],
            ['type' => ConsumableTypesEnum::other->name, 'name' => '1469197', 'color' => null, 'description' => 'Контейнер для сбора отработавших чернил (абсорбер) принтера',]
        ],
    ],
    'Canon PIXMA TS3440' => [
        'vendor' => 'Canon', 'model' => 'PIXMA TS3440', 'is_color_print' => true,
        'consumables' => [
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'PG-445', 'color' => 'black', 'description' => '8283B001'],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'PG-445XL', 'color' => 'black', 'description' => '8282B001'],
            ['type' => ConsumableTypesEnum::other->name, 'name' => 'Ролик захвата бумаги', 'color' => null, 'description' => null],
        ],
    ],
    'HP LaserJet Pro MFP M28w' => [
        'vendor' => 'HP', 'model' => 'LaserJet Pro MFP M28w', 'is_color_print' => false,
        'consumables' => [
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'HP 44A', 'color' => 'black', 'description' => 'CF244A'],
            ['type' => ConsumableTypesEnum::other->name, 'name' => 'CET341029', 'color' => null, 'description' => 'Ролики захвата бумаги, в сборе'],
        ],
    ],
    'Pantum M6507W' => [
        'vendor' => 'Pantum', 'model' => 'M6507W', 'is_color_print' => false,
        'consumables' => [
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'PC-211EV', 'color' => 'black', 'description' => 'с чипом'],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'PC-212EV', 'color' => 'black', 'description' => 'с чипом'],
            ['type' => ConsumableTypesEnum::other->name, 'name' => '301022060001', 'color' => null, 'description' => 'Ролик захвата бумаги'],
        ],
    ],
    'Brother DCP-L2520DWR' => [
        'vendor' => 'Brother', 'model' => 'DCP-L2520DWR', 'is_color_print' => false,
        'consumables' => [
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'TN-2335', 'color' => 'black', 'description' => null],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'TN-2375', 'color' => 'black', 'description' => null],
            ['type' => ConsumableTypesEnum::drum, 'name' => 'DR-2335', 'color' => null, 'description' => null],
            ['type' => ConsumableTypesEnum::other->name, 'name' => 'LY2144001', 'color' => null, 'description' => 'Электромагнитная муфта',],
        ],
    ],
    'Epson EcoTank L3250' => [
        'vendor' => 'Epson', 'model' => 'EcoTank L3250', 'is_color_print' => true,
        'consumables' => [
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Epson 103 Black', 'color' => 'black', 'description' => 'C13T00S14A'],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Epson 103 Cyan', 'color' => 'blue', 'description' => 'C13T00S24A'],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Epson 103 Magenta', 'color' => 'magenta', 'description' => 'C13T00S34A'],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Epson 103 Yellow', 'color' => 'yellow', 'description' => 'C13T00S44A'],
            ['type' => ConsumableTypesEnum::other->name, 'name' => 'EPSON Абсорбер', 'color' => null, 'description' => 'Памперс, поглотитель чернил',],
        ],
    ],
    'Canon PIXMA G3411' => [
        'vendor' => 'Canon', 'model' => 'PIXMA G3411', 'is_color_print' => true,
        'consumables' => [
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'GI-490 PGBK', 'color' => 'black', 'description' => '0663C001'],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'GI-490 C', 'color' => 'blue', 'description' => '0664C001'],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'GI-490 M', 'color' => 'magenta', 'description' => '0665C001'],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'GI-490 Y', 'color' => 'yellow', 'description' => '0666C001'],
            ['type' => ConsumableTypesEnum::other->name, 'name' => 'Canon QY6-8018, QY6-8006', 'color' => null, 'description' => 'Печатающая головка цветная',],
        ],
    ],
    'Epson L8050' => [
        'vendor' => 'Epson', 'model' => 'L8050', 'is_color_print' => true,
        'consumables' => [
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Epson 108 Black', 'color' => 'black', 'description' => 'C13T09C14A'],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Epson 108 Cyan', 'color' => 'blue', 'description' => 'C13T09C24A'],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Epson 108 Magenta', 'color' => 'magenta', 'description' => 'C13T09C34A'],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Epson 108 Yellow', 'color' => 'yellow', 'description' => 'C13T09C44A'],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Epson 108 Light Cyan', 'color' => 'blue', 'description' => 'C13T09C54A'],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Epson 108 Light Magenta', 'color' => 'magenta', 'description' => 'C13T09C64A'],
            ['type' => ConsumableTypesEnum::other->name, 'name' => 'C9345', 'color' => null, 'description' => 'Абсорбер чернил',],
        ],
    ],
];


foreach ($printers as &$printer) {
    // добавление локаций (для PrinterWorkplace)
    $printer['workplaces'] = array_map(static fn($item) => [
        'org_code' => $item['code'],
        'location' => $location(),
        'serial_number' => $serialNumber(),
        'inventory_number' => $serialNumber(20),
    ], $organizations);

}

return [
    'users' => $users,
    'organizations' => $organizations,
    'printers' => $printers,
];
