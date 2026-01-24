#!/usr/bin/env python3
"""
Generate KB article stubs from the inventory.
Creates one .md file per KB article with proper frontmatter and folder structure.
"""

import os
import re
from datetime import datetime
from pathlib import Path

# KB Article data extracted from inventory
KB_ARTICLES = {
    "security": [
        {"title": "Ada Lawsuit Scan", "url": "ada-lawsuit-scan", "course": "security-fundamentals"},
        {"title": "File Integrity Monitor", "url": "file-integrity-monitor", "course": "security-fundamentals"},
        {"title": "PHP CVE Check", "url": "php-cve-check", "course": "security-fundamentals"},
        {"title": "Malware Signature Scan", "url": "malware-signature-scan", "course": "security-fundamentals"},
        {"title": "Login URL Exposed", "url": "login-url-exposed", "course": "security-fundamentals"},
        {"title": "PCI Data Leak", "url": "pci-data-leak", "course": "enterprise-compliance"},
        {"title": "GDPR Cookie Audit", "url": "gdpr-cookie-audit", "course": "enterprise-compliance"},
        {"title": "Unlicensed Images", "url": "unlicensed-images", "course": "security-fundamentals"},
        {"title": "Remove Timthumb Vulnerability", "url": "remove-timthumb-vulnerability", "course": "security-fundamentals"},
        {"title": "XML-RPC Security", "url": "xml-rpc-security", "course": "security-fundamentals"},
        {"title": "PCI DSS Compliance", "url": "pci-dss-compliance", "course": "enterprise-compliance"},
        {"title": "SOX Compliance", "url": "sox-compliance", "course": "enterprise-compliance"},
        {"title": "HTTPS Everywhere", "url": "https-everywhere", "course": "enterprise-compliance"},
        {"title": "Disable WordPress File Editor", "url": "disable-wordpress-file-editor", "course": "security-fundamentals"},
        {"title": "Disable Gravatars", "url": "disable-gravatars", "course": "security-fundamentals"},
        {"title": "Prevent Asset Leakage", "url": "prevent-asset-leakage", "course": "security-fundamentals"},
    ],
    "performance": [
        {"title": "Duplicate Postmeta Keys", "url": "duplicate-postmeta-keys", "course": "database-mastery"},
        {"title": "Dashboard Widget Bloat", "url": "dashboard-widget-bloat", "course": "performance-optimization"},
        {"title": "Missing Database Indexes", "url": "missing-database-indexes", "course": "database-mastery"},
        {"title": "Large Menu Overhead", "url": "large-menu-overhead", "course": "performance-optimization"},
        {"title": "Orphaned Metadata", "url": "orphaned-metadata", "course": "database-mastery"},
        {"title": "Heartbeat API Overhead", "url": "heartbeat-api-overhead", "course": "performance-optimization"},
        {"title": "Database Table Overhead", "url": "database-table-overhead", "course": "database-mastery"},
        {"title": "Large Serialized Options", "url": "large-serialized-options", "course": "database-mastery"},
        {"title": "Post Revisions Bloat", "url": "post-revisions-bloat", "course": "database-mastery"},
        {"title": "Expired Transients Bloat", "url": "expired-transients-bloat", "course": "database-mastery"},
        {"title": "Lazy Loading", "url": "lazy-loading", "course": "performance-optimization"},
        {"title": "Image Lazy Loading", "url": "image-lazy-loading", "course": "performance-optimization"},
        {"title": "Increase Memory Limit", "url": "increase-memory-limit", "course": "performance-optimization"},
    ],
    "seo": [
        {"title": "XML Sitemap", "url": "xml-sitemap", "course": "seo-essentials"},
        {"title": "Keyword Density", "url": "keyword-density", "course": "seo-essentials"},
        {"title": "Meta Description", "url": "meta-description", "course": "seo-essentials"},
        {"title": "Breadcrumb Schema", "url": "breadcrumb-schema", "course": "seo-mastery"},
        {"title": "Structured Data", "url": "structured-data", "course": "seo-mastery"},
        {"title": "Referrer Quality", "url": "referrer-quality", "course": "seo-mastery"},
    ],
    "accessibility": [
        {"title": "Theme Accessibility", "url": "theme-accessibility", "course": "accessibility-intro"},
        {"title": "Theme Deprecated Hooks", "url": "theme-deprecated-hooks", "course": "accessibility-intro"},
        {"title": "WCAG Consistent Navigation", "url": "wcag-consistent-nav", "course": "accessibility-intro"},
        {"title": "WCAG Language", "url": "wcag-language", "course": "accessibility-intro"},
    ],
    "marketing": [
        {"title": "Search Zero Results", "url": "search-zero-results", "course": None},
        {"title": "Mobile Revenue Gap", "url": "mobile-revenue-gap", "course": "ux-conversion"},
        {"title": "Product Refund Rate", "url": "product-refund-rate", "course": "ux-conversion"},
        {"title": "Product Recommendation CTR", "url": "product-recommendation-ctr", "course": "ux-conversion"},
        {"title": "Speed Conversion Analysis", "url": "speed-conversion-analysis", "course": "ux-conversion"},
    ],
    "design": [
        {"title": "Button Best Practices", "url": "button-best-practices", "course": "ux-conversion"},
        {"title": "Mobile Navigation", "url": "mobile-navigation", "course": "ux-conversion"},
        {"title": "404 Page Optimization", "url": "404-page-optimization", "course": "ux-conversion"},
        {"title": "Keyboard Navigation", "url": "keyboard-navigation", "course": "accessibility-intro"},
        {"title": "Design Notification Toast Design", "url": "design-notification-toast-design", "course": None},
        {"title": "Design No Time Dependent Interactions", "url": "design-no-time-dependent-interactions", "course": None},
        {"title": "Design Custom Post Type Design", "url": "design-custom-post-type-design", "course": None},
        {"title": "Design Plugin Output Styling", "url": "design-plugin-output-styling", "course": None},
        {"title": "Design Multisite Customizer Sync", "url": "design-multisite-customizer-sync", "course": None},
    ],
    "developer": [
        {"title": "Code Standards No Param Types", "url": "code-standards-no-param-types", "course": "developer-workflows"},
        {"title": "Plugin Autoload Size", "url": "plugin-autoload-size", "course": "developer-workflows"},
        {"title": "Plugin CSS Specificity", "url": "plugin-css-specificity", "course": "developer-workflows"},
        {"title": "DX Database Sync", "url": "dx-database-sync", "course": "developer-workflows"},
    ],
    "enterprise": [
        {"title": "Understanding ROI Calculations", "url": "understanding-roi-calculations", "course": None},
        {"title": "Fatal Errors", "url": "fatal-errors", "course": "security-fundamentals"},
        {"title": "Tag Manager", "url": "tag-manager", "course": "enterprise-compliance"},
        {"title": "WordPress Graduation", "url": "wordpress-graduation", "course": None},
        {"title": "Create Terms of Service", "url": "create-terms-of-service", "course": "enterprise-compliance"},
    ],
    "sustainability": [
        {"title": "Sustainability Vendor Lock-In", "url": "sustainability-vendor-lock-in", "course": "sustainability"},
        {"title": "Sustainability Technical Debt", "url": "sustainability-technical-debt", "course": "sustainability"},
        {"title": "Environment Energy Score Calculated", "url": "env-energy-score-calculated", "course": "sustainability"},
    ],
    "onboarding": [
        {"title": "Moodle to WordPress", "url": "moodle-to-wordpress", "course": "wordpress-migration"},
        {"title": "Word to WordPress", "url": "word-to-wordpress", "course": "wordpress-migration"},
        {"title": "Squarespace to WordPress", "url": "squarespace-to-wordpress", "course": "wordpress-migration"},
        {"title": "Google Docs to WordPress", "url": "google-docs-to-wordpress", "course": "wordpress-migration"},
        {"title": "Wix to WordPress", "url": "wix-to-wordpress", "course": "wordpress-migration"},
        {"title": "Notion to WordPress", "url": "notion-to-wordpress", "course": "wordpress-migration"},
    ],
}

# Core principles mapping by category
PRINCIPLES_BY_CATEGORY = {
    "security": ["#01-helpful-neighbor", "#08-inspire-confidence", "#04-accessibility"],
    "performance": ["#07-ridiculously-good", "#08-inspire-confidence", "#09-show-value-kpis"],
    "seo": ["#07-ridiculously-good", "#09-show-value-kpis", "#03-community-first"],
    "accessibility": ["#04-accessibility", "#01-helpful-neighbor", "#05-learning-inclusive"],
    "marketing": ["#09-show-value-kpis", "#06-revenue-enables-mission", "#02-revenue-sustainability"],
    "design": ["#07-ridiculously-good", "#04-accessibility", "#01-helpful-neighbor"],
    "developer": ["#05-learning-inclusive", "#10-open-source", "#08-inspire-confidence"],
    "enterprise": ["#08-inspire-confidence", "#02-revenue-sustainability", "#06-revenue-enables-mission"],
    "sustainability": ["#02-revenue-sustainability", "#11-sustainability-first", "#10-open-source"],
    "onboarding": ["#01-helpful-neighbor", "#05-learning-inclusive", "#03-community-first"],
}


def generate_article_content(title, slug, category, course_name):
    """Generate article frontmatter and basic structure."""
    
    course_link = f"https://academy.wpshadow.com/courses/{course_name}" if course_name else None
    principles = PRINCIPLES_BY_CATEGORY.get(category, ["#01-helpful-neighbor"])
    principles_str = "\n  - ".join(f'"{p}"' for p in principles)
    
    content = f'''---
title: "{title}"
description: "[Brief description of what this article covers and why it matters]"
category: "{category}"
tags: ["wordpress", "{category}", "{slug}"]
difficulty: "intermediate"
read_time: "10"
status: "draft"
last_updated: "{datetime.now().strftime('%Y-%m-%d')}"
principles:
  - {principles_str}
related_articles:
  - "[related-article-1]"
  - "[related-article-2]"
wp_link: "https://wpshadow.com/kb/{slug}"
course_link: "{course_link if course_link else ''}"
course_name: "{course_name if course_name else ''}"
---

# {title}

> **Read on WPShadow:** For the latest version and community discussion, [visit this article on WPShadow.com →](https://wpshadow.com/kb/{slug})

---

## ✓ Quality Checklist Before Publishing

- [ ] **No duplicate code blocks** - Each code example appears once; remove any accidental copy-paste
- [ ] **Generic tool references** - Avoid competitor names (WP Rocket, Perfmatrix, etc.); use "caching plugin" or "optimization tool"
- [ ] **Citations for claims** - Any statistic ($X revenue impact, Y% abandonment) has a source or caveat
- [ ] **Precise metrics** - Distinguish between bounce rate, conversion rate, traffic drop, etc.
- [ ] **Backup warnings** - Any database/file changes include "⚠️ Create a backup" section at start of Tier 2
- [ ] **Tone check** - Read aloud; sounds like a helpful expert, not an AI bot
- [ ] **Principles mapped** - Article includes 3-5 core principle mappings with explanations
- [ ] **WPShadow focus** - Tier 1 is exclusively WPShadow; Tier 2 offers alternatives in order

---

## 📝 Summary (TLDR)

[Clear, concise overview in 1-2 sentences. What's the key takeaway? Why should they read this?]

---

## What This Means

[Clear, jargon-free explanation of the topic in 1-2 paragraphs. Explain it like you're talking to a smart friend, not a developer.]

---

## Why This Matters

[Real-world impact: speed, security, SEO, UX, revenue, etc. Use numbers and concrete examples.]

---

## Tier 1: Beginner Summary (Using WPShadow)

WPShadow makes this dead simple. In just 5 minutes, you can [describe benefit]. Here's how:

### Install WPShadow (Free)

If you don't have WPShadow installed:

1. **Login to WordPress admin** → Plugins → Add New
2. **Search for "WPShadow"** (by thisismyurl)
3. **Click Install** → Activate
4. **Go to WPShadow** → Dashboard

Already have WPShadow? Skip to the next section.

### Apply the Treatment

1. **Open WPShadow Dashboard**
2. **Go to Diagnostics & Treatments**
3. **Find "{title}"** → Shows the issue
4. **Click "Apply Treatment"** → Done in seconds

That's it! [Result/confirmation text].

---

## Tier 2: Intermediate (How-To Guide)

### ⚠️ Before You Start

**Create a backup.** [Optional—add if this involves database/file changes]

### What You'll Need
- WordPress admin access
- 5-10 minutes
- A recent backup (recommended)

### Recommended Approaches

**Approach 1: WPShadow (Free/Included)**  
[Steps for using WPShadow to solve this]

**Approach 2: WPShadow Pro**  
[Advanced features available in Pro version, if applicable]

**Approach 3: Manual via WP-CLI**  
[For developers who prefer command-line]

---

## Tier 3: Advanced (Technical Deep Dive)

[Technical explanation, code patterns, database concepts, for advanced users]

---

## Tier 4: Developer

[Benchmarking, optimization patterns, advanced customization, API integration]

---

## Learn More

- Related articles: [Links to related KB articles]
- External resources: [WordPress.org, developer docs, etc.]

---

## Master {category.title()}

**Interested in deepening your expertise?** {f"Explore our [**{course_name} course** →]({course_link})" if course_name else ""}

---

## Common Questions

**Q: [Common question about this topic?]**  
A: [Direct, helpful answer]

---

## Contribute

Found an issue with this article? [**Edit on GitHub** →](https://github.com/thisismyurl/wpshadow/blob/main/kb-articles/{category}/{slug}.md)

---

## Related Features

- [WPShadow Feature 1]
- [WPShadow Feature 2]
- [WPShadow Feature 3]

---

## Core Principles

This article aligns with WPShadow's core values:

- **[Principle 1]:** [How this article embodies it]
- **[Principle 2]:** [How this article embodies it]
- **[Principle 3]:** [How this article embodies it]

---

## Article Metadata

| Property | Value |
|----------|-------|
| Status | Draft - Needs Content |
| Category | {category.title()} |
| Difficulty | Intermediate |
| Read Time | ~10 minutes |
| Last Updated | {datetime.now().strftime('%B %d, %Y')} |
| Author | WPShadow Team |

'''
    
    return content


def main():
    base_path = Path("/workspaces/wpshadow/kb-articles")
    total = 0
    created = 0
    
    print("🚀 Generating KB Article Stubs")
    print("=" * 60)
    
    for category, articles in KB_ARTICLES.items():
        category_path = base_path / category
        category_path.mkdir(parents=True, exist_ok=True)
        
        print(f"\n📁 {category.upper()} ({len(articles)} articles)")
        
        for article in articles:
            slug = article["url"]
            title = article["title"]
            course = article.get("course")
            
            file_path = category_path / f"{slug}.md"
            
            # Skip if already exists (don't overwrite database-indexes.md)
            if file_path.exists() and slug != "missing-database-indexes":
                print(f"   ⏭️  {title} (already exists)")
                continue
            
            content = generate_article_content(title, slug, category, course)
            
            file_path.write_text(content, encoding='utf-8')
            print(f"   ✅ {title}")
            created += 1
            total += 1
    
    print("\n" + "=" * 60)
    print(f"✨ Generated {created} KB article stubs!")
    print(f"📊 Total articles in structure: {total}")
    print(f"📂 Location: /kb-articles/")
    print("\n✓ Next steps:")
    print("  1. Review each article's 'Draft - Needs Content' sections")
    print("  2. Fill in TLDR, principles, and content tiers")
    print("  3. Use quality checklist before publishing")
    print("  4. Run: git add kb-articles/ && git commit -m 'Scaffold KB articles'")


if __name__ == "__main__":
    main()
