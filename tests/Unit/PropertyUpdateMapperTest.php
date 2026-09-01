<?php

use App\Support\At_cl\PropertyUpdateMapper;

test('it preserves existing fields when a partial payload omits them', function () {
    $updates = PropertyUpdateMapper::map(
        ['reel_v' => '2026-08-20'],
        [
            'estado_venta' => 'id_estado_venta',
            'reel_v' => 'reel_v',
            'web_v' => 'web_v',
        ]
    );

    expect($updates)->toBe([
        'reel_v' => '2026-08-20',
    ]);
});

test('it retains explicitly supplied null values so users can clear a field', function () {
    $updates = PropertyUpdateMapper::map(
        ['reel_a' => null],
        ['reel_a' => 'reel_a']
    );

    expect($updates)->toBe([
        'reel_a' => null,
    ]);
});
