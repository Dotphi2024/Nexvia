# NEXVIA Self Dealer — Implementation Task List

## Phase 1 — Database Migrations
- [x] Migration: Extend `users` table (self dealer fields)
- [x] Migration: Extend `customer_category_progress` (stage, cycle_number)
- [x] Migration: Extend `referrals` table (status, cycle_number, eligible_value, rule_version)
- [x] Migration: Extend `wallet_transactions` (type, status, referral_id, incentive_pct)
- [x] Migration: Extend `products` (eligible_referral_value, referral_eligible, self_dealer_eligible)
- [x] Migration: Extend `categories` (referral_category_code, referral_eligible)
- [x] Migration: Create `referral_stage_config` table
- [x] Migration: Create `self_dealer_wallets` table
- [x] Migration: Create `fraud_flags` table

## Phase 2 — Models
- [x] Update `Customer.php` model (relationships, casts, fillable)
- [x] Update `CustomerCategoryProgress.php` model (stage rates, cycle advancement)
- [x] Update `Referral.php` model (scopes, cycle, eligible value)
- [x] Update `WalletTransaction.php` model (audit fields, scopes)
- [x] Update `Product.php` model (referral and self dealer eligibility)
- [x] Update `Category.php` model (referral code, eligibility)
- [x] Create `SelfDealerWallet.php` model (available, pending, redeemed)
- [x] Create `ReferralStageConfig.php` model (Variant A dynamic rates)
- [x] Create `FraudFlag.php` model (anti-fraud audit review)

## Phase 3 — Seeder
- [x] Create `ReferralStageConfigSeeder.php` (Stage 1=10%, 2=12%, 3=15%, 4=18%, 5=20%, Activation=20%)
- [x] Run seeder into database

## Phase 4 — Core Service (Brain of the System)
- [x] Rewrite `ReferralCommissionService.php`
  - [x] `activateSelfDealer()` — 20% activation points
  - [x] `processReferralBooking()` — link referral code, fraud check, create PENDING
  - [x] `qualifyReferral()` — mark AVAILABLE, advance stage, handle cycle reset
  - [x] `reverseReferral()` — cancellation/return reversal with audit
  - [x] `getStageRate()` — reads from DB config (NOT hardcoded)
  - [x] `advanceStage()` — 1→2→3→4→5→RESET to 1, increment cycle
  - [x] `runFraudChecks()` — self-referral, duplicate mobile, duplicate payment

## Phase 5 — Admin Controllers
- [x] Create `SelfDealerAdminController.php`
  - [x] `index()` — list all self dealers with KPIs
  - [x] `show($id)` — dealer profile + wallet + category progress + referrals
  - [x] `qualifyReferral()` — manually approve pending referral
  - [x] `reverseReferral()` — admin reversal with audit note
  - [x] `fraudFlags()` — fraud review list
  - [x] `reviewFraudFlag()` — approve/reject fraud flag
  - [x] `transactions()` — all referral transactions ledger
  - [x] `updateStatus()` — activate / suspend self dealer
- [x] Create `ReferralStageConfigController.php`
  - [x] `settings()` — show current config
  - [x] `update()` — save new percentages

## Phase 6 — Admin Blade Views
- [x] `admin/self_dealers/index.blade.php` (Self dealers list with KPIs)
- [x] `admin/self_dealers/show.blade.php` (Dealer profile, wallet cards, category progress, transactions)
- [x] `admin/self_dealers/fraud_flags.blade.php` (Fraud flag reviews and decision modal)
- [x] `admin/referral_config/settings.blade.php` (Configure 5 stages and activation credit)
- [x] `admin/self_dealers/transactions.blade.php` (Transactions log)

## Phase 7 — Routes
- [x] Add Self Dealer routes to `routes/admin.php`
- [x] Add Referral Config routes to `routes/admin.php`

## Phase 8 — Existing Form Updates
- [x] Product create/edit form — add `eligible_referral_value`, `referral_eligible`, `self_dealer_eligible`
- [x] Category form — add `referral_category_code`, `referral_eligible`
- [x] Update `ProductAdminController.php` to handle new fields
- [x] Update `CategoryAdminController.php` to handle new fields

## Phase 9 — Dashboard Stats Update
- [x] Update `DashboardController.php` — real self dealer count + points stats
- [x] Update `admin/dashboard/index.blade.php` — show Self Dealer Ecosystem banner & KPIs

## Phase 10 — Admin Navigation
- [x] Add "Self Dealers" link to admin sidebar
- [x] Add "Referral Config" link to admin sidebar
