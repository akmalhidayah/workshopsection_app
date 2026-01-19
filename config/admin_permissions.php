<?php

return [
    // List of permission keys used for admin menus/routes.
    'permissions' => [
        'admin.dashboard' => 'Dashboard',
        'admin.order.jasa' => 'Order Pekerjaan Jasa',
        'admin.order.kawatlas' => 'Order Kawat Las',
        'admin.order.bengkel' => 'Order Pekerjaan Bengkel',
        'admin.inputhpp' => 'Create HPP',
        'admin.verifikasianggaran' => 'Verifikasi Anggaran',
        'admin.purchaseorder' => 'Purchase Order',
        'admin.lhpp' => 'LHPP',
        'admin.lpj' => 'LPJ/PPL',
        'admin.garansi' => 'Garansi',
        'admin.updateoa' => 'Kuota Anggaran & OA',
        'admin.jenis_kawat_las' => 'Stock Kawat Las',
        'admin.users' => 'User Panel',
        'admin.uploadinfo' => 'Upload Informasi',
        'admin.unit_work' => 'Unit Kerja',
        'admin.access_control' => 'Access Control',
    ],

    // Map route name patterns to permission keys (admin area).
    'route_map' => [
        'admin.dashboard' => ['admin.dashboard'],
        'admin.inputhpp' => ['admin.inputhpp.*'],
        'admin.verifikasianggaran' => ['admin.verifikasianggaran.*'],
        'admin.purchaseorder' => ['admin.purchaseorder*'],
        'admin.lhpp' => ['admin.lhpp.*'],
        'admin.lpj' => ['admin.lpj', 'lpj.*'],
        'admin.garansi' => ['admin.garansi.*'],
        'admin.updateoa' => ['admin.updateoa', 'admin.storeOA'],
        'admin.jenis_kawat_las' => ['admin.jenis-kawat-las.*', 'admin.cost-element.*'],
        'admin.users' => ['admin.users.*'],
        'admin.uploadinfo' => ['admin.uploadinfo', 'admin.uploadinfo.*'],
        'admin.unit_work' => ['admin.unit_work.*'],
        'admin.order.bengkel' => ['admin.orderbengkel.*'],
        'admin.access_control' => ['admin.access-control.*'],
    ],
];
