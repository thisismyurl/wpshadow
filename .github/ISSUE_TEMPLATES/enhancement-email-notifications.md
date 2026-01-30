# [Enhancement] Email Notifications for Critical Findings

**Labels:** `enhancement`, `notifications`, `core`
**Assignee:** TBD
**Milestone:** v1.3

## Current State
Email notification infrastructure exists in cloud services build (`build/wpshadow-cloud-services/includes/services/class-uptime-monitor.php`) but is not available in the free core plugin.

## Enhancement Needed
Add optional email notifications for critical findings while respecting Philosophy #2 (Free as Possible) and Philosophy #10 (Beyond Pure - Privacy First).

## Proposed Solution
1. **Local email sending** using WordPress `wp_mail()` - no external service required (FREE)
2. **User opt-in** - disabled by default, requires explicit consent
3. **Configurable thresholds** - only send for critical/high severity findings
4. **Rate limiting** - max 1 email per day to prevent spam
5. **Clear unsubscribe** - one-click disable in settings

## Implementation Checklist
- [ ] Create `includes/notifications/class-email-notifier.php`
- [ ] Add settings page toggle: "Email me about critical issues"
- [ ] Add recipient email field (defaults to admin email)
- [ ] Add severity threshold selector (Critical only, High+Critical, etc.)
- [ ] Hook into diagnostic execution: when critical finding detected, queue email
- [ ] Implement daily digest (batch findings into single email)
- [ ] Add `wpshadow_email_notification` filter for extensibility
- [ ] Create email template with proper HTML/text alternatives
- [ ] Log notification attempts in Activity Logger
- [ ] Add unsubscribe link in footer of every email

## Philosophy Alignment
✅ **Philosophy #2**: Uses free WordPress email system, no paid service required
✅ **Philosophy #10**: User consent required, local-only by default
✅ **Philosophy #8**: Inspire confidence - proactive alerts without being naggy

## Technical Notes
- Use WordPress transients to implement rate limiting
- Template location: `includes/views/emails/critical-finding-notification.php`
- Should work with SMTP plugins (WP Mail SMTP, etc.)
- Consider adding to Pro: Slack/webhook notifications (requires external services)

## User Stories
- **Site Owner**: "I want to know immediately when my site has a critical security issue"
- **Agency**: "I manage 20 sites and need alerts when any client site has problems"
- **Developer**: "I'm off-site but need to know if something breaks"

## Success Metrics
- Notification delivery rate (should be >95%)
- User opt-in rate (target 30% of active installs)
- False positive reports (should be <5%)
- Time to resolution improvement (measure if email alerts lead to faster fixes)
