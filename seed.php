<?php
require 'config.php';

foreach ([
    ['Admin', 'admin@demo.test', 'Admin#2026', 'admin'],
    ['Staff', 'staff@demo.test', 'Staff#2026', 'staff'],
    ['User Demo', 'user@demo.test', 'User#2026', 'user']
] as $a) {
    $c = $pdo->prepare('SELECT 1 FROM users WHERE email=?');
    $c->execute([$a[1]]);

    if (!$c->fetch()) {
        $pdo->prepare('INSERT INTO users(name,email,phone,password_hash,role,is_active) VALUES(?,?,?,?,?,1)')
            ->execute([
                $a[0],
                $a[1],
                '0800000000',
                password_hash($a[2], PASSWORD_DEFAULT),
                $a[3]
            ]);
    }
}

if (!$pdo->query('SELECT COUNT(*) FROM units')->fetchColumn()) {
    foreach ([
        ['Dome Premium', 'dome', 'Dome dengan kasur king, AC, dan kamar mandi dalam.', 2, 5, 850000],
        ['Cabin Kayu', 'cabin', 'Cabin hangat dengan teras dan pemanas.', 4, 3, 1200000],
        ['Treehouse', 'treehouse', 'Rumah pohon dengan pemandangan hutan.', 2, 2, 1500000]
    ] as $x) {
        $pdo->prepare('INSERT INTO units(name,category,description,capacity,total_units,price_per_night,is_active) VALUES(?,?,?,?,?,?,1)')
            ->execute($x);
    }
}

echo 'Seed selesai. HAPUS file seed.php sekarang.';
