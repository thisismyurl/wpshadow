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
| `bug` | 🔴 Red | Bug report: Something is broken or not working as expected |
| `feature` | 🔵 Cyan | New feature request: Enhancement or new capability |
| `documentation` | 🔵 Blue | Documentation improvement or addition |
| `question` | 🟣 Purple | Question or need for clarification |
| `discussion` | 🔵 Blue | Discussion topic for community engagement |

**When to use:**
- Every issue should have ONE issue type label
- Helps categorize and filter issues by kind
- First label to apply when triaging

---

### 2. 💜 Core Principles (11 Commandments)

Labels for each of WPShadow's **11 Core Commandments**. Use when an issue relates to a specific principle.

| Label | Commandment | Usage |
|-------|-------------|-------|
| `principle-helpful-neighbor` | #1 | Guide users, don't manipulate |
| `principle-free-possible` | #2 | All local features free forever |
| `principle-advice-not-sales` | #3 | Educational, never pushy |
| `principle-accessibility-first` | #4 | Inclusive design for all abilities |
| `principle-learning-inclusive` | #5 | Clear documentation at all levels |
| `principle-culturally-respectful` | #6 | Honor diverse contexts and perspectives |
| `principle-ridiculously-good` | #7 | Better than premium alternatives |
| `principle-inspire-confidence` | #8 | UX that builds trust |
| `principle-show-value-kpis` | #9 | Track and display improvements |
| `principle-beyond-pure-privacy` | #10 | Consent-first and transparent |
| `principle-talk-worthy` | #11 | So good users recommend it |

**When to use:**
- Issue helps implement a specific commandment
- Issue might violate a commandment
- PR addresses a principle concern
- Feature design review for principle alignment

**Example:**
```
Title: "Ensure all new diagnostics have plain English names"
Labels: feature, principle-helpful-neighbor, area-accessibility
Reason: This helps users understand without jargon (Helpful), 
        and makes diagnostics accessible (Accessibility First)
```

---

### 3. 💚 CANON Foundational Pillars (Non-Negotiable)

**CANON labels represent non-negotiable requirements.** Use sparingly but firmly.

| Label | Pillar | Usage |
|-------|--------|-------|
| `canon-accessibility-first` | 🔒 CANON | All features must be accessible to diverse abilities |
| `canon-learning-inclusive` | 🔒 CANON | Documentation must be understandable to all levels |
| `canon-culturally-respectful` | 🔒 CANON | Implementation must honor diverse contexts |

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
| `feature-diagnostics` | 🔍 | Related to diagnostic tests and health checks |
| `feature-treatments` | 🔧 | Related to automatic fixes and reversible actions |
| `feature-workflow` | ⚙️ | Related to workflow automation and triggers |
| `feature-kpi-tracking` | 📊 | Related to KPI measurement and value tracking |
| `feature-dashboard` | 📱 | Related to dashboard UI and visualization |

**When to use:**
- Issue affects a specific feature area
- Work is within a particular component
- Can have multiple feature labels per issue

**Example:**
```
Title: "Add KPI tracking for treatment effectiveness"
Labels: feature, feature-treatments, feature-kpi-tracking
Reason: Spans both treatments and KPI tracking features
```

---

### 5. 🔴🟠🟡 Technical Areas

Labels for technical domains and concerns:

| Label | Area | Color | Usage |
|-------|------|-------|-------|
| `area-security` | Security | 🔴 | Security, compliance, and audit concerns |
| `area-performance` | Performance | 🟠 | Performance optimization and monitoring |
| `area-accessibility` | Accessibility | 🟢 | A11y compliance and inclusive design |
| `area-multisite` | Multisite | 🟣 | Multisite network support and features |
| `area-api` | API | 🔵 | REST API and external integrations |
| `area-database` | Database | 🩵 | Database queries, optimization, and migration |

**When to use:**
- Issue is in a specific technical domain
- Fix requires expertise in an area
- Can combine with `role-expert-needed` for visibility

**Example:**
```
Title: "Slow performance on large sites"
Labels: bug, area-performance, priority-high, area-database
Reason: Performance issue likely related to database efficiency
```

---

### 6. 💚 Community Feedback

Labels for collecting community input and engagement:

| Label | Type | Usage |
|-------|------|-------|
| `feedback-user-request` | 💬 | Feature request or idea from community |
| `feedback-suggestion` | 💭 | Improvement suggestion or enhancement idea |
| `feedback-discussion` | 🗣️ | Topic for community discussion and input |
| `feedback-testimonial` | ⭐ | User testimonial, success story, or case study |

**When to use:**
- Community member submits a feature request
- User shares a suggestion in discussion
- Positive feedback or success story is shared
- Topic needs community input before decision

**Example:**
```
Title: "Would love to see weekly health report emails"
Labels: discussion, feedback-suggestion, feature, priority-low
Reason: Great suggestion from community member, 
        low priority for now but worth discussing
```

---

### 7. 🔴 Priority Labels

Indicates urgency and importance:

| Label | Priority | Color | Usage |
|-------|----------|-------|-------|
| `priority-critical` | 🔴 | Critical | Urgent: Blocks users or site functionality |
| `priority-high` | 🟥 | High | High priority: Should be addressed soon |
| `priority-medium` | 🟨 | Medium | Medium priority: Important but not urgent |
| `priority-low` | ⚪ | Low | Low priority: Nice to have, can wait |

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
| `content-kb-article` | 📖 | Related to KB article creation or updates |
| `content-training-video` | 🎥 | Related to training video or tutorial |
| `content-blog-post` | 📝 | Blog post or article content |
| `content-social-media` | 📱 | Social media content or promotion |

**When to use:**
- Issue requires KB article creation
- Training video needed to explain feature
- Blog post or social content idea
- Documentation gap identified

**Example:**
```
Title: "Create KB article: How to schedule diagnostics"
Labels: documentation, content-kb-article, feature-workflow, priority-medium
Reason: Documentation needed for new scheduling feature
```

---

### 10. 🗺️ Roadmap & Phase Labels

For tracking roadmap alignment:

| Label | Phase | Color | Usage |
|-------|-------|-------|-------|
| `roadmap-phase-3` | Current | 🟢 | Phase 3: Accessibility & Inclusivity (Current) |
| `roadmap-future` | Future | ⚪ | Future phases or long-term roadmap |

**When to use:**
- Issue is part of current roadmap (Phase 3)
- Issue planned for future phase
- Long-term vision tracking

---

### 11. 🤝 Community Role Labels

For guiding contributors:

| Label | Role | Usage |
|-------|------|-------|
| `role-good-first-issue` | 🟢 | Good for newcomers and first-time contributors |
| `role-help-wanted` | 💪 | Help needed from community |
| `role-expert-needed` | 👨‍💻 | Requires expert knowledge in specific area |

**When to use:**
- **good-first-issue**: Simple task, good onboarding
- **help-wanted**: Need community contributions
- **expert-needed**: Requires specialist knowledge + area-* label

**Example:**
```
Title: "Add help text to Settings page"
Labels: documentation, role-good-first-issue, feature-dashboard
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
