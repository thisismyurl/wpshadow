# 📚 WPShadow Knowledge Base

Welcome to the WPShadow Knowledge Base! This is where we document everything about WordPress optimization, security, accessibility, and more—using our core philosophy as a guide.

---

## 📖 What's Here?

All articles follow our **3-Tier Learning Model:**

| Tier | Audience | Depth | Time |
|------|----------|-------|------|
| **Tier 1: Basics** | Everyone | Quick answer | 2-3 min |
| **Tier 2: Intermediate** | Most users | Step-by-step how-to | 5-8 min |
| **Tier 3: Advanced** | Developers/power users | Technical deep-dive | 5+ min |

Each article also:
- ✅ Maps to **Core Principles** (#01-#11)
- ✅ Links to **related courses** on WPShadow Academy
- ✅ Connects to **WPShadow.com** for live version + community
- ✅ Includes **troubleshooting & FAQs**
- ✅ Provides **code examples** when relevant

---

## 🗂️ Article Categories

### 🔐 [Security](./security/)
Protect your WordPress site from threats, vulnerabilities, and data leaks.

**Quick Links:**
- [File Integrity Monitor](./security/file-integrity-monitor.md)
- [PHP CVE Check](./security/php-cve-check.md)
- [Malware Detection & Removal](./security/malware-signature-scan.md)
- [PCI DSS Compliance](./security/pci-dss-compliance.md)

### 🚀 [Performance](./performance/)
Speed up your site, optimize database, reduce server load.

**Quick Links:**
- [Database Indexing](./performance/database-indexes.md)
- [Lazy Loading Images](./performance/lazy-loading.md)
- [Post Revisions Bloat](./performance/post-revisions-bloat.md)
- [Transients Optimization](./performance/expired-transients-bloat.md)

### ♿ [Accessibility](./accessibility/)
Make your site inclusive and usable for everyone.

**Quick Links:**
- [WCAG Compliance](./accessibility/wcag-language.md)
- [Keyboard Navigation](./accessibility/keyboard-navigation.md)
- [Screen Reader Testing](./accessibility/aria-labels.md)

### 🔍 [SEO](./seo/)
Improve search rankings and organic traffic.

**Quick Links:**
- [XML Sitemaps](./seo/xml-sitemap.md)
- [Meta Descriptions](./seo/meta-description.md)
- [Keyword Density](./seo/keyword-density.md)
- [Structured Data & Schema](./seo/structured-data.md)

### 📊 [Marketing & Revenue](./marketing/)
Increase conversions, analyze traffic, optimize revenue.

**Quick Links:**
- [Mobile Conversion Analysis](./marketing/mobile-revenue-gap.md)
- [Product Recommendations](./marketing/product-recommendation-ctr.md)
- [Refund Rate Analysis](./marketing/product-refund-rate.md)

### 🎨 [Design & UX](./design/)
Create beautiful, user-friendly experiences.

**Quick Links:**
- [Button Clarity & CTAs](./design/button-clarity.md)
- [Mobile Navigation](./design/mobile-navigation.md)
- [404 Page Optimization](./design/404-page-optimization.md)

### 🛠️ [Developer Resources](./developer/)
For developers building plugins, themes, and integrations.

**Quick Links:**
- [Code Standards](./developer/code-standards.md)
- [Plugin Performance](./developer/plugin-autoload-size.md)
- [CSS Specificity Best Practices](./developer/plugin-css-specificity.md)

### 🏢 [Enterprise](./enterprise/)
Solutions for large organizations and compliance requirements.

**Quick Links:**
- [SOX Compliance](./enterprise/sox-compliance.md)
- [HTTPS Everywhere](./enterprise/https-everywhere.md)
- [Data Privacy](./enterprise/data-privacy.md)

### 🌍 [Onboarding & Migration](./onboarding/)
Migrate to WordPress from other platforms.

**Quick Links:**
- [Moodle to WordPress](./onboarding/moodle-to-wordpress.md)
- [Squarespace to WordPress](./onboarding/squarespace-to-wordpress.md)
- [Google Docs to WordPress](./onboarding/google-docs-to-wordpress.md)

### 🌱 [Sustainability](./sustainability/)
Build ethical, sustainable WordPress sites.

**Quick Links:**
- [Technical Debt Management](./sustainability/technical-debt.md)
- [Energy Efficiency](./sustainability/energy-score.md)
- [Vendor Lock-In Prevention](./sustainability/vendor-lock-in.md)

---

## 🎯 Core Principles

Every KB article is aligned with our **11 Core Principles**:

| # | Principle | Focus |
|---|-----------|-------|
| **#01** | 🤝 Helpful Neighbor | Educational, not promotional |
| **#02** | 💰 Free as Possible | Maximize free resources |
| **#03** | 💡 Advice Not Sales | Recommendations over sales pitches |
| **#04** | ♿ Accessibility First | Inclusive for everyone |
| **#05** | 🎓 Learning Inclusive | Education for all skill levels |
| **#06** | 🌍 Culturally Respectful | Respectful of all backgrounds |
| **#07** | ⭐ Ridiculously Good | Exceed expectations |
| **#08** | 💪 Inspire Confidence | Empower users to take action |
| **#09** | 📈 Show Value - KPIs | Demonstrate real impact |
| **#10** | 🔒 Privacy First | Protect user data |
| **#11** | 📢 Talk-Worthy | Worth sharing with others |

---

## 📱 How to Use These Articles

### 1️⃣ **On GitHub** (You Are Here!)
- ✅ Read full, detailed articles
- ✅ Contribute improvements
- ✅ See version history
- ✅ Link from documentation

### 2️⃣ **On WPShadow.com**
- ✅ Read user-friendly versions
- ✅ Participate in community discussion
- ✅ Link to related courses
- ✅ Get link juice for SEO

### 3️⃣ **In the Plugin**
- ✅ KB links in diagnostic results
- ✅ Contextual help tooltips
- ✅ Course recommendations
- ✅ Direct from the dashboard

---

## ✍️ Creating a New Article

### Step 1: Use the Template
Copy `_TEMPLATE.md` to your category folder:

```bash
cp kb-articles/_TEMPLATE.md kb-articles/category/your-article.md
```

### Step 2: Fill in Frontmatter
```yaml
title: "Your Article Title"
description: "One-line description"
category: "security"  # or other category
tags: ["tag1", "tag2"]
principles:
  - "#01-helpful-neighbor"
  - "#04-accessibility-first"
wp_link: "https://wpshadow.com/kb/your-article-slug"
course_link: "https://academy.wpshadow.com/courses/your-course"
```

### Step 3: Write Content
- Follow the 3-tier structure
- Add core principles section
- Include course links
- Link to related articles

### Step 4: Submit for Review
- Create a pull request
- Tag for review
- Once approved → syncs to wpshadow.com

---

## 🔄 Article Status

| Status | Meaning |
|--------|---------|
| 🟢 **published** | Live on both GitHub & WPShadow.com |
| 🟡 **needs-review** | Ready for community review |
| 🔴 **draft** | Work in progress |

Check the frontmatter `status:` field to see where each article stands.

---

## 🔗 Link Strategy

### Internal Links (GitHub)
```markdown
[Related Article](/kb-articles/category/article-slug.md)
```

### External Links (WPShadow.com)
```markdown
[Read on WPShadow →](https://wpshadow.com/kb/article-slug)
[Take the Course →](https://academy.wpshadow.com/courses/course-slug)
```

### GitHub Links
```markdown
[Suggest edits on GitHub →](https://github.com/thisismyurl/wpshadow/edit/main/kb-articles/category/article-slug.md)
```

---

## 📊 Article Index (Auto-Generated)

### Total Articles: 97

| Category | Count | Status |
|----------|-------|--------|
| Security | 16 | 2 published, 14 planned |
| Performance | 13 | 8 published, 5 planned |
| Accessibility | 4 | 1 published, 3 planned |
| SEO | 6 | 1 published, 5 planned |
| Marketing | 5 | 0 published, 5 planned |
| Design | 9 | 0 published, 9 planned |
| Developer | 4 | 0 published, 4 planned |
| Enterprise | 10 | 0 published, 10 planned |
| Onboarding | 6 | 0 published, 6 planned |
| Sustainability | 3 | 0 published, 3 planned |

---

## 🤝 Contributing

We welcome contributions! Here's how:

1. **Find an Article** you want to improve
2. **Make Changes** locally or on GitHub
3. **Submit a Pull Request** with a clear description
4. **Our team reviews** within 48 hours
5. **Merged → Published** to both GitHub and WPShadow.com

See [CONTRIBUTING.md](../CONTRIBUTING.md) for guidelines.

---

## 📚 Related Resources

- **[WPShadow.com KB](https://wpshadow.com/kb/)** - Live KB with community
- **[WPShadow Academy](https://academy.wpshadow.com/)** - Full courses with certificates
- **[GitHub Issues](https://github.com/thisismyurl/wpshadow/issues)** - Request articles or report errors
- **[Plugin Documentation](../docs/)** - For developers

---

## ❓ Questions?

- **Article question?** [See if there's an issue →](https://github.com/thisismyurl/wpshadow/issues)
- **Want to contribute?** [Create an issue first →](https://github.com/thisismyurl/wpshadow/issues/new)
- **Found an error?** [Submit a fix →](https://github.com/thisismyurl/wpshadow/edit/main/kb-articles/)

---

**Last Updated:** January 24, 2026
**Maintainer:** WPShadow Team
**License:** [See LICENSE.md](../LICENSE)
