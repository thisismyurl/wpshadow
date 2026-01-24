# GitHub Labels Guide for WPShadow

> **Simplified Label System Aligned with Core Principles**

**Updated:** January 24, 2026  
**Total Labels:** 30+  
**Philosophy:** Labels reinforce our 11 Core Principles; redundant categories removed

---

## Overview

The streamlined WPShadow label system includes only essential labels:
- ✅ **Issue Types** - Bug, Feature, Documentation, Question, Discussion
- ✅ **11 Core Principles** - Each principle gets a label (all purple, consistent)
- ✅ **Status & Workflow** - Triage, In Progress, Blocked, Ready for Review
- ✅ **Priority** - Numbered 01-04 for proper sorting (Low to Critical)
- ✅ **Community Feedback** - User requests, suggestions, discussions, testimonials
- ✅ **Content & Knowledge** - KB articles, videos, blog posts, social media
- ✅ **Roadmap & Roles** - Phase tracking, good first issues, help wanted, expert needed

**Removed (Now Covered by Principles):**
- CANON pillars → Replaced by Principle #4, #5, #6
- Area/Technical tags → Covered by relevant principles
- Feature: X categories → Single "Feature" label; details in principle labels

---

## Label Categories

### 1. 📋 Issue Types (5 labels)

Standard classification for all issues:

| Label | Color | When to Use |
|-------|-------|------------|
| `Bug` | Red | Something is broken or not working as expected |
| `Feature` | Cyan | New feature request or enhancement |
| `Documentation` | Blue | Documentation improvement or addition |
| `Question` | Purple | Need for clarification or support question |
| `Discussion` | Blue | Topic for community discussion and input |

**Usage:** Use ONE type label per issue as your first label when triaging.

---

### 2. 💜 Core Principles (11 labels)

Each label represents one of our 11 Core Principles. Use when an issue directly relates to implementing or protecting that principle.

| # | Label | Description | Example |
|---|-------|-------------|---------|
| 1 | `Principle #01 (Helpful Neighbor)` | Help neighbors solve their most important problems | Improve diagnostic recommendations |
| 2 | `Principle #02 (Free as Possible)` | Affordable access for nonprofits and small organizations | Implement nonprofit discount or free tier |
| 3 | `Principle #03 (Advice Not Sales)` | Provide educational advice, never pushy sales | Educational content, no upsells |
| 4 | `Principle #04 (Accessibility First)` | Accessible to all users regardless of ability | WCAG compliance, screen reader support, keyboard navigation |
| 5 | `Principle #05 (Learning Inclusive)` | Support learners at all skill levels with clear documentation | Tutorial videos, beginner docs, glossary |
| 6 | `Principle #06 (Culturally Respectful)` | Honor diverse contexts and cultural perspectives | Internationalization, cultural sensitivities |
| 7 | `Principle #07 (Ridiculously Good)` | Exceptional quality better than premium alternatives | Polish, UX excellence, performance |
| 8 | `Principle #08 (Inspire Confidence)` | User experience that builds trust and confidence | Clear messaging, transparent processes |
| 9 | `Principle #09 (Show Value - KPIs)` | Demonstrable value through key performance indicators | Metrics, reporting, value tracking |
| 10 | `Principle #10 (Privacy First)` | All actions respect user privacy and data consent | Privacy controls, data protection, compliance |
| 11 | `Principle #11 (Talk-Worthy)` | Create experiences people want to discuss and recommend | Exceptional features worth sharing |

**Usage:**
- Use when an issue directly addresses implementing a principle
- Use when an issue might violate or undermine a principle
- Multiple principle labels OK if the work spans several principles
- **Example:** `Bug: User name not respecting international characters` → Add `Principle #06 (Culturally Respectful)`

---

### 3. 🔴 Priority (4 labels)

Urgency and importance using numbered format (01-04) for proper sorting:

| Label | Color | Definition |
|-------|-------|-----------|
| `Priority 01 (Low)` | Gray | Nice-to-have improvements that align with Principle #7 (Ridiculously Good) |
| `Priority 02 (Medium)` | Yellow | Important improvements supporting our core principles |
| `Priority 03 (High)` | Orange-Red | Significant impact addressing a core principle |
| `Priority 04 (Critical)` | Red | Urgent: blocks core principle implementation or site functionality |

**Usage:** Every issue should have ONE priority label. Guidelines:
- **Critical:** Breaks core functionality, violates a principle, or prevents release
- **High:** Significantly improves principle alignment or user experience
- **Medium:** Valuable improvement supporting our philosophy
- **Low:** Nice-to-have enhancements; non-urgent improvements

---

### 4. 🟡 Status & Workflow (4 labels)

Track issue status through the development cycle:

| Label | Color | Meaning |
|-------|-------|---------|
| `Status (Needs Triage)` | Yellow | Initial review needed; not yet categorized |
| `Status (In Progress)` | Blue | Currently being worked on |
| `Status (Blocked)` | Red | Cannot proceed; waiting for external action or decision |
| `Status (Ready for Review)` | Green | Complete and ready for testing or code review |

**Usage:** Only ONE status label per issue. Update as work progresses.

---

### 5. 💚 Community Feedback (4 labels)

Categorize community input and engagement:

| Label | Color | Usage |
|-------|-------|-------|
| `Feedback (User Request)` | Green | Direct feature request from users |
| `Feedback (Suggestion)` | Green | Community suggestion or improvement idea |
| `Feedback (Discussion)` | Green | Topic for community discussion and input |
| `Feedback (Testimonial)` | Green | User testimonial, success story, or case study |

**Usage:** Use when issues originate from community input or feedback.

---

### 6. 📚 Content & Knowledge (4 labels)

Track documentation and knowledge base content:

| Label | Color | Usage |
|-------|-------|-------|
| `Content (KB Article)` | Blue | Related to knowledge base article creation |
| `Content (Training Video)` | Blue | Training video or tutorial content |
| `Content (Blog Post)` | Blue | Blog post or article content |
| `Content (Social Media)` | Blue | Social media content or promotion |

**Usage:** For issues or tasks related to creating educational content.

---

### 7. 🗺️ Roadmap & Phases (2 labels)

Track alignment with release phases and long-term planning:

| Label | Color | Usage |
|-------|-------|-------|
| `Roadmap #01 (Phase 3 - Current)` | Green | Current phase 3 work (Accessibility & Inclusivity) |
| `Roadmap #02 (Future)` | Gray | Future phases or long-term planning |

**Usage:** Apply when issues align with current or future roadmap phases.

---

### 8. 🤝 Community Roles (3 labels)

Guide contributor engagement and identify needs:

| Label | Color | Usage |
|-------|-------|-------|
| `Role (Good First Issue)` | Purple | Good issue for newcomers and first-time contributors |
| `Role (Help Wanted)` | Purple | Help needed from the community |
| `Role (Expert Needed)` | Purple | Requires expert knowledge in specific area |

**Usage:**
- **Good First Issue:** Simple, isolated, well-defined requirements; great for onboarding
- **Help Wanted:** We need community contributions to move forward
- **Expert Needed:** Combine with principle labels to show expertise needed

---

## Example Labeling Scenarios

### Scenario 1: Accessibility Bug
```
Issue: "Color contrast insufficient in dark mode diagnostics section"

Labels:
- Bug (issue type)
- Principle #04 (Accessibility First) 
- Principle #07 (Ridiculously Good)
- Priority 03 (High)
- Status (Needs Triage)
- Role (Expert Needed)

Reason: Directly violates accessibility principle, affects quality principle,
medium/high impact, needs review, might need UX/color expertise
```

### Scenario 2: Community Feature Request
```
Issue: "Please add export to CSV for treatment logs"

Labels:
- Feature (issue type)
- Feedback (User Request)
- Principle #01 (Helpful Neighbor)
- Principle #07 (Ridiculously Good)
- Priority 02 (Medium)
- Role (Help Wanted)

Reason: User request helping them solve a problem, improves quality,
medium priority, community contribution welcome
```

### Scenario 3: Documentation Task
```
Issue: "Create beginner's guide to diagnostic categories"

Labels:
- Documentation (issue type)
- Content (KB Article)
- Principle #05 (Learning Inclusive)
- Priority 02 (Medium)
- Role (Good First Issue)

Reason: Documentation supporting learning principle, KB article,
good entry point for new contributor
```

### Scenario 4: Internationalization Work
```
Issue: "Add RTL (right-to-left) language support"

Labels:
- Feature (issue type)
- Principle #04 (Accessibility First)
- Principle #06 (Culturally Respectful)
- Principle #07 (Ridiculously Good)
- Priority 03 (High)
- Status (In Progress)
- Role (Expert Needed)

Reason: Multiple principles involved, accessibility + cultural respect,
high quality bar, needs expert knowledge
```

---

## Triage Checklist

When triaging a new issue, apply labels in this order:

1. ✅ **Issue Type** (Bug, Feature, Documentation, Question, Discussion)
   - Required for all issues

2. ✅ **Priority** (01-04)
   - Required; ensures prioritization

3. ✅ **Principles** (one or more #01-#11)
   - Recommended; helps alignment with philosophy

4. ✅ **Status** (optional until picked up)
   - Add when issue enters workflow

5. ✅ **Feedback** (if from community)
   - Add if issue originated from community input

6. ✅ **Content** (if documentation-related)
   - Add if article, video, or blog content needed

7. ✅ **Roadmap** (if phase-aligned)
   - Add if work aligns with current/future phases

8. ✅ **Role** (if guidance helpful)
   - Add if good first issue or special expertise needed

---

## Color Reference

- **Purple** (`#7057ff`): Principles, Roles
- **Red** (`#d73a4a`): Critical priority, Blocked status, Bugs
- **Yellow** (`#ffd700`): Medium priority, Needs Triage status
- **Orange-Red** (`#ff6b6b`): High priority
- **Gray** (`#c2c2c2`): Low priority, Future planning
- **Green** (`#28a745`): Ready for Review status, Feedback, Roadmap
- **Blue** (`#0075ca`, `#0052cc`): Documentation, Content, In Progress status
- **Cyan** (`#a2eeef`): Feature requests

---

## Philosophy

This label system reinforces our **11 Core Principles** while keeping the system lean and maintainable:

- **No redundancy:** CANON pillars and area tags are covered by the principles themselves
- **Clear hierarchy:** Issue type → Priority → Principles tells the full story
- **Principle-focused:** Almost every issue should have at least one principle label
- **Easy triaging:** Simple categories reduce decision-making paralysis
- **Proper sorting:** Leading zeros (01-04) on priorities and roadmaps ensure correct alphabetical ordering
- **Community-friendly:** Clear guidance for contributors on which issues need help

The system encourages contributors to think about our core values when working on issues, naturally embedding our philosophy into the development process.
