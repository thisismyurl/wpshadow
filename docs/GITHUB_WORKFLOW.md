# GitHub Workflow & Labels Strategy

**Version:** 1.0  
**Date:** January 22, 2026  
**Purpose:** Align issue labels, milestones, and project board with WPShadow philosophy and roadmap

---

## 🎯 Philosophy-Aligned Labels

### Priority Tiers
- **🔴 P0-Critical** - Plugin-breaking bugs, security issues
- **🟠 P1-High** - Major features, philosophy violations, production issues
- **🟡 P2-Medium** - Nice-to-haves, minor improvements
- **🟢 P3-Low** - Documentation, future considerations

### Category Labels

#### Features
- `feature/diagnostic` - New diagnostic check
- `feature/treatment` - New automatic fix
- `feature/workflow` - Workflow automation
- `feature/dashboard` - Dashboard UI/UX
- `feature/kb` - Knowledge base content
- `feature/api` - API/webhook functionality
- `feature/guardian` - Guardian cloud integration

#### Quality & Maintenance
- `quality/code-review` - Code quality issues
- `quality/refactor` - DRY violations, optimization
- `quality/test` - Test coverage, QA
- `quality/docs` - Documentation updates
- `quality/performance` - Performance optimization
- `quality/security` - Security hardening

#### Bug Categories
- `bug/critical` - Plugin crash or breaks functionality
- `bug/security` - Security vulnerability
- `bug/ui` - Dashboard or interface issue
- `bug/compatibility` - WordPress/plugin compatibility
- `bug/integration` - Third-party integration issue

#### Philosophy Alignment
- `philosophy/helpful-neighbor` - UX/messaging review needed
- `philosophy/free-first` - Ensure free tier complete
- `philosophy/show-value` - KPI tracking needed
- `philosophy/inspire-confidence` - UX clarity needed
- `philosophy/privacy-first` - Privacy/consent review

#### Status
- `status/ready` - Ready for development
- `status/in-progress` - Currently being worked on
- `status/review` - Under code review
- `status/blocked` - Waiting on dependencies
- `status/needs-info` - Needs clarification

#### Effort Estimates
- `effort/1h` - 1 hour
- `effort/4h` - 4 hours
- `effort/1d` - Full day
- `effort/3d` - 3 days
- `effort/1w` - Full week
- `effort/epic` - Multiple weeks

#### Knowledge Base
- `kb/article-needed` - Diagnostic needs KB article
- `kb/training-needed` - Treatment needs training video
- `kb/seo` - KB SEO optimization
- `kb/content` - KB content improvements

---

## 📋 Recommended Milestones

Aligned with ROADMAP.md phases:

### Current (Q1 2026)
- **Phase 3.5: Code Quality** (IN PROGRESS)
  - Target: 31% code reduction (1,160 → 500 lines duplicate)
  - Status: DRY refactoring, base class migrations
  - Deadline: End of January 2026

- **Phase 4: Dashboard Excellence** (NEXT)
  - Target: Real-time gauge system, improved UX
  - Status: Issue #563-567 in queue
  - Deadline: February 2026

### Upcoming (Q1-Q2 2026)
- **Phase 5: KB & Training Integration**
  - Target: All 57 diagnostics linked to KB/training
  - Deadline: March 2026

- **Phase 6: Privacy & Compliance**
  - Target: GDPR compliance, consent system
  - Deadline: April 2026

### Future (Q2-Q4 2026)
- **Phase 7: Guardian Cloud**
  - Target: Cloud scanning + monitoring
  - Deadline: May-June 2026

- **Phase 8: Advanced Features**
  - Target: Gamification, advanced analytics
  - Deadline: July-September 2026

---

## 🏷️ Label Application Guide

### For Each Issue

**Minimum Required:**
1. `priority/` - P0, P1, P2, or P3
2. `category/` - Feature, bug, quality
3. Milestone - Current phase

**Recommended:**
1. `effort/` - Time estimate
2. `status/` - Current status
3. Related philosophy label

**Example Issue Labels:**
```
issue #563: "Dashboard - Real-time Updates"
- priority/P1-High
- feature/dashboard
- effort/3d
- milestone: Phase 4: Dashboard Excellence
- philosophy/show-value
- philosophy/inspire-confidence
```

```
issue #500: "SSL Check Slow on Large Sites"
- priority/P2-Medium
- bug/performance
- feature/diagnostic
- effort/1d
- quality/performance
- philosophy/inspire-confidence
```

---

## 📊 Project Board Setup

### Columns (Kanban)
1. **Backlog** - Not yet scheduled
2. **Ready** - Philosophy-reviewed, ready for dev
3. **In Progress** - Currently being worked
4. **Review** - Awaiting code/philosophy review
5. **Done** - Merged and shipped

### Workflow
```
Issue Created 
    ↓
Philosophy Check (Add philosophy/* labels if needed)
    ↓
Ready for Dev (Move to Ready, add effort estimate)
    ↓
In Progress (Developer picks up)
    ↓
Code Review (Verify ARCHITECTURE.md patterns)
    ↓
Philosophy Review (Verify 11 Commandments)
    ↓
Done → Production Release
```

---

## ✅ Philosophy Verification Checklist

**Before marking issue "Ready":**

- [ ] Helps or empowers users (Commandment #1)
- [ ] Free tier is complete, not paywalled (Commandment #2, #3)
- [ ] Educational, not sales-focused (Commandment #4, #5, #6)
- [ ] Ridiculously good quality (Commandment #7)
- [ ] UX inspires confidence (Commandment #8)
- [ ] Will track KPIs showing value (Commandment #9)
- [ ] Respects privacy, consent-first (Commandment #10)
- [ ] Worth talking about with friends (Commandment #11)

**Before merge:**
- [ ] Follows CODING_STANDARDS.md
- [ ] Uses base classes (ARCHITECTURE.md)
- [ ] Has relevant KB/training links
- [ ] Includes KPI tracking if applicable
- [ ] All tests pass
- [ ] Code review approved
- [ ] Philosophy check approved

---

## 🔗 Integration Points

### Docs Automation
- Link from ROADMAP.md milestones to GitHub issues
- Link from FEATURE_MATRIX_*.md to GitHub discussions
- Reference CODE_REVIEW_SENIOR_DEVELOPER.md in code reviews

### Issue Templates
Create templates for:
- `Feature request` - Includes philosophy checklist
- `Bug report` - Includes reproduction steps
- `Code review` - Includes standards checklist

---

## 📝 Examples

### Creating a Feature Issue

**Title:** "Add memory limit diagnostic check"

**Labels:**
- `feature/diagnostic`
- `priority/P2-Medium`
- `effort/4h`
- `philosophy/helpful-neighbor`
- `kb/article-needed`

**Description:**
```markdown
## Proposal
Diagnostic to check if PHP memory limit is appropriately set for site.

## Philosophy Alignment
- **Helpful Neighbor:** Explains memory impact on performance
- **Show Value:** Display KB link explaining memory importance
- **Free First:** Completely free diagnostic

## Acceptance Criteria
- [ ] Checks memory_limit setting
- [ ] Compares to recommended minimums
- [ ] Suggests fix if too low
- [ ] Links to KB article explaining memory

## Related
- Commandment #5: Drive to KB
- TECHNICAL_STATUS.md: Diagnostics section
```

### Creating a Bug Issue

**Title:** "Dashboard gauge not updating in real-time"

**Labels:**
- `bug/ui`
- `priority/P1-High`
- `effort/1d`
- `status/ready`
- `quality/code-review`

---

## 🎯 Next Steps

1. **Implement labels** in GitHub repository settings
2. **Create issue templates** with philosophy checklists
3. **Audit existing issues** and apply labels retroactively
4. **Add board automation** (auto-move to Done when merged)
5. **Link ROADMAP.md** issues to GitHub milestones
6. **Brief team** on label usage and philosophy checks

---

## 📚 References

- [PRODUCT_PHILOSOPHY.md](PRODUCT_PHILOSOPHY.md) - 11 Commandments
- [ROADMAP.md](ROADMAP.md) - Phases 1-8
- [GITHUB_ISSUES_ALIGNMENT.md](GITHUB_ISSUES_ALIGNMENT.md) - Issues audit
- [CODING_STANDARDS.md](CODING_STANDARDS.md) - Code review standards
