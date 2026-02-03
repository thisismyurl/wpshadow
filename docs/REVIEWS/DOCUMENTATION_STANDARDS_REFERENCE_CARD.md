# 📋 Documentation Standards Reference Card
**Quick Reference for Inline Documentation Excellence**

---

## 🎯 Core Principles (The "Why")

All WPShadow documentation should embody:

1. **Helpful Neighbor** - Empathetic, teaching tone (not robotic)
2. **Free as Possible** - Explain what's free vs. paid
3. **Show Value** - Document business impact, not just technical details
4. **Inspire Confidence** - Show security and safety measures
5. **Drive to KB** - Link to knowledge base articles
6. **Drive to Training** - Link to learning resources

---

## 📐 Docblock Template by File Type

### Template 1: Diagnostic Class (File-Level)
```php
<?php
/**
 * [Feature] Diagnostic
 *
 * [What this checks in plain English. 1-2 sentences.]
 * 
 * [Paragraph explaining why this matters to users. Include:
 *  - Who cares (admins/agencies/developers)
 *  - What breaks if ignored
 *  - Real-world consequences (revenue impact, visitor loss, compliance)]
 *
 * **What This Check Does:**
 * - [Check 1]
 * - [Check 2]
 * - [Check 3]
 *
 * **Why This Matters:**
 * [2-3 sentences with business impact, not just technical.]
 *
 * **Real-World Scenario:**
 * [Vivid, specific example. Format:
 *  "Before: [Bad situation with metrics]. After: [Good situation with metrics].
 *   Impact: [Revenue, performance, or user impact]."]
 *
 * **Philosophy Alignment:**
 * - #[N] [Commandment]: [How this diagnostic embodies principle]
 * - #[N] [Commandment]: [How this diagnostic embodies principle]
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/[topic-slug] for detailed explanation
 * or https://wpshadow.com/training/[course-slug] for interactive guide
 *
 * @since   1.YDDD.HHMM
 * @package WPShadow\Diagnostics
 */
```

**Score Result:** 8.5/10 ✅ (Current diagnostic class standard)

---

### Template 2: Treatment Class (File-Level)
```php
<?php
/**
 * Treatment for [Feature Name]
 *
 * [What this treatment does in plain English.]
 * 
 * [Paragraph explaining business impact. Include:
 *  - Problem it solves (database bloat, performance, security)
 *  - Real-world consequences if not fixed
 *  - User-facing improvement after treatment applied]
 *
 * **Business Impact:**
 * - [Metric 1]: [Expected improvement with numbers]
 * - [Metric 2]: [Expected improvement with numbers]
 * - [Metric 3]: [Expected improvement with numbers]
 *
 * **Real-World Scenario:**
 * [Before/after with specific metrics. Example:
 *  "Before: 1000+ expired transients, queries took 200ms. 
 *   After: Clean database, queries now 50ms. 
 *   Impact: 30% server load reduction."]
 *
 * **Philosophy Alignment:**
 * - #[N] [Commandment]: [How this treatment embodies principle]
 * - #[N] [Commandment]: [How this treatment embodies principle]
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/[topic] for background
 * or https://wpshadow.com/training/[course] for guidance
 *
 * @since      1.YDDD.HHMM
 * @package    WPShadow\Treatments
 */
```

**Score Result:** 7.2/10 → 8.2/10 ⬆️ (Target after enhancement)

---

### Template 3: AJAX Handler (File-Level)
```php
<?php
/**
 * AJAX Handler: [Action Name]
 *
 * [One sentence: What user sees when they trigger this action.]
 *
 * [Paragraph explaining the user experience and why this handler exists.
 *  Focus on: "What is the user trying to accomplish?"
 *  Example: "Users need to run focused security scans (5s) instead of
 *   full health checks (45s). This handler enables that choice."]
 *
 * **What Users See:**
 * [Step-by-step UX flow. Format:
 * 1. User clicks "[Button Name]"
 * 2. Results appear within [timeframe]
 * 3. User sees [what they see]
 * 4. Optional: User can next [what they do next]]
 *
 * **Why This Matters:**
 * [Explain the UX principle. Reference philosophy if applicable.
 *  Example: "Focused scans reduce decision fatigue and provide
 *   faster feedback - both supporting Philosophy #1 (Helpful Neighbor)."]
 *
 * **Security Architecture:**
 * [Explicitly document security measures:
 * - Nonce verification: [What it prevents and why]
 * - Capability check: [Which cap and why appropriate]
 * - Input validation: [What's checked and how]
 * - Error handling: [Doesn't expose sensitive info]]
 *
 * **Accessibility Considerations:**
 * [If UI handler, document:
 * - Keyboard navigation: [How it's navigable without mouse]
 * - Screen reader support: [ARIA labels, live regions, etc.]
 * - Focus management: [Where focus moves]
 * - Loading states: [How progress is announced]]
 *
 * **Philosophy Alignment:**
 * - #[N] [Commandment]: [How this handler embodies principle]
 * - #[N] [Commandment]: [How this handler embodies principle]
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/[topic] for background
 * or https://wpshadow.com/training/[course] for guidance
 *
 * @since      1.YDDD.HHMM
 * @package    WPShadow\Admin
 */
```

**Score Result:** 6.8/10 → 8.0/10 ⬆️ (Target after enhancement)

---

### Template 4: Core Base Class (File-Level)
```php
<?php
/**
 * [Class Name] - [One-line summary]
 *
 * [What this class does and why it exists. Should explain:
 *  - Problem it solves
 *  - How it's used (inheritance, static methods, etc.)
 *  - Why this pattern chosen]
 *
 * **Architecture Pattern:**
 * [Explain the design pattern used:
 *  - What pattern (Template Method, Registry, etc.)
 *  - How it works (1-4 steps)
 *  - Why this pattern (DRY, extensibility, consistency)]
 *
 * **Example Pattern Flow:**
 * 1. [Step 1 with code/pseudo-code]
 * 2. [Step 2 with code/pseudo-code]
 * 3. [Step 3 with code/pseudo-code]
 * 4. [Step 4 with code/pseudo-code]
 *
 * **Why This Pattern:**
 * - [Benefit 1]: [How it helps]
 * - [Benefit 2]: [How it helps]
 * - [Benefit 3]: [How it helps]
 *
 * **Philosophy Alignment:**
 * - #[N] [Commandment]: [How this class embodies principle]
 * - #[N] [Commandment]: [How this class embodies principle]
 *
 * **Related Classes/Features:**
 * - [Related item]: [How it relates]
 * - [Related item]: [How it relates]
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/[architecture-topic]
 * or https://wpshadow.com/training/[extending-wpshadow]
 *
 * @since   1.YDDD.HHMM
 * @package WPShadow\[Category]
 */
```

**Score Result:** 5.5/10 → 7.5/10 ⬆️ (Target after enhancement)

---

### Template 5: Helper Function (File-Level Docblock Prefix)
```php
/**
 * [Function Name] - Use When [Specific Use Case]
 *
 * [What this function does and when to use it. Include:
 *  - Primary use case
 *  - When to use this vs. WordPress APIs
 *  - Performance characteristics]
 *
 * **When to Use This Function:**
 * - [Scenario 1]
 * - [Scenario 2]
 * - [Scenario 3]
 *
 * **When NOT to Use This Function:**
 * - [Non-scenario 1]
 * - [Non-scenario 2]
 * - [Non-scenario 3]
 *
 * **Philosophy Reminder:**
 * [How this function connects to WPShadow philosophy or patterns.
 *  Example: "Enables Pattern 2: Use WordPress APIs first, only
 *   HTML parsing for DOM validation."]
 *
 * **Performance Profile:**
 * - [Cold call performance]: [Timeframe]
 * - [Cached call performance]: [Timeframe]
 * - [Best use case]: [When to use]
 * - [Avoid when]: [When to avoid]
 *
 * **Code Example:**
 * [Practical usage example showing how to use this function]
 *
 * @since  1.YDDD.HHMM
 * @param  [type] $param1 Description
 * @param  [type] $param2 Description
 * @return [type] [Description]
 */
```

**Score Result:** 5.0/10 → 6.8/10 ⬆️ (Target after enhancement)

---

## ✅ Validation Checklist

**Before committing any documentation change:**

### File-Level Docblock
- [ ] One-sentence problem statement at top
- [ ] Business impact paragraph included
- [ ] Persona/role explanations present
- [ ] Real-world scenario is vivid and specific
- [ ] Philosophy alignment (≥2 commandments referenced)
- [ ] KB/training links provided (≥1 link each)
- [ ] @since and @package tags present
- [ ] Length: 200-400 words (substantial, not verbose)

### Class-Level Docblock  
- [ ] Implementation pattern explained
- [ ] Architectural lesson taught (why this pattern)
- [ ] Related classes/features listed
- [ ] For base classes: Design pattern identified
- [ ] Philosophy connection made (if appropriate)

### Method Docblocks
- [ ] @param documented with type and description
- [ ] @return documented with type and description
- [ ] Complex methods: execution flow explained (1-N steps)
- [ ] AJAX handlers: security architecture documented
- [ ] UI methods: accessibility notes included
- [ ] @since tag present

### Tone & Style
- [ ] **Empathetic** - Use "Helpful Neighbor" voice
- [ ] **Concrete** - Include real-world scenarios (not abstractions)
- [ ] **Scannable** - Use bold headers and bullet points
- [ ] **Educational** - Teach philosophy and patterns
- [ ] **Practical** - Include code examples where helpful
- [ ] **Complete** - No hand-wavy explanations (be specific)

---

## 🎯 Philosophy Quick Reference

**The 11 Commandments (Reference in Docblocks):**

```
#1  Helpful Neighbor       → Empathetic tone, helps users succeed
#2  Free as Possible       → No artificial limitations in free version
#3  Register Don't Pay     → Fair exchange model
#4  Advice Not Sales       → Educational, not promotional
#5  Drive to KB            → Link to knowledge base articles
#6  Drive to Training      → Link to learning resources
#7  Ridiculously Good      → Quality bar that surprises
#8  Inspire Confidence     → Show safety and control
#9  Show Value             → Track and measure impact
#10 Beyond Pure            → Privacy by design
#11 Talk-About-Worthy      → Features users want to recommend
```

**How to Reference in Docblocks:**
```php
/**
 * ...
 * **Philosophy Alignment:**
 * - #1 Helpful Neighbor: This diagnostic guides users toward solutions
 * - #8 Inspire Confidence: Explicit security measures prevent data loss
 * - #9 Show Value: Reports specific metrics showing cleanup impact
 * ...
 */
```

---

## 📊 Quick Scoring Guide

**File-Level Docblock Quality:**
```
1-100 words:           ❌ Minimal (1/10)
100-150 words:         ⚠️ Thin (3/10)
150-200 words:         ✅ Adequate (6/10)
200-300 words:         ✅ Good (8/10)
300-400 words:         ✅ Excellent (9-10/10)
400+ words:            ⚠️ Verbose (7/10 - trim it)
```

**Philosophy Coverage:**
```
No philosophy mentioned:     ❌ 2/10
1 commandment referenced:    ⚠️ 4/10
2 commandments referenced:   ✅ 7/10
3+ commandments referenced:  ✅ 9/10
```

**KB/Training Link Coverage:**
```
No links:              ❌ 2/10
1 link:                ✅ 7/10
1 KB + 1 Training:     ✅ 9/10
Broken links:          ❌ 1/10
```

---

## 🚀 Pro Tips

### Tip 1: Make Business Impact Vivid
```php
// ❌ DON'T:
/**
 * Cleans up expired options.
 */

// ✅ DO:
/**
 * Cleans up expired options that bloat the database.
 * Site with 5000 expired options: queries take 300ms.
 * After cleanup: queries take 50ms. Impact: 30% server load reduction.
 */
```

### Tip 2: Explain "Why" Not Just "What"
```php
// ❌ DON'T:
/**
 * Uses nonce verification for CSRF protection.
 */

// ✅ DO:
/**
 * Uses nonce verification to prevent CSRF attacks where:
 * 1. Attacker tricks admin to visit malicious site
 * 2. Malicious site makes unauthorized admin_ajax request
 * 3. Nonce check fails, request rejected
 * This prevents admins' site actions being hijacked.
 */
```

### Tip 3: Connect to Philosophy Explicitly
```php
// ❌ DON'T:
/**
 * Automatically runs cleanup without user action.
 */

// ✅ DO:
/**
 * Automatically runs cleanup without user action.
 * Philosophy #1 (Helpful Neighbor) teaches: Automate tedious tasks.
 * Philosophy #8 (Inspire Confidence) teaches: Users feel safe, not worried.
 */
```

### Tip 4: Include Real Code Examples
```php
// ❌ DON'T:
/**
 * Fetch and cache HTML from pages.
 */

// ✅ DO:
/**
 * Fetch and cache HTML from pages.
 * 
 * @example
 *   $html = wpshadow_fetch_page_html( admin_url( 'users.php' ) );
 *   if ( ! is_wp_error( $html ) ) {
 *       // Check for security issues in rendered page
 *   }
 */
```

---

## ❌ Common Mistakes to Avoid

| Mistake | Why It's Wrong | Fix |
|---------|----------------|-----|
| Too technical | Alienates "Helpful Neighbor" philosophy | Add business impact explanation |
| Generic explanations | Doesn't teach specific patterns | Include real-world scenario |
| No philosophy link | Misses teaching opportunity | Reference 2-3 commandments |
| Missing KB links | Users don't know where to learn | Add ≥1 KB/training link |
| Vague language | Developers unsure how to use | Be specific with examples |
| No examples | Abstract and hard to understand | Include real code example |
| Broken links | Breaks learning pathway | Verify KB/training URLs exist |
| Tone too casual | Unprofessional and confusing | Use WordPress standard tone |

---

## 📱 Copy-Paste Templates

### Snippet 1: Philosophy Section (Copy-Paste Ready)
```php
/**
 * ...
 *
 * **Philosophy Alignment:**
 * - #1 Helpful Neighbor: [Explain how]
 * - #8 Inspire Confidence: [Explain how]
 * - #9 Show Value: [Explain how]
 */
```

### Snippet 2: Real-World Scenario (Copy-Paste Ready)
```php
/**
 * ...
 *
 * **Real-World Scenario:**
 * Before: [Specific bad situation with metrics]
 * After: [Specific good situation with metrics]
 * Impact: [Business outcome - revenue, performance, users]
 */
```

### Snippet 3: KB/Training Links (Copy-Paste Ready)
```php
/**
 * ...
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/[topic-slug] for detailed explanation
 * or https://wpshadow.com/training/[course-slug] for interactive guide
 */
```

---

## 🎓 Learning Resources

**For Implementing These Standards:**
- 📖 Full Audit Report: `/docs/REVIEWS/INLINE_DOCUMENTATION_COMPREHENSIVE_AUDIT.md`
- 📋 Action Plan: `/docs/REVIEWS/DOCUMENTATION_ENHANCEMENT_ACTION_PLAN.md`
- 📚 Original Standards: `/docs/REVIEWS/PHASE_1_QUICK_REFERENCE.md`
- 📖 Copilot Instructions: `/.github/copilot-instructions.md`

**Key Patterns:**
- Pattern 5 (Documentation Comments)
- Pattern 6 (File Creation Template)
- Pattern 7 (Diagnostic Implementation Checklist)

---

**Last Updated:** February 3, 2026  
**Version:** 1.0  
**Status:** Ready for Implementation  

Use this card as your **quick reference** for inline documentation excellence in WPShadow.
