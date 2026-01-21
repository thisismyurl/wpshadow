# WPShadow Knowledge Base Writing Guide

**Version:** 1.0  
**Last Updated:** January 21, 2026  
**Philosophy Alignment:** Commandments #1, #4, #5, #6 (Helpful Neighbor, Educate Not Sell, Drive to KB/Training)

---

## Core Requirements

### 1. **Tone & Voice: Friendly Neighbor**
- **Approach:** Conversational, approachable, like talking to a knowledgeable friend
- **Style:** TLDR-first (answer the question immediately, then expand)
- **Connection Prompts:** Use questions to help readers relate ("Ever notice your site getting slower?")
- **Avoid:** Jargon without explanation, corporate speak, fear-mongering
- **Prolific:** Use concrete examples, real-world scenarios, relatable situations

**Example opener:**
> "Ever accidentally deleted something you wanted to keep? Yeah, that's why deletion is permanent in WordPress. Here's how to do it safely—and what to do if you need to undo it."

---

### 2. **3-Tier Article Structure** (Required for all articles)

Every KB article must include three learning levels:

#### **Tier 1: Simple (The TLDR)**
- 1-2 sentences answering the core question
- One sentence explanation of why it matters
- *Audience:* Beginners who just want the answer

*Example:*
> **What's this do?** Click to permanently remove this item from your site. You won't get it back unless you have a backup.

#### **Tier 2: Intermediate (Getting Deeper)**
- 2-3 paragraphs with step-by-step context
- Explain the "why" behind the action
- Mention potential consequences
- Offer safety tips or best practices
- *Audience:* Users who want to understand what they're doing

*Example:*
> "When you delete an item, it goes away forever (unless you have backups). Before deleting, ask yourself: Is this something you might need later? Can you archive it instead? Here's the safest way to delete..."

#### **Tier 3: Advanced (The Deep Dive)**
- Technical details, hooks, code snippets, performance implications
- Alternative approaches for power users
- Integration with WPShadow features, workflows, or related tools
- Database-level considerations
- *Audience:* Developers, advanced users, agency owners

*Example:*
> "Under the hood, WordPress triggers the `delete_post` hook. If you're building custom workflows, here's how to intercept deletion and apply custom logic..."

---

### 3. **Additional Required Sections**

#### **Table of Contents** (at top)
Quick-jump links to each section. Format:
```
- TLDR
- What This Does (Tier 2)
- Advanced: [Specific topic] (Tier 3)
- Related WPShadow Features
- Common Questions
- Further Reading
```

#### **Context & Related Steps**
- **Before this:** What should users do/check first?
- **After this:** What's the logical next step?
- **Related items:** Links to closely related articles
- *Use concrete examples:* "Before deleting a post, check if comments exist" (not vague)

#### **WPShadow Integration** (if applicable)
Only include if there's a real connection:
- **Diagnostic:** "The [diagnostic name] can detect..."
- **Treatment:** "The [treatment name] auto-fixes this by..."
- **Workflow:** "You can automate this with workflows by..."
- **Hooks/Filters:** If developers need to extend it
- Links to related KB articles on these features
- Code examples if relevant

#### **Common Questions/Troubleshooting**
- FAQs based on real user confusion
- "What if I accidentally..." scenarios
- "How do I undo this?"
- WordPress.org forum search links for each

#### **Helpful Upsell Path** (Philosophy-compliant)
- Mention where Guardian, Vault, or Pro could help, NOT as a paywall but as "here's what we handle for you"
- Example: "Worried about accidental deletion? Guardian monitors and can alert you before critical changes"
- Keep it optional, educational, not pushy
- Always include the free alternative first

#### **Official Documentation Links**
- Link to WordPress.org documentation (where applicable)
- Link to WordPress.org support forum searches
- Link to relevant WordPress.org tutorials
- Use this format: `[Learn more at WordPress.org →](https://wordpress.org/support/...)`

#### **Further Reading** (at bottom)
- Related KB articles (cross-linking)
- Related WPShadow KB articles
- Official WordPress resources
- Training videos (when available)

---

### 4. **Formatting & Structure**

#### **Headers**
```
# Article Title (Main topic)
## TLDR (Tier 1 - Quick answer)
## What This Does (Tier 2 - Context)
## Advanced: [Specific topic] (Tier 3 - Deep dive)
## Related WPShadow Features (if applicable)
## Common Questions
## Further Reading
```

#### **Metadata** (top of article)
```
**Read Time:** 3-5 minutes  
**Difficulty:** Beginner / Intermediate / Advanced  
**Category:** [Security/Performance/Content/Config/Workflow]  
**Last Updated:** [Date]
```

#### **Visual Formatting**
- Use bullet points for lists
- Use `> quotes` for important tips
- Use `code snippets` for technical terms
- Use **bold** for key concepts
- Use tables for comparisons (if helpful)

#### **Tone Markers**
- ✅ Use for "correct/best practice"
- ⚠️ Use for "warning/be careful"
- 💡 Use for "pro tip"
- ❌ Use for "don't do this"

---

### 5. **Specific Content Requirements**

#### **For General WordPress Features**
- Explain what it is (beginners don't know)
- Show the UI location ("Settings → General → [option]")
- Explain the default value and alternatives
- Link to WordPress.org official docs
- Provide next/previous steps with context

*Example structure:*
> "**Where to find it:** Go to Settings → Permalinks  
> **What it does:** Controls how your post URLs look  
> **Default:** /?p=123 (not pretty)  
> **Better options:** Post name (/my-post/), or date-based (/2026/01/my-post/)  
> **Why it matters:** Pretty URLs are better for SEO and user experience"

#### **For WPShadow Features/Hooks/Code**
- Document the hook/filter name and purpose
- Show a real-world code example
- Link to WPShadow documentation
- Explain where this runs (front-end, admin, CLI, etc.)
- Link to related KB articles on this feature
- Include performance/security implications

*Example structure:*
```php
// Hook documentation:
do_action('wpshadow_before_treatment_apply', $treatment_name, $args);

// Real example:
add_action('wpshadow_before_treatment_apply', function($treatment_name) {
    if ($treatment_name === 'disable_file_editor') {
        // Log this for audit purposes
        wp_mail(ADMIN_EMAIL, 'File editor disabled', 'Someone just disabled file editing');
    }
});
```

#### **For Performance/Security Implications**
- Explain the impact (time saved, risk reduced, etc.)
- Show the metric: "This can save 500ms on page load"
- Link to WPShadow KPI tracking if applicable
- Explain what happens if this is NOT done

#### **For Troubleshooting**
- Start with "What could go wrong?"
- Provide solutions in order of easiest-first
- Link to WordPress.org support forum searches
- Mention Guardian/Vault/Pro only if relevant to solving the problem

---

### 6. **Additional Suggestions to Consider**

These are optional but enhance the article:

#### **Visual/Screenshot Guidance**
- Mention "See screenshot below" but actual screenshots can be added later
- Describe UI elements clearly so non-visual readers understand

#### **Security & Backup Warnings**
- Mention if action is reversible/not reversible
- Link to backup articles if deleting/modifying is permanent
- Reference WPShadow Vault or Guardian when relevant
- Always show the undo path first

#### **Real-World Examples**
- "For a blog: You might have 100 scheduled posts..."
- "For an e-commerce site: Deleting a product should..."
- Helps users connect to their specific use case

#### **Performance Notes**
- "Deleting 1,000 items might take 30 seconds—here's why"
- Link to related performance KB articles
- Explain async options if available

#### **Accessibility Notes** (optional)
- "Use keyboard shortcut [Alt+D] to delete faster"
- "Screen readers will announce this as..."
- Helps all users, especially power users

#### **Version/Compatibility**
- "Works in WordPress 5.0+"
- "Not available in WPShadow Free tier" (if applicable)
- Links to version-specific docs

---

## Checklist: Before Publishing

- [ ] **Tone:** Friendly neighbor, conversational, no jargon
- [ ] **TLDR:** First section answers the question in 1-2 sentences
- [ ] **3-Tier Structure:** Simple / Intermediate / Advanced all present
- [ ] **Table of Contents:** Quick-jump navigation at top
- [ ] **Context:** Before/after/related steps explained with examples
- [ ] **WordPress.org Links:** Official docs or forum search links included
- [ ] **WPShadow Integration:** Features/hooks/code documented (if applicable)
- [ ] **Upsell Path:** Helpful, not pushy, optional, educational
- [ ] **Formatting:** Headers, bullets, code blocks, tone markers used
- [ ] **Further Reading:** Cross-links and resources at bottom
- [ ] **Metadata:** Read time, difficulty, category at top
- [ ] **No Jargon:** Vague terms expanded, concrete examples provided
- [ ] **Links Work:** All URLs tested (WordPress.org, WPShadow KB, etc.)

---

## Example Article Template

```markdown
# [Feature Name]

**Read Time:** 3-5 minutes  
**Difficulty:** Beginner | Intermediate | Advanced  
**Category:** [Category]  
**Last Updated:** [Date]

## Table of Contents
- TLDR
- What This Does
- Advanced: [Specific topic]
- Related WPShadow Features
- Common Questions
- Further Reading

---

## TLDR (Tier 1)
[1-2 sentences answering the core question]

---

## What This Does (Tier 2)
[2-3 paragraphs with context, why it matters, best practices]

**Where to find it:** [Specific UI location or WordPress.org link]

**Default value:** [What's set by default]

**Why it matters:** [Business/UX impact]

---

## Advanced: [Specific Topic] (Tier 3)
[Technical details, hooks, code examples, performance notes]

### Code Example
\`\`\`php
// Your code here
\`\`\`

### Related Hooks
- `hook_name` - Description

---

## Related WPShadow Features
- **[Feature Name]:** [What it does and why it relates]
  - [Link to related KB]

---

## Common Questions

### Q: [Common question]?
**A:** [Clear answer with examples]

### Q: [What if I...]?
**A:** [Troubleshooting steps + WordPress.org support link]

---

## Further Reading
- [Related KB Article 1](/)
- [Related KB Article 2](/)
- [WordPress.org: Official Doc](https://wordpress.org/...)
- [WordPress.org Support Forum Search](https://wordpress.org/support/forum/...)

---

**Questions?** [Search WPShadow support forums] or [contact support]
```

---

## Additional Thoughts

### ✅ What You Nailed
1. Friendly neighbor tone ✓
2. 3-tier structure ✓
3. TLDR-first approach ✓
4. Upsell path (helpful, not pushy) ✓
5. Internal cross-linking ✓

### 🤔 Nice-to-Haves (Not Required)
- Screenshots/GIFs (can add later)
- Video embeds (when training videos exist)
- Security/backup warnings (add when action is destructive)
- Real-world examples (especially for e-commerce/blogs)
- Performance metrics (when relevant)

### 🎯 Implementation Approach
**Start with:** Simple, high-impact articles (Delete Item, SSL, Backups)  
**Expand to:** Feature-specific articles (Workflows, Kanban Board)  
**Finish with:** Config/advanced articles (Hooks, custom code)

---

**Ready to write the first KB article?** I'd suggest starting with **"Delete Item"** (post 36) since you already have the tooltip content. It's straightforward, useful, and will establish your writing style before tackling more complex topics.

Want me to draft the first article following this guide?
