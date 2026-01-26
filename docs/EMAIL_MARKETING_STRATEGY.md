# WPShadow Personalized Email Marketing Strategy

**Version:** 1.0  
**Date:** January 26, 2026  
**Status:** Planning Phase  
**Target Implementation:** Q2 2026

> 📧 **Philosophy Alignment:** This strategy embodies the "Helpful Neighbor" principle—personalized, valuable, educational emails that users actually want to receive, not bulk marketing.

---

## Executive Summary

This document outlines a comprehensive email marketing strategy for WPShadow users who have opted in to marketing communications. The strategy focuses on:

- **Personalization:** Using real site data and WPShadow activity to make every email relevant
- **Education over Sales:** Following Commandment #4 (Advice, Not Sales)
- **Timing Precision:** Strategic cadence that respects user engagement
- **Charming Authenticity:** Personal messages from the founder (like the tweet example)
- **Value Demonstration:** Showing what WPShadow has done for their specific site

**Key Principle:** These emails should feel like updates from a trusted friend who's genuinely excited about your success, not automated marketing.

---

## Table of Contents

1. [Philosophy Alignment](#philosophy-alignment)
2. [Opt-In Mechanism](#opt-in-mechanism)
3. [Timing & Cadence Strategy](#timing--cadence-strategy)
4. [User Engagement Tracking](#user-engagement-tracking)
5. [Personalization Framework](#personalization-framework)
6. [Content Ideas & Templates](#content-ideas--templates)
7. [Technical Implementation](#technical-implementation)
8. [Success Metrics](#success-metrics)
9. [Phased Rollout Plan](#phased-rollout-plan)
10. [Privacy & Compliance](#privacy--compliance)

---

## Philosophy Alignment

### The 11 Commandments Applied to Email

| Commandment | Application to Email |
|-------------|---------------------|
| **#1: Helpful Neighbor** | Personal tone, anticipate needs, educate |
| **#2: Free as Possible** | Email service costs justified by value |
| **#3: Register, Don't Pay** | Opt-in required, but emails are free |
| **#4: Advice, Not Sales** | Focus on education, soft mentions of features |
| **#5: Drive to KB** | Every email links to relevant KB articles |
| **#6: Drive to Training** | Include free training resources |
| **#7: Ridiculously Good** | Email quality exceeds premium plugins |
| **#8: Inspire Confidence** | Show progress, celebrate wins |
| **#9: Everything Has a KPI** | Track email impact on engagement |
| **#10: Beyond Pure (Privacy)** | Explicit consent, easy unsubscribe |
| **#11: Talk-About-Worthy** | Emails so good users forward them |

---

## Opt-In Mechanism

### Where to Request Consent

#### 1. **During First Activation** (Highest Priority)
**Timing:** After WPShadow completes first diagnostic scan  
**Context:** User has just seen value (diagnostics completed)  
**Message Tone:** Grateful, helpful

```
✅ WPShadow found 12 ways to improve your site!

Would you like occasional updates about YOUR site's progress?
• Monthly summaries of improvements we've made
• Tips based on YOUR specific setup
• New features that match YOUR needs

☐ Yes! Send me personalized site updates (1-2x per month)
☐ No thanks, I'll check the dashboard myself

[Your email: user@example.com] [Continue]

Privacy Note: We never share your email. Unsubscribe anytime with one click.
```

#### 2. **In WordPress Admin Settings**
**Location:** WPShadow > Settings > Notifications  
**Granular Options:**
- ☐ Monthly site health summaries
- ☐ Tips based on my site's activity
- ☐ Educational content and training
- ☐ New feature announcements (matched to my usage)
- ☐ WPShadow blog posts and resources

#### 3. **After Major Success** (Re-engagement)
**Timing:** After WPShadow auto-fixes 5+ issues  
**Context:** User has seen clear value

```
🎉 WPShadow just improved your site's security by 40%!

Want to stay in the loop about your site's progress?
Get monthly updates about improvements like this one.

[Yes, send me updates] [No thanks]
```

### Consent Storage
- **Option Name:** `wpshadow_email_marketing_consent`
- **Values:** 
  - `opted_in` - Active consent
  - `opted_out` - Explicit opt-out
  - `pending` - Not yet decided (default)
- **Timestamp:** `wpshadow_email_consent_date`
- **Preferences:** `wpshadow_email_preferences` (array of opted-in types)

---

## Timing & Cadence Strategy

### Email Journey Map

```
Day 0: Plugin Activation
  ↓
Day 0-1: First diagnostic scan completes
  ↓ (if user opts in)
Trigger 1: Welcome Email #1
  After first diagnostic scan completes (or Day 3 if no activity)
  "Hi [Name], I'm [Founder Name]. Thanks for trusting WPShadow with [Site Name]!"
  ↓
Trigger 2: Quick Win Email #2
  After 3+ issues fixed OR 5+ diagnostics run (or Day 7 fallback)
  "Your first wins with WPShadow: [X] issues fixed, [Y]% faster"
  ↓
Trigger 3: Educational Email #3
  After first workflow created OR dashboard visited 3+ times (or Day 14 fallback)
  "Here's what WPShadow is watching on [Site Name]"
  ↓
Trigger 4: Monthly Summary #1
  After 10+ treatments applied OR 20+ diagnostics (or Day 30 fallback)
  "Your journey with WPShadow: [Site Name] is healthier than [X]% of WordPress sites"
  ↓
Every 30 days: Monthly Personalized Summary
  "What happened on [Site Name] in [Month]"
  ↓
Every 90 days: Quarterly Deep Dive (optional)
  "Three months with WPShadow: Your site's transformation"
```

### Frequency Rules

| Time Period | Max Frequency | Rationale |
|-------------|---------------|-----------|
| **First 2 weeks** | 2 emails max | Welcome + quick win (triggered by usage) |
| **Weeks 2-4** | 1 email | Educational (triggered by engagement) |
| **Month 2+** | 1-2 per month | Monthly summary + occasional tips |
| **Quarterly** | 1 deep dive | Optional detailed report |

**Feature-Based Triggers (Primary):**
- **Welcome:** After first diagnostic scan completes
- **Quick Win:** After 3+ issues fixed OR 5+ diagnostics run
- **Educational:** After first workflow created OR dashboard visited 3+ times
- **Monthly Summary:** After 10+ treatments applied OR 20+ diagnostics run

**Time-Based Fallback (Secondary):**
- If feature milestones aren't reached, fall back to: Day 3, Day 7, Day 14, Day 30
- Ensures all users receive emails even with minimal activity

**Why Feature-Based Triggers?**
1. **More Relevant:** Emails arrive when users are actively engaged and have context
2. **Better Timing:** Messages align with user's actual progress, not arbitrary days
3. **Higher Engagement:** Users who trigger emails by activity are more likely to engage
4. **Personalized Pace:** Fast adopters get emails sooner, slow adopters aren't rushed
5. **Graceful Fallback:** Inactive users still get helpful content at reasonable intervals

**Special Triggers (Outside Regular Cadence):**
- Critical security issue detected (immediate, if opted into alerts)
- Major milestone reached (100th fix, 1 year anniversary)
- New feature that matches their usage pattern (e.g., they use workflows → workflow update)

---

## User Engagement Tracking

### How to Avoid Annoying Inactive Users

#### Engagement Signals (Active Use)
Track these indicators to determine if user is still engaged:

1. **Plugin Activity**
   - Last time user accessed WPShadow dashboard
   - Last diagnostic run (automatic or manual)
   - Last treatment applied
   - Last workflow edited/created
   - Admin login frequency

2. **Email Engagement**
   - Email open rate
   - Link click rate
   - Time since last email interaction

3. **Site Activity**
   - Site is still active (not taken down)
   - WordPress admin logins detected
   - Content updates happening

#### Engagement Scoring System

```php
/**
 * Calculate user engagement score (0-100)
 * 
 * @return int Engagement score
 */
function wpshadow_calculate_engagement_score() {
    $score = 0;
    
    // Recent dashboard access (30 points max)
    $last_access = get_option('wpshadow_last_dashboard_access');
    if ($last_access > strtotime('-7 days')) {
        $score += 30;
    } elseif ($last_access > strtotime('-30 days')) {
        $score += 20;
    } elseif ($last_access > strtotime('-90 days')) {
        $score += 10;
    }
    
    // Recent treatments applied (20 points max)
    $recent_treatments = count_recent_treatments(30); // last 30 days
    $score += min(20, $recent_treatments * 5);
    
    // Email engagement (20 points max)
    $email_opens = get_email_opens(90); // last 90 days
    $score += min(20, $email_opens * 5);
    
    // Workflow usage (15 points max)
    $active_workflows = count_active_workflows();
    $score += min(15, $active_workflows * 5);
    
    // Activity logger events (15 points max)
    $recent_activities = count_recent_activities(30);
    $score += min(15, $recent_activities);
    
    return min(100, $score);
}
```

#### Engagement-Based Email Rules

| Engagement Score | Email Strategy | Frequency |
|------------------|----------------|-----------|
| **80-100** (Highly Active) | Full cadence, all content types | 2x per month |
| **50-79** (Active) | Monthly summaries + major updates | 1x per month |
| **25-49** (Declining) | Re-engagement email, then pause | 1 re-engagement attempt |
| **0-24** (Inactive) | Automatic pause, save opt-in preference | No emails until re-engaged |

#### Re-Engagement Strategy

**When score drops below 25:**

1. **Email #1: "We Miss You!" (Week 1)**
   ```
   Subject: Hey [Name], everything okay with [Site Name]?
   
   Hi [Name],
   
   I noticed WPShadow hasn't been used much on [Site Name] lately. 
   Just wanted to check in—is everything okay? Did we do something wrong?
   
   I'd love your feedback: [Quick 2-minute survey]
   
   Or if you're just busy, no worries! We'll pause these updates. 
   You can always come back in the dashboard to turn them on again.
   
   [Keep getting updates] [Pause updates for now]
   
   Cheers,
   [Founder Name]
   ```

2. **Email #2: "Last Check-In" (Week 3)**
   ```
   Subject: Final note from WPShadow
   
   Hi [Name],
   
   This is my last email unless I hear from you. I don't want to be 
   "that plugin" that won't stop emailing!
   
   If you're still using WPShadow, click here: [I'm still here!]
   
   If not, totally understand. We'll keep watching your site quietly 
   in the background, but no more emails.
   
   Thanks for trying WPShadow!
   
   [Founder Name]
   ```

3. **After Week 3: Automatic Pause**
   - Set `wpshadow_email_paused` = `true`
   - Preserve opt-in status (don't opt them out)
   - User can re-enable via dashboard

---

## Personalization Framework

### Data Sources for Personalization

#### 1. **Site Information**
```php
$site_data = array(
    'site_name'        => get_bloginfo('name'),
    'site_url'         => get_bloginfo('url'),
    'admin_email'      => get_option('admin_email'),
    'wp_version'       => get_bloginfo('version'),
    'php_version'      => phpversion(),
    'plugins_count'    => count(get_option('active_plugins')),
    'theme_name'       => wp_get_theme()->get('Name'),
    'install_date'     => get_option('wpshadow_install_date'),
);
```

#### 2. **WPShadow Activity Data**
```php
$activity_data = array(
    'total_diagnostics_run'  => count_total_diagnostics(),
    'total_treatments_applied' => count_total_treatments(),
    'issues_fixed'           => count_issues_fixed(),
    'last_scan_date'         => get_last_scan_date(),
    'active_workflows'       => count_active_workflows(),
    'health_score'           => get_overall_health_score(),
    'health_improvement'     => calculate_health_improvement(),
    'time_saved_hours'       => calculate_time_saved(),
    'top_categories_fixed'   => get_top_categories_fixed(),
);
```

#### 3. **User Behavior Data**
```php
$user_data = array(
    'first_name'           => get_user_meta($user_id, 'first_name', true),
    'last_dashboard_visit' => get_option('wpshadow_last_dashboard_access'),
    'favorite_features'    => get_most_used_features(),
    'learning_style'       => get_preferred_content_type(), // video, text, etc.
    'engagement_level'     => wpshadow_calculate_engagement_score(),
    'days_since_install'   => days_since_wpshadow_install(),
);
```

#### 4. **Comparison Data (Optional)**
```php
$benchmark_data = array(
    'health_vs_average'    => compare_to_average_site(),
    'percentile_rank'      => calculate_percentile_rank(),
    'similar_sites_fixed'  => get_common_issues_in_segment(),
);
```

### Personalization Variables

Use these in email templates:

| Variable | Example Value | Usage |
|----------|---------------|-------|
| `{{site_name}}` | "Acme Coffee Shop" | Subject lines, greetings |
| `{{first_name}}` | "Sarah" | Personal greeting |
| `{{issues_fixed}}` | 47 | Accomplishment stats |
| `{{health_score}}` | 87 | Current status |
| `{{health_improvement}}` | +23% | Progress over time |
| `{{days_active}}` | 42 | Tenure milestone |
| `{{top_fix_category}}` | "Security" | Most helped area |
| `{{time_saved}}` | 12 hours | Value delivered |
| `{{next_recommendation}}` | "Enable automated backups" | Actionable next step |

---

## Content Ideas & Templates

### Email #1: Welcome (Trigger: First Scan Complete)

**Trigger:** After first diagnostic scan completes (or Day 3 fallback if no activity)

**Subject:** `Hi {{first_name}}, I'm [Founder], and I want to help {{site_name}} succeed`

**Body:**
```
Hi {{first_name}},

I'm [Founder Name], the creator of WPShadow. I wanted to personally welcome 
you and say thanks for trusting us with {{site_name}}.

I built WPShadow because I was tired of WordPress management tools that feel 
like they're trying to sell you something every five minutes. I wanted 
something that just... helps. Like a good neighbor would.

Here's what WPShadow has done for {{site_name}} so far:

✅ Ran {{diagnostic_count}} diagnostics
✅ Fixed {{issues_fixed}} issues automatically
✅ Improved your health score by {{health_improvement}}%
✅ Saved you approximately {{time_saved}} hours of manual work

Pretty cool, right?

Over the next few weeks, I'll send you occasional updates about {{site_name}}'s 
progress. I promise to keep these:

• Short (under 2 minutes to read)
• Personal (based on YOUR site, not generic advice)
• Helpful (things you can actually use)
• Rare (1-2 per month max)

If you ever want to stop getting these, there's an unsubscribe link at the 
bottom. No hard feelings!

In the meantime, here are three things you might want to check out:

1. [Dashboard Overview] - See everything WPShadow is monitoring
2. [Your First Workflow] - Automate even more (it's easier than you think!)
3. [Free Training: WordPress Security Basics] - 5-minute video

Looking forward to helping {{site_name}} thrive!

Cheers,
[Founder Name]
[Founder Title]
WPShadow

P.S. - Seriously, if you have questions or feedback, just reply to this email. 
I actually read them. 📧
```

**Tone:** Warm, personal, humble, helpful  
**Links:** 3 maximum (dashboard, workflow guide, training)  
**Call-to-Action:** Soft (explore features, not "buy now")

---

### Email #2: Quick Win (Trigger: First Fixes Applied)

**Trigger:** After 3+ issues fixed OR 5+ diagnostics run (or Day 7 fallback)

**Subject:** `{{site_name}} is already {{improvement}}% healthier!`

**Body:**
```
Hi {{first_name}},

Quick update on {{site_name}}—and I think you'll like this.

In just one week with WPShadow:

🔐 Security: {{security_fixes}} vulnerabilities fixed
⚡ Performance: {{performance_fixes}} speed improvements
✅ Health: Overall score improved from {{old_score}} → {{new_score}}

Here's the cool part: All of that happened automatically. You didn't have 
to lift a finger.

The most impactful fix? {{top_fix_description}}. Here's what that means for 
your site: [Learn more: KB article link]

## What's Next?

WPShadow is still watching {{site_name}} 24/7. We'll keep fixing things as 
we find them, and I'll send you a summary at the end of the month.

Want to take it further? Here are two things that match how you're using WPShadow:

1. {{personalized_suggestion_1}} [Quick guide]
2. {{personalized_suggestion_2}} [Video tutorial]

Keep up the great work!

[Founder Name]

P.S. - I noticed you haven't set up any workflows yet. They're basically 
"if this, then that" rules for your site. Takes 2 minutes to set up, saves 
hours down the road. [Try your first workflow]
```

**Tone:** Celebratory, accomplishment-focused  
**Data-Driven:** All stats are real from their site  
**Actionable:** 2-3 next steps based on their usage pattern

---

### Email #3: Educational (Trigger: Engagement Milestone)

**Trigger:** After first workflow created OR dashboard visited 3+ times (or Day 14 fallback)

**Subject:** `What WPShadow is watching on {{site_name}} (and why it matters)`

**Body:**
```
Hi {{first_name}},

You know how WPShadow is always running diagnostics on {{site_name}}? 
I thought you might want to know what we're actually checking—and why it matters.

## Your Site's Health Check (Last 24 Hours)

🔐 **Security** ({{security_score}}/100)
   We're monitoring: {{top_3_security_checks}}
   Why it matters: [2-minute read]

⚡ **Performance** ({{performance_score}}/100)
   We're monitoring: {{top_3_performance_checks}}
   Why it matters: [2-minute read]

🛡️ **Reliability** ({{reliability_score}}/100)
   We're monitoring: {{top_3_reliability_checks}}
   Why it matters: [2-minute read]

## The Helpful Neighbor Approach

Here's our philosophy: We don't just tell you "fix this!" and leave you 
confused. We explain:

• What the issue is (in plain English)
• Why it matters to YOUR site specifically
• What we can fix automatically (most things)
• How to fix what we can't (with guides)

That's the difference between a tool that yells at you and a tool that 
actually helps.

## Want to Learn More?

I created a free 15-minute course called "WordPress Health Basics" that 
walks through everything WPShadow checks. It's pretty straightforward:

[Watch: WordPress Health Basics (Free)]

Or if you're more of a reader, here's the written guide:

[Read: Understanding Your Site's Health Score]

Thanks for being part of the WPShadow family!

[Founder Name]
```

**Tone:** Educational, empowering  
**Value:** Teaches, doesn't sell  
**Links:** Training resources, KB articles

---

### Email #4: Monthly Summary (Trigger: Usage Milestone)

**Trigger:** After 10+ treatments applied OR 20+ diagnostics run (or Day 30 fallback)

**Subject:** `{{site_name}}'s December Report: {{total_fixes}} fixes, {{time_saved}} hours saved`

**Body:**
```
Hi {{first_name}},

It's been a month since WPShadow started watching {{site_name}}, and I 
wanted to share your progress report.

## December 2026 Summary for {{site_name}}

### By The Numbers
• **{{total_scans}}** diagnostic scans completed
• **{{issues_found}}** issues detected
• **{{issues_fixed}}** issues fixed automatically
• **{{time_saved}}** hours of manual work saved
• **{{health_improvement}}%** overall health improvement

### What We Fixed

**🔐 Security ({{security_fixes}} fixes)**
{{list_of_security_fixes_with_kb_links}}

**⚡ Performance ({{performance_fixes}} fixes)**
{{list_of_performance_fixes_with_kb_links}}

**🛡️ Reliability ({{reliability_fixes}} fixes)**
{{list_of_reliability_fixes_with_kb_links}}

### Your Biggest Win This Month

{{detailed_description_of_top_fix}}

This fix alone {{impact_description}}. Not bad for a free plugin, right? 😊

### How {{site_name}} Compares

Your site is now healthier than {{percentile}}% of WordPress sites we monitor.
(That's {{comparison_description}})

### Looking Ahead

Based on {{site_name}}'s patterns, here are three things you might want 
to tackle in January:

1. {{personalized_recommendation_1}}
   Why: {{reason_1}}
   How: [Quick guide]

2. {{personalized_recommendation_2}}
   Why: {{reason_2}}
   How: [Video tutorial]

3. {{personalized_recommendation_3}}
   Why: {{reason_3}}
   How: [KB article]

## Share Your Success?

Sites using WPShadow are measurably healthier, and I'd love to share 
your success story (anonymously, of course). Mind if I use {{site_name}}'s 
stats in an upcoming case study?

[Yes, share my success] [No thanks]

Keep up the great work, {{first_name}}!

[Founder Name]

P.S. - Got feedback? Questions? Want me to add a specific diagnostic? 
Just reply—I read every email.

---

💡 **Did You Know?**
{{random_helpful_tip_based_on_their_usage}}
[Learn more]
```

**Tone:** Accomplishment-focused, data-rich, forward-looking  
**Value:** Comprehensive report with actionable insights  
**Personalization:** Heavy use of real site data

---

### Special Email: Re-Engagement (When engagement drops)

**Subject:** `Hey {{first_name}}, everything okay with {{site_name}}?`

**Body:**
```
Hi {{first_name}},

I noticed WPShadow hasn't been used much on {{site_name}} lately, and I 
wanted to check in.

Is everything okay? Did something not work right? Did I send too many emails?

I'm not asking because I want to sell you something—I genuinely want to 
know if we're being helpful or if we're in the way.

If you've got a minute, I'd love your feedback:
[Quick 2-question survey] (Seriously, just 2 questions)

Or if you're just busy and don't need updates right now, that's totally fine! 
I'll pause these emails and WPShadow will keep working quietly in the background.

[Pause emails, keep WPShadow working] [I'm still here, keep the updates coming!]

Thanks for trying WPShadow, {{first_name}}. I hope {{site_name}} is thriving!

[Founder Name]

P.S. - For what it's worth, WPShadow has still been monitoring {{site_name}} 
and has fixed {{recent_fixes}} issues in the background. You've got a good 
neighbor even if you don't open the dashboard! 😊
```

**Tone:** Humble, concerned, genuinely caring  
**Purpose:** Re-engage or gracefully exit  
**No Pressure:** Makes it easy to pause or unsubscribe

---

### Special Email: Major Milestone

**Subject:** `🎉 {{site_name}} just hit a major milestone!`

**Body:**
```
Hi {{first_name}},

I had to reach out because {{site_name}} just hit a pretty cool milestone:

🎯 **{{milestone_number}} issues fixed by WPShadow!**

That's {{milestone_number}} things that could've slowed down your site, 
compromised security, or caused headaches—all handled automatically.

Here's what those {{milestone_number}} fixes added up to:

• **{{security_fixes}}** security vulnerabilities closed
• **{{performance_fixes}}** performance improvements
• **{{total_hours}}** hours of manual work saved
• **{{health_score_change}}%** improvement in overall health

The average site? They're dealing with {{average_issues}} unresolved issues 
right now. You're crushing it.

## Want to Go Even Further?

{{site_name}} is already in great shape, but here are three advanced moves 
that could take it to the next level:

1. {{advanced_recommendation_1}}
2. {{advanced_recommendation_2}}
3. {{advanced_recommendation_3}}

Or just keep doing what you're doing! WPShadow will keep watching and fixing 
things automatically.

Congrats on the milestone, {{first_name}}! 🎉

[Founder Name]

P.S. - Mind if I share this milestone (anonymously) as a success story? 
I love showing people what's possible with good WordPress management.
[Sure!] [No thanks]
```

**Tone:** Celebratory, proud, appreciative  
**Purpose:** Recognition and celebration  
**Social Proof:** Encourages sharing success

---

## Technical Implementation

### Phase 1: Foundation (Week 1-2)

#### 1.1 Database Schema
```php
/**
 * Email marketing consent and tracking
 */
global $wpdb;
$table_name = $wpdb->prefix . 'wpshadow_email_marketing';

$sql = "CREATE TABLE {$table_name} (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_email varchar(255) NOT NULL,
    site_url varchar(255) NOT NULL,
    consent_status varchar(20) NOT NULL DEFAULT 'pending',
    consent_date datetime DEFAULT NULL,
    last_email_sent datetime DEFAULT NULL,
    email_count int(11) DEFAULT 0,
    engagement_score int(11) DEFAULT 0,
    paused tinyint(1) DEFAULT 0,
    unsubscribed tinyint(1) DEFAULT 0,
    preferences text,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY  (id),
    UNIQUE KEY user_email_site (user_email, site_url),
    KEY consent_status (consent_status),
    KEY engagement_score (engagement_score)
) {$wpdb->get_charset_collate()};";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);
```

#### 1.2 Consent UI Component
**File:** `includes/admin/class-email-consent-ui.php`

```php
<?php
namespace WPShadow\Admin;

class Email_Consent_UI {
    
    public static function render_opt_in_modal() {
        ?>
        <div id="wpshadow-email-consent-modal" class="wpshadow-modal">
            <div class="wpshadow-modal-content">
                <h2><?php esc_html_e('Stay Updated About Your Site', 'wpshadow'); ?></h2>
                
                <p><?php 
                    printf(
                        esc_html__('WPShadow has completed its first scan of %s!', 'wpshadow'),
                        '<strong>' . esc_html(get_bloginfo('name')) . '</strong>'
                    );
                ?></p>
                
                <p><?php esc_html_e('Would you like personalized updates about your site\'s health and improvements?', 'wpshadow'); ?></p>
                
                <div class="wpshadow-consent-options">
                    <label>
                        <input type="checkbox" name="monthly_summary" checked>
                        <?php esc_html_e('Monthly site health summaries', 'wpshadow'); ?>
                    </label>
                    <label>
                        <input type="checkbox" name="tips_and_education" checked>
                        <?php esc_html_e('Tips based on my site\'s activity', 'wpshadow'); ?>
                    </label>
                    <label>
                        <input type="checkbox" name="feature_updates">
                        <?php esc_html_e('New features that match my usage', 'wpshadow'); ?>
                    </label>
                </div>
                
                <p class="wpshadow-frequency-note">
                    <?php esc_html_e('Frequency: 1-2 emails per month. Unsubscribe anytime.', 'wpshadow'); ?>
                </p>
                
                <div class="wpshadow-modal-actions">
                    <button type="button" class="button button-primary" id="wpshadow-consent-yes">
                        <?php esc_html_e('Yes, Send Me Updates', 'wpshadow'); ?>
                    </button>
                    <button type="button" class="button" id="wpshadow-consent-no">
                        <?php esc_html_e('No Thanks', 'wpshadow'); ?>
                    </button>
                </div>
                
                <p class="wpshadow-privacy-note">
                    <?php 
                    printf(
                        esc_html__('Privacy: We never share your email. %s', 'wpshadow'),
                        '<a href="https://wpshadow.com/privacy" target="_blank">' . 
                        esc_html__('View our privacy policy', 'wpshadow') . '</a>'
                    );
                    ?>
                </p>
            </div>
        </div>
        <?php
    }
}
```

#### 1.3 Email Queue System
**File:** `includes/email/class-email-queue.php`

```php
<?php
namespace WPShadow\Email;

class Email_Queue {
    
    const QUEUE_OPTION = 'wpshadow_email_queue';
    
    /**
     * Add email to queue
     */
    public static function enqueue($user_email, $template_id, $data = array(), $send_after = null) {
        $queue = get_option(self::QUEUE_OPTION, array());
        
        $email = array(
            'id' => uniqid('email_', true),
            'user_email' => sanitize_email($user_email),
            'template_id' => sanitize_key($template_id),
            'data' => $data,
            'send_after' => $send_after ?: current_time('timestamp'),
            'status' => 'pending',
            'created_at' => current_time('mysql'),
        );
        
        $queue[] = $email;
        update_option(self::QUEUE_OPTION, $queue);
        
        return $email['id'];
    }
    
    /**
     * Process queue (called by cron)
     */
    public static function process_queue() {
        $queue = get_option(self::QUEUE_OPTION, array());
        $now = current_time('timestamp');
        $processed = array();
        
        foreach ($queue as $key => $email) {
            // Skip if not ready to send
            if ($email['send_after'] > $now) {
                continue;
            }
            
            // Check engagement before sending
            $engagement_score = Engagement_Tracker::get_score($email['user_email']);
            if ($engagement_score < 25) {
                // User is inactive, skip
                $email['status'] = 'skipped_inactive';
                $processed[] = $email;
                unset($queue[$key]);
                continue;
            }
            
            // Send email
            $result = Email_Sender::send($email['template_id'], $email['user_email'], $email['data']);
            
            if ($result['success']) {
                $email['status'] = 'sent';
                $email['sent_at'] = current_time('mysql');
            } else {
                $email['status'] = 'failed';
                $email['error'] = $result['message'];
            }
            
            $processed[] = $email;
            unset($queue[$key]);
        }
        
        // Update queue
        update_option(self::QUEUE_OPTION, array_values($queue));
        
        // Log processed emails
        if (!empty($processed)) {
            update_option('wpshadow_email_history', $processed, false);
        }
        
        return count($processed);
    }
}
```

### Phase 2: Email Templates (Week 3)

#### 2.1 Template Engine
**File:** `includes/email/class-email-template-engine.php`

```php
<?php
namespace WPShadow\Email;

class Email_Template_Engine {
    
    /**
     * Render template with personalization
     */
    public static function render($template_id, $user_email, $data = array()) {
        // Get user personalization data
        $personalization = self::get_personalization_data($user_email);
        
        // Merge with provided data
        $merged_data = array_merge($personalization, $data);
        
        // Get template
        $template = self::get_template($template_id);
        
        // Replace variables
        $subject = self::replace_variables($template['subject'], $merged_data);
        $body = self::replace_variables($template['body'], $merged_data);
        
        // Wrap in HTML template
        $html = \WPShadow\Utils\Email_Service::build_html_template($body, $subject);
        
        return array(
            'subject' => $subject,
            'body' => $html,
            'data' => $merged_data,
        );
    }
    
    /**
     * Get personalization data for user
     */
    private static function get_personalization_data($user_email) {
        global $wpdb;
        
        return array(
            // Site info
            'site_name' => get_bloginfo('name'),
            'site_url' => get_bloginfo('url'),
            
            // User info
            'first_name' => self::get_first_name($user_email),
            'user_email' => $user_email,
            
            // WPShadow stats
            'total_diagnostics' => self::get_total_diagnostics(),
            'total_treatments' => self::get_total_treatments(),
            'issues_fixed' => self::get_issues_fixed(),
            'health_score' => self::get_health_score(),
            'health_improvement' => self::get_health_improvement(),
            'time_saved' => self::calculate_time_saved(),
            'days_active' => self::get_days_since_install(),
            
            // Engagement
            'engagement_score' => Engagement_Tracker::get_score($user_email),
            'last_activity' => self::get_last_activity(),
            
            // Timestamps
            'current_month' => date('F'),
            'current_year' => date('Y'),
        );
    }
    
    /**
     * Replace {{variables}} in template
     */
    private static function replace_variables($text, $data) {
        foreach ($data as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }
        return $text;
    }
}
```

### Phase 3: Engagement Tracking (Week 4)

#### 3.1 Engagement Tracker
**File:** `includes/email/class-engagement-tracker.php`

```php
<?php
namespace WPShadow\Email;

use WPShadow\Core\Activity_Logger;

class Engagement_Tracker {
    
    /**
     * Calculate engagement score (0-100)
     */
    public static function get_score($user_email) {
        $score = 0;
        
        // Dashboard access (30 points)
        $last_access = get_option('wpshadow_last_dashboard_access');
        if ($last_access > strtotime('-7 days')) {
            $score += 30;
        } elseif ($last_access > strtotime('-30 days')) {
            $score += 20;
        } elseif ($last_access > strtotime('-90 days')) {
            $score += 10;
        }
        
        // Recent treatments (20 points)
        $recent_treatments = self::count_recent_treatments(30);
        $score += min(20, $recent_treatments * 5);
        
        // Email engagement (20 points)
        $email_opens = self::get_email_opens($user_email, 90);
        $score += min(20, $email_opens * 5);
        
        // Workflow usage (15 points)
        $active_workflows = self::count_active_workflows();
        $score += min(15, $active_workflows * 5);
        
        // Activity (15 points)
        $activities = Activity_Logger::get_activities(array(), 100, 0);
        $recent_count = count(array_filter($activities['activities'], function($a) {
            return $a['timestamp'] > strtotime('-30 days');
        }));
        $score += min(15, $recent_count);
        
        return min(100, $score);
    }
    
    /**
     * Update engagement after email interaction
     */
    public static function track_email_open($user_email, $email_id) {
        $history = get_option('wpshadow_email_engagement', array());
        
        if (!isset($history[$user_email])) {
            $history[$user_email] = array();
        }
        
        $history[$user_email][] = array(
            'email_id' => $email_id,
            'action' => 'open',
            'timestamp' => current_time('timestamp'),
        );
        
        update_option('wpshadow_email_engagement', $history);
    }
    
    /**
     * Track email link click
     */
    public static function track_email_click($user_email, $email_id, $link_id) {
        $history = get_option('wpshadow_email_engagement', array());
        
        if (!isset($history[$user_email])) {
            $history[$user_email] = array();
        }
        
        $history[$user_email][] = array(
            'email_id' => $email_id,
            'action' => 'click',
            'link_id' => $link_id,
            'timestamp' => current_time('timestamp'),
        );
        
        update_option('wpshadow_email_engagement', $history);
    }
}
```

### Phase 4: Scheduling System (Week 5)

#### 4.1 Email Scheduler
**File:** `includes/email/class-email-scheduler.php`

```php
<?php
namespace WPShadow\Email;

use WPShadow\Core\Activity_Logger;

class Email_Scheduler {
    
    /**
     * Schedule email campaign after opt-in
     * Uses feature-based triggers with time-based fallback
     */
    public static function schedule_campaign($user_email) {
        $install_date = get_option('wpshadow_install_date');
        $days_since_install = (current_time('timestamp') - strtotime($install_date)) / DAY_IN_SECONDS;
        
        // Trigger 1: Welcome email
        // Primary: After first diagnostic scan completes
        // Fallback: Day 3 if no activity
        $first_scan_completed = get_option('wpshadow_first_scan_completed');
        if ($first_scan_completed) {
            // Schedule immediately after first scan
            Email_Queue::enqueue(
                $user_email,
                'welcome',
                array(),
                current_time('timestamp')
            );
        } elseif ($days_since_install < 3) {
            // Fallback to Day 3
            Email_Queue::enqueue(
                $user_email,
                'welcome',
                array(),
                strtotime($install_date) + (3 * DAY_IN_SECONDS)
            );
        }
        
        // Trigger 2: Quick win email
        // Primary: After 3+ issues fixed OR 5+ diagnostics run
        // Fallback: Day 7 if milestones not reached
        $treatments_applied = self::count_treatments_applied();
        $diagnostics_run = self::count_diagnostics_run();
        
        if ($treatments_applied >= 3 || $diagnostics_run >= 5) {
            // Schedule based on feature use
            Email_Queue::enqueue(
                $user_email,
                'quick_win',
                array(),
                current_time('timestamp') + DAY_IN_SECONDS // Next day
            );
        } elseif ($days_since_install < 7) {
            // Fallback to Day 7
            Email_Queue::enqueue(
                $user_email,
                'quick_win',
                array(),
                strtotime($install_date) + (7 * DAY_IN_SECONDS)
            );
        }
        
        // Trigger 3: Educational email
        // Primary: After first workflow created OR dashboard visited 3+ times
        // Fallback: Day 14 if engagement milestones not reached
        $workflows_created = self::count_workflows_created();
        $dashboard_visits = self::count_dashboard_visits();
        
        if ($workflows_created >= 1 || $dashboard_visits >= 3) {
            // Schedule based on engagement
            Email_Queue::enqueue(
                $user_email,
                'educational',
                array(),
                current_time('timestamp') + (2 * DAY_IN_SECONDS) // 2 days later
            );
        } elseif ($days_since_install < 14) {
            // Fallback to Day 14
            Email_Queue::enqueue(
                $user_email,
                'educational',
                array(),
                strtotime($install_date) + (14 * DAY_IN_SECONDS)
            );
        }
        
        // Trigger 4: First monthly summary
        // Primary: After 10+ treatments OR 20+ diagnostics
        // Fallback: Day 30 if usage milestones not reached
        if ($treatments_applied >= 10 || $diagnostics_run >= 20) {
            // Schedule based on usage milestone
            Email_Queue::enqueue(
                $user_email,
                'monthly_summary',
                array(),
                current_time('timestamp') + (3 * DAY_IN_SECONDS) // 3 days later
            );
        } elseif ($days_since_install < 30) {
            // Fallback to Day 30
            Email_Queue::enqueue(
                $user_email,
                'monthly_summary',
                array(),
                strtotime($install_date) + (30 * DAY_IN_SECONDS)
            );
        }
    }
    
    /**
     * Count total treatments applied
     */
    private static function count_treatments_applied() {
        $activities = Activity_Logger::get_activities(
            array('action' => 'treatment_applied'),
            1000,
            0
        );
        return $activities['total'];
    }
    
    /**
     * Count total diagnostics run
     */
    private static function count_diagnostics_run() {
        $activities = Activity_Logger::get_activities(
            array('category' => 'diagnostics'),
            1000,
            0
        );
        return $activities['total'];
    }
    
    /**
     * Count workflows created
     */
    private static function count_workflows_created() {
        $activities = Activity_Logger::get_activities(
            array('action' => 'workflow_created'),
            1000,
            0
        );
        return $activities['total'];
    }
    
    /**
     * Count dashboard visits
     */
    private static function count_dashboard_visits() {
        $activities = Activity_Logger::get_activities(
            array('action' => 'dashboard_accessed'),
            1000,
            0
        );
        return $activities['total'];
    }
    
    /**
     * Schedule recurring monthly summaries
     */
    public static function schedule_monthly_summary($user_email) {
        // Schedule for the 1st of next month
        $next_month = strtotime('first day of next month 09:00:00');
        
        Email_Queue::enqueue(
            $user_email,
            'monthly_summary',
            array(),
            $next_month
        );
    }
}
```
            $user_email,
            'monthly_summary',
            array(),
            $next_month
        );
    }
}
```

### Phase 5: Cron Integration (Week 6)

```php
// In includes/core/class-hooks-initializer.php

// Add new cron schedule
add_action('wpshadow_process_email_queue', array('\\WPShadow\\Email\\Email_Queue', 'process_queue'));

// Schedule if not already scheduled
if (!wp_next_scheduled('wpshadow_process_email_queue')) {
    wp_schedule_event(time(), 'hourly', 'wpshadow_process_email_queue');
}
```

---

## Success Metrics

### Email Performance KPIs

| Metric | Target | Measurement |
|--------|--------|-------------|
| **Opt-In Rate** | 40%+ | % of users who opt in after first scan |
| **Open Rate** | 35%+ | % of emails opened |
| **Click Rate** | 15%+ | % of emails with link clicks |
| **Unsubscribe Rate** | <2% | % of users who unsubscribe |
| **Engagement Score** | 60+ avg | Average engagement score of recipients |
| **Re-engagement Success** | 25%+ | % of inactive users who re-engage |

### User Impact KPIs

| Metric | Target | Measurement |
|--------|--------|-------------|
| **Dashboard Visits** | +20% | Increase in dashboard visits after email |
| **Treatment Applications** | +15% | Increase in treatments applied |
| **Workflow Creation** | +30% | Increase in workflows created |
| **KB Article Views** | +40% | Increase in KB visits from emails |
| **Training Completions** | +25% | Increase in training video views |

### Business KPIs

| Metric | Target | Measurement |
|--------|--------|-------------|
| **User Retention** | +15% | % increase in 90-day retention |
| **Active Users** | +20% | % increase in weekly active users |
| **Word-of-Mouth** | 10+ per month | New installs mentioning emails |
| **Pro Conversions** | 5%+ | % of email recipients who upgrade |
| **Support Tickets** | -10% | Reduction in support requests |

---

## Phased Rollout Plan

### Phase 1: Internal Testing (Weeks 1-2)
- Build consent UI and database tables
- Create first 3 email templates (welcome, quick win, monthly)
- Test on internal sites only
- Refine based on team feedback

**Success Criteria:**
- ✅ Opt-in flow works smoothly
- ✅ Emails render correctly in major clients
- ✅ Personalization variables populate correctly
- ✅ Unsubscribe works

### Phase 2: Beta Program (Weeks 3-4)
- Invite 50-100 existing power users
- Send welcome email series
- Gather feedback via survey
- Iterate on templates and timing

**Success Criteria:**
- ✅ 40%+ opt-in rate
- ✅ 30%+ open rate
- ✅ <3% unsubscribe rate
- ✅ Positive qualitative feedback

### Phase 3: Limited Release (Weeks 5-6)
- Roll out to new installs only (10% of new users)
- Monitor engagement metrics
- A/B test subject lines and content
- Refine engagement scoring

**Success Criteria:**
- ✅ Metrics meet or exceed targets
- ✅ No technical issues
- ✅ Engagement scores accurately predict behavior
- ✅ Queue processing performant

### Phase 4: Full Release (Week 7+)
- Roll out to all new installs
- Offer opt-in to existing users via dashboard
- Continue monitoring and iterating
- Expand template library

**Success Criteria:**
- ✅ 40%+ opt-in rate maintained
- ✅ All KPIs in green zone
- ✅ System scales without performance issues
- ✅ User feedback remains positive

---

## Privacy & Compliance

### GDPR Compliance

#### Consent Requirements
- ✅ **Explicit Consent:** Clear opt-in checkbox, not pre-checked
- ✅ **Granular Control:** Separate options for different email types
- ✅ **Easy Withdrawal:** One-click unsubscribe in every email
- ✅ **Consent Record:** Timestamp and record of what was agreed to
- ✅ **Data Minimization:** Only collect email and preferences

#### User Rights
- ✅ **Right to Access:** Users can see all emails sent to them
- ✅ **Right to Erasure:** Unsubscribe removes them from all lists
- ✅ **Right to Portability:** Export email history as CSV/JSON
- ✅ **Right to Object:** Granular control over email types

#### Data Storage
```php
/**
 * GDPR Data Export
 */
function wpshadow_export_email_data($user_email) {
    return array(
        'consent' => array(
            'status' => get_consent_status($user_email),
            'date' => get_consent_date($user_email),
            'preferences' => get_preferences($user_email),
        ),
        'emails_sent' => get_email_history($user_email),
        'engagement' => get_engagement_history($user_email),
    );
}

/**
 * GDPR Data Erasure
 */
function wpshadow_erase_email_data($user_email) {
    delete_consent($user_email);
    delete_email_history($user_email);
    delete_engagement_history($user_email);
    remove_from_queue($user_email);
}
```

### CAN-SPAM Compliance (US)

- ✅ **Clear Sender Identity:** "From [Founder Name] at WPShadow"
- ✅ **Accurate Subject Lines:** No deceptive headers
- ✅ **Physical Address:** Include business address in footer
- ✅ **Unsubscribe Option:** Clear and conspicuous in every email
- ✅ **Honor Opt-Outs:** Process unsubscribe within 10 days

### CASL Compliance (Canada)

- ✅ **Express Consent:** Explicit opt-in required
- ✅ **Identification:** Clear sender identification
- ✅ **Unsubscribe Mechanism:** One-click unsubscribe
- ✅ **Record Keeping:** Maintain consent records for 3 years

---

## Integration with Existing Systems

### Activity Logger Integration

All email events should be logged:

```php
use WPShadow\Core\Activity_Logger;

// Log opt-in
Activity_Logger::log(
    'email_opt_in',
    sprintf('User opted in to email marketing with preferences: %s', implode(', ', $preferences)),
    'marketing',
    array(
        'user_email' => $email,
        'preferences' => $preferences,
    )
);

// Log email sent
Activity_Logger::log(
    'email_sent',
    sprintf('Sent "%s" email to user', $template_name),
    'marketing',
    array(
        'template_id' => $template_id,
        'user_email' => $email,
    )
);

// Log email opened
Activity_Logger::log(
    'email_opened',
    sprintf('User opened "%s" email', $template_name),
    'marketing',
    array(
        'template_id' => $template_id,
        'user_email' => $email,
    )
);
```

### Settings Integration

Add new settings page:

**WPShadow > Settings > Email Preferences**

```php
// Email preferences section
Settings_Registry::register(array(
    'wpshadow_email_consent' => array(
        'type' => 'string',
        'default' => 'pending',
        'sanitize_callback' => 'sanitize_text_field',
    ),
    'wpshadow_email_preferences' => array(
        'type' => 'array',
        'default' => array(),
        'sanitize_callback' => 'wpshadow_sanitize_email_preferences',
    ),
));
```

### Dashboard Widget

Add "Email Activity" widget to dashboard showing:
- Last email sent
- Open/click stats
- Upcoming emails
- Quick preference management

---

## Content Calendar Template

### Monthly Content Planning

| Week | Email Type | Topic | Template | Send Day |
|------|-----------|-------|----------|----------|
| **Week 1** | Educational | How [Feature] helps [Site Type] | `educational_feature` | Tuesday |
| **Week 3** | Monthly Summary | Your [Month] Report | `monthly_summary` | 1st of month |

### Quarterly Deep Dives (Optional)

| Quarter | Topic | Format |
|---------|-------|--------|
| **Q1** | Security Year in Review | Data-rich report |
| **Q2** | Performance Optimization Guide | Educational series |
| **Q3** | Workflow Automation Showcase | Case studies |
| **Q4** | Year-End Success Report | Celebration + stats |

---

## Appendix: Email Template Variables

### Complete Variable Reference

```php
// Site Information
{{site_name}}                  // "Acme Coffee Shop"
{{site_url}}                   // "https://acmecoffee.com"
{{site_tagline}}              // Site tagline/description

// User Information
{{first_name}}                // "Sarah"
{{user_email}}                // "sarah@acmecoffee.com"
{{username}}                  // WordPress username

// Time & Dates
{{current_month}}             // "January"
{{current_year}}              // "2026"
{{days_active}}               // Days since WPShadow installed
{{install_date}}              // "December 15, 2025"

// WPShadow Stats
{{total_scans}}               // Total diagnostic scans run
{{total_diagnostics}}         // Total diagnostic types run
{{total_treatments}}          // Total treatments applied
{{issues_found}}              // Issues detected (all time)
{{issues_fixed}}              // Issues resolved (all time)
{{issues_remaining}}          // Current open issues

// Health Scores
{{health_score}}              // Overall health score (0-100)
{{security_score}}            // Security subscore
{{performance_score}}         // Performance subscore
{{reliability_score}}         // Reliability subscore
{{health_improvement}}        // % change since install
{{health_trend}}              // "improving" | "stable" | "declining"

// Impact Metrics
{{time_saved}}                // Hours saved by automation
{{security_fixes}}            // Security issues fixed
{{performance_fixes}}         // Performance improvements
{{top_fix_category}}          // Most helped category

// Comparisons
{{percentile}}                // Percentile rank vs other sites
{{comparison_description}}    // "Better than average"

// Engagement
{{engagement_score}}          // User engagement score (0-100)
{{last_activity}}             // Last time user accessed dashboard
{{active_workflows}}          // Number of active workflows

// Recommendations
{{next_recommendation}}       // Next suggested action
{{personalized_suggestion_1}} // Tailored suggestion
{{personalized_suggestion_2}} // Tailored suggestion
{{personalized_suggestion_3}} // Tailored suggestion

// Milestones
{{milestone_number}}          // Milestone value (e.g., 100 fixes)
{{milestone_description}}     // Description of milestone

// Links
{{dashboard_url}}             // Link to WPShadow dashboard
{{kb_url}}                    // Link to Knowledge Base
{{training_url}}              // Link to training resources
{{unsubscribe_url}}           // One-click unsubscribe
```

---

## Next Steps

### Immediate Actions (Week 1)

1. **Review & Approve Strategy**
   - [ ] Founder reviews this document
   - [ ] Approve tone and philosophy alignment
   - [ ] Confirm timing/cadence strategy
   - [ ] Approve email templates

2. **Technical Preparation**
   - [ ] Create database schema
   - [ ] Build consent UI components
   - [ ] Set up email queue system
   - [ ] Configure cron jobs

3. **Content Creation**
   - [ ] Write welcome email template
   - [ ] Write quick win email template
   - [ ] Write monthly summary template
   - [ ] Create re-engagement template

### Planning Session Agenda

1. **Philosophy Questions**
   - Does the "Helpful Neighbor" tone match your vision?
   - Is the timing strategy (1-2 emails/month) appropriate?
   - Should we add any email types?

2. **Technical Questions**
   - Should we use an external email service (Mailgun, SendGrid) or wp_mail()?
   - How do we want to handle email tracking (opens/clicks)?
   - Should email preferences sync with cloud account (if user has one)?

3. **Content Questions**
   - Who should emails come "from"? (Founder name + title?)
   - Should we include photos/avatars to make it more personal?
   - Any specific topics/features to highlight in early emails?

4. **Metrics Questions**
   - What success metrics matter most to you?
   - How often should we review and adjust strategy?
   - When do we consider this a success?

---

## Conclusion

This email marketing strategy is designed to be **ridiculously helpful**, not just another marketing campaign. Every email should make users think: "Wow, they actually know my site and care about my success."

Key principles:
- ✅ **Personalized:** Real data from their actual site
- ✅ **Educational:** Teach, don't sell
- ✅ **Respectful:** Honor engagement signals, easy to unsubscribe
- ✅ **Authentic:** Personal tone, genuinely helpful
- ✅ **Valuable:** Every email should be worth opening

When done right, these emails won't feel like marketing—they'll feel like updates from a trusted friend who happens to be watching over their WordPress site.

**This is the kind of email campaign users want to receive.**

---

**Questions?** Reply to this planning doc or schedule a review meeting.

**Ready to implement?** Start with Phase 1 (database + consent UI) and work through the phased rollout plan.
