<?php

namespace App\Support;

use App\Models\Order;

class SopLibrary
{
    public static function stepsFor(string $slug, string $name): array
    {
        return match ($slug) {
            'paint' => self::paint(),
            'tender' => self::tender(),
            default => self::generic($name),
        };
    }

    public static function checksFor(string $slug): array
    {
        if ($slug === 'paint') {
            return [
                'الأكواد صحيحة وموجودة في السوق',
                'الباليتات مناسبة للإضاءة',
                'صور التصور واضحة',
                'الملاحظات مفهومة للعميل',
            ];
        }

        return [
            'النطاق مطابق لطلب العميل',
            'الأرقام والملاحظات متراجعة',
            'التسليم مفهوم وقابل للتنفيذ',
            'الجودة مناسبة للسعر',
        ];
    }

    public static function attach(Order $order): void
    {
        $order->loadMissing('service');

        if ($order->steps()->exists()) {
            return;
        }

        foreach (self::stepsFor($order->service->slug, $order->service->name) as $position => $step) {
            $order->steps()->create([
                'position' => $position + 1,
                'kind' => $step['kind'],
                'title' => $step['title'],
                'description' => $step['description'],
                'cta' => $step['cta'] ?? null,
                'done_label' => $step['done'],
                'preview' => $step['result'] ?? null,
            ]);
        }
    }

    private static function paint(): array
    {
        return [
            ['kind' => 'human', 'title' => 'افتحي الصور وحددي الإضاءة والمساحة', 'description' => 'راجعي كل صورة: اتجاه الإضاءة، ولون الأرضية، والمساحة بالمتر.', 'done' => 'الإضاءة والمساحة اتحددوا'],
            ['kind' => 'ai', 'title' => 'شغّلي المهارة على الصور', 'description' => 'ترشّح المهارة ألوانًا مناسبة للإضاءة، وتقدّر الكميات ودرجة اللمعة.', 'cta' => 'شغّلي المهارة', 'done' => 'المهارة اشتغلت والنتيجة اتأكدت', 'result' => ['3 باليتات مناسبة لإضاءة شمالية', 'أكواد دهانات متاحة في السوق', 'تقدير 18 لتر لجدار 5×4 متر']],
            ['kind' => 'human', 'title' => 'اختاري 3 باليتات بأكواد موجودة في السوق', 'description' => 'تأكدي من توفر الكود عند الموزع ومطابقته للعين.', 'done' => '3 باليتات بأكواد متاحة'],
            ['kind' => 'human', 'title' => 'جهّزي صورة شرح لكل باليتة', 'description' => 'صورة واحدة لكل باليتة فيها الألوان والاسم والسعر التقديري.', 'done' => 'صور الشرح جاهزة'],
            ['kind' => 'human', 'title' => 'اكتبي ملاحظات التشطيب والإضاءة', 'description' => 'نوع الدهان، عدد الأوجه، وأي ملاحظة عن الإضاءة الشمالية.', 'done' => 'الملاحظات اتكتبت'],
        ];
    }

    private static function tender(): array
    {
        return [
            ['kind' => 'human', 'title' => 'ارفعي كراسة الشروط واقرأي النظرة العامة', 'description' => 'تعرّفي على الجهة ونوع المناقصة والموعد النهائي للتقديم.', 'done' => 'الكراسة اتقرت'],
            ['kind' => 'ai', 'title' => 'شغّلي مهارة استخراج المتطلبات الإلزامية', 'description' => 'تُخرج المهارة البنود الإلزامية والمواعيد والمستندات المطلوبة في دقائق.', 'cta' => 'شغّلي المهارة', 'done' => 'المتطلبات اتستخرجت', 'result' => ['14 متطلب إلزامي من 32 صفحة', '3 مستندات ناقصة (سابقة أعمال، تأمين، ضمان)', 'الموعد النهائي: 12 الشهر']],
            ['kind' => 'human', 'title' => 'راجعي المتطلبات الإلزامية حسب الـ SOP', 'description' => 'تأكدي من وجود رد واضح لكل بند إلزامي في العرض.', 'done' => '14 من 14 بند اتغطوا'],
            ['kind' => 'human', 'title' => 'اكتبي العرض الفني في القالب المعتمد', 'description' => 'استخدمي قالب المنصة وأجيبي عن كل بند من البنود المستخرجة.', 'done' => 'العرض اتكتب'],
            ['kind' => 'human', 'title' => 'جهّزي النسخة النهائية', 'description' => 'راجعي الأرقام والملاحق ثم سلّمي النسخة.', 'done' => 'النسخة النهائية جاهزة'],
        ];
    }

    private static function generic(string $name): array
    {
        return [
            ['kind' => 'human', 'title' => "افتحي تفاصيل طلب {$name} وحددي النطاق", 'description' => 'راجعي الصور والوصف، وحددي المساحة والإضاءة والنطاق الذي يريده العميل.', 'done' => 'النطاق اتحدد'],
            ['kind' => 'ai', 'title' => 'شغّلي المهارة على الصور والبيانات', 'description' => 'تقرأ المهارة الصور والوصف وتُخرج مقترحات جاهزة، وتختارين منها المطابق للسوق.', 'cta' => 'شغّلي المهارة', 'done' => 'المهارة اشتغلت والنتيجة اتأكدت', 'result' => ['3 مقترحات مناسبة للإضاءة والميزانية', 'أكواد وخامات متاحة في السوق المصري', 'تقدير التكلفة والكميات']],
            ['kind' => 'human', 'title' => 'اختاري 3 مقترحات نهائية', 'description' => 'اختاري ثلاثة من نتيجة المهارة، وعدّلي ما لا يناسب طلب العميل.', 'done' => '3 مقترحات جاهزة'],
            ['kind' => 'human', 'title' => 'اكتبي شرحًا مبسطًا لكل مقترح', 'description' => 'لكل مقترح: السعر التقديري، والمدة، وسبب ملاءمته للعميل.', 'done' => 'الشرح اتكتب'],
            ['kind' => 'human', 'title' => 'اكتبي ملاحظات التنفيذ والميزانية', 'description' => 'كل ما يجب أن يعرفه العميل قبل بدء التنفيذ.', 'done' => 'الملاحظات اتكتبت'],
        ];
    }
}
