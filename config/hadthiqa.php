<?php

return [
    'tracking_fee' => 150,
    'instapay_handle' => 'had.thiqa@instapay',
    'support_phone' => '01150056226',
    'partner_name' => 'ليلى',

    'discount_codes' => [
        'THIQA10' => 10,
    ],

    'splits' => [
        ['id' => 'executor', 'label' => 'المنفذة', 'pct' => 45, 'tone' => 'mint'],
        ['id' => 'partner', 'label' => 'البارتنر', 'pct' => 30, 'tone' => 'peach'],
        ['id' => 'author', 'label' => 'بائعة الخدمة', 'pct' => 15, 'tone' => 'pink'],
        ['id' => 'platform', 'label' => 'المنصة', 'pct' => 10, 'tone' => 'lavender'],
    ],

    'speeds' => [
        'normal' => ['label' => 'عادي', 'mult' => 1, 'note' => 'ترتيب عادي'],
        'rush' => ['label' => 'مستعجل', 'mult' => 1.5, 'note' => 'أولوية · +50%'],
    ],

    'tiers' => [
        'beginner' => ['name' => 'مستوى ابتداء', 'detail' => 'مبدئي', 'tag' => 'مستوى 1', 'desc' => 'مهام واضحة وخطوات كاملة'],
        'pro' => ['name' => 'مستوى احتراف', 'detail' => 'متقدم', 'tag' => 'مستوى 2–3', 'desc' => 'مهام أكبر واعتماد أسرع'],
        'expert' => ['name' => 'مستوى خبرة', 'detail' => 'مستوى مميز', 'tag' => 'مستوى 4+', 'desc' => 'خبرة عملية في المجال'],
    ],

    'levels' => [
        ['level' => 1, 'min' => 0, 'name' => 'ابتداء', 'tier' => 'beginner'],
        ['level' => 2, 'min' => 400, 'name' => 'احتراف', 'tier' => 'pro'],
        ['level' => 3, 'min' => 900, 'name' => 'احتراف', 'tier' => 'pro'],
        ['level' => 4, 'min' => 1500, 'name' => 'خبرة', 'tier' => 'expert'],
        ['level' => 5, 'min' => 2500, 'name' => 'بارتنر', 'tier' => 'expert'],
    ],

    'worker_stages' => ['المهمة', 'خطوات الـ SOP', 'تسليم العمل', 'اعتماد البارتنر', 'تقييم العميل', 'نصيبك'],
    'client_stages' => ['الطلب', 'السعر والدفع', 'بدأ التنفيذ', 'التسليم', 'التقييم'],

    'help_topics' => [
        'سؤال عن خطوات العمل',
        'مشكلة في الدفع',
        'مشكلة في التسليم',
        'مهارة بانتظار الاعتماد',
    ],
];
