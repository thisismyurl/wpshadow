# WPShadow Implementation Roadmap

> **Philosophy:** Every feature should be a "helpful neighbor" that educates and empowers users.  
> **Product Ecosystem:** See [PRODUCT_ECOSYSTEM.md](PRODUCT_ECOSYSTEM.md) for complete product family architecture.  
> **Philosophy:** See [PRODUCT_PHILOSOPHY.md](PRODUCT_PHILOSOPHY.md) for our 11 commandments.

---

## Guiding Principles

1. **Free First:** If it doesn't require our servers, it's free forever
2. **Educate, Don't Sell:** Link to KB articles and training, not sales pages
3. **Show Value:** Every action tracks KPIs to demonstrate impact
4. **Inspire Confidence:** UX so intuitive users assume all WordPress is this easy
5. **Beyond Pure:** Consent-first, privacy-focused, transparent always

---

## Product Delivery Timeline

| Product | Phase | Timeline | Status |
|---------|-------|----------|--------|
| **WPShadow Core** | 1-4 | Q1 2026 | 🚧 In Progress (Phase 3.5) |
| **WPShadow Academy** | 5 | Q1-Q2 2026 | 📋 Planned |
| **WPShadow Guardian** | 7 | Q2-Q3 2026 | 📋 Planned |
| **WPShadow Vault** | 9 | Q4 2026 | 📋 Planned |
| **Gamification System** | 8 | Q3-Q4 2026 | 📋 Planned |
| **WPShadow Pro** | Ongoing | Current | ✅ Active Development |

---

## Current State Summary (Phases 1-2 Complete)

**Production Features (All Free Forever):**

### Diagnostics: 57 Checks Across 10 Categories
📊 [Complete diagnostic matrix →](FEATURE_MATRIX_DIAGNOSTICS.md)

- **Security** (12): SSL, security headers, admin username, REST API, RSS feeds, hotlink protection, consent checks
- **Performance** (15): Memory limit, lazy load, external fonts, jQuery migrate, emoji scripts, asset versions, caching
- **Code Quality** (12): Debug mode, error logging, WP generator, embed disable, interactivity, accessibility
- **WordPress Config** (10): WP version, PHP version, permalinks, tagline, email deliverability, timezone
- **Monitoring** (5): Database health, broken links, plugin count, mobile friendliness, theme health
- **Workflow/System** (3): Initial setup validation, registry health, maintenance mode

### Treatments: 44 Safe Auto-Fixes with Backup/Undo
🔧 [Complete treatment matrix →](FEATURE_MATRIX_TREATMENTS.md)

- **Security** (8): SSL enforcement, file editors disabled, security headers, hotlink protection, REST API restrictions
- **Performance** (14): Asset versioning, lazy loading, jQuery cleanup, memory limit increases, head cleanup, caching
- **Code Cleanup** (12): Emoji scripts removal, WP generator removal, embed disable, interactivity cleanup, HTML optimization
- **WordPress Config** (7): Permalinks fix, debug mode disable, RSS feed management, search indexing, accessibility
- **System/Workflow** (3): Registry management, maintenance mode, pre-publish review

### Core Systems
- ✅ **Kanban Board:** 6 columns (Detected → Ignored → User to Fix → Fix Now → Workflows → Fixed)
- ✅ **KPI Tracking:** Time saved, issues fixed, site health score, value $ equivalent
- ✅ **1200+ Contextual Tooltips:** Category-filtered, user-dismissible, KB-linked
- ✅ **Workflow Automation:** 11-file engine (triggers, actions, executor, scheduler, wizard)
- ✅ **Activity Logging:** Diagnostic runs, treatment applications, user actions
- ✅ **Multisite Support:** Network-aware capabilities (`manage_network_options`)

### Code Quality Achievements (Phases A-C)
📋 [Full refactoring analysis →](CODE_REVIEW_SENIOR_DEVELOPER.md)

- ✅ **Treatment Base Class:** 43/43 treatments use `Treatment_Base` (100% DRY compliance)
- ✅ **AJAX Handler Base Class:** 17/25 handlers migrated to `AJAX_Handler_Base` (89% coverage)
- ✅ **Registry Architecture:** Abstract_Registry pattern established
- ✅ **Duplicate Code Reduction:** 1,160 lines → 800 lines (31% elimination)
- ✅ **Type Safety:** `declare(strict_types=1)` in all PHP files
- ✅ **WordPress Standards:** phpcs + phpstan passing
- ✅ **Security Patterns:** Centralized nonce/capability verification

**Next Steps:** Phase 3.5 (final code optimization) → Phase 4 (UX excellence) → Phase 5 (education integration)

---

## Phase 1: Foundation (COMPLETED ✅)
✅ Diagnostic registry system (57 checks)
✅ Treatment interface and registry (44 auto-fixes)
✅ KPI tracking system (time saved, value delivered)
✅ Finding status manager (Kanban board backend)
✅ Kanban board UI (drag-drop finding management)
✅ Initial treatments with backup/rollback

## Phase 2: Core Diagnostics (IN PROGRESS 🚧)

**Goal:** Comprehensive health checks that educate users about their site

### Security Diagnostics
- [x] Post via Email security (threat level 16)
- [x] Post via Email category (threat level 12)
- [x] SSL Configuration
- [x] Debug Mode detection
- [x] File editors enabled
- [ ] Plugin security audit
- [ ] Theme security check
- [ ] File integrity monitoring
- [ ] API health check

**Each diagnostic must:**
- ✅ Link to KB article explaining why it matters
- ✅ Show real-world impact (speed, security, SEO)
- ✅ Offer auto-fix when possible (free)
- ✅ Include "learn more" link to free training

### Performance Diagnostics
- [x] Memory Limit
- [x] Image optimization status
- [ ] Database optimization needs
- [ ] Cache configuration
- [ ] CDN status
- [ ] Performance monitoring baseline

### WordPress Config Diagnostics
- [x] Permalinks structure
- [x] Site description/tagline
- [x] Backup plugin installed
- [ ] WordPress version currency
- [ ] Email deliverability
- [ ] Custom field validation

---

## Phase 3: Treatment Expansion (NEXT 📋)

**Goal:** Safe, reversible auto-fixes that demonstrate value

### Priority Treatments (Auto-fixable + Free)
- [x] Treatment_Debug_Mode - Disable debug mode safely
- [x] Treatment_File_Editors - Disable file editors with backup
- [x] Treatment_Memory_Limit - Increase PHP memory
- [x] Treatment_Permalinks - Fix permalink structure
- [ ] Treatment_Tagline - Add site tagline (educate about SEO)
- [ ] Treatment_Cache_Control - Enable caching headers
- [ ] Treatment_GZIP_Compression - Enable GZIP
- [ ] Treatment_Image_Optimization - Bulk optimize images

**Each treatment must:**
- ✅ Create backup before applying
- ✅ Show undo button always
- ✅ Explain what it's doing
- ✅ Track KPI (time saved, value delivered)
- ✅ Link to KB article about the fix
- ✅ Provide "learn why this matters" training link

### Medium Priority (Requires Confirmation)
- [ ] Treatment_Plugin_Update - Update plugins safely (with testing)
- [ ] Treatment_WordPress_Update - Update WP core (with staging)
- [ ] Treatment_Disable_Unused_Plugins - Disable unused plugins (with confirmation)

### Educational Integration
- [ ] "What we did and why" explanations after every auto-fix
- [ ] "Learn more about this" link to free training course
- [ ] Before/after metrics display
- [ ] Cumulative value tracking

---

## Phase 3.5: Code Quality & DRY Optimization (PARALLEL 🔧)

**Goal:** WordCamp-ready code — eliminate remaining DRY violations, optimize performance

**Philosophy Integration:** "Ridiculously good" (Commandment #7) applies to code quality too. Clean code = maintainable = sustainable = long-term helpful neighbor.

### Technical Debt Elimination (4-6 hours focused work)

**Status:** ⭐⭐⭐⭐ (4/5) - Strong foundation, optimization needed

**Progress So Far:**
- ✅ Phase A Complete: 43 treatments refactored to use `Treatment_Base`
- ✅ Phase B Complete: 17 AJAX handlers migrated to `AJAX_Handler_Base`
- ✅ Phase C Complete: Base class architecture established
- ✅ Duplicate code: 1,160 lines → 800 lines (31% reduction)
- ✅ AJAX handler coverage: 0% → 89% class-based

**Remaining Tasks:**

1. **Create Color Utils Class** (20 min) - Priority: HIGH
   - Consolidate `hex_to_rgb()`, `contrast_ratio()`, WCAG compliance
   - Currently scattered: wpshadow.php:225, :244, diagnostic classes
   - Single source of truth for color calculations
   - Code savings: ~40 lines
   - File: `includes/core/class-color-utils.php`

2. **Create Theme Data Provider** (30 min) - Priority: HIGH
   - Consolidate 3 theme getter functions (contexts, palette, background)
   - All use same fallback: block theme → classic → defaults
   - Code savings: ~80 lines
   - File: `includes/core/class-theme-data-provider.php`

3. **Upgrade Tooltip Manager** (20 min) - Priority: MEDIUM
   - Replace static variable caching with transient caching
   - Survives across page loads (better for multisite)
   - Invalidation hooks when tooltips change
   - Performance improvement for tooltip-heavy pages

4. **Create User Preferences Manager** (20 min) - Priority: MEDIUM
   - Consolidate scattered user meta get/set patterns
   - Schema validation, type checking
   - Centralized user data handling (privacy-friendly)
   - File: `includes/core/class-user-preferences-manager.php`

5. **Migrate Workflow AJAX Handlers** (90 min) - Priority: HIGH
   - 8 inline handlers → class-based (matching Phase B pattern)
   - Eliminates last major DRY violation (~120 lines)
   - All AJAX handlers use `AJAX_Handler_Base`
   - Files: `includes/admin/ajax/class-workflow-*-handler.php` (8 new)

6. **Option Query Batching** (30 min) - Priority: LOW
   - Batch related `get_option()` calls
   - Reduce database queries on settings pages
   - Performance improvement: 15+ queries → < 8

7. **Transient Caching Strategy** (30 min) - Priority: LOW
   - Expensive operations use transient cache
   - Smart invalidation hooks
   - Multisite-aware cache keys

### Success Metrics (Code Quality)
- Duplicate code lines: 800 → 500 (38% reduction total)
- AJAX handlers class-based: 89% → 100%
- Database queries (settings page): 15+ → < 8
- Code complexity: Improved maintainability index
- WordCamp presentation ready: Full refactoring story with metrics

### WordCamp Presentation Goal 🎤
- **Talk:** "DRY Refactoring in WordPress Plugins" (15-20 min)
- **Before/After:** 1,160 duplicate lines → 500 lines
- **Live Examples:** Treatment_Base, AJAX_Handler_Base patterns
- **Takeaway:** Copy-paste template for attendees
- **Story:** Journey from messy to elegant (3 phases)

### Technical Reference Docs
- 📄 [CODE_REVIEW_SENIOR_DEVELOPER.md](CODE_REVIEW_SENIOR_DEVELOPER.md) - Complete analysis (900 lines)
- 📄 [WORDCAMP_READINESS_GUIDE.md](WORDCAMP_READINESS_GUIDE.md) - Presentation strategy
- 📄 [PHASE_4_QUICK_WINS_IMPLEMENTATION.md](PHASE_4_QUICK_WINS_IMPLEMENTATION.md) - Task breakdown
- 📄 [VISUAL_SUMMARY_ONE_PAGE.md](VISUAL_SUMMARY_ONE_PAGE.md) - Architecture diagrams
- 📄 [FEATURE_MATRIX_DIAGNOSTICS.md](FEATURE_MATRIX_DIAGNOSTICS.md) - All 57 diagnostics
- 📄 [FEATURE_MATRIX_TREATMENTS.md](FEATURE_MATRIX_TREATMENTS.md) - All 44 treatments

**Why This Matters (Philosophy):**
Clean code isn't just developer ego—it's sustainability. A maintainable codebase means:
- Faster bug fixes (better for users)
- Easier contributions (community-friendly)
- Long-term support (trustworthy product)
- Clear patterns (educational for developers)

This is "ridiculously good" applied to the foundation, not just the surface.

---

## Phase 4: Dashboard & UX Excellence (CURRENT FOCUS 🎯)

> **Note:** Follows Phase 3.5 code quality work. Clean codebase enables confident UI iteration.

**Goal:** Make WordPress management ridiculously easy and intuitive

### Health Gauge System (#563)
- [x] 8 category gauges (Security, Performance, Code Quality, etc.)
- [ ] WordPress Site Health gauge (WP's findings minus ours)
- [ ] Overall Site Health gauge (combines all 11)
- [ ] Unique color per category (consistent across plugin)
- [ ] New layout: 1 large gauge (overall) + 10 small (33/33/33 columns)

**Philosophy:** Visual confidence-building. Users see health at a glance.

### Dashboard Breakout Views (#564)
- [ ] Click any gauge → Filtered dashboard for that category
- [ ] Primary gauge + related secondary gauges
- [ ] Plain English test results ("What this means for your site")
- [ ] Category-filtered Kanban board
- [ ] Category-filtered Activity History
- [ ] **Important:** Use same codebase with GET parameter filtering

**Philosophy:** Deep-dive without overwhelming. Users explore at their pace.

### Comprehensive Activity History (#565)
- [ ] Plugin activation/deactivation logging
- [ ] Kanban column moves (with reasoning)
- [ ] Diagnostic run logging (auto vs manual)
- [ ] Treatment application logging (auto vs manual, success/failure)
- [ ] User actions (dismiss, ignore, schedule)
- [ ] Timeline view with filters
- [ ] Export audit log (privacy feature)

**Philosophy:** Complete transparency. Admins see everything the plugin does.

### Kanban Smart Actions (#567)
- [ ] **User to Fix:** Log action, exclude from future scans, reactivate if moved back
- [ ] **Fix Now:** Create disposable workflow, run at next cron, log completion
- [ ] **Workflows:** Create visible workflow with defaults, prompt for completion, show status dot (green/yellow/red)
- [ ] Workflow status indicators on Kanban cards
- [ ] "Why we excluded this" tooltips

**Philosophy:** Actions have consequences. Users understand cause and effect.

### KPI Dashboard Enhancements
- [ ] Monthly value report ("You achieved X this month")
- [ ] Time saved calculator (15 min per fix default, customizable)
- [ ] Before/after site health comparison
- [ ] Trend graphs (health over time)
- [ ] Milestone celebrations ("100% site health achieved!")
- [ ] Share achievements (social proof)

**Philosophy:** Prove value constantly. Users see concrete impact.

### Educational Tooltips Everywhere
- [x] 8 JSON files with contextual tooltips
- [x] KB URL integration (https://wpshadow.com/kb/{context}-{slug})
- [ ] "?" icons on every confusing element
- [ ] Plain English explanations
- [ ] "Learn more" links to free training
- [ ] Progressive disclosure (basic → advanced)

**Philosophy:** Explain, don't assume. Every user is learning.

### WordPress Site Health Integration (#558)
- [x] WPShadow tests appear in Site Health
- [ ] Each critical finding = standalone recommendation (not lumped)
- [ ] Affect WordPress Site Health percentage
- [ ] Link back to WPShadow dashboard for fixes
- [ ] "Fixed by WPShadow" badges on resolved items

**Philosophy:** Integrate, don't compete. We make WordPress better.

---

## Phase 5: Knowledge Base & Training Integration (FUTURE 📚)

**Goal:** Education-first approach. Every diagnostic is a learning opportunity.

### KB Article Auto-Creation
- [ ] Every diagnostic has dedicated KB article
- [ ] Format: What it is → Why it matters → Fix yourself → Learn more
- [ ] Plain English explanations
- [ ] Screenshots and examples
- [ ] Related articles linkage

### Training Course Integration
- [ ] In-plugin course recommendations
- [ ] "Learn about this topic" links
- [ ] Progress tracking (optional, requires registration)
- [ ] Certification badges (motivational, free)
- [ ] "Complete this course" CTA after related fixes

### Helpful Neighbor Messaging
- [ ] Post-fix education: "Here's what we did and why"
- [ ] Contextual tips: "Understanding this will help you..."
- [ ] Success celebrations with learning: "Great job! Learn more about..."
- [ ] Weekly tips widget: Free lesson recommendations

### Drive Traffic (The Right Way)
- [ ] "Learn more" always goes to free content first
- [ ] No gating content behind emails
- [ ] Registration optional for progress tracking
- [ ] Clear value: "5-minute video that will help you"
- [ ] Never interrupt workflow to push training

**Philosophy:** Educate to empower, not to upsell. Training is a gift, not a funnel.

---

## Phase 6: Privacy & Consent Excellence (#566, Future 🔒)

**Goal:** Set the standard for plugin privacy. Beyond reproach.

### First Activation Experience
- [ ] Welcome screen explaining plugin
- [ ] Anonymous usage data opt-in (clear explanation)
- [ ] What we collect / what we don't (transparent list)
- [ ] Link to full privacy policy
- [ ] "You can change this anytime" messaging
- [ ] Default: No data collection until consent

### Data Collection Framework
- [ ] Anonymous-by-default
- [ ] Consent required for any data
- [ ] Re-confirm for new data types
- [ ] Easy opt-out in Settings
- [ ] Data export tool (GDPR compliance)
- [ ] Complete data deletion

### Admin Transparency
- [ ] Admin panel showing what data is collected
- [ ] Audit log of all plugin actions
- [ ] User data visibility (what we store per user)
- [ ] Network-wide privacy controls (multisite)
- [ ] "Phone home" indicator (show when contacting our servers)

### Privacy Policy Integration
- [ ] In-plugin privacy policy viewer
- [ ] Version tracking (notify on changes)
- [ ] Export consent history
- [ ] Third-party service disclosure

**Philosophy:** Privacy isn't compliance, it's respect. Users deserve complete control.

---

## Phase 7: WPShadow Guardian Launch (Q2-Q3 2026 ☁️)

**Product:** Cloud-based AI-powered analysis and monitoring tools  
**See:** [PRODUCT_ECOSYSTEM.md](PRODUCT_ECOSYSTEM.md#2-wpshadow-guardian-saasai-tools)

**Goal:** Launch Guardian SaaS with token-based revenue model. Generous free tier, pay for what you use.

### Registration System
- [ ] User registration flow (email + password)
- [ ] OAuth integration (Google, GitHub)
- [ ] Email verification
- [ ] Account dashboard (wpshadow.com/account/)
- [ ] API key generation
- [ ] Token balance display

### Free Tier Implementation
- [ ] 100 AI scans/month per registered user
- [ ] 50 email notifications/month
- [ ] 3-site multi-site dashboard
- [ ] 30-day historical analytics
- [ ] Community forum access

### Token System Infrastructure
- [ ] Token purchase flow (Stripe integration)
- [ ] Token pricing: 100/$5, 500/$20, 2000/$60
- [ ] Token balance tracking
- [ ] Usage reporting dashboard
- [ ] Token expiration (never)
- [ ] Refund policy implementation

### Guardian Pro Subscription
- [ ] $19/month unlimited tier
- [ ] Subscription management (Stripe)
- [ ] Auto-renewal with notification
- [ ] Easy cancellation (no dark patterns)
- [ ] Pro-rated refunds

### AI Scanning Features
- [ ] Deep security scanning (AI-powered)
- [ ] Plugin vulnerability detection
- [ ] Theme security audit
- [ ] Performance profiling
- [ ] Database optimization analysis
- [ ] SEO technical audit
- [ ] Accessibility compliance scan

### Cloud Infrastructure
- [ ] API endpoint architecture
- [ ] Rate limiting (prevent abuse)
- [ ] Queue system for scans
- [ ] Result caching
- [ ] CDN for fast delivery
- [ ] Monitoring and alerts

### Plugin Integration
- [ ] "Run Guardian Scan" button in Core
- [ ] Token balance display in dashboard
- [ ] Clear explanation: "Uses 1 token"
- [ ] Registration prompt for non-registered
- [ ] Purchase tokens flow from plugin
- [ ] Scan results display in Core UI

### Email Notification System
- [ ] Critical issue alerts
- [ ] Weekly summary emails
- [ ] Custom notification rules
- [ ] Notification preferences
- [ ] Unsubscribe flow
- [ ] Email templates (philosophy-compliant)

**Philosophy:** Register for free tier, pay only when you need more. Token system = fair pricing.

---

## Phase 8: Gamification System (Q3-Q4 2026 🎮)

**See:** [PRODUCT_ECOSYSTEM.md](PRODUCT_ECOSYSTEM.md#6-gamification-system-new---needs-implementation)

**Goal:** Build comprehensive achievement and rewards system to increase engagement and demonstrate value.

### Core Infrastructure
- [ ] `includes/gamification/class-gamification-manager.php`
- [ ] `includes/gamification/class-achievement-registry.php`
- [ ] `includes/gamification/class-badge-system.php`
- [ ] `includes/gamification/class-points-system.php`
- [ ] `includes/gamification/class-leaderboard.php`
- [ ] `includes/gamification/class-reward-system.php`
- [ ] `includes/gamification/class-gamification-ui.php`

### Achievement System
**5 Categories, 20+ Achievements:**
- [ ] Getting Started (4 achievements: First diagnostic, treatment, backup, workflow)
- [ ] Site Health (4 achievements: Clean health, performance, security, optimization)
- [ ] Learning (4 achievements: KB articles, videos, courses, certifications)
- [ ] Engagement (4 achievements: Weekly, monthly, community, referrals)
- [ ] Advanced (4 achievements: Workflows, multi-site, zero downtime, agency)

### Point System
- [ ] Point earning actions (run diagnostic: 5pts, apply treatment: 10pts, etc.)
- [ ] Point milestone rewards (Bronze: 500pts, Silver: 2000pts, Gold: 5000pts, Platinum: 10000pts)
- [ ] Point balance tracking
- [ ] Point history log
- [ ] Point earning notifications

### Badge System
- [ ] Badge definitions (25+ badges)
- [ ] Badge awarding logic
- [ ] Badge display UI (profile, dashboard widget)
- [ ] Badge sharing (social media)
- [ ] Badge showcase page

### Leaderboard System
- [ ] Global leaderboard (top 100)
- [ ] Monthly leaderboard (resets)
- [ ] Category leaderboards (security, performance, learning)
- [ ] Team leaderboards (agencies)
- [ ] Opt-in system (privacy-first, default OFF)
- [ ] Username/alias display only
- [ ] Hide from leaderboard option

### Reward Redemption
- [ ] Reward catalog: Guardian tokens, Vault tier, Pro subscription, Academy Pro
- [ ] Redemption flow (1000pts → 50 tokens, etc.)
- [ ] Redemption history
- [ ] Reward delivery automation
- [ ] Fraud prevention

### Dashboard Integration
- [ ] Achievement widget (recent achievements, next milestone)
- [ ] Profile badge display
- [ ] Points balance widget
- [ ] Leaderboard widget (if opted in)
- [ ] "Redeem Rewards" CTA

### Notifications
- [ ] Achievement unlocked notifications
- [ ] Milestone reached notifications
- [ ] New badge earned notifications
- [ ] Leaderboard rank change (if opted in)
- [ ] Reward redemption confirmations

**Philosophy:** Gamification demonstrates user progress and value. Rewards are meaningful, not manipulative.

---

## Phase 9: WPShadow Vault Enhancement (Q4 2026 💾)

**Product:** Comprehensive backup and disaster recovery system  
**Repository:** `wpshadow-pro-vault` → rename to `wpshadow-vault`  
**See:** [PRODUCT_ECOSYSTEM.md](PRODUCT_ECOSYSTEM.md#3-wpshadow-vault-backup-package)

**Goal:** Rebrand and enhance Vault with free tier, paid upgrades, and Core plugin integration.

### Rebranding
- [ ] Rename repository: `wpshadow-pro-vault` → `wpshadow-vault`
- [ ] Update plugin header: "WPShadow Vault"
- [ ] New icon and branding assets
- [ ] Update wpshadow.com/vault/ landing page

### Registration Integration
- [ ] Require registration for cloud backup storage
- [ ] Free tier: 3 full-site backups, 7-day retention
- [ ] Registration flow from plugin
- [ ] Account dashboard integration

### Free Tier Implementation
- [ ] 3 backup storage slots
- [ ] 7-day retention policy
- [ ] Local + cloud storage options
- [ ] Manual backup anytime
- [ ] One-click restore
- [ ] Email backup reports

### Paid Tier Infrastructure
**Starter ($9/month):**
- [ ] 10 backups stored
- [ ] 30-day retention
- [ ] Scheduled daily backups
- [ ] Automatic cleanup
- [ ] Priority restore support

**Professional ($29/month):**
- [ ] Unlimited backups
- [ ] 90-day retention
- [ ] Scheduled hourly backups
- [ ] Multi-site support (5 sites)
- [ ] Real-time backup monitoring
- [ ] Off-site backup copies
- [ ] 24-hour restore support

**Agency ($99/month):**
- [ ] Everything in Professional
- [ ] Unlimited sites
- [ ] 1-year retention
- [ ] White-label reports
- [ ] Dedicated account manager
- [ ] 4-hour restore support SLA
- [ ] Custom backup schedules

### Core Plugin Integration
- [ ] Vault status badge in Core dashboard
- [ ] "Backup before treatment" option
- [ ] Auto-backup before risky operations
- [ ] Vault registration prompt
- [ ] Clear messaging: "3 free backups for registered users"
- [ ] Upgrade flow from Core

### Advanced Features
- [ ] Incremental backups (Pro/Agency)
- [ ] Real-time file monitoring (Pro/Agency)
- [ ] Automatic malware scanning (Pro/Agency)
- [ ] Backup testing/validation (Pro/Agency)
- [ ] Custom retention policies (Agency)
- [ ] Backup analytics dashboard

**Philosophy:** Everyone needs backups. First 3 are free. Pay only for more storage/features.

---

## Phase 10: WPShadow Academy Adaptive Learning (Q1 2027 📚)

**Product:** Online learning platform integrated with plugin  
**See:** [PRODUCT_ECOSYSTEM.md](PRODUCT_ECOSYSTEM.md#4-wpshadow-academy-learning-platform)

**Goal:** Turn every site issue into a learning opportunity. Smart prompts guide users to relevant education.

### Content Creation
- [ ] 200+ KB articles (all diagnostic/treatment topics)
- [ ] 100+ training videos (5-10 minutes each)
- [ ] 10+ complete courses (10-15 videos each)
- [ ] Course structure: WordPress Security, Performance, Database, Plugins, Themes, etc.

### Plugin Integration - Smart Prompts
- [ ] Detect user struggling (same issue 3+ times)
- [ ] Contextual video prompts: "Having trouble with SSL? Watch this 7-min video"
- [ ] Post-diagnostic education: "Want to understand what this checks?"
- [ ] Post-treatment education: "See how this fixes the issue"
- [ ] Achievement-based: "Fixed 3 issues! Want to master this?"

### Adaptive Learning System
- [ ] Track user's site issues (what they struggle with)
- [ ] Recommend personalized learning paths
- [ ] "Your site needs X, here's a course for that"
- [ ] Progress tracking (courses started/completed)
- [ ] Next recommended course

### Progress Tracking UI
- [ ] "Your Learning Progress" dashboard widget
- [ ] Badges earned display
- [ ] Courses completed
- [ ] Next recommended course CTA
- [ ] Link to full Academy dashboard (wpshadow.com/academy/)

### Certification System
- [ ] Course completion certificates
- [ ] LinkedIn-shareable credentials
- [ ] "WPShadow Certified" badges
- [ ] Agency showcase: Display certifications

### Academy Pro Features (Paid)
- [ ] Advanced courses (agency workflows, advanced optimization)
- [ ] Monthly live webinars with Q&A
- [ ] Expert case study presentations
- [ ] Agency-specific training
- [ ] Early access to new courses

**Philosophy:** Education empowers. Never interrupt workflow, always offer relevant learning.

---

## Success Metrics

By completion of Phase 4-5, we should demonstrate:

### User Value
- ✅ 95%+ of users see measurable site health improvement
- ✅ Average 3+ hours saved per month per user
- ✅ 90%+ of issues auto-fixed (vs. manual)
- ✅ Users can explain what we fixed and why

### Product Quality
- ✅ Better UX than premium competitors
- ✅ Design so polished people assume it's paid
- ✅ Feature set rivals $200 premium plugins
- ✅ Support response times under 24 hours

### Educational Impact
- ✅ 50%+ of users click through to KB articles
- ✅ 25%+ watch free training videos
- ✅ Users rate content as "actually helpful"
- ✅ KB articles are bookmarked and shared

### Privacy & Trust
- ✅ Zero privacy complaints
- ✅ Consent flows rated "clear and respectful"
- ✅ Featured on privacy-focused blogs/podcasts
- ✅ Becomes privacy standard for plugins

### Business Goals
- ✅ High retention (daily active usage)
- ✅ Organic growth (recommendations)
- ✅ Natural Pro conversion (value-driven, not pressure)
- ✅ Invited to speak about approach

**The Ultimate Metric:** People talk about us. Podcasts invite us. Users recommend us without being asked.

---

## Feature Decision Framework

Before adding any feature to roadmap, verify:

### ✅ Must Have:
- [ ] Delivers clear, measurable user value
- [ ] Can be free (or has generous free tier)
- [ ] Tracks KPI to demonstrate impact
- [ ] Includes educational component (KB/training link)
- [ ] Passes "helpful neighbor" test
- [ ] Has undo/rollback if it changes site
- [ ] Requires explicit consent if collecting data
- [ ] Plain English explanations throughout
- [ ] Intuitive UX (no manual needed)

### 🛑 Reject If:
- [ ] Feels like sales pitch
- [ ] Artificially limited to push upgrade
- [ ] Unclear value proposition
- [ ] Privacy concerns
- [ ] Confusing or overwhelming UX
- [ ] No educational value

---

## Development Priorities

### Current Sprint (January 2026)
1. Complete Phase 4 dashboard enhancements (#563, #564, #565)
2. Implement Kanban smart actions (#567)
3. Fix tooltip issues (#561)
4. Create KB article template
5. Document first activation privacy flow (#566)

### Next Sprint (February 2026)
1. WordPress Site Health deep integration (#558)
2. Activity History comprehensive logging
3. Breakout dashboard views
4. KB article generation for all diagnostics
5. Training course integration points

### Q1 2026 Goals
- Complete Phase 4 (Dashboard & UX Excellence)
- Begin Phase 5 (KB & Training Integration)
- Ship first activation privacy flow
- Publish 20+ KB articles
- Launch free training course integration

---

## Living Roadmap

This roadmap evolves based on:
- User feedback and requests
- Privacy/security landscape changes
- WordPress core updates
- Competitive analysis
- Our product philosophy

**Review quarterly.** Update based on:
1. What users actually need (not just want)
2. What aligns with helpful neighbor philosophy
3. What demonstrates measurable value
4. What educates and empowers

**See Also:**
- [PRODUCT_PHILOSOPHY.md](PRODUCT_PHILOSOPHY.md) - Our complete vision
- [GITHUB_ISSUES_ALIGNMENT.md](GITHUB_ISSUES_ALIGNMENT.md) - Issue tracking
- [FEATURE_CODE_AUDIT.md](FEATURE_CODE_AUDIT.md) - Current state

---

*Last Updated: January 21, 2026*  
*Next Review: April 2026*

## Data Structures

### Finding Status Map (in options)
```php
'wpshadow_finding_status_map' => array(
    'detected' => array(
        array( 'id' => 'ssl-missing', 'timestamp' => 1234567890, 'notes' => '' ),
    ),
    'ignored' => array(),
    'manual' => array(
        array( 'id' => 'backup-missing', 'timestamp' => 1234567890, 'notes' => '' ),
    ),
    'automated' => array(
        array( 'id' => 'memory-limit-low', 'timestamp' => 1234567890, 'notes' => '' ),
    ),
    'fixed' => array(),
)
```

### KPI Tracking (in options)
```php
'wpshadow_kpi_tracking' => array(
    'findings_detected' => array(
        'ssl-missing_2026-01-20' => array(
            'finding_id' => 'ssl-missing',
            'severity' => 'critical',
            'date' => '2026-01-20 14:30:00',
            'count' => 1,
        ),
    ),
    'fixes_applied' => array(
        array(
            'finding_id' => 'memory-limit-low',
            'method' => 'auto',
            'date' => '2026-01-20 14:31:00',
        ),
    ),
    'findings_dismissed' => array(),
)
```

## File Organization

```
includes/
├── diagnostics/              # Problem detection
│   ├── class-diagnostic-memory-limit.php
│   ├── class-diagnostic-backup.php
│   ├── class-diagnostic-permalinks.php
│   ├── class-diagnostic-tagline.php
│   ├── class-diagnostic-ssl.php
│   ├── class-diagnostic-outdated-plugins.php
│   ├── class-diagnostic-debug-mode.php
│   ├── class-diagnostic-wordpress-version.php
│   ├── class-diagnostic-plugin-count.php
│   ├── class-diagnostic-registry.php
│   └── README.md
│
├── treatments/              # Problem solutions
│   ├── interface-treatment.php
│   ├── class-treatment-permalinks.php
│   ├── class-treatment-memory-limit.php
│   ├── class-treatment-debug-mode.php        # TO ADD
│   ├── class-treatment-registry.php
│   └── README.md
│
├── core/                    # Utilities
│   ├── class-kpi-tracker.php
│   ├── class-finding-status-manager.php
│   └── (other core utilities)
│
└── ARCHITECTURE.md          # This file
```

## Integration Points

### Main Plugin File (wpshadow.php)
- Load Diagnostic_Registry on `plugins_loaded`
- Load Treatment_Registry on `plugins_loaded`
- AJAX endpoints for status changes
- AJAX endpoints for treatment application

### Dashboard UI
- Display findings by status
- Kanban board for organizing
- KPI metrics display
- Treatment application buttons

### Admin Menu
- Main dashboard
- Findings board (Kanban)
- KPI/History report
- Settings
- Help/KB articles

## Success Metrics

By end of Phase 4, we should have:
- ✅ 20+ diagnostics detecting issues
- ✅ 10+ treatments auto-fixing problems
- ✅ Kanban board UI for organizing findings
- ✅ KPI dashboard showing value delivered
- ✅ Ability to track issues and fixes over time
- ✅ Data proving "X hours saved" and "Y issues prevented"

## Notes

- Keep treatments safe by default (file backups, validation)
- All fixes tracked via KPI system for proof of value
- Status manager enables user control (Kanban board)
- Separate diagnostics from treatments for flexibility
- Scalable design allows easy addition of new checks/fixes
