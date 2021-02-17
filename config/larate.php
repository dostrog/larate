<?php

return [
    'default_base_currency' => 'RUB',
    'service' => [
        'RUB' => Dostrog\Larate\Services\RussianCentralBank::class,
        'UAH' => Dostrog\Larate\Services\NationalBankOfUkraine::class,
    ],
];
