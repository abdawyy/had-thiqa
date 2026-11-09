@include('partials.panel-nav', [
    'home' => route('worker.home'),
    'mark' => 'م',
    'role' => 'المنفذة',
    'links' => [
        ['worker.home', 'الرئيسية', 'worker.home'],
        ['worker.skills', 'مواهبي', 'worker.skills'],
        ['worker.projects', 'المشاريع', 'worker.projects,worker.orders.*'],
        ['worker.account', 'حسابي', 'worker.account'],
        ['worker.analytics', 'تحليل', 'worker.analytics'],
    ],
])
