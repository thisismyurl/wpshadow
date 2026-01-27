# Core Values Quick Reference

This document shows which WPShadow core values apply to each documentation section. Use these when adding value references to documentation.

---

## The 11 Commandments

1. **Helpful Neighbor Experience** - Education over selling
2. **Free as Possible** - No artificial limitations
3. **Register, Don't Pay** - Fair exchange when needed
4. **Advice, Not Sales** - Educational approach
5. **Drive to Knowledge Base** - Link to learning
6. **Drive to Free Training** - Education first
7. **Ridiculously Good for Free** - Quality bar
8. **Inspire Confidence** - Empower users
9. **Everything Has a KPI** - Measure impact
10. **Beyond Pure** - Privacy first
11. **Talk-About-Worthy** - Share-worthy features

---

## The 3 Accessibility Pillars

- 🌍 **Accessibility First** - No compromises on accessibility
- 🎓 **Learning Inclusive** - Multiple learning modalities
- 🌐 **Culturally Respectful** - Global community considerations

---

## Values by Folder

### CORE/ (Architecture & Foundation)
**Primary Values:**
- #10: Beyond Pure (Privacy & security architecture)
- #8: Inspire Confidence (Trustworthy design patterns)
- 🌍 Accessibility First (Built-in from start)

**Usage:** Reference these when documenting system architecture and security decisions.

### PHILOSOPHY/ (Core Values & Vision)
**Primary Values:**
- All 11 Commandments (this is where they're documented)
- All 3 Pillars (this is where they're documented)

**Usage:** These files ARE the core values documentation.

### FEATURES/ (Feature Documentation)
**Primary Values:**
- #1: Helpful Neighbor Experience (All features should educate)
- #7: Ridiculously Good for Free (All features are high quality)
- #8: Inspire Confidence (Features empower users)
- #9: Everything Has a KPI (Features are measurable)
- #11: Talk-About-Worthy (Features worth sharing)

**Usage:** Add "Core Values Alignment" section to each feature doc.

**Specific Features:**
- **Diagnostics:** #8 (empowers), #9 (measurable), #5 (KB links)
- **Treatments:** #2 (free fixes), #7 (quality), #8 (safe & reversible)
- **Kanban Board:** #11 (visual = shareable), #7 (free feature)
- **Workflows:** #1 (automates education), #8 (saves time)
- **KPI Tracking:** #9 (everything measured), #6 (training data)

### DEVELOPMENT/ (Developer Guides)
**Primary Values:**
- #3: Register, Don't Pay (Open source, no barriers)
- #6: Drive to Free Training (Learning resources)
- #10: Beyond Pure (Security in dev practices)
- 🎓 Learning Inclusive (Multiple code examples)

**Usage:** Reference when explaining contribution process, security requirements, and testing.

### TESTING/ (Quality Assurance)
**Primary Values:**
- 🌍 Accessibility First (WCAG compliance core)
- #8: Inspire Confidence (Quality assurance)
- #7: Ridiculously Good for Free (No quality compromises)
- 🎓 Learning Inclusive (Testing everyone can understand)

**Usage:** Link accessibility docs to the 3 Pillars framework.

### DESIGN/ (UI/Design System)
**Primary Values:**
- 🌍 Accessibility First (WCAG compliance)
- 🎓 Learning Inclusive (Visual language clear to all)
- 🌐 Culturally Respectful (Inclusive design)
- #1: Helpful Neighbor Experience (Intuitive UI)

**Usage:** Reference accessibility pillars in design guidelines.

### DEPLOYMENT/ (Release & Deployment)
**Primary Values:**
- #8: Inspire Confidence (Reliable releases)
- #10: Beyond Pure (Security in deployment)

**Usage:** Reference when discussing release safety and security.

### REFERENCE/ (Quick References & API Docs)
**Primary Values:**
- #5: Drive to Knowledge Base (These ARE the KB)
- #6: Drive to Free Training (Learning reference)
- 🎓 Learning Inclusive (Clear for all skill levels)

**Usage:** Link to PHILOSOPHY/ for deeper learning.

---

## Adding Core Values to Documentation

### Pattern 1: Feature Overview (Most Common)
```markdown
## Core Values Alignment

This feature embodies our commitment to:
- ✅ **Commandment #1:** Helpful Neighbor Experience (provides education)
- ✅ **Commandment #7:** Ridiculously Good for Free (high-quality feature)
- ✅ **Pillar:** 🌍 Accessibility First (fully accessible)

Learn more about our values: [PHILOSOPHY/VISION.md](../../PHILOSOPHY/VISION.md)
```

### Pattern 2: Architecture Decision
```markdown
## Design Philosophy

We built this around:
- ✅ **Commandment #10:** Beyond Pure (privacy-by-design)
- ✅ **Commandment #8:** Inspire Confidence (transparent design)

For our complete philosophy: [PHILOSOPHY/VISION.md](../../PHILOSOPHY/VISION.md)
```

### Pattern 3: Testing/Compliance
```markdown
## Accessibility Standard

All components meet:
- ✅ **Pillar 1:** 🌍 Accessibility First (WCAG 2.1 AA)
- ✅ **Pillar 2:** 🎓 Learning Inclusive (multiple input methods)

Details: [TESTING/ACCESSIBILITY_TESTING_GUIDE.md](../../TESTING/ACCESSIBILITY_TESTING_GUIDE.md)
```

### Pattern 4: Quick Reference Footer
```markdown
---

**Core Values:** This documentation reflects our commitment to accessibility first, learning inclusive, and culturally respectful design. See [PHILOSOPHY/](../../PHILOSOPHY/) for our complete values framework.
```

---

## Files to Update (Priority Order)

### High Priority (Add full value sections)
1. [FEATURES/DIAGNOSTICS_GUIDE.md](FEATURES/DIAGNOSTICS_GUIDE.md)
   - Add: #1, #7, #8, #9, #5
   
2. [FEATURES/WORKFLOW_BUILDER.md](FEATURES/WORKFLOW_BUILDER.md)
   - Add: #1, #8, #9, #11

3. [FEATURES/DASHBOARD.md](FEATURES/DASHBOARD.md)
   - Add: #7, #8, #11, 🌍

4. [TESTING/ACCESSIBILITY_TESTING_GUIDE.md](TESTING/ACCESSIBILITY_TESTING_GUIDE.md)
   - Link to: All 3 Pillars

5. [CORE/CODING_STANDARDS.md](CORE/CODING_STANDARDS.md)
   - Add: #10 (security), #8 (quality), 🌍 (accessibility)

### Medium Priority (Add brief references)
6. [DESIGN/GUIDELINES.md](DESIGN/GUIDELINES.md)
   - Link to: All 3 Pillars
   
7. [DEVELOPMENT/QUICK_START_GUIDE.md](DEVELOPMENT/QUICK_START_GUIDE.md)
   - Add: #3, #6

8. [REFERENCE/KB_ARTICLE_WRITING_GUIDE.md](REFERENCE/KB_ARTICLE_WRITING_GUIDE.md)
   - Add: #5, #6

### Lower Priority (Footer only)
9. All other FEATURES files
10. All other REFERENCE files

---

## Implementation Notes

- **Consistency:** Use the patterns above for consistency
- **Linking:** Always link back to PHILOSOPHY/ for deeper understanding
- **Accessibility:** Use proper heading hierarchy and semantic markup
- **Brevity:** Values section should be 2-3 lines maximum, not essays
- **Authenticity:** Only mention values that are genuinely implemented in that feature

---

## Completed ✅

When you add core values to a file, mark it below:

- [ ] DIAGNOSTICS_GUIDE.md
- [ ] WORKFLOW_BUILDER.md
- [ ] DASHBOARD.md
- [ ] ACCESSIBILITY_TESTING_GUIDE.md
- [ ] CODING_STANDARDS.md
- [ ] DESIGN/GUIDELINES.md
- [ ] QUICK_START_GUIDE.md
- [ ] KB_ARTICLE_WRITING_GUIDE.md

---

**Last Updated:** January 27, 2026  
**Status:** Ready to be embedded throughout documentation
