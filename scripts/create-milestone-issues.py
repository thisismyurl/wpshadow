#!/usr/bin/env python3
"""
WPShadow Milestone Issue Generator

Automatically creates GitHub issues for milestone-related tasks:
- Pre-release activities (2 Mondays before release)
- Documentation review (1 week before release)
- Quarterly recurring tasks (accessibility audits, security audits, core principles review)

Usage:
    python3 scripts/create-milestone-issues.py [--dry-run] [--year 2026]

Requirements:
    - gh CLI installed and authenticated
    - Repository: thisismyurl/wpshadow
"""

import subprocess
import json
import argparse
from datetime import datetime, timedelta
from typing import List, Dict, Tuple
import sys

# Milestone data extracted from MILESTONES.md
MILESTONES_2026 = [
    {"date": "2026-02-28", "version": "1.6059", "phase": "Phase 4: Dashboard Excellence", "month": "February"},
    {"date": "2026-03-31", "version": "1.6090", "phase": "Phase 5: Academy & Training", "month": "March"},
    {"date": "2026-04-30", "version": "1.6120", "phase": "Phase 6: Privacy & Compliance", "month": "April"},
    {"date": "2026-05-31", "version": "1.6151", "phase": "Phase 6: Privacy & Compliance (Continued)", "month": "May"},
    {"date": "2026-06-30", "version": "1.6181", "phase": "Phase 7: Guardian Cloud", "month": "June"},
    {"date": "2026-07-31", "version": "1.6212", "phase": "Phase 7: Guardian Cloud (Continued)", "month": "July"},
    {"date": "2026-08-31", "version": "1.6243", "phase": "Phase 7: Guardian Cloud (Enhancement)", "month": "August"},
    {"date": "2026-09-30", "version": "1.6273", "phase": "Phase 8: Gamification", "month": "September"},
    {"date": "2026-10-31", "version": "1.6304", "phase": "Phase 8: Gamification (Continued)", "month": "October"},
    {"date": "2026-11-30", "version": "1.6334", "phase": "Phase 8: Gamification (Completion)", "month": "November"},
    {"date": "2026-12-31", "version": "1.6365", "phase": "Phase 9: WPShadow Vault", "month": "December"},
]

MILESTONES_2027 = [
    {"date": "2027-01-31", "version": "1.7031", "phase": "Phase 9: WPShadow Vault (Continued)", "month": "January"},
    {"date": "2027-02-28", "version": "1.7059", "phase": "Phase 9: WPShadow Vault (Enhancement)", "month": "February"},
    {"date": "2027-03-31", "version": "1.7090", "phase": "Phase 10: Enterprise Features", "month": "March"},
    {"date": "2027-04-30", "version": "1.7120", "phase": "Phase 10: Enterprise Features (Continued)", "month": "April"},
    {"date": "2027-05-31", "version": "1.7151", "phase": "Phase 10: Enterprise Features (Enhancement)", "month": "May"},
    {"date": "2027-06-30", "version": "1.7181", "phase": "Phase 11: AI & Automation", "month": "June"},
    {"date": "2027-07-31", "version": "1.7212", "phase": "Phase 11: AI & Automation (Continued)", "month": "July"},
    {"date": "2027-08-31", "version": "1.7243", "phase": "Phase 11: AI & Automation (Enhancement)", "month": "August"},
    {"date": "2027-09-30", "version": "1.7273", "phase": "Phase 12: Community & Ecosystem", "month": "September"},
    {"date": "2027-10-31", "version": "1.7304", "phase": "Phase 12: Community & Ecosystem (Continued)", "month": "October"},
    {"date": "2027-11-30", "version": "1.7334", "phase": "Phase 12: Community & Ecosystem (Enhancement)", "month": "November"},
    {"date": "2027-12-31", "version": "1.7365", "phase": "Year-End Review & 2028 Planning", "month": "December"},
]

# Quarterly recurring tasks
QUARTERLY_TASKS_2026 = [
    {"date": "2026-03-31", "quarter": "Q1 2026"},
    {"date": "2026-06-30", "quarter": "Q2 2026"},
    {"date": "2026-09-30", "quarter": "Q3 2026"},
    {"date": "2026-12-31", "quarter": "Q4 2026"},
]

QUARTERLY_TASKS_2027 = [
    {"date": "2027-03-31", "quarter": "Q1 2027"},
    {"date": "2027-06-30", "quarter": "Q2 2027"},
    {"date": "2027-09-30", "quarter": "Q3 2027"},
    {"date": "2027-12-31", "quarter": "Q4 2027"},
]

# Bi-annual security audits
SECURITY_AUDITS = [
    {"date": "2026-06-30", "period": "H1 2026"},
    {"date": "2026-12-31", "period": "H2 2026"},
    {"date": "2027-06-30", "period": "H1 2027"},
    {"date": "2027-12-31", "period": "H2 2027"},
]


def get_previous_monday(date: datetime, weeks_back: int = 0) -> datetime:
    """Get the Monday before a given date, optionally N weeks back."""
    # Find the most recent Monday
    days_since_monday = (date.weekday() - 0) % 7
    if days_since_monday == 0 and weeks_back == 0:
        # If date is already Monday and we want this week's Monday
        return date
    
    most_recent_monday = date - timedelta(days=days_since_monday)
    
    # Go back additional weeks if needed
    if weeks_back > 0:
        most_recent_monday = most_recent_monday - timedelta(weeks=weeks_back)
    
    return most_recent_monday


def create_github_issue(title: str, body: str, labels: List[str], milestone: str = None, 
                        assignees: List[str] = None, dry_run: bool = False) -> bool:
    """Create a GitHub issue using gh CLI."""
    cmd = ["gh", "issue", "create", "--repo", "thisismyurl/wpshadow", "--title", title, "--body", body]
    
    if labels:
        cmd.extend(["--label", ",".join(labels)])
    
    if milestone:
        cmd.extend(["--milestone", milestone])
    
    if assignees:
        cmd.extend(["--assignee", ",".join(assignees)])
    
    if dry_run:
        print(f"\n[DRY RUN] Would create issue:")
        print(f"  Title: {title}")
        print(f"  Labels: {', '.join(labels)}")
        print(f"  Milestone: {milestone}")
        print(f"  Assignees: {', '.join(assignees) if assignees else 'None'}")
        return True
    
    try:
        result = subprocess.run(cmd, capture_output=True, text=True, check=True)
        print(f"✅ Created: {title}")
        return True
    except subprocess.CalledProcessError as e:
        print(f"❌ Failed to create issue '{title}': {e.stderr}")
        return False


def create_prerelease_issue(milestone: Dict, dry_run: bool = False) -> bool:
    """Create pre-release issue (2 Mondays before release date)."""
    release_date = datetime.strptime(milestone["date"], "%Y-%m-%d")
    prerelease_date = get_previous_monday(release_date, weeks_back=2)
    
    title = f"🚀 Pre-Release: Version {milestone['version']} ({milestone['month']} 2026)"
    
    body = f"""## Pre-Release Checklist for Version {milestone['version']}

**Release Date:** {milestone['date']}  
**Pre-Release Date:** {prerelease_date.strftime('%Y-%m-%d')} (2 Mondays before)  
**Phase:** {milestone['phase']}

### Two Weeks Before Release

- [ ] **Code Freeze** - No new features after this point
- [ ] **Create pre-release branch** - `git checkout -b release/{milestone['version']}`
- [ ] **Run full test suite** - `composer test`
- [ ] **Run PHPCS** - `composer phpcs`
- [ ] **Check for PHP errors** - Enable WP_DEBUG
- [ ] **Review all open issues** - Close or defer to next milestone
- [ ] **Update version numbers** - wpshadow.php, readme.txt, package.json
- [ ] **Generate changelog** - Review all commits since last release

### Testing Checklist

- [ ] Test on WordPress 6.4+ (minimum supported)
- [ ] Test on latest WordPress version
- [ ] Test on PHP 8.1, 8.2, 8.3
- [ ] Test with common plugins (WooCommerce, Yoast SEO, etc.)
- [ ] Test all diagnostics execute without errors
- [ ] Test all treatments apply successfully
- [ ] Test dashboard loads and displays correctly
- [ ] Test accessibility with screen reader
- [ ] Test keyboard navigation
- [ ] Test RTL language support
- [ ] Test on mobile devices

### Documentation Review

- [ ] Update README.md
- [ ] Update readme.txt (WordPress.org)
- [ ] Review all docblocks for accuracy
- [ ] Verify KB article links work
- [ ] Check video tutorial links
- [ ] Update screenshots if UI changed

### Security & Performance

- [ ] Run security scan
- [ ] Check for SQL injection vulnerabilities
- [ ] Verify all nonces are checked
- [ ] Verify all capabilities are checked
- [ ] Verify all input is sanitized
- [ ] Verify all output is escaped
- [ ] Run performance benchmarks
- [ ] Check for memory leaks

### Philosophy Alignment

- [ ] All features embody 12 Commandments
- [ ] All features meet 3 CANON Pillars (Accessibility, Learning, Culture)
- [ ] No artificial limitations in free version
- [ ] Educational tooltips and KB links present
- [ ] Privacy-first design maintained

### Release Preparation

- [ ] Create release notes draft
- [ ] Prepare blog post announcement
- [ ] Update WordPress.org assets (screenshots, banner)
- [ ] Tag release in Git: `git tag {milestone['version']}`
- [ ] Build release package: `composer build`
- [ ] Test installation from ZIP file
- [ ] Submit to WordPress.org SVN

**Related Milestone:** Release {milestone['version']} ({milestone['month']} {milestone['date'].split('-')[0]})

---

**Alignment:** [CORE_PHILOSOPHY.md](../docs/CORE_PHILOSOPHY.md) | [MILESTONES.md](../docs/MILESTONES.md)
"""
    
    labels = ["release", "pre-release", milestone['phase'].split(':')[0].strip().lower().replace(' ', '-')]
    milestone_name = f"Release {milestone['version']} ({milestone['month']} {milestone['date'].split('-')[0]})"
    
    return create_github_issue(title, body, labels, milestone_name, ["thisismyurl"], dry_run)


def create_documentation_review_issue(milestone: Dict, dry_run: bool = False) -> bool:
    """Create documentation review issue (1 week before release date)."""
    release_date = datetime.strptime(milestone["date"], "%Y-%m-%d")
    review_date = release_date - timedelta(days=7)
    
    title = f"📚 Documentation Review: Version {milestone['version']} ({milestone['month']} {milestone['date'].split('-')[0]})"
    
    body = f"""## Documentation Review for Version {milestone['version']}

**Release Date:** {milestone['date']}  
**Review Deadline:** {review_date.strftime('%Y-%m-%d')} (1 week before release)  
**Phase:** {milestone['phase']}

### Documentation Coverage

- [ ] **All new features documented** - Text + Video
- [ ] **All diagnostics have KB articles**
- [ ] **All treatments have walkthroughs**
- [ ] **Screenshots updated** - If UI changed
- [ ] **README.md updated** - Installation, features, requirements
- [ ] **readme.txt updated** - WordPress.org description, changelog
- [ ] **CHANGELOG.md updated** - All changes since last release

### Accessibility Documentation

- [ ] **WCAG compliance verified** - All docs meet AA standards
- [ ] **Alt text on all images**
- [ ] **Headings properly structured** - H1 → H2 → H3 hierarchy
- [ ] **Links descriptive** - No "click here" or "read more"
- [ ] **Code examples accessible** - Screen reader friendly
- [ ] **Video captions/transcripts** - All videos have text alternatives

### Multi-Modal Documentation (Learning Inclusive)

- [ ] **Text documentation** - Complete written guides
- [ ] **Video tutorials** - Visual learners supported
- [ ] **Interactive examples** - Hands-on learning
- [ ] **Diagrams/screenshots** - Visual aids where helpful
- [ ] **Real-world use cases** - Practical examples

### Cultural Respect (Global Audience)

- [ ] **Simple English** - No idioms or colloquialisms
- [ ] **RTL-ready examples** - Works for Arabic, Hebrew, Urdu
- [ ] **Date format flexible** - Not assuming MM/DD/YYYY
- [ ] **No cultural assumptions** - Globally applicable
- [ ] **Translation-ready** - Text domain used correctly

### Knowledge Base Articles

- [ ] **All diagnostic KB links work**
- [ ] **All treatment KB links work**
- [ ] **KB articles explain WHY not just WHAT**
- [ ] **KB articles link to training courses**
- [ ] **KB articles have related articles section**

### Developer Documentation

- [ ] **API documentation updated**
- [ ] **Code examples tested** - All code snippets work
- [ ] **Extension guides updated** - If API changed
- [ ] **Changelog detailed** - Breaking changes highlighted
- [ ] **Migration guides** - If backward compatibility broken

### Release Notes

- [ ] **Changelog complete** - All changes documented
- [ ] **Breaking changes highlighted**
- [ ] **New features explained** - With examples
- [ ] **Bug fixes listed**
- [ ] **Known issues documented**
- [ ] **Upgrade instructions** - If special steps needed

### WordPress.org Specific

- [ ] **Plugin description accurate**
- [ ] **Screenshots up to date** (max 10)
- [ ] **Banner/icon current**
- [ ] **FAQ section updated**
- [ ] **Installation instructions clear**
- [ ] **Tested up to version** - Latest WordPress version

---

**Documentation Standards:** [CORE_PHILOSOPHY.md](../docs/CORE_PHILOSOPHY.md) - Learning Inclusive Pillar 🎓

**Related Milestone:** Release {milestone['version']} ({milestone['month']} {milestone['date'].split('-')[0]})
"""
    
    labels = ["documentation", "release", milestone['phase'].split(':')[0].strip().lower().replace(' ', '-')]
    milestone_name = f"Release {milestone['version']} ({milestone['month']} {milestone['date'].split('-')[0]})"
    
    return create_github_issue(title, body, labels, milestone_name, ["thisismyurl"], dry_run)


def create_quarterly_review_issue(quarter_data: Dict, dry_run: bool = False) -> bool:
    """Create quarterly core principles review issue."""
    title = f"🧭 Quarterly Review: Core Principles & Pillars ({quarter_data['quarter']})"
    
    body = f"""## Quarterly Core Philosophy Review - {quarter_data['quarter']}

**Review Date:** {quarter_data['date']}  
**Focus:** 12 Commandments + 3 CANON Pillars  
**Owner:** Leadership Team

### Review Process

This quarterly review ensures our development stays aligned with our foundational principles.

#### Week 1-2: Gather Feedback
- [ ] **User feedback collection** - Surveys, forums, support tickets
- [ ] **Developer feedback** - Team retrospective
- [ ] **Community feedback** - GitHub discussions, social media
- [ ] **Accessibility testing results** - WCAG compliance reports
- [ ] **Performance benchmarks** - Speed, memory, efficiency

#### Week 2-3: Analyze Conflicts
- [ ] **Identify principle violations** - Where did we compromise?
- [ ] **Document conflicts** - Features vs. philosophy
- [ ] **Impact assessment** - Who was affected? How many?
- [ ] **Root cause analysis** - Why did conflicts occur?

#### Week 3: Propose Changes
- [ ] **Implementation adjustments** - How to better align
- [ ] **Process improvements** - Prevent future conflicts
- [ ] **Documentation updates** - Clarify ambiguous principles
- [ ] **Training needs** - Team education gaps

#### Week 4: Team Discussion
- [ ] **Full team review session** - 2-hour meeting
- [ ] **Debate proposed changes** - Open discussion
- [ ] **Vote on updates** - Consensus required for core changes
- [ ] **Document decisions** - Why changes made/rejected

#### Week 4-5: Update & Communicate
- [ ] **Update CORE_PHILOSOPHY.md** - If principles changed
- [ ] **Update MILESTONES.md** - If timeline adjusted
- [ ] **Communicate to community** - Blog post, GitHub discussion
- [ ] **Update training materials** - Team onboarding docs

---

## 12 Commandments Review

### 1. Helpful Neighbor Experience ✅
- [ ] Review user feedback on helpfulness
- [ ] Check error messages are educational
- [ ] Verify KB links are relevant and helpful
- [ ] Ensure advice tone (not sales)

### 2. Free as Possible ✅
- [ ] Audit for artificial limitations
- [ ] Verify no features moved to paid unnecessarily
- [ ] Check free tier remains generous
- [ ] Confirm core features fully functional

### 3. Register, Don't Pay ✅
- [ ] Review registration friction
- [ ] Verify free tier is sufficient
- [ ] Check no dark patterns
- [ ] Ensure clear value proposition

### 4. Advice, Not Sales ✅
- [ ] Review all copy for sales language
- [ ] Check upsell messaging is educational
- [ ] Verify recommendations are genuine
- [ ] Ensure Pro mentions are contextual

### 5. Drive to Knowledge Base ✅
- [ ] Verify all diagnostics link to KB
- [ ] Check KB articles are educational
- [ ] Ensure no KB links lead to sales
- [ ] Confirm KB explains WHY not just WHAT

### 6. Drive to Free Training ✅
- [ ] Review training platform usage
- [ ] Verify free content is valuable
- [ ] Check training links work
- [ ] Ensure no bait-and-switch

### 7. Ridiculously Good for Free ✅
- [ ] Compare to premium competitors
- [ ] User satisfaction surveys
- [ ] Feature completeness audit
- [ ] Quality bar maintained

### 8. Inspire Confidence ✅
- [ ] Review error handling
- [ ] Check undo functionality works
- [ ] Verify backup systems reliable
- [ ] Ensure clear feedback on actions

### 9. Everything Has a KPI ✅
- [ ] Verify all features log activity
- [ ] Check KPI tracking functional
- [ ] Review metrics dashboard
- [ ] Ensure impact measurable

### 10. Beyond Pure (Privacy First) ✅
- [ ] Privacy audit completed
- [ ] GDPR compliance verified
- [ ] No tracking without consent
- [ ] Data encryption functional

### 11. Talk-About-Worthy ✅
- [ ] Review user testimonials
- [ ] Check social mentions
- [ ] Verify referral rates
- [ ] Identify share-worthy features

### 12. Expandable ✅
- [ ] Developer API functional
- [ ] Extension docs complete
- [ ] Community contributions active
- [ ] Developer feedback collected

---

## 3 CANON Pillars Review

### 🌍 Accessibility First
- [ ] **WCAG AA compliance** - Run accessibility audit
- [ ] **Keyboard navigation** - Test all features
- [ ] **Screen reader compatible** - NVDA/JAWS testing
- [ ] **Color contrast** - All text meets 4.5:1 ratio
- [ ] **Focus indicators** - Always visible
- [ ] **No time limits** - All interactions accessible
- [ ] **Mobile accessible** - Touch targets 44×44px minimum

### 🎓 Learning Inclusive
- [ ] **Multi-modal docs** - Text, video, interactive
- [ ] **All learning styles** - Visual, auditory, kinesthetic, reading
- [ ] **Neurodiversity support** - ADHD, dyslexia, autism, anxiety
- [ ] **Non-technical users** - No jargon, plain language
- [ ] **Searchable docs** - Easy to find information
- [ ] **Real-world examples** - Practical use cases

### 🌐 Culturally Respectful
- [ ] **RTL support** - Arabic, Hebrew, Urdu tested
- [ ] **No idioms** - Simple, clear English
- [ ] **Date/time flexible** - Locale-aware formatting
- [ ] **Translation-ready** - Text domain consistent
- [ ] **Diverse imagery** - Representation matters
- [ ] **No cultural assumptions** - Global design

---

## Metrics Review

### Product Health
- Active installations: _____
- Monthly active users: _____
- User retention rate: _____% (target: 60%+)
- Average session time: _____ min (target: 10+ min)
- Feature adoption rate: _____% (target: 40%+)

### Quality Metrics
- WCAG AA compliance: _____% (target: 100%)
- Critical security issues: _____ (target: 0)
- Average bug response time: _____ hours (target: <24h)
- Code coverage: _____% (target: 80%+)
- User satisfaction (NPS): _____ (target: 50+)

### Community Health
- GitHub stars: _____
- Active contributors: _____
- KB articles: _____
- Forum members: _____
- Video tutorials: _____

---

## Output Documents

After completing this review:

1. **Update CORE_PHILOSOPHY.md** - Document any principle changes
2. **Update MILESTONES.md** - Adjust timeline if needed
3. **Create blog post** - Communicate changes to community
4. **Update team training** - Onboard team to any changes
5. **GitHub Discussion** - Public transparency on decisions

---

**Related Documents:**
- [CORE_PHILOSOPHY.md](../docs/CORE_PHILOSOPHY.md)
- [MILESTONES.md](../docs/MILESTONES.md)
- [PRODUCT_FAMILY.md](../docs/PRODUCT_FAMILY.md)

**Next Review:** {quarter_data.get('next_review', 'Next quarter')}
"""
    
    labels = ["documentation", "philosophy", "quarterly-review", "team"]
    
    return create_github_issue(title, body, labels, None, ["thisismyurl"], dry_run)


def create_accessibility_audit_issue(quarter_data: Dict, dry_run: bool = False) -> bool:
    """Create quarterly accessibility audit issue."""
    title = f"♿ Accessibility Audit ({quarter_data['quarter']})"
    
    body = f"""## Quarterly Accessibility Audit - {quarter_data['quarter']}

**Audit Date:** {quarter_data['date']}  
**Standard:** WCAG 2.1 AA compliance (minimum)  
**Goal:** AAA compliance where possible

### Pre-Audit Setup

- [ ] **Update testing tools** - Latest NVDA, JAWS, VoiceOver
- [ ] **Test environment ready** - Latest WordPress, PHP
- [ ] **Browser setup** - Chrome, Firefox, Safari, Edge
- [ ] **Documentation review** - Previous audit findings

---

## Physical Accessibility

### Keyboard Navigation
- [ ] **Tab order logical** - Follows visual flow
- [ ] **All interactions accessible** - No mouse-only features
- [ ] **Skip links functional** - Skip to main content works
- [ ] **Focus trap handled** - Modals contain focus properly
- [ ] **Keyboard shortcuts** - Documented and discoverable
- [ ] **No keyboard traps** - Can navigate in and out

### Screen Reader Compatibility
- [ ] **NVDA testing** - All features announced correctly
- [ ] **JAWS testing** - Compatible with JAWS
- [ ] **VoiceOver testing** - macOS/iOS compatibility
- [ ] **ARIA labels present** - All interactive elements labeled
- [ ] **Landmarks used** - Proper semantic structure
- [ ] **Live regions** - Dynamic content announced

### Visual Accessibility
- [ ] **Color contrast** - All text 4.5:1 ratio (AA), 7:1 for AAA
- [ ] **Large text contrast** - 3:1 minimum (18pt+ or 14pt+ bold)
- [ ] **Focus indicators** - 2px minimum, high contrast
- [ ] **200% zoom** - Content readable and functional
- [ ] **No color-only information** - Patterns or icons supplement
- [ ] **Text resizing** - Works up to 200% without horizontal scroll

### Motor Accessibility
- [ ] **Touch targets** - 44×44px minimum
- [ ] **No timing requirements** - Or generous time limits
- [ ] **Error tolerance** - Confirm dangerous actions
- [ ] **No hover-only content** - Alternative access method
- [ ] **Drag-and-drop alternatives** - Keyboard equivalent

---

## Cognitive Accessibility

### Clarity & Simplicity
- [ ] **Plain language** - 8th grade reading level
- [ ] **Consistent patterns** - Predictable behavior
- [ ] **Clear error messages** - What went wrong, how to fix
- [ ] **Instructions clear** - Step-by-step guidance
- [ ] **No jargon** - Or jargon explained

### Neurodiversity Support
- [ ] **ADHD-friendly** - Clear priorities, save-in-progress
- [ ] **Dyslexia-friendly** - Good fonts, line spacing, no walls of text
- [ ] **Autism-friendly** - Predictable, explicit instructions
- [ ] **Anxiety-friendly** - Undo buttons, confirmation prompts

### Timing & Motion
- [ ] **No auto-play** - User controls media
- [ ] **Pause/stop/hide** - Animated content controllable
- [ ] **prefers-reduced-motion** - CSS respects user preference
- [ ] **No flashing** - Nothing flashes >3 times per second
- [ ] **Time limits generous** - Or can be extended

---

## Testing Tools

### Automated Testing
- [ ] **axe DevTools** - Run on all admin pages
- [ ] **WAVE** - Browser extension testing
- [ ] **Lighthouse** - Accessibility score >90
- [ ] **Pa11y** - Command-line testing
- [ ] **WordPress Accessibility Checker** - Plugin testing

### Manual Testing
- [ ] **Keyboard-only navigation** - Entire workflow
- [ ] **Screen reader walkthrough** - All major features
- [ ] **Color blindness simulator** - Coblis, Color Oracle
- [ ] **Voice control** - Dragon NaturallySpeaking, macOS
- [ ] **Mobile screen reader** - TalkBack (Android), VoiceOver (iOS)

---

## Pages/Features to Audit

### Admin Dashboard
- [ ] Main dashboard page
- [ ] Diagnostics list page
- [ ] Individual diagnostic detail pages
- [ ] Treatments application page
- [ ] Activity logger page
- [ ] Settings page
- [ ] Kanban board
- [ ] Workflow automation

### User-Facing (if applicable)
- [ ] Front-end widgets
- [ ] Public-facing reports
- [ ] User account pages

### Modals & Popups
- [ ] Confirmation dialogs
- [ ] Treatment preview modals
- [ ] Help tooltips
- [ ] Alert/notification modals

### Forms
- [ ] Settings forms
- [ ] Workflow creation forms
- [ ] User preference forms
- [ ] Search forms

---

## Common Issues to Check

### Critical (Must Fix)
- [ ] Missing alt text on images
- [ ] Insufficient color contrast (<4.5:1)
- [ ] Keyboard traps
- [ ] Missing form labels
- [ ] Non-descriptive link text ("click here")
- [ ] Missing ARIA labels on icons
- [ ] Unlabeled form controls

### High Priority
- [ ] Incorrect heading hierarchy (H1 → H3, skipping H2)
- [ ] Focus not visible
- [ ] Modals don't trap focus
- [ ] Live regions not announced
- [ ] Invalid ARIA attributes
- [ ] Missing skip links

### Medium Priority
- [ ] Redundant alt text ("image of...")
- [ ] Non-semantic HTML (<div> buttons instead of <button>)
- [ ] Empty links or buttons
- [ ] ARIA overuse (when semantic HTML sufficient)
- [ ] Missing fieldset/legend on related inputs

---

## Documentation

- [ ] **Audit report created** - Document all findings
- [ ] **Severity ratings** - Critical, High, Medium, Low
- [ ] **Screenshots** - Visual evidence of issues
- [ ] **Steps to reproduce** - For each finding
- [ ] **Recommended fixes** - How to resolve
- [ ] **Timeline estimates** - How long fixes will take

---

## Remediation Plan

- [ ] **Create GitHub issues** - One per finding
- [ ] **Prioritize by severity** - Critical first
- [ ] **Assign to developers** - Clear ownership
- [ ] **Set deadlines** - Critical within 1 week
- [ ] **Re-test after fixes** - Verify resolution
- [ ] **Update documentation** - Accessibility guidelines

---

## Success Criteria

- ✅ WCAG 2.1 AA compliance: 100%
- ✅ Lighthouse accessibility score: >90
- ✅ axe DevTools: 0 critical issues
- ✅ Keyboard navigation: 100% functional
- ✅ Screen reader compatible: All features
- ✅ Color contrast: All text passes
- ✅ Documentation: All findings documented

---

**Related Documents:**
- [CORE_PHILOSOPHY.md](../docs/CORE_PHILOSOPHY.md) - Accessibility First Pillar 🌍
- [Accessibility Guidelines](../docs/ACCESSIBILITY.md)

**Previous Audit:** {quarter_data.get('previous_audit', 'N/A')}  
**Next Audit:** {quarter_data.get('next_audit', 'Next quarter')}
"""
    
    labels = ["accessibility", "audit", "quarterly-review", "canon-pillar"]
    
    return create_github_issue(title, body, labels, None, ["thisismyurl"], dry_run)


def create_security_audit_issue(audit_data: Dict, dry_run: bool = False) -> bool:
    """Create bi-annual security audit issue."""
    title = f"🔒 Security Audit ({audit_data['period']})"
    
    body = f"""## Security Audit - {audit_data['period']}

**Audit Date:** {audit_data['date']}  
**Standard:** OWASP WordPress Security Guidelines  
**Target:** Zero critical vulnerabilities

### Pre-Audit Preparation

- [ ] **Update scanning tools** - Latest WPScan, Security plugins
- [ ] **Review previous findings** - Check all resolved
- [ ] **Code freeze** - No new code during audit
- [ ] **Backup production data** - In case of issues

---

## OWASP Top 10 for WordPress

### 1. SQL Injection
- [ ] **All queries use $wpdb->prepare()** - No direct SQL
- [ ] **Placeholders correct** - %s for strings, %d for integers
- [ ] **No string concatenation** - In SQL queries
- [ ] **Custom queries reviewed** - Manual verification
- [ ] **Third-party libraries checked** - Dependencies secure

### 2. Cross-Site Scripting (XSS)
- [ ] **All output escaped** - esc_html(), esc_attr(), esc_url()
- [ ] **User input sanitized** - sanitize_text_field(), etc.
- [ ] **wp_kses() used** - For allowed HTML
- [ ] **JavaScript strings escaped** - esc_js()
- [ ] **AJAX responses escaped** - wp_send_json_*()

### 3. Cross-Site Request Forgery (CSRF)
- [ ] **All forms have nonces** - wp_nonce_field()
- [ ] **Nonces verified** - wp_verify_nonce()
- [ ] **AJAX requests protected** - check_ajax_referer()
- [ ] **Admin actions protected** - check_admin_referer()
- [ ] **Nonce timeouts reasonable** - Default 24 hours

### 4. Authentication & Session Management
- [ ] **No custom auth** - Use WordPress functions
- [ ] **Passwords hashed** - wp_hash_password()
- [ ] **Sessions secure** - Using WordPress sessions
- [ ] **Login attempts limited** - Rate limiting
- [ ] **2FA support** - Compatible with 2FA plugins

### 5. Authorization & Access Control
- [ ] **All capabilities checked** - current_user_can()
- [ ] **Per-feature capability checks** - manage_options, etc.
- [ ] **Multisite network checks** - is_super_admin()
- [ ] **No role assumptions** - Always verify
- [ ] **File access restricted** - No direct file access

### 6. Security Misconfiguration
- [ ] **No debug info in production** - WP_DEBUG off
- [ ] **Error reporting disabled** - No sensitive info leaked
- [ ] **Directory listing disabled** - .htaccess configured
- [ ] **File permissions correct** - 644 files, 755 dirs
- [ ] **wp-config.php protected** - Not web-accessible

### 7. Sensitive Data Exposure
- [ ] **API keys not in code** - Use constants or options
- [ ] **Credentials encrypted** - If stored
- [ ] **No sensitive data in logs** - Sanitized logging
- [ ] **SSL/TLS enforced** - HTTPS for admin
- [ ] **Database credentials secure** - wp-config.php protected

### 8. File Upload Vulnerabilities
- [ ] **File types restricted** - Whitelist, not blacklist
- [ ] **MIME type verified** - Not just extension
- [ ] **File size limits** - Prevent DoS
- [ ] **Upload directory protected** - No PHP execution
- [ ] **Filenames sanitized** - sanitize_file_name()

### 9. Unvalidated Redirects
- [ ] **Redirects validated** - wp_safe_redirect()
- [ ] **No open redirects** - Whitelist destinations
- [ ] **URLs validated** - esc_url_raw()
- [ ] **External links checked** - User confirmation

### 10. Outdated Components
- [ ] **WordPress core updated** - Latest version
- [ ] **Dependencies updated** - composer update
- [ ] **PHP version supported** - 8.1+
- [ ] **Third-party libraries patched** - No known vulnerabilities
- [ ] **Deprecated functions removed** - WordPress API changes

---

## Additional WordPress-Specific Checks

### Nonce Verification
- [ ] **Admin actions** - All protected with nonces
- [ ] **AJAX handlers** - check_ajax_referer()
- [ ] **Settings saves** - settings_fields()
- [ ] **Meta box saves** - wp_verify_nonce()
- [ ] **Custom forms** - wp_verify_nonce()

### Capability Checks
- [ ] **Settings pages** - manage_options minimum
- [ ] **Treatment application** - Appropriate capability
- [ ] **File modifications** - edit_files capability
- [ ] **Plugin activation** - activate_plugins
- [ ] **User management** - edit_users

### Sanitization & Escaping
- [ ] **Text fields** - sanitize_text_field()
- [ ] **Textareas** - sanitize_textarea_field()
- [ ] **Emails** - sanitize_email()
- [ ] **URLs** - esc_url_raw()
- [ ] **Keys** - sanitize_key()
- [ ] **File names** - sanitize_file_name()
- [ ] **HTML output** - esc_html(), wp_kses()
- [ ] **Attributes** - esc_attr()
- [ ] **JavaScript** - esc_js()

### Database Security
- [ ] **Prepared statements** - 100% coverage
- [ ] **Table prefixes used** - $wpdb->prefix
- [ ] **Charset/collation correct** - UTF-8
- [ ] **No SQL injection vectors** - Verified manually
- [ ] **Database backups** - Regular and tested

---

## Automated Security Scans

- [ ] **WPScan** - Run vulnerability scan
- [ ] **Sucuri SiteCheck** - Online scanner
- [ ] **Wordfence** - If installed
- [ ] **iThemes Security** - If installed
- [ ] **Plugin Check** - WordPress.org tool
- [ ] **PHPCS Security Sniffs** - WordPress-VIP-Go standards

---

## Manual Code Review

### Critical Files
- [ ] wpshadow.php (main plugin file)
- [ ] includes/core/*.php (base classes)
- [ ] includes/diagnostics/**/*.php
- [ ] includes/treatments/**/*.php
- [ ] includes/admin/**/*.php (AJAX handlers)

### Focus Areas
- [ ] All $_POST/$_GET/$_REQUEST usage
- [ ] All wp_redirect() calls
- [ ] All file operations
- [ ] All database queries
- [ ] All user input handling
- [ ] All output to browser

---

## Penetration Testing

- [ ] **SQL injection attempts** - All forms and AJAX
- [ ] **XSS attempts** - All user input fields
- [ ] **CSRF attempts** - Missing nonce attacks
- [ ] **File upload attacks** - Malicious file types
- [ ] **Authentication bypass** - Try to access without login
- [ ] **Privilege escalation** - Try admin features as subscriber

---

## Third-Party Dependencies

- [ ] **composer.json reviewed** - All packages necessary
- [ ] **Dependency versions** - No known vulnerabilities
- [ ] **License compliance** - GPL compatible
- [ ] **Abandoned packages** - None in use
- [ ] **Update strategy** - Regular updates planned

---

## Documentation

- [ ] **Audit report created** - All findings documented
- [ ] **Severity ratings** - Critical, High, Medium, Low
- [ ] **Proof of concept** - For each vulnerability
- [ ] **Remediation steps** - How to fix
- [ ] **Timeline** - When fixes will be applied
- [ ] **Security.md updated** - Responsible disclosure

---

## Remediation Plan

- [ ] **Critical issues** - Fix within 24 hours
- [ ] **High priority** - Fix within 1 week
- [ ] **Medium priority** - Fix within 1 month
- [ ] **Low priority** - Fix in next release
- [ ] **Create GitHub issues** - Track all findings
- [ ] **Emergency release** - If critical found
- [ ] **Notify users** - If vulnerability exploited

---

## Success Criteria

- ✅ Zero critical vulnerabilities
- ✅ All OWASP Top 10 checks pass
- ✅ WPScan: No known vulnerabilities
- ✅ All nonces verified
- ✅ All capabilities checked
- ✅ All input sanitized
- ✅ All output escaped
- ✅ 100% prepared statements

---

**Related Documents:**
- [SECURITY.md](../SECURITY.md) - Responsible disclosure
- [CORE_PHILOSOPHY.md](../docs/CORE_PHILOSOPHY.md) - Beyond Pure (Privacy First)

**Previous Audit:** {audit_data.get('previous', 'N/A')}  
**Next Audit:** {audit_data.get('next', 'Next period')}
"""
    
    labels = ["security", "audit", "critical"]
    
    return create_github_issue(title, body, labels, None, ["thisismyurl"], dry_run)


def create_performance_benchmark_issue(quarter_data: Dict, dry_run: bool = False) -> bool:
    """Create quarterly performance benchmark issue."""
    title = f"⚡ Performance Benchmark ({quarter_data['quarter']})"
    
    body = f"""## Quarterly Performance Benchmark - {quarter_data['quarter']}

**Benchmark Date:** {quarter_data['date']}  
**Target:** <200ms diagnostic execution time  
**Measurement:** Against 10 reference sites

### Benchmark Setup

- [ ] **Reference sites prepared** - 10 test WordPress sites
- [ ] **Consistent environment** - Same PHP version, WordPress version
- [ ] **Baseline measurements** - Record current performance
- [ ] **Profiling tools ready** - Xdebug, Query Monitor, Debug Bar

---

## Performance Targets

| Metric | Target | Current | Status |
|--------|--------|---------|--------|
| Diagnostic execution | <200ms | ___ms | ⬜ |
| Dashboard load time | <1s | ___s | ⬜ |
| Database queries | <50 per page | ___ | ⬜ |
| Memory usage | <64MB | ___MB | ⬜ |
| API response time | <500ms | ___ms | ⬜ |

---

## Diagnostic Performance

Test each diagnostic individually:

- [ ] **Admin diagnostics** (48 total) - Average execution time
- [ ] **Security diagnostics** - Response time
- [ ] **Performance diagnostics** - No infinite loops
- [ ] **SEO diagnostics** - Efficient checks
- [ ] **Plugin compatibility diagnostics** - Scalable

### Performance Breakdown by Family

| Family | Count | Target | Average Time | Status |
|--------|-------|--------|--------------|--------|
| Admin | 48 | <100ms | ___ms | ⬜ |
| Security | __ | <150ms | ___ms | ⬜ |
| Performance | __ | <100ms | ___ms | ⬜ |
| SEO | __ | <150ms | ___ms | ⬜ |
| Content | __ | <200ms | ___ms | ⬜ |

---

## Dashboard Performance

- [ ] **Initial load** - Time to interactive
- [ ] **Real-time gauges** - Update frequency and latency
- [ ] **Activity logger** - Query optimization
- [ ] **Kanban board** - Rendering performance
- [ ] **Settings page** - Form responsiveness

---

## Database Performance

- [ ] **Query count** - Minimize per page load
- [ ] **Query time** - Optimize slow queries
- [ ] **Index usage** - Ensure proper indexing
- [ ] **N+1 queries** - Eliminate where possible
- [ ] **Caching strategy** - Transients, object cache

### Slow Query Analysis

Use Query Monitor to identify:
- [ ] Queries >100ms
- [ ] Queries without index
- [ ] Duplicate queries
- [ ] Queries in loops

---

## Memory Usage

- [ ] **Plugin activation** - Memory footprint
- [ ] **Dashboard load** - Peak memory usage
- [ ] **Diagnostic execution** - Memory per diagnostic
- [ ] **Treatment application** - Memory spikes
- [ ] **Memory leaks** - Long-running processes

---

## API Performance

If Guardian Cloud features active:

- [ ] **Cloud API latency** - Request/response time
- [ ] **Token authentication** - Auth overhead
- [ ] **Multi-site sync** - Sync performance
- [ ] **Historical analytics** - Query performance
- [ ] **Rate limiting** - No bottlenecks

---

## Front-End Performance

If user-facing features:

- [ ] **JavaScript load time** - Bundle size
- [ ] **CSS load time** - Stylesheet optimization
- [ ] **Image optimization** - Lazy loading
- [ ] **Third-party resources** - CDN performance
- [ ] **Render blocking** - Critical CSS inline

---

## Scaling Tests

Test with different site sizes:

### Small Site (< 100 posts)
- [ ] Diagnostic suite: ___ms
- [ ] Dashboard load: ___ms
- [ ] Memory usage: ___MB

### Medium Site (100-1,000 posts)
- [ ] Diagnostic suite: ___ms
- [ ] Dashboard load: ___ms
- [ ] Memory usage: ___MB

### Large Site (1,000-10,000 posts)
- [ ] Diagnostic suite: ___ms
- [ ] Dashboard load: ___ms
- [ ] Memory usage: ___MB

### Enterprise Site (>10,000 posts)
- [ ] Diagnostic suite: ___ms
- [ ] Dashboard load: ___ms
- [ ] Memory usage: ___MB

---

## Optimization Opportunities

Document findings:

### Quick Wins (< 1 day)
- [ ] _____________________
- [ ] _____________________

### Medium Effort (1-3 days)
- [ ] _____________________
- [ ] _____________________

### Large Projects (> 1 week)
- [ ] _____________________
- [ ] _____________________

---

## Regression Testing

Compare to previous benchmark:

- [ ] **Performance regressions** - Identify slowdowns
- [ ] **Memory increases** - Explain growth
- [ ] **Query count increases** - Justify additional queries
- [ ] **Improvements** - Document optimizations

---

## Profiling Tools

- [ ] **Xdebug profiler** - Function-level performance
- [ ] **Query Monitor** - Database query analysis
- [ ] **Debug Bar** - WordPress-specific profiling
- [ ] **Blackfire.io** - Production profiling (if available)
- [ ] **New Relic** - APM monitoring (if available)

---

## Documentation

- [ ] **Benchmark report** - All metrics documented
- [ ] **Performance graphs** - Visual trends
- [ ] **Comparison to previous** - Quarter-over-quarter
- [ ] **Optimization recommendations** - Actionable items
- [ ] **GitHub issues created** - Track improvements

---

## Success Criteria

- ✅ All diagnostics <200ms average
- ✅ Dashboard loads <1 second
- ✅ <50 database queries per page
- ✅ <64MB memory usage
- ✅ No performance regressions
- ✅ Improvements documented

---

**Related Documents:**
- [Performance Guidelines](../docs/PERFORMANCE.md)
- [MILESTONES.md](../docs/MILESTONES.md)

**Previous Benchmark:** {quarter_data.get('previous', 'N/A')}  
**Next Benchmark:** {quarter_data.get('next', 'Next quarter')}
"""
    
    labels = ["performance", "benchmark", "quarterly-review"]
    
    return create_github_issue(title, body, labels, None, ["thisismyurl"], dry_run)


def main():
    parser = argparse.ArgumentParser(description="Create WPShadow milestone-related issues")
    parser.add_argument("--dry-run", action="store_true", help="Show what would be created without creating")
    parser.add_argument("--year", type=int, choices=[2026, 2027], default=2026, 
                       help="Year to create issues for (default: 2026)")
    parser.add_argument("--type", choices=["all", "prerelease", "docs", "quarterly", "security", "performance"],
                       default="all", help="Type of issues to create")
    
    args = parser.parse_args()
    
    print(f"\n{'='*60}")
    print(f"WPShadow Milestone Issue Generator")
    print(f"{'='*60}")
    print(f"Mode: {'DRY RUN' if args.dry_run else 'LIVE'}")
    print(f"Year: {args.year}")
    print(f"Type: {args.type}")
    print(f"{'='*60}\n")
    
    milestones = MILESTONES_2026 if args.year == 2026 else MILESTONES_2027
    quarterly = QUARTERLY_TASKS_2026 if args.year == 2026 else QUARTERLY_TASKS_2027
    
    created = 0
    failed = 0
    
    # Pre-release issues
    if args.type in ["all", "prerelease"]:
        print("\n📦 Creating Pre-Release Issues...")
        for milestone in milestones:
            if create_prerelease_issue(milestone, args.dry_run):
                created += 1
            else:
                failed += 1
    
    # Documentation review issues
    if args.type in ["all", "docs"]:
        print("\n📚 Creating Documentation Review Issues...")
        for milestone in milestones:
            if create_documentation_review_issue(milestone, args.dry_run):
                created += 1
            else:
                failed += 1
    
    # Quarterly review issues
    if args.type in ["all", "quarterly"]:
        print("\n🧭 Creating Quarterly Review Issues...")
        for quarter in quarterly:
            if create_quarterly_review_issue(quarter, args.dry_run):
                created += 1
            else:
                failed += 1
            
            if create_accessibility_audit_issue(quarter, args.dry_run):
                created += 1
            else:
                failed += 1
            
            if create_performance_benchmark_issue(quarter, args.dry_run):
                created += 1
            else:
                failed += 1
    
    # Security audit issues (bi-annual)
    if args.type in ["all", "security"]:
        print("\n🔒 Creating Security Audit Issues...")
        for audit in SECURITY_AUDITS:
            if audit["date"].startswith(str(args.year)):
                if create_security_audit_issue(audit, args.dry_run):
                    created += 1
                else:
                    failed += 1
    
    print(f"\n{'='*60}")
    print(f"Summary:")
    print(f"  Created: {created}")
    print(f"  Failed: {failed}")
    print(f"  Mode: {'DRY RUN (no issues actually created)' if args.dry_run else 'LIVE'}")
    print(f"{'='*60}\n")
    
    if args.dry_run:
        print("💡 Run without --dry-run to actually create issues")
    
    return 0 if failed == 0 else 1


if __name__ == "__main__":
    sys.exit(main())
