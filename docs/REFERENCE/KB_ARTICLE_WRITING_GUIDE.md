# WPShadow Knowledge Base Article Writing Guide

**Last Updated:** January 21, 2026  
**Version:** 2.0

This guide defines our writing standards, structure, and shortcode usage for all WPShadow Knowledge Base articles.

---

## ✅ Core Values Embedded

**Commandment #5 - Drive to Knowledge Base:** This guide ensures our KB contains genuinely helpful, comprehensive articles that empower users.

**Commandment #6 - Drive to Free Training:** All KB articles are free and accessible, supporting the learning of every user.

**Accessibility Pillar 🎓 - Learning Inclusive:** Articles serve diverse learning styles with clear explanations, screenshots, step-by-step instructions, and troubleshooting.

Learn more: [PHILOSOPHY/VISION.md](../../PHILOSOPHY/VISION.md) | [PHILOSOPHY/ACCESSIBILITY.md](../../PHILOSOPHY/ACCESSIBILITY.md)

---

## 📋 Table of Contents
1. [Article Structure Template](#article-structure-template)
2. [Writing Style Guidelines](#writing-style-guidelines)
3. [Shortcodes Reference](#shortcodes-reference)
4. [Standard Sections](#standard-sections)
5. [Best Practices](#best-practices)

---

## Article Structure Template

Every KB article must follow this exact structure:

```markdown
# [Article Title] (Action-Oriented, Clear)

**Read Time:** X-Y minutes  
**Difficulty:** Beginner | Intermediate | Advanced  
**Category:** [Category Name]  
**Last Updated:** [Date]  
**Points Available:** [Total] ([breakdown])

> This article is written for the WordPress Block Editor (Gutenberg). Screens and steps assume modern WP Admin.

---

## Table of Contents
- TLDR: Quick Answer
- [Context/Overview Section]
- [Main Instructions]
- Troubleshooting & Rollback
- Advanced: [Technical Details]
- Related WPShadow Features
- Common Questions (FAQ)
- Further Reading & Resources
- WPShadow Academy
- Get Expert Help

---

## TLDR: Quick Answer

[2-3 paragraph summary with the fastest path to success]

[wpshadow_image id="tldr-diagram" alt="Quick visual overview"]

---

## [Main Content Sections]

[Your detailed content with clear headings, steps, visuals]

[wpshadow_video id="section-demo" caption="Watch: Step-by-step walkthrough"]

---

## Troubleshooting & Rollback

[Common issues and solutions]

[wpshadow_screenshot id="error-example" alt="Common error message"]

---

## Advanced: [Technical Topic]

[For intermediate/advanced users: CLI commands, hooks, code examples]

---

## Related WPShadow Features

[How WPShadow helps with this topic - specific features, workflows, treatments]

---

## Common Questions (FAQ)

**Q: [Question]**  
A: [Clear, concise answer]

[Repeat 5-8 FAQs]

---

## Further Reading & Resources

We've carefully curated these external resources to help you learn more. While we strive to ensure all links lead to reliable, high-quality content, please note these are third-party sites not under our control. If you encounter any broken links or questionable content, [please let us know](https://wpshadow.com/contact) so we can review and update this list.

- [Resource Title](URL) — Brief description of what this covers
- [Resource Title](URL) — Why this is valuable
- [Resource Title](URL) — What you'll learn

[wpshadow_image id="external-resources" alt="Additional learning resources"]

---

## WPShadow Academy

**This is a FREE course** designed to make you confident and skilled in WordPress management. We created the WPShadow Academy because we believe a stronger WordPress community benefits everyone. Whether you're managing your first site or your fiftieth, our academy will help you develop best practices, avoid common pitfalls, and become the WordPress expert your team relies on.

**Enroll now (completely free):** [WPShadow Academy: WordPress Site Management Essentials](https://wpshadow.com/academy)

**What you'll learn:**
- [Specific skill 1 related to this article]
- [Specific skill 2 related to this article]
- [Specific skill 3 related to this article]

[wpshadow_video id="academy-preview" caption="Preview: Academy course overview"]

---

## Get Expert Help

Solved your immediate problem? Excellent! But if you're looking for ongoing peace of mind, **WPShadow Pro Services** can take WordPress management entirely off your plate.

**Our team handles:**
- 24/7 monitoring and instant issue alerts
- Automated backups with one-click restore
- Security hardening and malware prevention
- Performance optimization and caching
- Plugin/theme updates (tested safely)
- Emergency troubleshooting and rollback

**Perfect for:**
- Agencies managing multiple client sites
- Businesses that can't afford downtime
- Anyone who wants WordPress to "just work"

[Learn more about WPShadow Pro Services →](https://wpshadow.com/pro-services)

[wpshadow_cta id="pro-services" variant="standard"]

---

*Was this article helpful? [Rate it and help us improve](https://wpshadow.com/rate/[article-slug])*
```

---

## Writing Style Guidelines

### Voice & Tone
- **Helpful Neighbor:** Never pushy, always empowering
- **Plain English:** No jargon without explanation
- **Action-Oriented:** Lead with "what to do," then "why it matters"
- **Confident:** "Do this" not "You might want to consider..."
- **Transparent:** Admit limitations, link to better resources when needed

### Technical Level
- **Beginner:** Assume no WordPress knowledge, explain every term
- **Intermediate:** Assume WP Admin familiarity, introduce new concepts
- **Advanced:** Can use technical terms, CLI examples, code snippets

### Grammar & Style
- **Active voice:** "Click the button" not "The button should be clicked"
- **Present tense:** "This fixes..." not "This will fix..."
- **Second person:** "You" not "Users" or "One"
- **Short sentences:** 15-20 words average
- **Short paragraphs:** 2-4 sentences max
- **Contractions OK:** "don't" not "do not" (feels conversational)

### Formatting
- **Headers:** Use H2 for major sections, H3 for subsections
- **Lists:** Bulleted for features/benefits, numbered for sequential steps
- **Code blocks:** Always specify language (bash, php, etc.)
- **Emphasis:** Bold for UI elements, italic for terms, code for tech terms
- **Links:** Descriptive text, never "click here"

---

## Shortcodes Reference

### Visual Content Shortcodes

#### 1. wpshadow_image
Displays contextual images (screenshots, diagrams, illustrations)

```
[wpshadow_image id="unique-id" alt="Descriptive alt text" caption="Optional caption"]
```

**Parameters:**
- `id` (required): Unique identifier (slug format: lowercase-with-hyphens)
- `alt` (required): Screen reader description
- `caption` (optional): Visible caption text
- `size` (optional): `thumbnail|medium|large|full` (default: `large`)

**Examples:**
```
[wpshadow_image id="plugin-activation-screen" alt="WordPress plugins page showing activate button"]

[wpshadow_image id="backup-success-notice" alt="Green success message after backup completes" caption="Your backup completed successfully"]

[wpshadow_image id="ssl-comparison-diagram" alt="Side-by-side comparison of HTTP vs HTTPS" size="full"]
```

**Current behavior:** Returns filler image placeholder  
**Future behavior:** API-generated screenshots, diagrams, or curated images

---

#### 2. wpshadow_video
Embeds instructional videos or screen recordings

```
[wpshadow_video id="unique-id" caption="Optional caption" duration="X:XX"]
```

**Parameters:**
- `id` (required): Unique identifier for the video
- `caption` (optional): Video description/title
- `duration` (optional): Video length (e.g., "2:45")
- `autoplay` (optional): `true|false` (default: `false`)

**Examples:**
```
[wpshadow_video id="plugin-activation-walkthrough" caption="Watch: How to safely activate a plugin"]

[wpshadow_video id="ssl-setup-demo" caption="Complete SSL setup in 5 minutes" duration="5:12"]

[wpshadow_video id="advanced-wp-cli" caption="Using WP-CLI for plugin management" duration="8:30"]
```

**Current behavior:** Returns filler video placeholder  
**Future behavior:** Embedded YouTube/Vimeo or hosted screen recordings

---

#### 3. wpshadow_screenshot
High-fidelity UI screenshots (specific to WordPress screens)

```
[wpshadow_screenshot id="unique-id" alt="Description" highlight="selector"]
```

**Parameters:**
- `id` (required): Unique identifier
- `alt` (required): Screen reader description
- `highlight` (optional): CSS selector to highlight (e.g., `#activate-button`)
- `annotate` (optional): Add numbered callouts (e.g., `1,2,3`)

**Examples:**
```
[wpshadow_screenshot id="plugins-list-screen" alt="WordPress installed plugins page" highlight=".activate"]

[wpshadow_screenshot id="plugin-editor-warning" alt="Warning message in plugin editor" annotate="1,2"]
```

**Current behavior:** Returns filler screenshot placeholder  
**Future behavior:** API-captured live WordPress screenshots with annotations

---

#### 4. wpshadow_cta
Call-to-action blocks (Pro services, Academy, support)

```
[wpshadow_cta id="type" variant="style"]
```

**Parameters:**
- `id` (required): `pro-services|academy|support|vault|diagnostics`
- `variant` (optional): `standard|compact|banner` (default: `standard`)

**Examples:**
```
[wpshadow_cta id="pro-services" variant="standard"]

[wpshadow_cta id="academy" variant="compact"]

[wpshadow_cta id="vault" variant="banner"]
```

**Renders:** Styled CTA block with appropriate messaging

---

## Standard Sections

### TLDR: Quick Answer
- 2-3 paragraphs max
- Bold key actions
- Include 1 visual (image or diagram)
- Answer: "What's the fastest way to solve this?"

### Troubleshooting & Rollback
- List 5-8 common issues
- Each with clear solution
- Include at least 1 error screenshot
- Always mention rollback/undo path

### Advanced Section
- Only for intermediate/advanced articles
- CLI examples, code snippets, hooks
- Clearly labeled "Advanced" so beginners can skip

### Related WPShadow Features
- Specific features that help (not generic product pitch)
- Link to relevant Dashboard areas
- Mention treatments, diagnostics, workflows by name

### Further Reading & Resources
- 3-8 external links to authoritative sources
- Each with brief description (10-15 words)
- **Must include disclaimer paragraph:**

> We've carefully curated these external resources to help you learn more. While we strive to ensure all links lead to reliable, high-quality content, please note these are third-party sites not under our control. If you encounter any broken links or questionable content, [please let us know](https://wpshadow.com/contact) so we can review and update this list.

**Variations (use different wording each time):**
- "These hand-picked resources provide additional depth..."
- "We've tested and verified these links to ensure quality..."
- "The following resources are maintained by trusted WordPress experts..."

### WPShadow Academy Section
- **Must include free emphasis:**

> **This is a FREE course** designed to make you confident and skilled in WordPress management. We created the WPShadow Academy because we believe a stronger WordPress community benefits everyone.

**Template:**
```markdown
**This is a FREE course** designed to make you confident and skilled in WordPress management. We created the WPShadow Academy because we believe a stronger WordPress community benefits everyone. Whether you're managing your first site or your fiftieth, our academy will help you develop best practices, avoid common pitfalls, and become the WordPress expert your team relies on.

**Enroll now (completely free):** [WPShadow Academy: WordPress Site Management Essentials](https://wpshadow.com/academy)

**What you'll learn:**
- [Skill relevant to this article topic]
- [Related best practice or technique]
- [Confidence-building outcome]
```

### Get Expert Help (Final CTA)
- Always final section before rating prompt
- Lead with solution, not problem
- Specific services, not vague promises
- Clear benefit for agencies, businesses, individuals

**Template:**
```markdown
## Get Expert Help

Solved your immediate problem? Excellent! But if you're looking for ongoing peace of mind, **WPShadow Pro Services** can take WordPress management entirely off your plate.

**Our team handles:**
- [Specific service 1 relevant to article]
- [Specific service 2 relevant to article]  
- [Specific service 3 relevant to article]
- Emergency troubleshooting and rollback

**Perfect for:**
- Agencies managing multiple client sites
- Businesses that can't afford downtime
- Anyone who wants WordPress to "just work"

[Learn more about WPShadow Pro Services →](https://wpshadow.com/pro-services)
```

---

## Best Practices

### Visual Content Strategy
- **1 visual per major section** (minimum)
- **TLDR:** Always include diagram or overview image
- **Steps:** Screenshot for complex UI steps
- **Troubleshooting:** Screenshot of error messages
- **Academy:** Preview video of course content

### Accessibility
- All images must have descriptive `alt` text
- Videos must have captions (note in shortcode)
- Code blocks must specify language
- Headers follow logical hierarchy (no skipping levels)

### SEO & Discoverability
- Title: Action-oriented, includes primary keyword
- First paragraph: Answers the question directly
- Headers: Use natural language questions
- Internal links: To related WPShadow features/articles
- External links: To authoritative WordPress sources only

### Maintenance
- Review every 6 months for accuracy
- Update "Last Updated" date with changes
- Check external links quarterly (automated tool)
- Update screenshots when WP UI changes

---

## Checklist for New Articles

Before publishing, verify:

- [ ] Follows structure template exactly
- [ ] TLDR section complete with 1 visual
- [ ] All steps tested in real WordPress install
- [ ] Includes troubleshooting section
- [ ] Related WPShadow features listed (specific, not generic)
- [ ] FAQ section with 5-8 questions
- [ ] Further Reading has disclaimer paragraph (unique wording)
- [ ] WPShadow Academy section emphasizes FREE
- [ ] Get Expert Help (final CTA) included
- [ ] All shortcodes have required parameters
- [ ] All images have alt text
- [ ] No broken internal links
- [ ] External links tested and open in new tab
- [ ] Code blocks specify language
- [ ] Headers follow hierarchy
- [ ] Read time accurate (250 words/minute)
- [ ] Difficulty level appropriate
- [ ] Category assigned correctly

---

**Questions about this guide?** Contact the content team or update this document with clarifications.
