# حد ثقة — AI implementation plan

Read this file before changing code. The business story for the supervisor is in `docs/had-thiqa-project-plan.md`. This file is the build order for improving the running Laravel app.

Do not redesign the product. Keep the two paths, the Arabic copy, the escrow story, and the split **45 / 30 / 15 / 10**. The platform keeps 10% as the party in the middle of every Instapay payment.

The app lives in `c:\e_bussiness`. UI language is Arabic, right to left. Demo payments stay fake: Instapay is a simulated handle (`had.thiqa@instapay`). The platform collects the order, keeps 10%, and records the rest. Worker, seller, and admin each have a navbar, a footer, and an analytics page.

---

## 0. Instructions for the implementing agent

1. Implement **one sprint only**, then stop and summarize. The active sprint is **Sprint 1** at the bottom of section 8.
2. Do not change split percentages in `config/hadthiqa.php` except the agreed 10% platform fee.
3. Do not add a real payment gateway, a mobile app, open chat before payment, or purchasable XP.
4. Do not replace Blade + Tailwind CDN with a new frontend stack.
5. Every money rule needs a feature test. Run `php artisan test` before finishing.
6. After UI changes, open `http://127.0.0.1:8010` and walk the changed path. If the server is down: `php artisan serve --host=127.0.0.1 --port=8010`.
7. Keep seeded demo numbers working: سارة, balance 3,480, level 3, paint share 90 EGP on a 200 EGP normal order.
8. New Arabic strings should sound like the existing UI: short, spoken, not formal MSA.

---

## 1. How the site works

### Actors

| Actor | How they enter today | What they do |
| --- | --- | --- |
| Visitor | `GET /` `home` | Chooses worker, client, or admin |
| Worker | `GET /worker` always loads سارة via `App\Support\Demo` | Sees balance, level, offers, claims a task, walks SOP |
| Client | `GET /client/request` | Four-step order, then pays into a simulated hold |
| Partner | No account | Name `ليلى` only. The worker presses the partner-approve button |
| SOP author | No account | Receives 15% in the calculated split |
| Admin | `GET /admin` with no login | Services, payments, ratings |

### Worker path

1. Dashboard shows balance, level, XP bar, availability, and active offers (`worker/dashboard.blade.php`).
2. `POST worker/offers/{offer}/claim` creates an order at pipeline stage 1, payment `held`, and attaches SOP steps (`OrderFlow::claim`).
3. Stage 0 shows “ابدئي المهمة”. Stage 1 is human steps plus one AI-looking step. Stage 2 is the quality checklist. Stage 3 is partner approval. Stage 4 waits for the client. Stage 5 shows the worker share.
4. Pipeline labels are `config('hadthiqa.worker_stages')`.

### Client path

1. `client/request.blade.php` is one form with four `data-step` sections.
2. Price = `service.price * speed multiplier`, then minus `THIQA10` (10%), then plus 150 if detailed tracking is checked.
3. `OrderFlow::createClientOrder` assigns the highest-rated available worker in the chosen tier, or the picked worker.
4. Pay sets `payment_status` from `unpaid` to `held`. Nothing moves a wallet.
5. `simulate` jumps milestones for the demo. Rating at stage 4+ creates a `Rating`, sets stage 5, releases payment, and adds the worker share plus XP.

### Money rule already in code

`Order::distributable()` is `total - tracking_fee`. The 45/30/15/10 split applies only to that base. The 150 EGP tracking fee is excluded from the split. The platform is the middle party: Instapay collection is held, then 10% stays with the platform and the rest is recorded for the worker, the partner, and the gig seller.

Rush multiplier is 1.5. Worker share on a normal paint offer is 45% of 200 = 90. Platform share is 20. That card copy must stay true.

### Level rule

Thresholds in `config/hadthiqa.php`: 0 ابتداء, 400 احتراف, 900 احتراف, 1500 خبرة, 2500 بارتنر. Feasibility offers use `min_level` 4. سارة is level 3, so that offer stays locked. On-time bonus is +30 XP, but `on_time` is always saved as `true`, so the bonus is not real.

### Main files

- Routes: `routes/web.php`
- Flow: `app/Services/OrderFlow.php`
- Steps: `app/Support/SopLibrary.php`
- Split math: `app/Support/Split.php`
- Levels: `app/Support/Levels.php`
- Models: `app/Models/User.php`, `Order.php`, `OrderStep.php`, `Offer.php`, `Service.php`, `Skill.php`, `Rating.php`, `Ticket.php`
- Views: `resources/views/layouts/app.blade.php`, `chooser.blade.php`, `worker/*`, `client/*`, `admin/index.blade.php`
- Seed: `database/seeders/DatabaseSeeder.php`
- Tests: `tests/Feature/MarketplaceTest.php`

---

## 2. Product rules that guide every enhancement

Use these when a UI choice and a business choice conflict.

1. **Trust is the product.** Escrow, a closed checklist, a second review, and a rating are more important than a new visual style.
2. **The platform earns 10% because it holds the process and sits between payer and payee,** not because it added a fee on top of a chat.
3. **The worker must see her net share before she accepts.** Price and share stay on the offer card.
4. **The client must see the hold before paying.** “المبلغ محجوز إلى أن توافق” stays next to the total.
5. **Skills and SOP output are drafts** until a human confirms them. Never present a fixed paint quantity as an engineering fact.
6. **A service without steps and a quality list does not ship.** Admin-created services already fall through `SopLibrary::generic()`. Keep that.
7. **Acquisition discounts must be attributed.** A code that only reduces price, with no referrer, is a cost with no growth loop.
8. **Disintermediation is the main commercial risk.** Do not add a public contact channel on an unpaid order.
9. **Partner 30% stays for now.** Do not “optimize” it in code. A later business decision can change config after review time is measured.
10. **Green means released money or a finished rating.** Do not use green for a mere click.

---

## 3. Bugs to fix

Fix these in the sprint that names them. Do not drive-by refactor unrelated controllers.

| ID | Bug | Where | Required behavior |
| --- | --- | --- | --- |
| B1 | `on_time` is always true, so the speed badge and +30 XP are free | `OrderFlow::claim`, `createClientOrder`, `rate` | Store `due_at`. On rating or partner approval, set `on_time` from `now() <= due_at`. Add +30 XP only when that is true |
| B2 | Tracking fee disappears from the economics | `worker/split.blade.php`, `admin/index.blade.php` | Show the four-way split on `distributable()`, then a separate line “متابعة تفصيلية: 150 جنيه للمنصة” |
| B3 | `THIQA10` discounts with no referrer | `OrderFlow::createClientOrder`, `users.referral_code` | If the code matches a user, save `referrer_id`. Do not pay the commission until the ledger sprint. Do not invent a second discount |
| B4 | Quality can be submitted with every box empty and still look like a normal delivery | `ExecutionController::deliver`, `worker/execution.blade.php` | Allow delivery, but if any box is off, status copy is “تسليم وفيه بنود مراجعة” and `deliveredCleanly()` stays false |
| B5 | Skill step completes on the first press, before the worker reads the result | `OrderFlow::runSkill`, execution view | First press stores `result` and does **not** set `completed_at`. Second press “أعتمد النتيجة” completes the step |
| B6 | Partner approval is the worker approving herself, with no record | `ExecutionController::approve` | Keep the demo button for Sprint 1, but save `partner_approved_at` and a note. Sprint 4 moves this to a partner user. Do not pretend a second person acted |
| B7 | Pending balance never changes when money is held or released | `User` balance fields, `OrderFlow::rate` | Do not rewrite the wallet in Sprint 1. Add a code comment and a failing-test skip only if touched. Sprint 3 replaces these fields with a ledger. Until then, do not add new writes that make pending more wrong |
| B8 | Admin and worker routes have no actor | `Demo::worker`, `routes/web.php` | Sprint 1 may add a session role switcher on the home page. Sprint 2 replaces it with real auth. Do not leave new admin actions public without at least the switcher knowing who pressed them |
| B9 | Skills stay `pending` forever and matching ignores them | `WorkerController::storeSkill`, `AdminController`, `OrderFlow::createClientOrder` | Admin can approve or reject. Matching prefers an available worker in the tier who has an approved skill whose name relates to the service, then highest rating |
| B10 | Help tickets have no reader | `tickets` table, admin view | Admin lists tickets and can mark one closed |
| B11 | Offer lock and pricing can regress with no test | `tests/Feature/MarketplaceTest.php` | Add the tests in section 7 |
| B12 | Bottom nav covers the last action on short viewports | `layouts/app.blade.php`, `partials/nav.blade.php` | `scroll-margin-bottom: 7rem` on primary buttons. Confirm claim, “أنهيت الخطوة”, and “سجّل الطلب” sit fully above the nav |
| B13 | Client price is duplicated in JavaScript and PHP | `client/request.blade.php`, `OrderFlow` | One PHP preview endpoint or a shared JSON config printed once. The summary the client sees must equal `orders.total` after submit |
| B14 | Simulate can skip a real worker and still look like production | `client/show.blade.php` | Keep the button. Label stays “نموذج تجريبي”. Do not call it the main action. Visual weight below the real pay / rate buttons |

---

## 4. UI / UX work

Identity stays: cream page `#F6F0E8`, ink `#3F2C39`, plum `#5C4454`, Cairo, `max-w-[460px]`, bottom nav on worker screens. Do not introduce a second visual language.

### Global

- One primary button per screen. Secondary actions are white or outlined.
- Money uses Western digits and `number_format`, as it does now.
- Status colors: plum = in progress, gold = waiting on a person, green = released or accepted, rose = rejected or needs review.
- Any screen about money repeats the hold sentence once, not three times.
- Empty states say what to do next: “لا توجد مهام اليوم” plus the availability switch, not a blank card.

### Worker dashboard

- Offer card order: title, **نصيبك بالجنيه**, full price, duration, due, level lock.
- Locked card explains “تفتح عند مستوى 4” and does not look clickable.
- Availability is a single switch, not two equal buttons that both look primary.
- Referral block shows the code and the sentence “الخصم على عميلك 10%، وعمولتك تتسجل على الطلب”. Do not promise a payout amount until the ledger sprint.

### Execution

- Show only the current step as a card. Completed steps collapse to their `done_label`.
- Skill card: result list, then “أعتمد النتيجة”, plus the existing line that skills stay inside the platform.
- Stage rail stays six steps. The current step is the only filled dot.
- After delivery, the headline is the waiting state and the worker share, not a second explanation of the whole company.

### Client wizard

- Step indicator “2 من 4” visible.
- Back button restores the step without wiping typed description.
- Step 4 summary is the server total: service, rush, discount, tracking, grand total, hold sentence.
- Payment choices stay إنستاباي / كارت / محفظة and stay labeled experimental.

### Admin

- Split the long page into three anchored sections: الخدمات، المدفوعات، التقييمات. Add المهارات and المساعدة.
- Hide / delete keeps a confirm step. Delete stays blocked when orders exist.
- New service form stays. Generic SOP must attach on the first order of that service.

### Partner (Sprint 4, not Sprint 1)

- Separate queue. Each quality row is yes/no. A “no” requires a reason and returns that step, not the whole order.

---

## 5. Features to add, by commercial job

Only build a feature in the sprint that lists it.

| Feature | Commercial job | Sprint |
| --- | --- | --- |
| Session role switcher: سارة، عميل، بارتنر، إدارة | Demo can be shown without lying that everyone is سارة | 1 |
| Skill approve / reject | Supply quality is curated, which is what the client pays for | 1 |
| Ticket inbox | Support is part of trust | 1 |
| `due_at` and real `on_time` | Speed premium and the speed badge are earned | 1 |
| Referrer stored on the order | Discount becomes a growth loop | 1 |
| Tracking fee line on the split | Platform sees income it already charges | 1 |
| Auth: one user, one role, route middleware | Stops public admin and public wallets | 2 |
| Ledger table: hold, release, refund, withdraw, platform fee, tracking fee | Wallet numbers become explainable | 3 |
| Partner user and review queue | The 30% has a real job | 4 |
| Delivery file on the order | The client rates an artifact, not a sentence | 4 |
| Dispute state that freezes release | Protects margin and both sides | 4 |
| Notification rows (in-app) on assign, deliver, reject, release | Brings people back without a new app | 5 |
| Company client: one account, many orders | First profitable demand is offices, not one-off individuals | 5 |
| Margin report: collected, platform 10%, tracking fees, discounts, open disputes | Supervisor can see unit economics | 5 |
| Repeat-order button on a rated order | Raises repeat purchase | 5 |

### Explicitly later

- Paymob, Fawry, or real Instapay. Only after disputes can freeze a hold.
- SMS and email. In-app notices first.
- Native apps.
- A public worker directory that shows phone numbers.
- Letting workers set their own prices.
- Auto-sending skill quantities to the client with no human confirm.

---

## 6. Data changes by sprint

### Sprint 1 migration

Add columns, do not rebuild tables.

`orders`

- `due_at` nullable timestamp
- `referrer_id` nullable foreignId to users
- `partner_approved_at` nullable timestamp
- `partner_note` nullable text

`skills`

- `reviewed_at` nullable timestamp

`tickets`

- `status` string default `open` (`open` or `closed`)

Seed `due_at` for existing orders from their `due_label` only as a display fallback. New orders set `due_at` from speed: rush = now + the service duration, normal = now + 24 hours. Parse duration minutes when the string starts with a number (`30 دقيقة`).

### Sprint 2

`users.role` already exists. Add Laravel session auth or a simple password login for seeded users. Middleware aliases: `worker`, `client`, `partner`, `admin`. Remove `Demo::worker()` from controllers after login exists. Keep a demo login list on the home page so the supervisor can enter as سارة without a lecture on passwords.

### Sprint 3

`ledger_entries`: `id`, `order_id` nullable, `user_id` nullable, `type`, `amount` integer EGP, `direction` credit/debit, `memo`, timestamps.

Types: `hold`, `release_worker`, `release_partner`, `release_author`, `release_platform`, `tracking_fee`, `discount`, `refund`, `withdraw`.

Screen balances become sums of credits minus debits. Seed opening balances for سارة so the demo still shows 3,480 / 1,950 / 1,530. Do not delete those figures.

### Sprint 4

`orders.delivery_path` nullable string. `orders.status` gains `disputed`. `order_steps.rejection_note` nullable. Partner id on the order, defaulting to the seeded partner user.

---

## 7. Tests the agent must add

Extend `tests/Feature/MarketplaceTest.php`. Use `RefreshDatabase` and `$this->seed()`.

Sprint 1 minimum:

1. Home and worker dashboard still show `اختر مسارك`, `أهلاً سارة`, and `3,480`.
2. Claiming the first active offer still completes the first human step.
3. Level 3 worker cannot claim the feasibility offer (`min_level` 4). Response is a redirect with an error, offer stays active.
4. Normal paint total is 200. Rush paint total is 300. `THIQA10` on normal paint total is 180. With tracking, 330.
5. Worker share of a 200 hold with no tracking is 80. Platform slice of that base is 30. Tracking fee is not inside those four numbers.
6. Skill route called once leaves `completed_at` null and fills `result`. Second confirm sets `completed_at`.
7. Admin can approve a pending skill. Unauthenticated admin is unchanged in Sprint 1 if auth is Sprint 2. If the role switcher exists, the test acts as admin.
8. An order with `due_at` in the past rates with `on_time` false and does not describe a new speed bonus. Build the rating through `OrderFlow` or HTTP after moving the order to stage 4.

Do not delete the existing two tests. Update them only if a route name changes.

---

## 8. Sprints

### Sprint 1 — current sprint

Correctness and clarity on the demo people already click. No login system yet.

Work in this order:

1. Migration for B1, B3, B6, B10 columns.
2. `due_at` and `on_time` in `OrderFlow`.
3. Two-step skill confirm.
4. Referrer id when the code matches `users.referral_code`.
5. Split and admin views show the tracking fee as platform-only.
6. Admin skill review and ticket list, including close.
7. Home role switcher stored in session: worker سارة, client, admin. Wire `Demo` to the session worker when the role is worker, so the switcher is real. Client and admin links already exist. Partner can be a disabled choice labeled “يصل في مرحلة المراجعة”.
8. UI fixes in section 4 that are on the worker dashboard, execution page, client step 4, and admin anchors.
9. Tests in section 7.
10. Browser check: chooser, claim or open an in-progress order, run the skill and confirm it, open split, open client step 4 and see the total, open admin skills.

Done when those tests pass and the painted totals match the server.

### Sprint 2 — accounts

Replace the switcher with login. Seed passwords stay `password` and the login screen says the data is demo. Middleware blocks `/admin` for non-admins and blocks another worker’s order. Client orders are visible to the client who created them. Add `client_user_id` on orders.

Done when a guest hitting `/admin` is sent to login, and سارة cannot open another worker’s order.

### Sprint 3 — ledger

Implement section 6 Sprint 3. `rate()` writes release entries instead of incrementing `balance` directly. Dashboard reads the ledger. Opening seed balances remain.

Done when a new released 200 EGP paint order increases سارة’s withdrawable by 90 and the platform report by 20, and a second rating of the same order does not pay twice.

### Sprint 4 — review, file, dispute

Partner login sees only stage 3 orders. Reject returns the named step to incomplete and sets stage 1. Accept sets stage 4. Worker uploads one delivery file before stage 3. Client can open a dispute before rating, which sets `disputed` and refuses `rate()` until admin resolves it.

Done when a rejected checklist item is visible to the worker with the partner’s reason, and a disputed order cannot be rated.

### Sprint 5 — repeat revenue

In-app notifications. Company name on the client. “اطلبي نفس الخدمة” clones description and service into a new unpaid order. Admin margin section sums platform releases, tracking fees, and discounts.

Done when the margin section matches the ledger for the seeded completed orders plus one new order.

---

## 9. Screen copy to keep

Do not rewrite these into formal Arabic:

- معايا وقت / معايا شغل
- المبلغ محجوز لدى المنصة
- نصيبك
- شارة السرعة
- كل البيانات هنا تجريبية — لا توجد مدفوعات حقيقية
- لماذا للـ SOP والمهارة نصيب؟

New copy for Sprint 1, use as written:

- أعتمد النتيجة
- تسليم وفيه بنود مراجعة
- متابعة تفصيلية: مبلغ للمنصة، خارج توزيع الأربعة
- عمولتك تتسجل على الطلب
- يصل في مرحلة المراجعة

---

## 10. Definition of an acceptable change

A change is acceptable when:

- The supervisor can still tell the story in section 1 of the other plan without a new metaphor.
- A normal 200 EGP paint job still pays the worker 90 EGP and the platform 20 EGP.
- No new route moves money before client acceptance, except the existing demo `simulate` button, which stays visibly fake.
- Tests cover the money rule that was touched.
- The worker can finish a step on a phone-width layout without the nav covering the button.
