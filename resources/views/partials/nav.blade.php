@include('partials.panel-nav', [
    'home' => route('worker.home'),
    'mark' => 'م',
    'role' => 'المنفذة',
    'links' => [
        ['worker.home', 'الرئيسية', 'worker.home'],
        ['worker.skills', 'مهاراتي', 'worker.skills'],
        ['worker.projects', 'المشاريع', 'worker.projects,worker.orders.*'],
        ['worker.about', 'التعريفية', 'worker.about'],
        ['worker.account', 'حسابي', 'worker.account'],
    ],
])
