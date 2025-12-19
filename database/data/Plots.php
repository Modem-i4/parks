<?php

namespace Database\Data;

$R = fn (int $from, int $to) => array_map('strval', range($from, $to));

return [
  'hotkevych_park' => [
    'I'   => $R(1, 5),
    'II'  => $R(1, 7),
    'III' => $R(1, 6),
    'IV'  => $R(2, 4),
    'V'   => $R(1, 5),
  ],

  'liberators_park' => [
    'I'   => $R(1, 10),
    'II'  => $R(1, 8),
    'III' => $R(2, 6),
  ],

  'shevchenko_park' => [
    'I'   => $R(1, 5),
    'II'  => $R(1, 22),
    'III' => $R(1, 20),
    'IV'  => $R(1, 11),
    'V'   => $R(1, 7),
    'VI'  => $R(1, 11),
  ],
];
