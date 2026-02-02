# Phase 1 Quick Reference: The Enhanced Documentation Pattern

**Purpose:** Copy-paste template for consistent, verbose, teaching-focused documentation  
**Use This:** When enhancing any diagnostic, AJAX handler, or core class  
**Remember:** Documentation is a teaching opportunity - make every word count  

---

## Template 1: Diagnostic Class (File-Level Docblock)

```php
<?php
/**
 * [Name] Diagnostic
 *
 * [One-sentence explanation of what this checks]
 * 
 * [Paragraph explaining why this matters to end users. Include:
 *  - Who cares (admins/agencies/developers)
 *  - What breaks if ignored
 *  - Real-world consequences (subscribers lost, traffic drop, compliance violation)]
 *
 * **What This Check Does:**
 * - [Check 1]
 * - [Check 2]
 * - [Check 3]
 *
 * **Why This Matters:**
 * [2-3 sentences explaining business impact. NOT just technical explanation.
 *  Make it personal: "Your content won't reach subscribers" not "Feed is broken"]
 *
 * **Who Should Care:**
 * - [Role 1]: [Why they care]
 * - [Role 2]: [Why they care]
 * - [Role 3]: [Why they care]
 *
 * **Real-World Scenario:**
 * [Concrete example showing consequences. Make it vivid:
 *  "Admin logs in, sees empty feed reader. Subscribers have already moved to competitors.
 *   Revenue impact: $5K/month in lost ad revenue." NOT "Feed didn't load"]
 *
 * **Philosophy Alignment:**
 * - #[N] [Commandment]: [How this diagnostic embodies this principle]
 * - #[N] [Commandment]: [How this diagnostic embodies this principle]
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/[topic-slug] for detailed explanation
 * or https://wpshadow.com/training/[course-slug] for interactive guide
 *
 * @since   1.YDDD.HHMM
 * @package WPShadow\Diagnostics
 */
```

---

## Template 2: AJAX Handler (File-Level Docblock)

```php
<?php
/**
 * AJAX Handler: [Action Name]
 *
 * [One sentence: What user sees/does when this action fires]
 *
 * [Paragraph explaining UX philosophy and why this handler exists.
 *  Example: "Users often need breaks between scans. Forcing notifications
 *  creates alert fatigue. This handler respects user preferences."]
 *
 * **What This Handler Does:**
 * - [Step 1]
 * - [Step 2]
 * - [Step 3]
 *
 * **Why This Handler Exists:**
 * UX Principle: [Explain philosophy. Reference 11 Commandments]
 * Philosophy #1 teaches: [How this handler demonstrates principle]
 *
 * **Security Architecture:**
 * This handler demonstrates secure AJAX pattern:
 * - Nonce verification: [What it prevents and why it matters]
 * - Capability check: [Which cap and why this is the right choice]
 * - [Other security measures]
 *
 * **[Concept] Explanation (Teaching Section):**
 * [Detailed explanation of a key concept. This is your teaching moment.
 *  Example: "A nonce is a cryptographic token tied to the current user and session.
 *  Without nonce verification, an attacker could craft a malicious website..."]
 *
 * **Accessibility Considerations:**
 * - Keyboard: [How keyboard users interact]
 * - Screen readers: [What screen reader users hear]
 * - Focus: [Where focus goes after action]
 * - [Other accessibility concerns]
 *
 * **Philosophy Alignment:**
 * - #[N] [Commandment]: [Specific example from this handler]
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/[topic] for user guide
 * or https://wpshadow.com/training/[course] for developer details
 *
 * @package WPShadow
 */
```

---

## Template 3: Class-Level Docblock

```php
/**
 * [Class Name] Class
 *
 * [One-paragraph explanation of what this class does]
 *
 * **Implementation Pattern:**
 * [Step-by-step breakdown of how it works, using code/pseudo-code if helpful]
 *
 * **Why This Pattern:** [Explain architectural choice]
 * - Benefit 1
 * - Benefit 2
 * - Benefit 3
 *
 * **Related [Classes/Features]:**
 * - [Related item]: [How it relates]
 * - [Related item]: [How it relates]
 *
 * @since [version]
 */
```

---

## Template 4: Method-Level Docblock (Complex Methods)

```php
/**
 * [Method Name] - [Brief description]
 *
 * **Execution Flow:**
 * 1. [Step 1 with context]
 * 2. [Step 2 with context]
 * 3. [Step 3 with context]
 * 
 * **[Key Concept]:**
 * [Detailed explanation of important concept used in method]
 *
 * **Error Handling:**
 * [Explain what happens if things go wrong]
 *
 * **Performance Notes:**
 * [Any performance implications worth noting]
 *
 * @since  [version]
 * @param  [type] $param Description
 * @return [type] [Description of return value]
 */
```

---

## Key Principles for Every Enhancement

### 1. **"Why This Matters" is Non-Negotiable**
Every diagnostic and handler needs this section. It's the difference between:
- ❌ Technical docs (for developers who already understand the problem)
- ✅ Teaching docs (for everyone to understand business impact)

**Template:**
```
Why This Matters:
[1-2 sentences about business/user impact]

Real-World Impact:
- [Consequence 1]: [Specific effect]
- [Consequence 2]: [Specific effect]
- [Consequence 3]: [Specific effect]
```

### 2. **Explain Concepts, Not Just Actions**
When documenting security/architecture, take a teaching moment:

✅ **Teaching approach:**
```
A nonce is a cryptographic token tied to the current user and session.
Without nonce verification, an attacker could craft a malicious website
that tricks your browser into making requests to your WordPress admin.
Nonce verification proves the request came from within your site.
```

❌ **Non-teaching approach:**
```
Nonce verification required
```

### 3. **Real-World Scenarios Win**
Abstract examples confuse. Concrete scenarios teach:

✅ **Concrete:**
```
Example: Admin sees subscription tool stopped sending emails.
Investigation: Email service reports feed URL returning 404.
Cause: Permalink settings changed but feeds not updated.
Impact: 10,000 subscribers stop receiving updates. Unsubscribe spike.
```

❌ **Abstract:**
```
Feed must return valid response
```

### 4. **Different Personas Need Different Explanations**
When possible, address multiple audiences:

```
**Who Should Care:**
- Admins: "Your feed is broken, subscribers won't get updates"
- Developers: "This diagnostic uses SimpleXML for strict parsing"
- Learners: "XML is stricter than HTML - learn the difference"
```

### 5. **Philosophy References Aren't Decoration**
Every reference to 11 Commandments should be specific:

✅ **Specific:**
```
Philosophy #1 Helpful Neighbor: Rather than saying "feed is broken",
we explain "your subscribers can't access your content" so admin
understands the impact.
```

❌ **Generic:**
```
Philosophy #1: Helpful neighbor approach
```

### 6. **KB/Training Links Matter**
Add links at bottom of file-level docblock:
```
**Learn More:**
See https://wpshadow.com/kb/[topic-slug] for detailed explanation
or https://wpshadow.com/training/[course-slug] for interactive walkthrough
```

**Format:**
- KB URLs: `https://wpshadow.com/kb/[topic-slug]` (all lowercase, hyphens)
- Training: `https://wpshadow.com/training/[course-slug]`
- Examples: `/kb/feed-xml-validity`, `/training/wordpress-security-fundamentals`

---

## Checklist: Before Committing Enhancements

- [ ] File-level docblock includes:
  - [ ] What it does (1 sentence)
  - [ ] Why it matters (business impact)
  - [ ] Who should care (personas)
  - [ ] Real-world scenario (concrete example)
  - [ ] Philosophy alignment (2-3 Commandments)
  - [ ] KB/training links (at least 2 links)

- [ ] Class-level docblock includes:
  - [ ] Implementation pattern
  - [ ] Why this pattern (architectural lesson)
  - [ ] Related features/classes

- [ ] Method docblocks (complex methods):
  - [ ] Execution flow (steps 1-N)
  - [ ] Error handling approach
  - [ ] Performance notes (if relevant)

- [ ] AJAX handlers also include:
  - [ ] Security architecture section
  - [ ] Accessibility considerations
  - [ ] Execution flow (1-5 steps)

- [ ] All additions:
  - [ ] Use bold headers (`**Header:**`) for scannability
  - [ ] Include code examples where helpful
  - [ ] Reference specific philosophy commandments
  - [ ] Avoid jargon or explain it
  - [ ] Make it scannable (bullet points, short paragraphs)

---

## Common Enhancement Patterns

### Pattern A: Security Diagnostic
**Focus Areas:**
- What attackers try to do
- How detection prevents attacks
- Defense-in-depth principle
- Real attack scenario

**Example Diagnostics:**
- Admin User Enumeration
- Admin Color Scheme Security
- Any admin/security diagnostic

### Pattern B: Performance Diagnostic
**Focus Areas:**
- How this impacts users
- Server load implications
- SEO consequences
- Real-world metrics

**Example Diagnostics:**
- Feed Content Length
- Database optimization checks
- Caching configuration

### Pattern C: Compatibility Diagnostic
**Focus Areas:**
- What breaks if misconfigured
- Which services depend on it
- User experience impact
- Often silent failures

**Example Diagnostics:**
- Feed URL Accessibility
- Feed XML Validity
- Theme/plugin compatibility

### Pattern D: Configuration Diagnostic
**Focus Areas:**
- This is a choice, not an error
- Different strategies have tradeoffs
- Help admin make informed decision
- Non-judgmental tone

**Example Diagnostics:**
- Feed Content Length (excerpt vs. full)
- Any "settings" diagnostic

---

## Quick Examples (Copy-Paste Ready)

### Real-World Impact Template
```
**Real-World Impact:**
- [Role/Scenario]: [Specific consequence]
- [Role/Scenario]: [Specific consequence]
- [Role/Scenario]: [Specific consequence]

Example: Admin logs into WordPress at 9am. By 9:30am, [consequence].
By end of day, [bigger consequence]. Revenue impact: [quantified impact].
```

### Who Should Care Template
```
**Who Should Care:**
- **Admins:** [Why admins care - e.g., "Prevents subscriber complaints"]
- **Agencies:** [Why agencies care - e.g., "One less thing to monitor"]
- **Developers:** [Why devs care - e.g., "Teaches architectural pattern"]
- **Security Teams:** [Why relevant - e.g., "Closes attack vector"]
```

### Philosophy Template
```
**Philosophy Alignment:**
- #1 Helpful Neighbor: [Specific example from this check]
- #8 Inspire Confidence: [How this prevents fear/uncertainty]
- #9 Show Value: [How this quantifies impact]
```

---

## Next File to Enhance?

When you complete a file and move to the next:

1. **Read current state** - Understand existing documentation
2. **Identify problem** - What does this check? Why should users care?
3. **Find real-world scenario** - How would failure manifest?
4. **Map philosophy** - Which 11 Commandments does this embody?
5. **Write teaching docs** - Assume reader knows nothing
6. **Add KB/training links** - At least 2 external resources
7. **Validate** - Use checklist above
8. **Commit** - Include before/after comparison in commit message

---

## Metrics to Track

As you enhance files, track:

1. **Scope:** How many files enhanced this session?
2. **Depth:** Average lines added per file?
3. **Pattern Consistency:** Do all files follow same structure?
4. **Philosophy Coverage:** % of 11 Commandments referenced?
5. **Quality:** Any files needing revision? Why?

**Target Metrics (Phase 1):**
- 30-50 files enhanced
- 60-80 lines added per file average
- 90%+ pattern consistency
- 80%+ philosophy coverage
- 5% revision rate

---

## When You're Stuck

**"How do I make this educational?"**
→ Answer: **Why would a non-technical user care about this?**

**"Is this enough documentation?"**
→ Ask: **Could a new developer understand this quickly without asking questions?**

**"Does this need philosophy reference?"**
→ Check: **Does this embody helpful behavior, security confidence, value demonstration, or privacy first?** If yes, reference it.

**"What's a good real-world scenario?"**
→ Think: **What breaks first? Who notices? What's the financial impact?**

---

## Remember

Documentation is your opportunity to **teach**, not just describe.

Every diagnostic, every handler, every class is a chance to help developers understand:
- Why the feature exists
- What breaks if ignored
- How the architecture works
- What philosophy it represents

**Make it count.**

---

**Template Version:** 1.0  
**Last Updated:** February 2, 2026  
**Status:** Ready to use for Phase 1 continuation  
