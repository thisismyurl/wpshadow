# WPShadow Email Marketing Journey - Visual Guide

**Visual representation of the email strategy and user journey**

---

## Email Journey Timeline

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        USER JOURNEY TIMELINE                             │
└─────────────────────────────────────────────────────────────────────────┘

Day 0: Plugin Activation
  │
  ├── First diagnostic scan completes
  │   └── Opt-in modal appears: "Want personalized updates?"
  │       ├── [Yes] → User opts in
  │       └── [No] → No emails sent (can opt-in later via settings)
  │
  ▼
Day 3: Welcome Email 👋
  │   Subject: "Hi {{first_name}}, I'm [Founder], and I want to help {{site_name}} succeed"
  │   Content: Personal welcome, show initial value, set expectations
  │   From: [Founder Name] <founder@wpshadow.com>
  │
  ▼
Day 7: Quick Win Email 🎯
  │   Subject: "{{site_name}} is already {{improvement}}% healthier!"
  │   Content: First week accomplishments, top fixes, what's next
  │   From: [Founder Name] <founder@wpshadow.com>
  │
  ▼
Day 14: Educational Email 🎓
  │   Subject: "What WPShadow is watching on {{site_name}} (and why it matters)"
  │   Content: Behind-the-scenes, explain diagnostics, link to training
  │   From: [Founder Name] <founder@wpshadow.com>
  │
  ▼
Day 30: First Monthly Summary 📊
  │   Subject: "{{site_name}}'s {{month}} Report: {{total_fixes}} fixes, {{time_saved}} hours saved"
  │   Content: Complete monthly report with stats, recommendations, comparison
  │   From: [Founder Name] <founder@wpshadow.com>
  │
  ▼
Monthly: Ongoing Summaries 📈
  │   Sent on 1st of each month (or install anniversary)
  │   Content: Monthly progress, personalized insights, next steps
  │   Frequency: 1-2x per month max
  │
  ├── SPECIAL EMAILS (As Appropriate)
  │   ├── Milestone Reached 🎉 (100th fix, 1 year, etc.)
  │   ├── New Feature Matches Usage 🆕
  │   └── Educational Content 📚 (Quarterly)
  │
  └── ENGAGEMENT MONITORING
      │
      ├── Engagement Score > 80: Full cadence continues
      ├── Engagement Score 50-79: Monthly only
      ├── Engagement Score 25-49: Re-engagement campaign
      │   ├── Re-engagement Email #1 (Week 1)
      │   ├── Re-engagement Email #2 (Week 3)
      │   └── Auto-Pause (Week 4)
      └── Engagement Score < 25: Emails paused automatically
```

---

## Engagement Scoring System

```
┌─────────────────────────────────────────────────────────────────────┐
│                    ENGAGEMENT SCORE (0-100)                          │
└─────────────────────────────────────────────────────────────────────┘

Components:
┌──────────────────────────────┬──────────┐
│ Dashboard Access             │ 30 pts   │
│ • Last 7 days: 30 pts        │          │
│ • Last 30 days: 20 pts       │          │
│ • Last 90 days: 10 pts       │          │
├──────────────────────────────┼──────────┤
│ Recent Treatments Applied    │ 20 pts   │
│ • 1 treatment = 5 pts        │          │
│ • Max: 20 pts                │          │
├──────────────────────────────┼──────────┤
│ Email Engagement             │ 20 pts   │
│ • 1 open = 5 pts             │          │
│ • Max: 20 pts                │          │
├──────────────────────────────┼──────────┤
│ Workflow Usage               │ 15 pts   │
│ • 1 active workflow = 5 pts  │          │
│ • Max: 15 pts                │          │
├──────────────────────────────┼──────────┤
│ Activity Logger Events       │ 15 pts   │
│ • Recent activities count    │          │
│ • Max: 15 pts                │          │
└──────────────────────────────┴──────────┘

Score Categories:
┌─────────────┬────────────────────────────────────┐
│  80-100     │ 🟢 Highly Active - Full cadence   │
│  50-79      │ 🟡 Active - Monthly only           │
│  25-49      │ 🟠 Declining - Re-engage           │
│  0-24       │ 🔴 Inactive - Auto-pause           │
└─────────────┴────────────────────────────────────┘
```

---

## Email Decision Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                      EMAIL SEND DECISION TREE                        │
└─────────────────────────────────────────────────────────────────────┘

Email queued to send
        │
        ▼
    Check consent
    ┌─────────────┐
    │ Opted in?   │──── No ───→ ❌ Don't send
    └─────────────┘
         │ Yes
         ▼
    Check status
    ┌──────────────┐
    │ Email paused?│──── Yes ──→ ❌ Don't send
    └──────────────┘
         │ No
         ▼
    Check engagement
    ┌──────────────────┐
    │ Score >= 25?     │──── No ───→ ❌ Skip (mark inactive)
    └──────────────────┘
         │ Yes
         ▼
    Check frequency
    ┌──────────────────────────┐
    │ Last email < 7 days ago? │── Yes ─→ ⏸️ Delay
    └──────────────────────────┘
         │ No
         ▼
    Check personalization
    ┌─────────────────────────┐
    │ All variables populated?│── No ──→ ⚠️ Use defaults
    └─────────────────────────┘
         │ Yes
         ▼
    ✅ SEND EMAIL
         │
         ▼
    Log activity
    ┌──────────────────────┐
    │ Record in Activity   │
    │ Logger and email     │
    │ history              │
    └──────────────────────┘
```

---

## Personalization Data Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                    PERSONALIZATION PIPELINE                          │
└─────────────────────────────────────────────────────────────────────┘

Template with {{variables}}
        │
        ▼
Collect Data Sources
┌────────────────────────────────┐
│ 1. Site Information            │
│    • site_name, site_url       │
│    • wp_version, php_version   │
│    • theme, plugins            │
├────────────────────────────────┤
│ 2. WPShadow Activity           │
│    • total_diagnostics_run     │
│    • total_treatments_applied  │
│    • issues_fixed, health_score│
│    • time_saved, improvements  │
├────────────────────────────────┤
│ 3. User Behavior               │
│    • first_name, user_email    │
│    • last_dashboard_visit      │
│    • favorite_features         │
│    • engagement_score          │
├────────────────────────────────┤
│ 4. Comparison Data (Optional)  │
│    • health_vs_average         │
│    • percentile_rank           │
│    • similar_sites_data        │
└────────────────────────────────┘
        │
        ▼
Replace {{variables}}
        │
        ▼
Wrap in HTML template
        │
        ▼
Final personalized email
```

---

## Re-Engagement Campaign

```
┌─────────────────────────────────────────────────────────────────────┐
│                   RE-ENGAGEMENT WORKFLOW                             │
└─────────────────────────────────────────────────────────────────────┘

Engagement Score Drops Below 25
        │
        ▼
Week 1: Send Re-Engagement Email #1
┌───────────────────────────────────────┐
│ "Hey {{first_name}}, everything       │
│ okay with {{site_name}}?"             │
│                                       │
│ [2-Question Survey]                   │
│ [Pause Emails] [Keep Active]         │
└───────────────────────────────────────┘
        │
        ▼
    User Response?
    ┌─────────────┐
    │ Clicked     │──── Yes ──→ ✅ Restore engagement
    │ survey/keep │              Update score
    │ active?     │              Resume emails
    └─────────────┘
         │ No
         ▼
Week 3: Send Re-Engagement Email #2
┌───────────────────────────────────────┐
│ "Final note from WPShadow"            │
│                                       │
│ Last chance to stay connected         │
│ [I'm still here!] [Pause updates]    │
└───────────────────────────────────────┘
        │
        ▼
    User Response?
    ┌─────────────┐
    │ Clicked     │──── Yes ──→ ✅ Restore engagement
    │ "still here"│
    └─────────────┘
         │ No
         ▼
Week 4: Auto-Pause
┌─────────────────────────────────────┐
│ • Set email_paused = true           │
│ • Preserve opt-in (don't opt-out)   │
│ • Stop all automated emails         │
│ • User can re-enable via dashboard  │
│ • Log activity: "emails_auto_paused"│
└─────────────────────────────────────┘
```

---

## Implementation Phases

```
┌─────────────────────────────────────────────────────────────────────┐
│                    IMPLEMENTATION ROADMAP                            │
└─────────────────────────────────────────────────────────────────────┘

Phase 1: Foundation (Weeks 1-2)
├── Database schema
├── Consent UI (modal + settings)
├── Email queue system
└── Basic testing

Phase 2: Templates (Week 3)
├── Template engine
├── Personalization data collection
├── HTML email wrapper
└── Variable replacement logic

Phase 3: Engagement (Week 4)
├── Engagement scoring system
├── Email tracking (opens/clicks)
├── Activity Logger integration
└── Scoring algorithm

Phase 4: Scheduling (Week 5)
├── Email scheduler
├── Campaign timing logic
├── Recurring summary scheduling
└── Special event triggers

Phase 5: Cron Integration (Week 6)
├── WordPress cron setup
├── Queue processing (hourly)
├── Error handling
└── Performance testing

Phase 6: Testing & Launch (Weeks 7-10)
├── Week 7-8: Internal testing
├── Week 9-10: Beta program (50-100 users)
├── Week 11-12: Limited release (10% of new installs)
└── Week 13+: Full rollout (if metrics hit targets)
```

---

## Email Types Comparison

```
┌──────────────────────┬──────────┬───────────┬──────────────────┐
│ Email Type           │ Timing   │ Frequency │ Primary Goal     │
├──────────────────────┼──────────┼───────────┼──────────────────┤
│ Welcome              │ Day 3    │ Once      │ Set expectations │
│ Quick Win            │ Day 7    │ Once      │ Show early value │
│ Educational          │ Day 14   │ Quarterly │ Teach concepts   │
│ Monthly Summary      │ Monthly  │ Monthly   │ Report progress  │
│ Re-Engagement        │ Variable │ Max 2x    │ Restore activity │
│ Milestone            │ Variable │ As earned │ Celebrate wins   │
│ Feature Announcement │ Variable │ Ad-hoc    │ Drive adoption   │
│ Content/Education    │ Variable │ Quarterly │ Deep learning    │
└──────────────────────┴──────────┴───────────┴──────────────────┘
```

---

## Success Metrics Dashboard

```
┌─────────────────────────────────────────────────────────────────────┐
│                      SUCCESS METRICS                                 │
└─────────────────────────────────────────────────────────────────────┘

Email Performance
┌────────────────────┬──────────┬──────────┐
│ Metric             │ Target   │ Status   │
├────────────────────┼──────────┼──────────┤
│ Opt-in Rate        │ 40%+     │ [____]   │
│ Open Rate          │ 35%+     │ [____]   │
│ Click Rate         │ 15%+     │ [____]   │
│ Unsubscribe Rate   │ <2%      │ [____]   │
└────────────────────┴──────────┴──────────┘

User Impact
┌────────────────────┬──────────┬──────────┐
│ Metric             │ Target   │ Status   │
├────────────────────┼──────────┼──────────┤
│ Dashboard Visits   │ +20%     │ [____]   │
│ Treatments Applied │ +15%     │ [____]   │
│ Workflow Creation  │ +30%     │ [____]   │
│ KB Article Views   │ +40%     │ [____]   │
│ Training Views     │ +25%     │ [____]   │
└────────────────────┴──────────┴──────────┘

Business Impact
┌────────────────────┬──────────┬──────────┐
│ Metric             │ Target   │ Status   │
├────────────────────┼──────────┼──────────┤
│ User Retention     │ +15%     │ [____]   │
│ Active Users       │ +20%     │ [____]   │
│ Word-of-Mouth      │ 10+/mo   │ [____]   │
│ Pro Conversions    │ 5%+      │ [____]   │
│ Support Tickets    │ -10%     │ [____]   │
└────────────────────┴──────────┴──────────┘
```

---

## Privacy & Compliance

```
┌─────────────────────────────────────────────────────────────────────┐
│                    COMPLIANCE CHECKLIST                              │
└─────────────────────────────────────────────────────────────────────┘

GDPR (EU)
✅ Explicit consent (not pre-checked)
✅ Granular control (email types)
✅ Easy withdrawal (one-click unsubscribe)
✅ Consent timestamp recorded
✅ Data minimization (email + preferences only)
✅ Right to access (view email history)
✅ Right to erasure (complete removal)
✅ Right to portability (export as CSV/JSON)

CAN-SPAM (US)
✅ Clear sender identity
✅ Accurate subject lines
✅ Physical address in footer
✅ Unsubscribe option in every email
✅ Honor opt-outs within 10 days

CASL (Canada)
✅ Express consent required
✅ Clear sender identification
✅ One-click unsubscribe mechanism
✅ Maintain consent records 3+ years
```

---

## Key Differentiators

```
┌─────────────────────────────────────────────────────────────────────┐
│            WPSHADOW vs TYPICAL PLUGIN EMAILS                         │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────────────────┬──────────────────────────────────────┐
│ Typical Plugin Emails    │ WPShadow Email Strategy              │
├──────────────────────────┼──────────────────────────────────────┤
│ Generic bulk messages    │ Personalized with real site data     │
│ Sales-focused            │ Educational and helpful              │
│ Frequent interruptions   │ Respectful cadence (1-2x/month)     │
│ One-size-fits-all        │ Engagement-based sending             │
│ Anonymous sender         │ Personal from founder                │
│ Hard to unsubscribe      │ One-click unsubscribe, easy pause    │
│ Ignore inactive users    │ Smart engagement tracking            │
│ Feature announcements    │ Value demonstrations                 │
│ Scare tactics            │ Confidence-building                  │
│ Corporate tone           │ Friendly neighbor tone               │
└──────────────────────────┴──────────────────────────────────────┘
```

---

## Example Variable Replacements

```
┌─────────────────────────────────────────────────────────────────────┐
│                    PERSONALIZATION EXAMPLES                          │
└─────────────────────────────────────────────────────────────────────┘

Template:
"Hi {{first_name}}, WPShadow fixed {{issues_fixed}} issues on 
{{site_name}} this month and saved you {{time_saved}} hours!"

Becomes:
"Hi Sarah, WPShadow fixed 47 issues on Acme Coffee Shop this 
month and saved you 8 hours!"

---

Template:
"Your {{category}} score improved from {{old_score}} → {{new_score}}, 
making {{site_name}} healthier than {{percentile}}% of WordPress sites."

Becomes:
"Your security score improved from 65 → 92, making Acme Coffee Shop 
healthier than 87% of WordPress sites."

---

Template:
"The most impactful fix? {{top_fix_description}}. This {{impact}}."

Becomes:
"The most impactful fix? Enabled automatic WordPress updates. 
This protects against 95% of common vulnerabilities."
```

---

## Philosophy in Action

```
┌─────────────────────────────────────────────────────────────────────┐
│               THE 11 COMMANDMENTS IN EMAIL FORM                      │
└─────────────────────────────────────────────────────────────────────┘

#1: Helpful Neighbor
→ Personal tone, anticipate needs, genuinely care

#2: Free as Possible  
→ Emails are free, no hidden costs

#3: Register, Don't Pay
→ Just opt-in required, not payment

#4: Advice, Not Sales
→ Educational content, not promotional

#5: Drive to KB
→ Every email links to relevant articles

#6: Drive to Training
→ Free courses and videos included

#7: Ridiculously Good
→ Better than premium plugins' emails

#8: Inspire Confidence
→ Show progress, celebrate achievements

#9: Everything Has a KPI
→ Track all email impact metrics

#10: Beyond Pure (Privacy)
→ GDPR compliant, explicit consent

#11: Talk-About-Worthy
→ So good users forward to friends
```

---

This visual guide complements the detailed strategy documents and provides an at-a-glance understanding of the email marketing system.

For complete details, see:
- **[EMAIL_MARKETING_STRATEGY.md](EMAIL_MARKETING_STRATEGY.md)** - Full strategy
- **[EMAIL_TEMPLATES.md](EMAIL_TEMPLATES.md)** - Ready-to-use templates
- **[EMAIL_MARKETING_SUMMARY.md](EMAIL_MARKETING_SUMMARY.md)** - Executive summary
