# GitHub Labels Guide for WPShadow

> **Label System Aligned with Philosophy & Community Values**

**Created:** January 24, 2026  
**Total Labels:** 50+  
**Last Updated:** January 24, 2026

---

## Overview

The WPShadow label system is designed to:
- ✅ Reflect our **11 Core Commandments** (principles)
- ✅ Enforce **3 Foundational CANON Pillars** (non-negotiable)
- ✅ Support **community feedback** collection
- ✅ Organize **technical work** by area
- ✅ Track **priority, status, and workflow**
- ✅ Guide **first-time contributors**

---

## Label Categories

### 1. 📋 Issue Types (Standard)

Use these to classify the type of issue being reported:

| Label | Color | Usage |
|-------|-------|-------|
| `Bug` | 🔴 Red | Bug report: Something is broken or not working as expected |
| `Feature` | 🔵 Cyan | New feature request: Enhancement or new capability |
| `Documentation` | 🔵 Blue | Documentation improvement or addition |
| `Question` | 🟣 Purple | Question or need for clarification |
| `Discussion` | 🔵 Blue | Discussion topic for community engagement |

**When to use:**
- Every issue should have ONE issue type label
- Helps categorize and filter issues by kind
- First label to apply when triaging

---

### 2. 💜 Core Principles (11 Commandments)

Labels for each of WPShadow's **11 Core Commandments**. Use when an issue relates to a specific principle.

| Label | Commandment | Usage |
|-------|-------------|-------|
| `Principle #01 (Helpful Neighbor)` | #1 | Guide users, don't manipulate |
| `Principle #02 (Free as Possible)` | #2 | All local features free forever |
| `Principle #03 (Advice Not Sales)` | #3 | Educational, never pushy |
| `Principle #04 (Accessibility First)` | #4 | Inclusive design for all abilities |
| `Principle #05 (Learning Inclusive)` | #5 | Clear documentation at all levels |
| `Principle #06 (Culturally Respectful)` | #6 | Honor diverse contexts and perspectives |
| `Principle #07 (Ridiculously Good)` | #7 | Better than premium alternatives |
| `Principle #08 (Inspire Confidence)` | #8 | UX that builds trust |
| `Principle #09 (Show Value - KPIs)` | #9 | Track and display improvements |
| `Principle #10 (Privacy First)` | #10 | Consent-first and transparent |
| `Principle #11 (Talk-Worthy)` | #11 | So good users recommend it |

**When to use:**
- Issue helps implement a specific commandment
- Issue might violate a commandment
- PR addresses a principle concern
- Feature design review for principle alignment

**Example:**
```
Title: "Ensure all new diagnostics have plain English names"
Labels: Feature, Principle #01 (Helpful Neighbor), Area (Accessibility)
Reason: This helps users understand without jargon (Helpful), 
        and makes diagnostics accessible (Accessibility First)
```

---

### 3. 💚 CANON Foundational Pillars (Non-Negotiable)

**CANON labels represent non-negotiable requirements.** Use sparingly but firmly.

| Label | Pillar | Usage |
|-------|--------|-------|
| `CANON: Accessibility First` | 🔒 CANON | All features must be accessible to diverse abilities |
| `CANON: Learning Inclusive` | 🔒 CANON | Documentation must be understandable to all levels |
| `CANON: Culturally Respectful` | 🔒 CANON | Implementation must honor diverse contexts |

**When to use:**
- Issue violates a CANON principle
- PR must pass CANON requirements before merge
- Design review identifies CANON conflict
- Community concern about CANON violation

**Important:**
- **CANON labels are escalation points**
- Issues with CANON labels require discussion before closure
- PRs cannot merge if CANON concerns are unresolved
- Community feedback on CANON matters is always welcomed

**Example:**
```
Title: "New treatment not keyboard accessible"
Labels: bug, canon-accessibility-first, priority-critical
Reason: This is a CANON requirement that must be fixed.
        Blocks release until resolved.
```

---

### 4. 🔵 Feature Categories

Labels for WPShadow's core feature areas:

| Label | Category | Usage |
|--------|----------|-------|
| `Feature: Diagnostics` | 🔍 | Related to diagnostic tests and health checks |
| `Feature: Treatments` | 🔧 | Related to automatic fixes and reversible actions |
| `Feature: Workflow` | ⚙️ | Related to workflow automation and triggers |
| `Feature: KPI Tracking` | 📊 | Related to KPI measurement and value tracking |
| `Feature: Dashboard` | 📱 | Related to dashboard UI and visualization |

**When to use:**
- Issue affects a specific feature area
- Work is within a particular component
- Can have multiple feature labels per issue

**Example:**
```
Title: "Add KPI tracking for treatment effectiveness"
Labels: Feature, Feature: Treatments, Feature: KPI Tracking
Reason: Spans both treatments and KPI tracking features
```

---

### 5. 🔴🟠🟡 Technical Areas

Labels for technical domains and concerns:

| Label | Area | Color | Usage |
|-------|------|-------|-------|
| `Area (Security)` | Security | 🔴 | Security, compliance, and audit concerns |
| `Area (Performance)` | Performance | 🟠 | Performance optimization and monitoring |
| `Area (Accessibility)` | Accessibility | 🟢 | A11y compliance and inclusive design |
| `Area (Multisite)` | Multisite | 🟣 | Multisite network support and features |
| `Area (API)` | API | 🔵 | REST API and external integrations |
| `Area (Database)` | Database | 🩵 | Database queries, optimization, and migration |

**When to use:**
- Issue is in a specific technical domain
- Fix requires expertise in an area
- Can combine with `role-expert-needed` for visibility

**Example:**
```
Title: "Slow performance on large sites"
Labels: Bug, Area (Performance), Priority (High), Area (Database)
Reason: Performance issue likely related to database efficiency
```

---

### 6. 💚 Community Feedback

Labels for collecting community input and engagement:

| Label | Type | Usage |
|-------|------|-------|
| `Feedback (User Request)` | 💬 | Feature request or idea from community |
| `Feedback (Suggestion)` | 💭 | Improvement suggestion or enhancement idea |
| `Feedback (Discussion)` | 🗣️ | Topic for community discussion and input |
| `Feedback (Testimonial)` | ⭐ | User testimonial, success story, or case study |

**When to use:**
- Community member submits a feature request
- User shares a suggestion in discussion
- Positive feedback or success story is shared
- Topic needs community input before decision

**Example:**
```
Title: "Would love to see weekly health report emails"
Labels: Discussion, Feedback (Suggestion), Feature, Priority (Low)
Reason: Great suggestion from community member, 
        low priority for now but worth discussing
```

---

### 7. 🔴 Priority Labels

Indicates urgency and importance:

| Label | Priority | Color | Usage |
|-------|----------|-------|-------|
| `Priority (Critical)` | 🔴 | Critical | Urgent: Blocks users or site functionality |
| `Priority (High)` | 🟥 | High | High priority: Should be addressed soon |
| `Priority (Medium)` | 🟨 | Medium | Medium priority: Important but not urgent |
| `Priority (Low)` | ⚪ | Low | Low priority: Nice to have, can wait |

**Guidelines:**
- **Critical**: Site broken, security hole, data loss risk
- **High**: Major feature broken, important regression
- **Medium**: Normal bug or feature, some impact
- **Low**: Enhancement, nice-to-have, cosmetic

**Only ONE priority label per issue.**

---

### 8. 🟡 Status & Workflow Labels

Tracks issue progress and current state:

| Label | Status | Color | Usage |
|-------|--------|-------|-------|
| `status-needs-triage` | 🟨 | Needs evaluation and prioritization |
| `status-in-progress` | 🔵 | Currently being worked on |
| `status-blocked` | 🔴 | Blocked by another issue or dependency |
| `status-ready-for-review` | 🟢 | Ready for code review or feedback |

**Workflow:**
1. New issue → `status-needs-triage`
2. Triaged & assigned → `status-in-progress` (remove triage)
3. Hit blocker → Add `status-blocked` (+ blocking issue reference)
4. Ready for review → `status-ready-for-review` (keep in-progress)
5. Merged → Remove all status labels

**Example:**
```
Issue opened
→ Labels: bug, feature, status-needs-triage

Triaged and assigned to developer
→ Labels: bug, feature, status-in-progress

Blocked waiting for dependency
→ Labels: bug, feature, status-in-progress, status-blocked

Ready for review
→ Labels: bug, feature, status-in-progress, status-ready-for-review
```

---

### 9. 📚 Content & Knowledge Labels

For KB articles, training, and documentation:

| Label | Content Type | Usage |
|-------|--------------|-------|
| `Content (KB Article)` | 📖 | Related to KB article creation or updates |
| `Content (Training Video)` | 🎥 | Related to training video or tutorial |
| `Content (Blog Post)` | 📝 | Blog post or article content |
| `Content (Social Media)` | 📱 | Social media content or promotion |

**When to use:**
- Issue requires KB article creation
- Training video needed to explain feature
- Blog post or social content idea
- Documentation gap identified

**Example:**
```
Title: "Create KB article: How to schedule diagnostics"
Labels: Documentation, Content (KB Article), Feature: Workflow, Priority (Medium)
Reason: Documentation needed for new scheduling feature
```

---

### 10. 🗺️ Roadmap & Phase Labels

For tracking roadmap alignment:

| Label | Phase | Color | Usage |
|-------|-------|-------|-------|
| `Roadmap #01 (Phase 3 - Current)` | Current | 🟢 | Phase 3: Accessibility & Inclusivity (Current) |
| `Roadmap #02 (Future)` | Future | ⚪ | Future phases or long-term roadmap |

**When to use:**
- Issue is part of current roadmap (Phase 3)
- Issue planned for future phase
- Long-term vision tracking

---

### 11. 🤝 Community Role Labels

For guiding contributors:

| Label | Role | Usage |
|-------|------|-------|
| `Role (Good First Issue)` | 🟢 | Good for newcomers and first-time contributors |
| `Role (Help Wanted)` | 💪 | Help needed from community |
| `Role (Expert Needed)` | 👨‍💻 | Requires expert knowledge in specific area |

**When to use:**
- **good-first-issue**: Simple task, good onboarding
- **help-wanted**: Need community contributions
- **expert-needed**: Requires specialist knowledge + area-* label

**Example:**
```
Title: "Add help text to Settings page"
Labels: Documentation, Role (Good First Issue), Feature: Dashboard
Reason: Great first issue - simple, isolated, clear requirements
```

---

## Label Usage Guidelines

### Do's ✅

- **Use 3-5 labels per issue** (typically)
  - 1 issue type (bug, feature, etc.)
  - 0-2 principle labels
  - 0-1 feature label
  - 1 status label
  - 1 priority label (for bugs/features)

- **Always use issue type label**
  - Helps categorize and filter

- **Apply principle labels** when relevant
  - Aligns work with core values
  - Helps track alignment across codebase

- **Mark CANON issues clearly**
  - Non-negotiable requirement
  - Escalation point for discussion

- **Update status labels** as work progresses
  - Keeps issue visibility accurate

### Don'ts ❌

- **Don't use multiple priorities** (pick ONE)
- **Don't ignore CANON conflicts** (escalate immediately)
- **Don't apply labels without triage** (understand the issue first)
- **Don't keep status-needs-triage** on active issues (triage promptly)
- **Don't apply both feature AND feature-* together** (use feature-category instead)

---

## Examples by Issue Type

### Bug Report
```
Title: "Treatment fails on multisite"
Labels:
- bug (issue type)
- priority-high (blocks users)
- area-multisite (technical area)
- status-needs-triage (new)
```

### Feature Request from Community
```
Title: "Could we add weekly health email summaries?"
Labels:
- feature (issue type)
- feedback-user-request (source)
- feature-dashboard (area)
- priority-low (nice-to-have)
- status-needs-triage (new)
```

### Accessibility Bug (CANON)
```
Title: "Dashboard not keyboard navigable"
Labels:
- bug (issue type)
- canon-accessibility-first (CANON pillar)
- priority-critical (blocks accessibility)
- area-accessibility (technical area)
- status-needs-triage (new)
```

### Good First Issue for Contributor
```
Title: "Add FAQ section to KB"
Labels:
- documentation (issue type)
- feature (type)
- role-good-first-issue (role)
- content-kb-article (content type)
- priority-low (not urgent)
- status-needs-triage (new)
```

---

## Triaging Process

When a new issue arrives:

1. **Understand the issue**
   - Read description carefully
   - Ask for clarification if needed

2. **Apply issue type label**
   - bug, feature, documentation, question, or discussion

3. **Check principle alignment**
   - Does this relate to any commandments?
   - Does it violate any CANON pillars?
   - Apply principle-* or canon-* labels

4. **Categorize technical area** (if applicable)
   - area-security, area-performance, area-accessibility, etc.

5. **Apply priority** (if bug/feature)
   - critical, high, medium, or low

6. **Set initial status**
   - status-needs-triage → status-in-progress when assigned

7. **Add helper labels**
   - role-good-first-issue for simple tasks
   - feedback-* for community input
   - content-* for documentation needs

---

## Label Metrics

Track these metrics to understand community engagement:

- **Total issues by principle**: Shows which commandments are most relevant
- **CANON violations**: Escalation points requiring discussion
- **Community feedback**: User requests and suggestions
- **Good first issues**: Onboarding capacity
- **Help wanted**: Community contribution opportunities

---

## Getting Help

**Questions about labels?**
- See [GITHUB_WORKFLOW.md](docs/GITHUB_WORKFLOW.md) for issue workflow
- Check [PRODUCT_PHILOSOPHY.md](docs/PRODUCT_PHILOSOPHY.md) for principles explanation
- Review [ACCESSIBILITY_AND_INCLUSIVITY_CANON.md](docs/ACCESSIBILITY_AND_INCLUSIVITY_CANON.md) for CANON details

---

**Remember:** Labels are tools to serve the community and maintain alignment with our philosophy. When in doubt, ask! 💚
