<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Support\SopLibrary;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $sara = $this->person([
            'name' => 'سارة',
            'email' => 'sara@hadthiqa.test',
            'letter' => 'س',
            'role' => 'worker',
            'tier' => 'pro',
            'level' => 3,
            'level_name' => 'احتراف',
            'points' => 1240,
            'rating' => 4.9,
            'jobs_text' => '32 مهمة دهانات',
            'balance' => 3480,
            'withdrawable' => 1950,
            'pending' => 1530,
            'jobs_done' => 23,
            'jobs_clean' => 18,
            'speed_badge' => true,
            'streak' => 4,
            'referral_code' => 'THIQA10',
        ]);

        $this->person([
            'name' => 'منى فتحي',
            'email' => 'mona@hadthiqa.test',
            'letter' => 'م',
            'role' => 'client',
            'tier' => 'pro',
            'level' => 1,
            'level_name' => 'عميل',
        ]);

        $this->person([
            'name' => 'إدارة المنصة',
            'email' => 'admin@hadthiqa.test',
            'letter' => 'إ',
            'role' => 'admin',
            'tier' => 'expert',
            'level' => 5,
            'level_name' => 'إدارة',
        ]);

        $seller = $this->person([
            'name' => 'ندى',
            'email' => 'seller@hadthiqa.test',
            'letter' => 'ن',
            'role' => 'seller',
            'tier' => 'pro',
            'level' => 1,
            'level_name' => 'بائعة',
        ]);

        $workers = [
            ['منة', 'م', 'beginner', 1, 'ابتداء', 4.7, '12 مهمة دهانات', true, 'menna@hadthiqa.test'],
            ['هاجر', 'ه', 'beginner', 1, 'ابتداء', 4.8, '21 مهمة دهانات', true, 'hagar@hadthiqa.test'],
            ['ياسمين', 'ي', 'beginner', 1, 'ابتداء', 4.6, '9 مهام دهانات', false, 'yasmin@hadthiqa.test'],
            ['هبة', 'ه', 'pro', 3, 'احتراف', 4.8, '21 مهمة', false, 'heba@hadthiqa.test'],
            ['نور', 'ن', 'pro', 2, 'احتراف', null, 'اترفعت حديثاً', true, 'nour@hadthiqa.test'],
            ['هدى', 'ه', 'expert', 4, 'خبرة', 5.0, '61 مهمة', false, 'hoda@hadthiqa.test'],
            ['مروة', 'م', 'expert', 4, 'خبرة', 4.9, '40 مهمة', false, 'marwa@hadthiqa.test'],
        ];

        foreach ($workers as [$name, $letter, $tier, $level, $levelName, $rating, $jobs, $isNew, $email]) {
            $this->person([
                'name' => $name,
                'email' => $email,
                'letter' => $letter,
                'role' => 'worker',
                'tier' => $tier,
                'level' => $level,
                'level_name' => $levelName,
                'points' => $level * 400,
                'rating' => $rating,
                'jobs_text' => $jobs,
                'is_new' => $isNew,
                'is_available' => true,
            ]);
        }

        Service::create([
            'seller_id' => $seller->id,
            'slug' => 'flowers',
            'name' => 'تنسيق باقة',
            'sample' => 'باقة صغيرة للمكتب، ألوان هادية',
            'xp' => 80,
            'price' => 250,
            'duration' => '40 دقيقة',
            'min_level' => 1,
            'available' => true,
        ]);

        $sara->skills()->createMany([
            ['name' => 'استشارة دهانات', 'level_label' => 'مستوى احتراف', 'progress' => 100, 'status' => 'approved'],
            ['name' => 'تقسيم مطبخ', 'level_label' => 'مستوى احتراف', 'progress' => 70, 'status' => 'approved'],
            ['name' => 'دراسة جدوى مبدئية', 'level_label' => 'قيد التدريب', 'progress' => 25, 'status' => 'pending'],
        ]);

        $catalog = [
            ['paint', 'استشارة دهانات', 'صالة 5×4 متر، إضاءة شمالية، عاوزة ألوان هادية', 120, 200, '30 دقيقة', 1],
            ['kitchen', 'تقسيم مطبخ', 'مطبخ L-shape 3×2.5 متر، عاوزة تقسيم عملي وأدراج', 200, 200, '45 دقيقة', 2],
            ['feasibility', 'دراسة جدوى', 'كافيه صغير، رأس مال 300 ألف، محتاجة دراسة مبدئية', 260, 400, '60 دقيقة', 4],
            ['furnish', 'فرش وحدة', 'شقة 120 متر، 3 غرف، ميزانية 150 ألف', 180, 300, '45 دقيقة', 2],
            ['tender', 'تحليل كراسة شروط', 'كراسة شروط مناقصة توريدات، الموعد النهائي 12 الشهر', 300, 500, '60 دقيقة', 3],
        ];

        $services = [];
        foreach ($catalog as [$slug, $name, $sample, $xp, $price, $duration, $min]) {
            $services[$slug] = Service::create([
                'seller_id' => $seller->id,
                'slug' => $slug,
                'name' => $name,
                'sample' => $sample,
                'xp' => $xp,
                'price' => $price,
                'duration' => $duration,
                'min_level' => $min,
                'available' => true,
            ]);
        }

        Offer::create([
            'service_id' => $services['paint']->id,
            'title' => 'استشارة دهانات · صالة',
            'client_name' => 'أسرة من المهندسين',
            'meta' => 'مستوى ابتداء',
            'duration' => '30 دقيقة',
            'due_label' => 'اليوم · 6 م',
            'xp' => 120,
            'min_level' => 1,
        ]);
        Offer::create([
            'service_id' => $services['kitchen']->id,
            'title' => 'تقسيم مطبخ L-shape',
            'client_name' => 'عميلة من التجمع',
            'meta' => 'مستوى احتراف',
            'duration' => '45 دقيقة',
            'due_label' => 'غداً · 12 ظ',
            'xp' => 200,
            'min_level' => 2,
        ]);
        Offer::create([
            'service_id' => $services['feasibility']->id,
            'title' => 'دراسة جدوى مبدئية',
            'client_name' => 'كافيه تحت التأسيس',
            'meta' => 'مقفولة · تفتح عند مستوى 4',
            'duration' => '60 دقيقة',
            'due_label' => 'بعد غد · 6 م',
            'xp' => 260,
            'min_level' => 4,
        ]);

        $this->finished($services['paint'], $sara, [
            'number' => 1029,
            'client_name' => 'م. هالة سامي',
            'title' => 'استشارة دهانات · غرفة',
            'description' => 'غرفة نوم، إضاءة دافئة، ميزانية متوسطة.',
            'total' => 200,
            'base_price' => 200,
            'created_at' => '2026-02-14 10:00:00',
        ], [4, 4, 3, 'شغل مرتب، ومحتاجة تفاصيل أكتر عن أكواد الدهان المكافئة.']);

        $this->finished($services['tender'], $sara, [
            'number' => 1035,
            'client_name' => 'شركة النيل للاستشارات',
            'title' => 'تحليل كراسة شروط',
            'description' => 'كراسة شروط مناقصة توريدات.',
            'total' => 500,
            'base_price' => 500,
            'xp' => 300,
            'duration' => '60 دقيقة',
            'created_at' => '2026-03-18 12:00:00',
        ], [5, 5, 5, 'العرض الفني مطابق للكراسة والتسليم في الميعاد.']);

        $this->finished($services['paint'], $sara, [
            'number' => 1038,
            'client_name' => 'كافيه بيت القهوة',
            'title' => 'استشارة دهانات · صالة',
            'description' => 'صالة الكافيه، إضاءة شمالية، ألوان هادية.',
            'total' => 200,
            'base_price' => 200,
            'created_at' => '2026-04-02 15:00:00',
        ], [5, 5, 4, 'الشرح واضح والسعر مناسب، والصور ساعدت العميل يختار بسرعة.']);

        $running = $this->open($services['paint'], $sara, [
            'number' => 1039,
            'client_name' => 'مكتب الدلتا الهندسي',
            'title' => 'استشارة دهانات · صالة',
            'description' => 'صالة 5×4 متر، إضاءة شمالية، عاوزة ألوان هادية.',
            'pipeline_stage' => 1,
            'total' => 200,
            'base_price' => 200,
        ]);

        $waiting = $this->open($services['tender'], $sara, [
            'number' => 1040,
            'client_name' => 'شركة رويال للتشطيبات',
            'title' => 'تحليل كراسة شروط',
            'description' => 'كراسة شروط مناقصة توريدات، الموعد النهائي 12 الشهر.',
            'pipeline_stage' => 3,
            'total' => 500,
            'base_price' => 500,
            'xp' => 300,
            'duration' => '60 دقيقة',
            'due_label' => 'غداً · 12 ظ',
        ], true);
        $waiting->update([
            'quality_checks' => collect(SopLibrary::checksFor('tender'))
                ->map(fn (string $label) => ['label' => $label, 'ok' => true])
                ->all(),
        ]);

        $this->open($services['kitchen'], $sara, [
            'number' => 1041,
            'client_name' => 'مكتب الأمل',
            'title' => 'تقسيم مطبخ L-shape',
            'description' => 'مطبخ L-shape 3×2.5 متر، عاوزة تقسيم عملي وأدراج.',
            'pipeline_stage' => 0,
            'total' => 200,
            'base_price' => 200,
            'xp' => 200,
            'duration' => '45 دقيقة',
            'due_label' => 'بعد غد · 6 م',
            'detail_level' => 'pro',
        ]);
    }

    private function person(array $attrs): User
    {
        return User::create($attrs + [
            'password' => 'password',
            'is_available' => $attrs['is_available'] ?? true,
            'is_new' => $attrs['is_new'] ?? false,
            'points' => $attrs['points'] ?? 0,
            'balance' => $attrs['balance'] ?? 0,
            'withdrawable' => $attrs['withdrawable'] ?? 0,
            'pending' => $attrs['pending'] ?? 0,
            'jobs_done' => $attrs['jobs_done'] ?? 0,
            'jobs_clean' => $attrs['jobs_clean'] ?? 0,
            'speed_badge' => $attrs['speed_badge'] ?? false,
            'streak' => $attrs['streak'] ?? 0,
        ]);
    }

    private function finished(Service $service, User $worker, array $attrs, array $rating): void
    {
        $order = $this->open($service, $worker, $attrs + [
            'pipeline_stage' => 5,
            'payment_status' => 'released',
        ], true);

        $order->rating()->create([
            'quality' => $rating[0],
            'price_score' => $rating[1],
            'dealing' => $rating[2],
            'note' => $rating[3],
            'author_name' => $attrs['client_name'],
            'created_at' => $attrs['created_at'] ?? now(),
        ]);
    }

    private function open(Service $service, User $worker, array $attrs, bool $completeSteps = false): Order
    {
        $order = Order::create($attrs + [
            'service_id' => $service->id,
            'worker_id' => $worker->id,
            'speed' => $attrs['speed'] ?? 'normal',
            'detail_level' => $attrs['detail_level'] ?? 'pro',
            'discount_percent' => 0,
            'tracking_fee' => $attrs['tracking_fee'] ?? 0,
            'duration' => $attrs['duration'] ?? $service->duration,
            'due_label' => $attrs['due_label'] ?? 'اليوم · 6 م',
            'xp' => $attrs['xp'] ?? $service->xp,
            'payment_method' => 'instapay',
            'payment_status' => $attrs['payment_status'] ?? 'held',
            'source' => 'seed',
            'partner_name' => config('hadthiqa.partner_name'),
            'on_time' => true,
        ]);

        SopLibrary::attach($order);

        if ($completeSteps) {
            foreach ($order->steps()->get() as $step) {
                $step->update([
                    'completed_at' => now(),
                    'result' => $step->kind === 'ai' ? $step->preview : null,
                ]);
            }
        }

        return $order;
    }
}
