@include('partials.panel-nav', [
    'home' => route('admin.home'),
    'mark' => 'إ',
    'role' => 'الإدارة',
    'links' => [
        ['admin.home', 'اللوحة', 'admin.home'],
        ['admin.analytics', 'الأطراف', 'admin.analytics'],
    ],
])
