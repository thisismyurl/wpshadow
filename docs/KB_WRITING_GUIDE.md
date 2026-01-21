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

### 6. **Visual & Media Integration** (Required)

#### **Images/Screenshots**
- **Placement:** After each tier heading to reinforce explanation
- **Quantity:** 
  - Tier 1 (TLDR): 1 key screenshot (the UI element being discussed)
  - Tier 2 (Intermediate): 2-3 screenshots showing steps
  - Tier 3 (Advanced): 1-2 technical diagrams or code output examples
- **Quality:** 1200x800px minimum, labeled with arrows/callouts
- **Alt Text:** Descriptive for accessibility and SEO ("WordPress Settings page showing Permalinks dropdown with 'Post name' selected")
- **Storage:** `/assets/kb-images/[category]/[article-slug].jpg`
- **Naming:** `delete-item-ui.jpg`, `delete-item-step-1.jpg`, `delete-item-confirmation.jpg`

#### **Video Tutorials** (Optional but encouraged)
- **When to include:** Complex procedures, visual workflows, or anything with multiple steps
- **Placement:** After Tier 2 section as reinforcement
- **Length:** 2-5 minutes (embed, not full videos)
- **Content:** Demonstrate the feature + explain the "why"
- **Format:** 
  - Embed Academy training video (if exists)
  - Or link to YouTube tutorial with timestamp
  - Or create placeholder: `[Video Tutorial: Deleting Items Safely - 3 min]`
- **Storage:** Link to WPShadow Academy: `https://academy.wpshadow.com/courses/[course-slug]/lessons/[lesson-slug]`
- **Code in article:**
```html
<div class="kb-video">
  <h4>📺 Watch: Deleting Items Safely</h4>
  <iframe src="https://academy.wpshadow.com/embed/..." width="100%" height="400"></iframe>
  <p><a href="https://academy.wpshadow.com/...">Full course: WordPress Content Management</a></p>
</div>
```

---

### 7. **WPShadow Academy Integration** (Required)

#### **Training Course Links**
- **After Tier 2:** Link to related Academy course for deeper learning
- **After Tier 3:** Link to advanced/developer course if applicable
- **Format:** 
```
## Want to Master This?
Take the [Course Name](https://academy.wpshadow.com/courses/[slug]) course on WPShadow Academy:
- Lesson 1: [Topic] (5 min)
- Lesson 2: [Topic] (8 min)
- Lesson 3: [Topic] (4 min)
- **Certificate:** Free completion badge
```

#### **Course Creation Guidance**
Map KB articles to Academy courses:
- **Beginner Courses:** Link to Tier 1/2 articles
- **Advanced Courses:** Link to Tier 3 articles
- **Developer Courses:** Link to hooks/code articles
- Each course should have:
  - 2-5 short videos (2-5 min each)
  - Downloadable resources (checklists, code templates)
  - Quizzes to reinforce learning
  - Completion certificate

---

### 8. **Gamification System** (Required)

#### **Reading Badges & Points**
Every KB article awards points for engagement:

**Point Structure:**
- **Read article (TLDR):** 5 points
- **Read full article (Tier 2):** 15 points
- **Read advanced section (Tier 3):** 25 points
- **Watch embedded video:** 10 points
- **Take Academy course:** 50 points
- **Complete quiz:** 20 points
- **Total possible per article:** 125 points

**Badges Earned:**
- 🥉 "Security Scout" (10 KB articles read)
- 🥈 "Performance Pro" (25 KB articles read)
- 🥇 "WPShadow Master" (50+ KB articles read)
- 🎓 "Academy Graduate" (3+ courses completed)
- 🏆 "Guardian Angel" (Security category mastery)
- ⚡ "Speed Demon" (Performance category mastery)

**Implementation:**
```php
// In KB article display:
do_action('wpshadow_kb_article_loaded', $article_id, 'tldr'); // 5 points
do_action('wpshadow_kb_article_loaded', $article_id, 'intermediate'); // 15 points
do_action('wpshadow_kb_article_loaded', $article_id, 'advanced'); // 25 points
```

**Dashboard Display:**
- Show user's total points in navigation
- Show current badge/level
- "You're 15 points away from Security Scout badge"
- Leaderboard (optional, can be turned off for privacy)

**UI Elements:**
- Progress bar at top of KB article: "You've earned 45/125 points on this article"
- "Achievement Unlocked" notification when badge earned
- "Stats" dashboard showing reading progress

---

### 9. **SEO Optimization** (Required)

#### **Keyword Research Strategy**
For each WordPress feature, target the most-searched questions:

**Keywords to Target (by intent):**
1. **How-to:** "How to [feature name]", "How do I [action]"
2. **Why:** "Why [feature name] matters", "Why is [action] important"
3. **What:** "What is [feature name]", "What does [action] do"
4. **Troubleshooting:** "[Feature name] not working", "[Action] isn't available"
5. **Comparison:** "[Feature name] vs [alternative]"

**Example for "Delete Item":**
- ✅ "How to delete a post in WordPress"
- ✅ "Permanently delete WordPress posts"
- ✅ "How to undo deleted posts in WordPress"
- ✅ "What happens when you delete a post"
- ✅ "Delete vs trash in WordPress"

#### **On-Page SEO Elements**

**Meta Tags (in article header):**
```html
<meta name="description" content="Learn how to permanently delete WordPress posts, pages, and items. Includes safety tips, undo methods, and automation options.">
<meta name="keywords" content="delete post WordPress, permanently delete, trash WordPress, restore deleted post">
```

**Headers (for Google "People Also Ask"):**
- Use question-based H2s: "How do I permanently delete a post?"
- Use comparison H2s: "Delete vs. Trash: What's the difference?"
- Use problem-solution H2s: "Accidentally deleted something? Here's how to restore it"

**Schema Markup:**
```json
{
  "@context": "https://schema.org",
  "@type": "HowTo",
  "name": "How to Delete a WordPress Post",
  "step": [
    {
      "@type": "HowToStep",
      "name": "Go to Posts",
      "text": "Navigate to Posts in WordPress admin"
    }
  ]
}
```

**Content Structure for Google:**
- **H1:** Your main title (includes primary keyword)
- **H2:** Major sections with secondary keywords
- **H3:** Subsections with long-tail keywords
- **Lists:** Use bullet/numbered lists (Google loves these)
- **Bold:** Highlight keywords naturally

**Internal Linking Strategy:**
- Link to related articles (helps Google understand site structure)
- Use descriptive anchor text (not "click here")
- Link to 2-3 related articles per article
- Example: "[Learn about WordPress backups](/kb/backups)" instead of "[learn more](/kb/backups)"

**External Linking Strategy:**
- Link to WordPress.org official docs (builds authority)
- Link to WordPress.org support forum searches (social proof)
- 1-2 external links per article maximum (don't dilute authority)
- Use rel="noopener noreferrer" for external links

#### **Technical SEO Elements**

**Metadata:**
- **URL Slug:** Use primary keyword (`/kb/how-to-delete-wordpress-posts/`)
- **Word Count:** 1,500-3,000 words (optimal for ranking)
- **Read Time:** Include (Google factors this in)
- **Last Updated:** Include (freshness signal)
- **Featured Image:** Include (CTR booster in search results)

**Performance:**
- Image optimization (compressed, <100KB)
- Mobile-responsive design
- Fast load time (<3 seconds)
- No intrusive ads/popups (Google penalizes)

**Readability Score:**
- Flesch Reading Ease: Aim for 60+ (conversational, not too complex)
- Flesch-Kincaid Grade: Aim for 8-10 (accessible to most readers)
- Use short paragraphs (2-3 sentences max)
- Use short sentences (12-15 words average)

#### **User-First Content, Google-Friendly Format**

**Principle:** Write for users FIRST, Google SECOND

1. **Answer the question immediately** (TLDR = featured snippet goldmine)
2. **Use natural language** (don't keyword-stuff)
3. **Structure for scanning** (headers, lists, bold text)
4. **Include examples** (users want to see it in action)
5. **Link helpfully** (not to game the system, but because it's useful)

**Example optimization flow:**
```
User searches: "How to delete a post in WordPress"
↓
Google shows featured snippet: Your TLDR section
↓
User clicks to read more
↓
User reads Tier 2 for practical steps
↓
User watches embedded video for visual confirmation
↓
User bookmarks for later (trust building)
↓
User comes back to learn more (Tier 3)
```

---

### 10. **Additional Suggestions** (Optional)

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
- [ ] **Images/Screenshots:** 
  - [ ] Tier 1: 1 key screenshot
  - [ ] Tier 2: 2-3 screenshots showing steps
  - [ ] Tier 3: 1-2 technical diagrams
  - [ ] All images have alt text for accessibility
- [ ] **Video Tutorial:** Embedded (2-5 min), after Tier 2
- [ ] **Academy Integration:** Links to training courses
- [ ] **Gamification:** Points structure included in metadata
- [ ] **Context:** Before/after/related steps explained with examples
- [ ] **WordPress.org Links:** Official docs or forum search links included
- [ ] **WPShadow Integration:** Features/hooks/code documented (if applicable)
- [ ] **Upsell Path:** Helpful, not pushy, optional, educational
- [ ] **Formatting:** Headers, bullets, code blocks, tone markers used
- [ ] **Further Reading:** Cross-links and resources at bottom
- [ ] **Metadata:** Read time, difficulty, category, points at top
- [ ] **SEO Optimization:**
  - [ ] Primary keyword in H1 and first paragraph
  - [ ] Secondary keywords in H2/H3 headers
  - [ ] Meta description written (150 characters)
  - [ ] URL slug includes primary keyword
  - [ ] 1,500-3,000 words minimum
  - [ ] Internal links to 2-3 related articles
  - [ ] 1-2 external WordPress.org links
  - [ ] Flesch Reading Ease: 60+
  - [ ] Mobile-responsive format confirmed
  - [ ] Featured image added (1200x800px+)
  - [ ] Schema markup included (HowTo or FAQ)
- [ ] **No Jargon:** Vague terms expanded, concrete examples provided
- [ ] **Links Work:** All URLs tested (WordPress.org, WPShadow KB, Academy, etc.)
- [ ] **User First:** Content prioritizes user needs over SEO tactics
- [ ] **Real-World Examples:** At least one scenario relevant to target users

---

## Example Article Template

```markdown
# [Feature Name]

**Read Time:** 3-5 minutes  
**Difficulty:** Beginner | Intermediate | Advanced  
**Category:** [Category]  
**Last Updated:** [Date]  
**Points Available:** 125 (5 TLDR + 15 Intermediate + 25 Advanced + 10 Video + 50 Academy + 20 Quiz)

---

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

**Why it matters:** [1 sentence on business/UX impact]

[KEY SCREENSHOT showing the UI element]

---

## What This Does (Tier 2)
[2-3 paragraphs with context, why it matters, best practices]

**Where to find it:** [Specific UI location or WordPress.org link]

**Default value:** [What's set by default]

**Why it matters:** [Business/UX impact with example]

[SCREENSHOT 1: First step]

[SCREENSHOT 2: Second step]

[SCREENSHOT 3: Confirmation or result]

### 📺 Watch: [Video Title]
[Embedded video or Academy course lesson]
- Runtime: 3-5 minutes
- [Link to full Academy course with related lessons]

---

## Advanced: [Specific Topic] (Tier 3)
[Technical details, hooks, code examples, performance notes]

[TECHNICAL DIAGRAM or CODE OUTPUT]

### Code Example
\`\`\`php
// Your code here
\`\`\`

### Related Hooks
- `hook_name` - Description
- `filter_name` - Description

### Performance Considerations
- [Metric if applicable]: "Deleting 1,000 posts takes ~30 seconds"
- [Link to related performance article]

---

## Related WPShadow Features
- **[Feature Name]:** [What it does and why it relates]
  - [Link to related KB]
  - 🎓 [Related Academy course]

---

## Common Questions

### Q: [Common question]?
**A:** [Clear answer with examples]
- Related: [Link to relevant KB article]
- Support: [Link to WordPress.org forum search]

### Q: [What if I...]?
**A:** [Troubleshooting steps + WordPress.org support link]
- Try this first: [Simple fix]
- If that doesn't work: [Intermediate fix]
- Advanced troubleshooting: [Link to advanced KB article]

---

## 🎓 Want to Master This?
Take the [**Course Name**](https://academy.wpshadow.com/courses/[slug]) on WPShadow Academy:
- Lesson 1: [Topic] (5 min)
- Lesson 2: [Topic] (8 min)
- Lesson 3: [Topic] (4 min)
- **Certificate:** Free completion badge when finished
- **Points:** 50 points + "Academy Graduate" badge

---

## Further Reading
- [Related KB Article 1](/) — [Why you should read this]
- [Related KB Article 2](/) — [Connection to this topic]
- [WordPress.org: Official Documentation](https://wordpress.org/...) — Official WordPress guide
- [WordPress.org Support Forum: Common Issues](https://wordpress.org/support/forum/...) — Community Q&A
- [Related WPShadow Feature KB](/) — How WPShadow automates this

---

**Questions?** 
- [Search WPShadow support forums]
- [Contact WPShadow support]
- [Join WPShadow community Discord]

---

_This article earned you **+5 points** for reading. Keep learning to unlock badges and achievements!_
```

---

## Additional Thoughts

### ✅ What You Nailed
1. Friendly neighbor tone ✓
2. 3-tier structure ✓
3. TLDR-first approach ✓
4. Upsell path (helpful, not pushy) ✓
5. Internal cross-linking ✓
6. Images/screenshots ✓
7. Video tutorials ✓
8. Academy integration ✓
9. Gamification ✓
10. SEO optimization ✓

### 🎯 Implementation Priority

**Phase 1 (MVP - 2 weeks):**
- Write 5-10 core articles (Delete, SSL, Backup, Debug, Permalinks)
- Include Tiers 1-2 (simple + intermediate)
- Add 1 screenshot per article
- Basic gamification (points system)
- Basic SEO (keywords, meta descriptions, internal links)

**Phase 2 (Enhancement - 3 weeks):**
- Add Tier 3 to all articles (advanced sections)
- Add embedded videos to 50% of articles
- Add Academy course links
- Advanced gamification (badges, leaderboard)
- Full SEO optimization (schema markup, performance)

**Phase 3 (Refinement - ongoing):**
- Update articles based on user feedback
- Add real-world examples from support tickets
- Create Academy courses to match KB articles
- Monitor search rankings and update as needed
- Expand gamification with new badges

### 🚀 Launch Strategy

**Week 1:** 
- Document KB Writing Guide (✅ Done)
- Create first 3 articles (Delete Item, SSL, Backup)
- Test gamification system
- Set up image directory structure

**Week 2:**
- Create Academy placeholder courses (link to KB for now)
- Set up SEO tracking (Google Search Console)
- Create content calendar for remaining 172 articles
- Get community feedback on first 3 articles

**Week 3+:**
- Batch-write articles (10 at a time)
- Prioritize by: Search volume → User questions → Feature impact
- Update articles monthly based on:
  - Search rankings (improve underperformers)
  - Support ticket themes (address pain points)
  - WordPress updates (keep content fresh)

### 📊 Success Metrics

**After 1 month:**
- [ ] 20+ KB articles published
- [ ] 1,000+ monthly page views to KB
- [ ] 10+ search rankings in top 100 (Google)
- [ ] 500+ user points earned across community
- [ ] 3+ badges "in the wild" (users earned them)

**After 3 months:**
- [ ] 100+ KB articles published
- [ ] 5,000+ monthly page views to KB
- [ ] 50+ search rankings in top 50 (Google)
- [ ] 5,000+ user points earned
- [ ] 20+ active badge holders
- [ ] 10+ Academy courses created

**After 6 months:**
- [ ] 175+ KB articles published (complete)
- [ ] 15,000+ monthly page views to KB
- [ ] 100+ search rankings in top 20 (Google)
- [ ] 20,000+ user points earned
- [ ] 50+ badge holders
- [ ] 30+ Academy courses
- [ ] KB becoming primary traffic source (20%+ of organic)

### 🤝 Integration Points

**For Developers/Agencies:**
- KB articles in plugin help panels
- Hooks to inject Academy course links
- Gamification API for custom badge systems
- SEO metadata available via REST API

**For Community:**
- Share best articles on social media
- User-generated KB article translations
- Community voting on which articles to write next
- Guest author contributions

**For WPShadow Pro/Guardian/Vault:**
- Link to KB articles from features
- Use gamification as onboarding
- Academy courses as upsell to Pro
- KB search in premium dashboard

---

## File Structure After Implementation

```
docs/
├── KB_WRITING_GUIDE.md                    # This guide
├── KB_ARTICLE_MAP.md                      # Content calendar
└── [to be created after publishing]

assets/
└── kb-images/                             # NEW
    ├── security/
    │   ├── delete-item-ui.jpg
    │   ├── ssl-setup-step-1.jpg
    │   └── ...
    ├── performance/
    │   ├── caching-enabled.jpg
    │   └── ...
    └── config/
        └── ...

includes/
├── knowledge-base/                        # NEW (Phase 5)
│   ├── class-kb-article.php
│   ├── class-kb-gamification.php
│   ├── class-kb-seo-optimizer.php
│   ├── class-kb-video-manager.php
│   └── class-kb-academy-integration.php
│
└── views/
    └── kb-article-template.php            # NEW
```

---

## Real-World Example: "Delete Item" Article Outline

**Primary Keyword:** "How to delete a post in WordPress"  
**Secondary Keywords:** "Permanently delete", "Undo deleted post", "Trash vs delete"  
**Target Search Volume:** 1,200/month  
**Current Google Position:** Unranked (opportunity)

**Sections:**
1. TLDR (includes featured snippet trigger)
2. What This Does (with 3 screenshots)
3. Advanced: Database Impact & Hooks (with code)
4. Common Questions (target "People Also Ask")
5. Academy course link
6. 45 points available

**Expected Outcome:**
- Rank in top 50 within 3 months
- Top 20 within 6 months
- 200-400 monthly views
- 50-100 users earning points
- Drive 5-10 Guardian/Vault signups/month

---

**Ready to draft the first KB article?**

I recommend starting with one of these (in order):
1. **Delete Item** (post 36) - High user intent, great for SEO
2. **SSL/HTTPS** (security flagship) - Critical feature, searchable
3. **Backups** (most important protection) - Highly searchable, guides users

Which would you like me to draft first?
