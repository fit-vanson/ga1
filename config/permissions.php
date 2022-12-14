<?php
return [
    'access' =>[

        'user-index'  =>'index_user',
        'user-show'  =>'show_user',
        'user-add'  =>'add_user',
        'user-edit'  =>'edit_user',
        'user-update'  =>'edit_user',
        'user-delete'  =>'delete_user',

        'vai_tro-index'  =>'index_vai_tro',
        'vai_tro-show'  =>'show_vai_tro',
        'vai_tro-add'  =>'add_vai_tro',
        'vai_tro-edit'  =>'edit_vai_tro',
        'vai_tro-update'  =>'edit_vai_tro',
        'vai_tro-delete'  =>'delete_vai_tro',

        'phan_quyen-index'  =>'index_phan_quyen',
        'phan_quyen-show'  =>'show_phan_quyen',
        'phan_quyen-add'  =>'add_phan_quyen',
        'phan_quyen-edit'  =>'edit_phan_quyen',
        'phan_quyen-update'  =>'edit_phan_quyen',
        'phan_quyen-delete'  =>'delete_phan_quyen',

        'admod-index'  =>'index_admod',
        'admod-show'  =>'show_admod',
        'admod-add'  =>'add_admod',
        'admod-edit'  =>'edit_admod',
        'admod-update'  =>'edit_admod',
        'admod-delete'  =>'delete_admod',

    ],


    'table-module' => [
        'AdMod',
        'User','Vai trò', 'Phân quyền',

    ],
    'module-child' =>[
        'Index','Show','Add','Edit','Delete'
    ]
];
