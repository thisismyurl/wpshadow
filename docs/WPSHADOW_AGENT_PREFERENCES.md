# WPShadow Agent Preferences & Configuration

**Last Updated:** January 22, 2026  
**Version:** v2026.01.22  
**Status:** Active

---

## Core Agent Preferences

### Default Communication Style
- **Verbosity:** Minimal (concise, direct, fact-based)
- **Preamble:** Disabled (no unnecessary introductions)
- **Progress Updates:** User request only (provide status only when user asks)

### Documentation & Summary Behavior
- **Summary Documents:** ✅ **ALWAYS CREATE** (Updated: January 22, 2026)
  - Create summary markdown files in `/docs/` folder after all significant work
  - Naming convention: `[FEATURE]_IMPLEMENTATION_COMPLETE.md` or `[PHASE]_SUMMARY.md`
  - Include: What was completed, what remains, key decisions, technical details
  - Timing: After completing feature/phase or at natural checkpoints
  - Examples: `DASHBOARD_COMPLETE_VERIFICATION.md`, `PHASE_4_COMPLETION_SUMMARY.md`

- **Changelog Updates:** Avoid auto-updating in dev mode (user confirmation required)
- **Philosophy Documentation:** Never auto-update (reference existing docs, suggest updates to user)

### Decision-Making Framework
- **Philosophy First:** ✅ Always check philosophy alignment before feature work
- **Code Quality First:** ✅ Enforce DRY principles, security patterns, WordPress standards
- **User Intent Validation:** ✅ Clarify ambiguous requests before implementation

---

## Summary Document Requirements

Every summary document MUST include:

### Header Section
```
Feature/Phase: [Name]
Status: Completed | In Progress | Blocked
Last Updated: [Date]
Token Investment: [Approx. tokens used, if available]
```

### What Was Completed
- Specific features implemented
- Files created/modified with line counts
- Tests performed/results
- Integration steps taken

### What Remains
- Blockers or pending work
- Future phases
- Known limitations

### Technical Summary
- Architecture decisions
- Key design patterns used
- Security/compliance notes
- Performance impacts (if any)

### Key Decisions
- Why certain approaches were chosen
- Trade-offs evaluated
- Philosophy alignment (which commandments)

---

## File Organization

### Summary Document Locations
```
docs/
├── FEATURE_NAME_IMPLEMENTATION_COMPLETE.md
├── PHASE_N_COMPLETION_SUMMARY.md
├── [ISSUE_NUMBER]_RESOLUTION_SUMMARY.md
└── [COMPONENT]_INTEGRATION_STATUS.md
```

### Naming Conventions
- **Features:** `{FEATURE}_IMPLEMENTATION_COMPLETE.md`
- **Phases:** `PHASE_{N}_COMPLETION_SUMMARY.md` (e.g., `PHASE_4_COMPLETION_SUMMARY.md`)
- **GitHub Issues:** `ISSUE_{NUMBER}_RESOLUTION_SUMMARY.md` (e.g., `ISSUE_563_RESOLUTION_SUMMARY.md`)
- **Integration Work:** `{COMPONENT}_INTEGRATION_STATUS.md` (e.g., `EMAIL_SETTINGS_INTEGRATION_STATUS.md`)

---

## When to Create Summary Documents

### ✅ ALWAYS Create Summary When:
1. Completing a user-requested feature
2. Finishing a major refactoring effort
3. Resolving a GitHub issue
4. Completing a phase of work
5. Making significant architectural changes
6. Creating new classes/patterns others will reference
7. Work spans multiple file edits (3+ files)
8. Work takes significant effort or investigation

### ⏭️ Skip Summary Only When:
1. Trivial single-file edits
2. Quick bug fixes (under 1 hour of work)
3. Minor copy/label updates
4. User explicitly says "no documentation needed"

---

## Recent Implementation Tracking

### January 22, 2026 - Notification Builder Integration
- **Files Created:** 3 (Notification_Builder class + 2 AJAX handlers = 523 lines)
- **Files Modified:** 1 (wpshadow.php - integrated builders + handlers)
- **Status:** Complete & Ready for Testing
- **Next:** Should create `NOTIFICATION_BUILDER_INTEGRATION_COMPLETE.md` summary

### Integration Checklist
- ✅ Required files added to wpshadow.php
- ✅ AJAX handlers registered
- ✅ Notifications tab updated to use Notification_Builder('notification')
- ✅ Email tab updated to use Notification_Builder('email')
- ✅ Syntax validation passed
- ⏳ Full integration testing pending
- ⏳ Summary document creation pending

---

## Philosophy Alignment Reminders

When creating summary documents, confirm alignment with WPShadow's 11 Commandments:

1. **Helpful Neighbor** - Does this feature anticipate needs?
2. **Free as Possible** - Is local functionality free forever?
3. **Register Not Pay** - Do features gate cloud only, not local?
4. **Advice Not Sales** - Does documentation educate, not pressure?
5. **Drive to KB** - Are KB articles linked?
6. **Drive to Training** - Are training videos linked?
7. **Ridiculously Good** - Does this exceed premium plugin quality?
8. **Inspire Confidence** - Is UX intuitive and clear?
9. **Show Value (KPIs)** - Does it track measurable impact?
10. **Beyond Pure (Privacy)** - Is consent-first and transparent?
11. **Talk-Worthy** - Would users recommend/share?

---

## Example Summary Document Structure

See: [PHASE_4_COMPLETION_SUMMARY.md](PHASE_4_COMPLETION_SUMMARY.md)

Quick template:
```markdown
# [Feature/Phase Name] - Implementation Summary

**Status:** Completed  
**Date:** January 22, 2026  
**Lines of Code:** XXX new, YYY modified  

## What Was Built
- Feature 1: [description]
- Feature 2: [description]

## Files Changed
- [file1.php](file1.php) - XXX lines (Created | Modified)
- [file2.php](file2.php) - YYY lines (Created | Modified)

## Key Decisions
1. [Decision 1] - [Why] → [Result]
2. [Decision 2] - [Why] → [Result]

## Testing Status
- [ ] Unit tests
- [ ] Integration tests
- [ ] Manual verification

## Philosophy Alignment
✅ Commandment #X: [How this feature meets it]

## Next Steps
- Step 1
- Step 2
```

---

## References

- [PRODUCT_PHILOSOPHY.md](PRODUCT_PHILOSOPHY.md) - 11 Commandments
- [TECHNICAL_STATUS.md](TECHNICAL_STATUS.md) - Current system state
- [CODE_REVIEW_SENIOR_DEVELOPER.md](CODE_REVIEW_SENIOR_DEVELOPER.md) - Code quality standards
- [ROADMAP.md](ROADMAP.md) - Phases 1-8 timeline

---

**This preference is now active and should be followed for all WPShadow Agent work going forward.**
