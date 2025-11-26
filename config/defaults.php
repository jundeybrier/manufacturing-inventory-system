<?php

return [
    'receipt_type' => 'booklet_form51',

    'page' => [
        'continuous_form51' => ['width' => '217.5mm', 'height' => '279mm'],
        'booklet_form51'   => ['width' => '100mm', 'height' => '205mm'],
    ],

    'office_name' => ['font' => 10, 'x' => 25, 'y' => 30],
    'date'        => ['font' => 11, 'x' => 8, 'y' => 49],
    'payor_info'  => ['font' => 11, 'x' => 7, 'y' => 61],

    'particulars' => [
        'font'    => 10,
        'x'       => 6,
        'x_amount'=> 88,   // NEW COLUMN FOR AMOUNT ALIGNMENT
        'y'       => 87,
        'spacing' => 6,
    ],

    'total' => ['font' => 12, 'x' => 77, 'y' => 137],

    'amount_words' => ['font' => 10, 'x' => 7, 'y' => 152],

    'cashier_name' => ['font' => 10, 'x' => 24, 'y' => 186],
];
