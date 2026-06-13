<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tracked public page routes
    |--------------------------------------------------------------------------
    |
    | GET requests to these named routes will record a page_view event.
    |
    */
    'tracked_page_routes' => [
        'home',
        'workshops.index',
        'workshops.show',
        'community.index',
        'community.show',
        'members.show',
        'login',
        'register',
        'community.create',
        'community.my-posts',
        'community.saved',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route labels (dashboard)
    |--------------------------------------------------------------------------
    */
    'route_labels' => [
        'home' => 'Trang chủ',
        'workshops.index' => 'Danh sách workshop',
        'workshops.show' => 'Chi tiết workshop',
        'community.index' => 'Feed cộng đồng',
        'community.show' => 'Chi tiết bài viết',
        'members.show' => 'Trang tác giả',
        'login' => 'Đăng nhập',
        'register' => 'Đăng ký',
        'community.create' => 'Viết bài mới',
        'community.my-posts' => 'Bài của tôi',
        'community.saved' => 'Bài đã lưu',
    ],

];
