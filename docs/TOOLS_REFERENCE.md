# WPShadow Tools Reference

**Version:** 1.2601.2148  
**Last Updated:** January 26, 2026  
**Status:** Production Ready

---

## Overview

WPShadow includes 16 built-in tools accessible from **WPShadow → Tools** menu. Each tool serves a specific purpose for maintaining, testing, and optimizing your WordPress site.

**Philosophy:** Every tool follows the "Helpful Neighbor" principle—providing genuine value without upselling or requiring registration.

---

## Accessibility Tools

### 1. A11y Audit (Accessibility Audit)

**Purpose:** Comprehensive WCAG 2.1 AA compliance check for your WordPress site.

**What It Does:**
- Scans pages for accessibility issues
- Checks color contrast ratios
- Validates ARIA labels
- Tests keyboard navigation
- Identifies missing alt text
- Reviews heading hierarchy

**How to Use:**
1. Navigate to **WPShadow → Tools → A11y Audit**
2. Enter URL to scan (or use current page)
3. Click **Run Audit**
4. Review findings with severity levels
5. Follow remediation suggestions

**Output:**
- WCAG violation list with line numbers
- Severity ratings (A, AA, AAA)
- Fix recommendations
- Before/after examples

**Use Cases:**
- Pre-launch accessibility review
- Compliance audits
- Ongoing accessibility monitoring
- Client deliverables

---

### 2. Color Contrast Checker

**Purpose:** Validate text/background color combinations meet WCAG standards.

**What It Does:**
- Tests foreground/background color pairs
- Calculates contrast ratios
- Validates against WCAG AA/AAA requirements
- Provides passing/failing feedback
- Suggests alternative colors

**How to Use:**
1. Navigate to **WPShadow → Tools → Color Contrast**
2. Enter foreground color (hex/rgb)
3. Enter background color (hex/rgb)
4. View calculated ratio
5. See WCAG compliance status

**Standards:**
- **WCAG AA:** 4.5:1 normal text, 3:1 large text
- **WCAG AAA:** 7:1 normal text, 4.5:1 large text

**Use Cases:**
- Theme design validation
- Brand color compliance
- Client accessibility requirements
- Design system audits

---

## Site Health Tools

### 3. Broken Links Checker

**Purpose:** Scan your site for broken internal and external links.

**What It Does:**
- Crawls all published pages/posts
- Tests internal links
- Tests external links (optional)
- Identifies 404 errors
- Detects redirects
- Reports slow-loading links

**How to Use:**
1. Navigate to **WPShadow → Tools → Broken Links**
2. Choose scan scope (all/specific pages)
3. Click **Start Scan**
4. Review results table
5. Fix or ignore links

**Output:**
- Link URL
- Source page
- HTTP status code
- Response time
- Suggested action

**Use Cases:**
- Pre-launch quality check
- Post-migration validation
- Regular maintenance scans
- SEO cleanup

---

### 4. Deep Scan

**Purpose:** Advanced diagnostic scan with detailed analysis beyond standard Guardian checks.

**What It Does:**
- Runs all 57 diagnostics
- Performs extended security checks
- Analyzes database health
- Tests performance metrics
- Reviews code quality
- Generates comprehensive report

**How to Use:**
1. Navigate to **WPShadow → Tools → Deep Scan**
2. Click **Run Deep Scan** (takes 2-5 minutes)
3. Review multi-tab report
4. Apply recommended treatments
5. Export PDF report (optional)

**Report Sections:**
- Security vulnerabilities
- Performance bottlenecks
- Code quality issues
- Configuration problems
- Database inefficiencies

**Use Cases:**
- Monthly maintenance review
- Pre-client handoff audit
- Troubleshooting performance issues
- Security hardening

---

### 5. Mobile Friendliness

**Purpose:** Test how your site performs on mobile devices.

**What It Does:**
- Tests responsive design
- Checks touch target sizes
- Validates viewport configuration
- Tests font readability
- Checks image scaling
- Measures mobile page speed

**How to Use:**
1. Navigate to **WPShadow → Tools → Mobile Friendliness**
2. Enter URL to test
3. Click **Test Mobile**
4. Review mobile simulation
5. View improvement suggestions

**Checks:**
- Viewport meta tag
- Touch targets (min 44x44px)
- Font sizes (min 16px body)
- Horizontal scrolling
- Mobile page speed

**Use Cases:**
- Mobile-first design validation
- Google Mobile-Friendly test alternative
- Client mobile demos
- Responsive debugging

---

## Configuration Tools

### 6. Customization Audit

**Purpose:** Review all theme and plugin customizations for conflicts and issues.

**What It Does:**
- Lists active theme customizations
- Identifies modified core files
- Detects plugin conflicts
- Reviews child theme usage
- Checks custom CSS/JS
- Flags risky modifications

**How to Use:**
1. Navigate to **WPShadow → Tools → Customization Audit**
2. Click **Run Audit**
3. Review customization list
4. Check for warnings
5. Document customizations

**Output:**
- Customization type (theme/plugin/core)
- File path
- Risk level (safe/caution/danger)
- Recommendation
- Backup status

**Use Cases:**
- Pre-update safety check
- Inheritance before site migration
- Client handoff documentation
- Troubleshooting conflicts

---

### 7. Timezone Alignment

**Purpose:** Ensure all timezone settings are consistent across WordPress, PHP, and database.

**What It Does:**
- Checks WordPress timezone setting
- Verifies PHP timezone configuration
- Tests database timezone
- Detects mismatches
- Offers one-click fix

**How to Use:**
1. Navigate to **WPShadow → Tools → Timezone Alignment**
2. Review current settings
3. See detected mismatches
4. Click **Align Timezones**
5. Verify consistency

**Why It Matters:**
- Scheduled posts publish at correct time
- Cron jobs run when expected
- Analytics data accurate
- User-facing timestamps correct

**Use Cases:**
- Post-migration timezone fix
- Cron job troubleshooting
- Scheduling system setup
- Multi-timezone site management

---

## Performance Tools

### 8. Simple Cache

**Purpose:** Basic page caching system for improved performance.

**What It Does:**
- Caches full page HTML
- Serves cached pages to visitors
- Bypasses WordPress for cached requests
- Auto-clears on content update
- Configurable expiration

**How to Use:**
1. Navigate to **WPShadow → Tools → Simple Cache**
2. Click **Enable Cache**
3. Set expiration time (default: 1 hour)
4. View cache statistics
5. Clear cache manually if needed

**Settings:**
- Cache duration (minutes/hours/days)
- Exclude URLs (regex patterns)
- Exclude user roles (logged-in users)
- Mobile cache separate

**Performance Gain:**
- 50-90% reduction in page load time
- Reduced server load
- Better for high-traffic sites

**Limitations:**
- Not suitable for personalized content
- No minification (use dedicated plugin)
- Basic implementation (consider dedicated cache plugin for advanced needs)

---

## Testing Tools

### 9. Email Test

**Purpose:** Verify WordPress can send emails correctly.

**What It Does:**
- Sends test email to specified address
- Tests SMTP configuration
- Checks mail server connectivity
- Validates email headers
- Reports send success/failure

**How to Use:**
1. Navigate to **WPShadow → Tools → Email Test**
2. Enter recipient email address
3. Click **Send Test Email**
4. Check inbox (and spam folder)
5. Review diagnostic results

**Tests:**
- PHP mail() function
- WordPress wp_mail()
- SMTP connection (if configured)
- SPF/DKIM records
- Deliverability

**Common Issues Detected:**
- Server blocks outbound port 25
- Missing SMTP configuration
- Invalid from address
- Spam filter rejection

**Use Cases:**
- Post-hosting-migration email test
- Contact form troubleshooting
- Notification system validation
- Client onboarding checklist

---

## Reporting Tools

### 10. Kanban Report

**Purpose:** Export Kanban board data for reporting and analysis.

**What It Does:**
- Exports findings by status
- Generates CSV/PDF reports
- Creates visual charts
- Summarizes KPI metrics
- Tracks progress over time

**How to Use:**
1. Navigate to **WPShadow → Tools → Kanban Report**
2. Select date range
3. Choose export format (CSV/PDF)
4. Include/exclude resolved items
5. Download report

**Report Contents:**
- Finding summary table
- Status distribution chart
- Trend analysis (if historical data)
- KPI metrics (time saved, issues fixed)
- Treatment effectiveness

**Use Cases:**
- Client monthly reports
- Internal team tracking
- Performance reviews
- Compliance documentation

---

### 11. Visual Comparisons

**Purpose:** Before/after visual comparison tool for treatments and changes.

**What It Does:**
- Captures screenshots before treatment
- Captures screenshots after treatment
- Displays side-by-side comparison
- Highlights differences
- Stores comparison history

**How to Use:**
1. Navigate to **WPShadow → Tools → Visual Comparisons**
2. Select finding with auto-fix
3. Click **Capture Before**
4. Apply treatment
5. Click **Capture After**
6. View side-by-side comparison

**Features:**
- Full page screenshots
- Desktop/mobile views
- Difference highlighting
- Annotation tools
- Export as image

**Use Cases:**
- Client approval workflows
- Visual treatment validation
- Design change tracking
- Quality assurance

---

## Support Tools

### 12. Magic Link Support

**Purpose:** Generate secure, time-limited login links for support access.

**What It Does:**
- Creates unique login URL
- Sets expiration time (1 hour - 7 days)
- Grants specified user role
- Logs all access
- Auto-expires after use

**How to Use:**
1. Navigate to **WPShadow → Tools → Magic Link Support**
2. Set expiration duration
3. Choose access level (admin/editor/author)
4. Click **Generate Link**
5. Share link with support technician
6. Link expires automatically

**Security:**
- One-time use (or configurable)
- Time-limited access
- Activity logging
- Role-based permissions
- Revocable anytime

**Use Cases:**
- Support ticket resolution
- Developer debugging
- Client assistance
- Emergency access

**⚠️ Warning:** Only share magic links with trusted parties. Treat them like passwords.

---

## User Experience Tools

### 13. Dark Mode

**Purpose:** Toggle dark mode theme for WPShadow admin interface.

**What It Does:**
- Applies dark color scheme to WPShadow pages
- Reduces eye strain in low-light environments
- Saves battery on OLED screens
- Respects user preference
- Syncs across tabs

**How to Use:**
1. Navigate to **WPShadow → Tools → Dark Mode**
2. Toggle **Enable Dark Mode**
3. Choose auto-switch (system preference)
4. Preview changes
5. Save preferences

**Options:**
- Manual toggle
- Auto (follows system)
- Scheduled (auto-enable at night)
- High contrast variant

**Benefits:**
- Reduced eye strain (40% less blue light)
- Better for late-night work
- Improved battery life (OLED screens)
- Modern aesthetic

---

### 14. Tips Coach

**Purpose:** Interactive help system with contextual tips and guidance.

**What It Does:**
- Displays helpful tips throughout admin
- Provides step-by-step walkthroughs
- Offers keyboard shortcuts
- Shows feature highlights
- Adapts to user skill level

**How to Use:**
1. Navigate to **WPShadow → Tools → Tips Coach**
2. Set skill level (beginner/intermediate/advanced)
3. Choose tip frequency (always/sometimes/rarely)
4. Enable/disable specific tip categories
5. Reset seen tips (to replay)

**Tip Categories:**
- Getting started
- Diagnostics guide
- Treatment safety
- Workflow automation
- Keyboard shortcuts
- Hidden features

**Adaptive Learning:**
- Tracks which tips you've seen
- Hides tips after you demonstrate understanding
- Shows advanced tips as you progress
- Never repeats dismissed tips

**Use Cases:**
- New user onboarding
- Feature discovery
- Skill development
- Productivity improvement

---

## Utility Tools

### 15. Password Generator

**Purpose:** Generate secure, random passwords for WordPress users.

**What It Does:**
- Creates cryptographically secure passwords
- Customizable length (8-64 characters)
- Configurable complexity rules
- Tests password strength
- Copies to clipboard

**How to Use:**
1. Navigate to **WPShadow → Tools → Password Generator**
2. Set password length (default: 16)
3. Choose character types:
   - Uppercase letters
   - Lowercase letters
   - Numbers
   - Special characters
4. Click **Generate**
5. Copy password to clipboard
6. Use in WordPress user creation

**Strength Indicator:**
- Weak (< 8 characters or simple pattern)
- Fair (8-12 characters, limited variety)
- Good (12-16 characters, mixed types)
- Strong (16+ characters, full complexity)
- Excellent (20+ characters, maximum entropy)

**Use Cases:**
- New user account creation
- Password reset workflows
- Security policy compliance
- Client account handoff

---

### 16. Onboarding Wizard

**Purpose:** First-run setup wizard for new WPShadow installations.

**What It Does:**
- Guides initial configuration
- Runs first diagnostic scan
- Explains key features
- Sets notification preferences
- Configures Guardian monitoring

**How to Use:**
- Automatically launches on first activation
- Can be manually restarted from **WPShadow → Tools → Onboarding Wizard**
- Step-by-step walkthrough (5-10 minutes)
- Skippable at any time

**Wizard Steps:**
1. **Welcome** - Introduction to WPShadow
2. **Initial Scan** - Run first diagnostic scan
3. **Review Findings** - Explain what was detected
4. **Quick Fixes** - Apply safe auto-fixes
5. **Notifications** - Configure alerting preferences
6. **Guardian Setup** - Enable monitoring
7. **Complete** - Dashboard tour

**Data Collection:**
- Site language
- Timezone
- Notification email
- Preferred scan frequency
- Skill level (for tips)

**Privacy:**
- No data sent to external servers
- All preferences stored locally
- Can be reset/replayed anytime
- Optional registration for SaaS features

---

## Gamification Features

### Leaderboard

**Purpose:** Track your site health improvement over time with achievements.

**Location:** Dashboard widget + **WPShadow → Tools → Leaderboard**

**How It Works:**
- Earn points for fixing issues
- Unlock badges for milestones
- Track monthly progress
- Compare to previous periods
- Celebrate achievements

**Point System:**
- Low severity fix: 10 points
- Medium severity fix: 25 points
- High severity fix: 50 points
- Critical severity fix: 100 points
- Complete workflow: 50 points

**Badges:**
- 🎯 First Fix (fix 1 issue)
- 🔧 Handyman (fix 10 issues)
- 🛡️ Guardian (fix 25 issues)
- 🏆 Master (fix 100 issues)
- ⚡ Speed Demon (fix 10 issues in one day)
- 🧹 Spring Cleaner (achieve 100% health score)

**Graduation:**
When you reach 100% site health for first time:
- "Graduation" notification
- Certificate of completion
- Share achievement option
- Confetti animation 🎉

---

## Tool Access Permissions

| Tool | Required Capability | Multisite Network |
|------|-------------------|-------------------|
| A11y Audit | `read` | All sites |
| Broken Links | `read` | All sites |
| Color Contrast | `read` | All sites |
| Customization Audit | `edit_theme_options` | Site admin |
| Dark Mode | `read` | All sites |
| Deep Scan | `manage_options` | Site admin |
| Email Test | `manage_options` | Site admin |
| Kanban Report | `read` | All sites |
| Magic Link Support | `manage_options` | Network admin only |
| Mobile Friendliness | `read` | All sites |
| Onboarding Wizard | `manage_options` | Site admin |
| Password Generator | `create_users` | User managers |
| Simple Cache | `manage_options` | Site admin |
| Timezone Alignment | `manage_options` | Site admin |
| Tips Coach | `read` | All sites |
| Visual Comparisons | `read` | All sites |

---

## Best Practices

### Regular Usage
- **Weekly:** Run Quick Scan, check for broken links
- **Monthly:** Run Deep Scan, generate Kanban Report
- **Quarterly:** Run A11y Audit, Customization Audit
- **As Needed:** Email Test, Mobile Friendliness

### Tool Combinations
- **Pre-Launch:** Deep Scan + A11y Audit + Mobile Friendliness + Broken Links
- **Post-Migration:** Timezone Alignment + Email Test + Broken Links
- **Client Handoff:** Customization Audit + Kanban Report + Visual Comparisons
- **Ongoing Maintenance:** Quick Scan + Simple Cache + Guardian monitoring

### Performance Impact
- **Low Impact:** Color Contrast, Password Generator, Dark Mode, Tips Coach
- **Medium Impact:** A11y Audit, Mobile Friendliness, Email Test
- **High Impact:** Deep Scan, Broken Links (full site), Customization Audit

**Tip:** Run high-impact tools during off-peak hours.

---

## Troubleshooting

### Tool Not Loading
1. Clear browser cache
2. Disable other plugins temporarily
3. Check JavaScript console for errors
4. Verify PHP version (8.1+ required)
5. Contact support with error details

### Scan Times Out
1. Increase PHP `max_execution_time`
2. Run scan on fewer pages/posts
3. Schedule scan during off-peak hours
4. Check server resources (CPU/RAM)

### Inaccurate Results
1. Clear plugin cache
2. Re-run scan
3. Verify test data/configuration
4. Check for plugin conflicts
5. Review recent site changes

---

## Additional Resources

- **Video Tutorials:** [wpshadow.com/tools](https://wpshadow.com/tools)
- **KB Articles:** Full guides for each tool
- **Community Forum:** Get help from other users
- **Support:** [support@wpshadow.com](mailto:support@wpshadow.com)

---

**Last Updated:** January 26, 2026  
**Plugin Version:** 1.2601.2148
