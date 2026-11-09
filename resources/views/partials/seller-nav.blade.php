@include('partials.panel-nav', [
    'home' => route('seller.home'),
    'mark' => 'ب',
    'role' => 'بائعة الخدمة',
    'links' => [
        ['seller.home', 'خدماتي', 'seller.home,seller.gigs.*,seller.orders.*'],
        ['seller.analytics', 'تحليل', 'seller.analytics'],
    ],
])
