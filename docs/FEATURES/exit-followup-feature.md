# Exit Interview Followup Feature

## Overview

This feature implements a comprehensive exit interview followup system that schedules personalized contact with users who gave permission during plugin deactivation. It's designed to genuinely understand why users left and what we could improve, not to try to win them back.

## Philosophy

Aligned with WPShadow's core principles:

- **Helpful Neighbor:** Questions are conversational and show genuine curiosity
- **Advice Not Sales:** We want to learn, not persuade them to come back
- **Beyond Pure Privacy:** GDPR-compliant with explicit consent and easy opt-out
- **Everything Has a KPI:** All actions logged for measurement and improvement

## Architecture

### Database Schema

Two new tables added in schema version 2:

**`wpshadow_exit_interviews`**
Stores exit interview data with contact permissions:
- User details (user_id, contact_email)
- Exit context (exit_date, exit_reason, detailed_feedback)
- Competitive info (competitor_name, features_needed)
- Usage analytics (usage_duration_days, features_used, site_type)
- Consent (contact_allowed)

**`wpshadow_exit_followups`**
Manages scheduled followup contacts:
- Interview reference (interview_id)
- Scheduling (followup_type, scheduled_date, completed_date, status)
- Survey data (survey_questions, survey_responses)
- Contact method and notes

### Core Classes

**`Exit_Followup_Manager`** (`includes/engagement/class-exit-followup-manager.php`)
- Records exit interviews with consent
- Calculates optimal followup timing based on exit context
- Schedules multiple followups intelligently
- Tracks followup status and responses
- Provides statistics for dashboard

**`Exit_Survey_Builder`** (`includes/engagement/class-exit-survey-builder.php`)
- Generates personalized survey questions
- Three survey types:
  1. **Competitor Analysis:** When user mentions switching to another plugin
  2. **Feature Needs:** When user left due to missing features
  3. **General Followup:** Catch-all for other exit reasons
- Validates survey responses

### Admin Interface

**Exit Followups Page** (`includes/screens/class-exit-followups-page.php`)
- Located at: WP Admin → WPShadow → Exit Followups
- Statistics dashboard showing:
  - Total interviews with contact permission
  - Pending followups
  - Due now count
  - Completed followups
- Filterable table by status
- Action buttons for status management
- Responsive design with modern UI

### AJAX Handlers

**`exit-followup-handlers.php`**
Three handlers grouped together:
1. `Get_Exit_Followups_Handler` - Retrieve followups with filtering
2. `Update_Exit_Followup_Handler` - Update status and notes
3. `Cancel_Exit_Followups_Handler` - Cancel all pending for an interview

## Intelligent Scheduling Algorithm

The system analyzes exit interview context and schedules followups strategically:

### Immediate Followup (3 days)
**Triggers:** User mentioned a competitor plugin
**Purpose:** Competitive intelligence gathering
**Priority:** High
**Questions:**
- How's the competitor working out?
- What does it do better than WPShadow?
- What would have kept you with us?

### Short-term Followup (14 days)
**Triggers:** User mentioned missing features OR exit reason was "missing_features"
**Purpose:** Feature needs deep dive
**Priority:** Medium
**Questions:**
- Did you find an alternative solution?
- Can you describe the specific features?
- How critical was this feature?
- What's your feature priority?

### Long-term Followup (30 days)
**Triggers:** Always scheduled
**Purpose:** General retrospective feedback
**Priority:** Low
**Questions:**
- Overall experience rating
- What worked well?
- What could we improve?
- What are you using now?
- Would you recommend us for specific use cases?

## Personalized Survey Questions

### Competitor Analysis Survey
Generated when user switches to a competitor:
```php
1. Rating: "How's [competitor] working out so far?" (1-5 scale)
2. Text: "What does [competitor] do better than WPShadow?"
3. Multiple Choice: "Which of these would have kept you with WPShadow?"
4. Text: "Is there a specific type of website where you'd still recommend WPShadow?"
5. Yes/No: "If we address these issues, would you like us to let you know?"
```

### Feature Needs Survey
Generated when features were missing:
```php
1. Yes/No: "Did you find another solution that has the features you needed?"
2. Text: "Can you describe the specific features you were looking for?"
3. Rating: "How critical was this feature to your workflow?" (1-5 scale)
4. Multiple Choice: "Which types of features matter most to your workflow?"
5. Text: "Did you try any workarounds before deciding to leave?"
6. Yes/No: "If we implemented these features, would you consider coming back?"
```

### General Followup Survey
Default survey for all exits:
```php
1. Rating: "How would you rate your overall experience with WPShadow?" (1-5 scale)
2. Text: "What did WPShadow do well?"
3. Text: "What could we have done better?"
4. Text: "What are you using now instead of WPShadow?"
5. NPS: "How likely would you recommend WPShadow to a friend?" (0-10 scale)
6. Text: "Is there anything we could help you with, even if you're not using WPShadow?"
```

## Usage Example

### Recording an Exit Interview

```php
use WPShadow\Engagement\Exit_Followup_Manager;

// User completes exit interview during deactivation
$interview_id = Exit_Followup_Manager::record_exit_interview([
    'user_id'             => get_current_user_id(),
    'exit_reason'         => 'missing_features',
    'detailed_feedback'   => 'I needed more advanced analytics capabilities',
    'competitor_name'     => 'AnalyticsPlugin Pro',
    'features_needed'     => 'Real-time traffic monitoring, custom dashboards',
    'contact_allowed'     => true,  // User gave permission
    'contact_email'       => 'user@example.com',
    'usage_duration_days' => 45,    // Used plugin for 45 days
    'features_used'       => ['diagnostics', 'treatments', 'workflows'],
    'site_type'           => 'ecommerce'
]);

// This automatically schedules 3 followups:
// 1. Day 3: Competitor analysis survey
// 2. Day 14: Feature needs survey  
// 3. Day 30: General feedback survey
```

### Processing Due Followups

```php
// Get followups that are due (for cron job or manual processing)
$due_followups = Exit_Followup_Manager::get_due_followups();

foreach ($due_followups as $followup) {
    // Get personalized survey questions
    $survey = json_decode($followup['survey_questions'], true);
    
    // Send email with survey link
    // (Email sending implementation required)
    
    // Mark as sent
    Exit_Followup_Manager::update_followup_status(
        $followup['id'],
        'sent',
        ['notes' => 'Email sent successfully']
    );
}
```

### Getting Statistics

```php
$stats = Exit_Followup_Manager::get_statistics();

echo $stats['total_interviews_with_contact'];  // e.g., 42
echo $stats['followups_by_status']['pending']; // e.g., 15
echo $stats['followups_by_status']['completed']; // e.g., 8
echo $stats['pending_due_count']; // e.g., 3
```

## Settings

Configure followup timing in WordPress admin:

```php
// Enable/disable the entire followup system
get_option('wpshadow_exit_followup_enabled'); // bool, default: true

// Customize timing (in days)
get_option('wpshadow_exit_followup_immediate_days');  // default: 3, range: 1-90
get_option('wpshadow_exit_followup_short_term_days'); // default: 14, range: 1-90
get_option('wpshadow_exit_followup_long_term_days');  // default: 30, range: 1-90

// Auto-send emails (requires email service integration)
get_option('wpshadow_exit_followup_auto_send'); // bool, default: false
```

## Activity Logging

All actions are logged for KPI tracking:

- `exit_interview_recorded` - When user data is captured
- `exit_followups_scheduled` - When followups are planned
- `exit_followup_status_changed` - When status updates occur
- `exit_followups_cancelled` - When followups are cancelled

## Privacy & Security

### GDPR Compliance
- ✅ Explicit consent required (`contact_allowed` field)
- ✅ Contact email stored securely
- ✅ User can cancel anytime (via `cancel_followups()`)
- ✅ Data retention configurable
- ✅ Consent audit trail in activity log

### Security Measures
- ✅ SQL injection prevention (all queries use `$wpdb->prepare()`)
- ✅ Nonce verification (all AJAX requests)
- ✅ Capability checks (`manage_options` required)
- ✅ Input sanitization (`sanitize_text_field`, `sanitize_email`, etc.)
- ✅ Output escaping (`esc_html`, `esc_attr`, `esc_url`)

### CodeQL Security Scan
**Result:** ✅ 0 vulnerabilities detected

## Future Enhancements

1. **Email Integration**
   - Automated email sending via transactional email service
   - Email templates for each survey type
   - Unsubscribe link generation

2. **Detailed View Modal**
   - Full survey display in admin
   - Response viewing
   - Contact history timeline

3. **Analytics Dashboard**
   - Followup response rates
   - Common exit reasons aggregated
   - Competitor analysis summary
   - Feature request aggregation and prioritization

4. **Testing**
   - Unit tests for `Exit_Followup_Manager`
   - Unit tests for `Exit_Survey_Builder`
   - Integration tests for AJAX handlers
   - Browser tests for admin interface

## Files Structure

```
includes/
├── core/
│   ├── class-database-migrator.php (+67 lines)
│   ├── class-plugin-bootstrap.php (+28 lines)
│   └── class-settings-registry.php (+74 lines)
├── engagement/
│   ├── class-exit-followup-manager.php (524 lines, NEW)
│   └── class-exit-survey-builder.php (421 lines, NEW)
├── screens/
│   └── class-exit-followups-page.php (271 lines, NEW)
└── admin/ajax/
    ├── exit-followup-handlers.php (161 lines, NEW)
    └── ajax-handlers-loader.php (+3 lines)

assets/
├── js/
│   └── exit-followups.js (212 lines, NEW)
└── css/
    └── exit-followups.css (161 lines, NEW)
```

**Total:** 1,922 lines of new code across 10 files

## Related Issues

- **#1178** - Exit Interview (parent feature)
- **Current** - Exit Followup Scheduling (this feature)

## License

Same as WPShadow core plugin.
