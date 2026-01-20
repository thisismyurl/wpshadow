# WordPress Settings Audit & Best Practices Guide

## Test Date: January 20, 2026

This document covers the recommended settings for WordPress across all major settings sections to ensure optimal site performance, security, and user experience.

---

## 1. GENERAL SETTINGS

### Recommended Configuration:

| Setting | Recommendation | Why |
|---------|---|---|
| **Site URL** | Use HTTPS protocol | Security, SEO ranking, browser trust indicators |
| **WordPress URL** | Should match Site URL | Prevents redirect loops and access issues |
| **Site Title** | Clear, descriptive (50 chars max) | SEO, browser tabs, search results |
| **Tagline** | Unique value proposition or empty | Differentiator for search engines |
| **Admin Email** | Valid, monitored email | Critical security alerts, password resets |
| **Membership** | Disabled for most sites | Prevents spam registrations unless needed |
| **New User Default Role** | Subscriber (not Editor/Admin) | Security best practice - least privilege |
| **Timezone** | Set to your primary location | Scheduling accuracy, timestamp consistency |
| **Date Format** | F j, Y (e.g., January 20, 2026) | International readability |
| **Time Format** | g:i a (e.g., 3:25 pm) | 12-hour is most common globally |

### Rationale:
- HTTPS is now a ranking factor (Google algorithm)
- Limited registration prevents abuse
- Proper timezone ensures scheduled posts publish at correct times
- Clear email ensures you receive critical notifications

---

## 2. WRITING SETTINGS

### Recommended Configuration:

| Setting | Recommendation | Why |
|---------|---|---|
| **Default Post Type** | Post | Most common content type |
| **Default Category** | Uncategorized | Fallback for uncategorized posts |
| **Default Post Format** | Standard/None | Most flexible option |
| **Post via Email** | Not configured | Usually not needed for most sites |

### Rationale:
- These rarely need changing from defaults
- Email-to-post introduces security risks unless strictly needed

---

## 3. READING SETTINGS ⭐ CRITICAL

### Recommended Configuration:

| Setting | Recommendation | Why |
|---------|---|---|
| **Posts Per Page** | 10 | Good balance of performance and content visibility |
| **Posts Per RSS Feed** | 10 | Same as above for feed consistency |
| **For each article in feed, show** | Full text | Increases engagement (excerpts lose readers) |
| **Search Engine Visibility** | ✅ Checked (public) | Allows indexing for SEO |
| **Front Page Displays** | Static page (if using homepage) | Professional appearance, better CTA placement |
| **Homepage** | Select marketing/landing page | Better for conversions than blog |
| **Posts Page** | Select dedicated blog page | Organized content architecture |

### ⚠️ Common Mistakes:
- ❌ Unchecking "Search Engine Visibility" hides site from Google (usually accidental)
- ❌ Setting posts_per_page too high (>20) slows page load
- ❌ Using blog as homepage instead of static page loses sales opportunities

### Rationale:
- Public sites need indexing for organic traffic
- Static homepage allows custom messaging, CTAs, conversions
- Separate blog page maintains content organization

---

## 4. DISCUSSION SETTINGS

### Recommended Configuration:

| Setting | Recommendation | Why |
|---------|---|---|
| **Default Article Settings** | Comments OFF by default | Reduces moderation burden |
| **Enable threaded comments** | Yes (3 levels deep) | Better conversation flow |
| **Comments Per Page** | 50 (or 20 for high-traffic) | Balance readability with page load |
| **Comment Pagination** | Newest first | Recent feedback more relevant |
| **Email Notifications** | Enable (comment moderation) | Stay informed of comments |
| **Comment Moderation** | ✅ Hold all first comments | Filters spam immediately |
| **Disallowed Comment Keys** | Add common spam patterns | Pre-filter obvious spam |
| **Default Avatar** | Mystery Person or Gravatar | Professional appearance |

### ⚠️ Common Mistakes:
- ❌ Comments ON by default = spam overload
- ❌ Too many comments per page = slower page loads
- ❌ No moderation = spam/toxic comments visible
- ❌ Showing emails = privacy exposure & harvesting

### Rationale:
- First-time comment moderation is ESSENTIAL anti-spam
- Threading improves conversation readability
- Email alerts keep you informed of engagement

---

## 5. MEDIA SETTINGS

### Recommended Configuration:

| Setting | Recommendation | Why |
|---------|---|---|
| **Thumbnail Size** | 150×150px, Crop ✓ | Perfect for featured images |
| **Medium Size** | 300×300px, No Crop | Used in content flow |
| **Large Size** | 1024×1024px, No Crop | Full-width content display |
| **Image Upload Location** | /wp-content/uploads/ (default) | Standard, predictable paths |
| **Organize uploads by date** | ✓ Checked | Better file organization |

### Additional Recommendations:
- Use a plugin like **Smush** or **Imagify** for automatic image optimization
- Compress images BEFORE uploading (reduce by 50-80%)
- Use modern formats (WebP) for better compression
- Set maximum upload size: 25-50MB (wp-config.php)

### Rationale:
- Proper image sizes prevent unnecessary large files
- Cropped thumbnails maintain aspect ratios
- Organized uploads make management easier
- Compression is critical for Core Web Vitals/SEO

---

## 6. PERMALINKS SETTINGS

### Recommended Configuration:

| Setting | Recommendation | Why |
|---------|---|---|
| **Common Structure** | `/%postname%/` (Post name) | SEO-friendly, readable URLs |
| **Category Base** | `/category/` or empty | Keeps hierarchy logical |
| **Tag Base** | `/tag/` or `/topics/` | Descriptive, crawlable |

### ⚠️ URL Structure Comparison:

```
❌ Plain (default):
   http://site.com/?p=123
   - Not SEO friendly
   - Not user-readable

✅ Post name:
   http://site.com/my-blog-post/
   - SEO keywords in URL
   - Readable, memorable
   - Shareable

✅ Date-based (for news):
   http://site.com/2026/01/20/my-blog-post/
   - Good for news/archives
   - Shows content freshness
```

### ⚠️ CRITICAL WARNING:
**DO NOT CHANGE after launch!** Changing URLs:
- Breaks ALL existing links
- Kills SEO rankings
- Requires full 301 redirect setup
- Confuses your audience

### Rationale:
- Keywords in URLs = 5-10% SEO boost
- Readable URLs improve click-through rates
- `/postname/` structure is simplest to implement

---

## 7. PRIVACY SETTINGS

### Recommended Configuration:

| Setting | Recommendation | Why |
|---------|---|---|
| **Privacy Policy Page** | Create & assign (required!) | Legal requirement in most jurisdictions |
| **Content** | Use WP template, customize | Must explain data collection |

### Privacy Policy Must Include:
- ✅ What data you collect (IP, email, etc.)
- ✅ Third-party services (Google Analytics, ads, etc.)
- ✅ Cookie usage and tracking
- ✅ GDPR/CCPA compliance statements
- ✅ How users can request/delete data
- ✅ Contact information for privacy questions

### Tools & Resources:
- WordPress Privacy Policy Template (built-in)
- Termly.io or Iubenda for auto-generation
- Consult legal for compliance requirements

### ⚠️ Legal Compliance:
- **GDPR** (Europe): Privacy policy required, consent for tracking
- **CCPA** (California): Right to know, delete, opt-out
- **Other regions**: Similar requirements emerging

### Rationale:
- Required by law in most countries
- Protects you from legal liability
- Builds user trust
- Explains data practices transparently

---

## TESTING CHECKLIST

### Before Going Live:

- [ ] **General**: Site URL is HTTPS, email is monitored, registrations disabled (if not needed)
- [ ] **Writing**: Defaults are sensible, post types set correctly
- [ ] **Reading**: Site is PUBLIC, homepages configured, posts_per_page = 10-15
- [ ] **Discussion**: First-time moderation ON, spam filtering enabled, comments defaulted OFF
- [ ] **Media**: Images auto-optimize, thumbnail sizes standard, uploads organized by date
- [ ] **Permalinks**: Structure set to `/%postname%/`, category/tag bases configured
- [ ] **Privacy**: Privacy policy created and assigned, GDPR/CCPA compliant

### Performance Checks:

```bash
✓ Core Web Vitals optimized (LCP < 2.5s, FID < 100ms, CLS < 0.1)
✓ Images optimized and compressed
✓ Caching enabled (WP Super Cache or similar)
✓ Database optimized
✓ 404 errors resolved
✓ SSL certificate valid and installed
```

### Security Checks:

```bash
✓ Admin email configured and accessible
✓ No test users left in system
✓ File permissions correct (644 files, 755 directories)
✓ wp-config.php moved outside web root (optional but recommended)
✓ Regular backups scheduled
✓ Security plugin active (Wordfence, iThemes, etc.)
```

---

## QUICK FIXES FOR COMMON ISSUES

### Issue: Site not appearing in Google
**Fix**: Settings → Reading → Check "Search Engine Visibility" ✅

### Issue: Comments are spam overloaded
**Fix**: Settings → Discussion → Enable "Hold comment for moderation"

### Issue: Scheduled posts not publishing
**Fix**: Check Settings → General → Timezone is correct, verify loopback requests work

### Issue: Images too large/slow to load
**Fix**: Settings → Media → Run image optimization plugin, regenerate thumbnails

### Issue: Old URLs broken after changing permalink structure
**Fix**: Install Redirection plugin, set up 301 redirects from old → new URLs

---

## RECOMMENDED WORDPRESS PLUGINS FOR SETTINGS

| Plugin | Purpose | Type |
|--------|---------|------|
| **Wordfence** | Security, firewall, malware scanning | Security |
| **WP Super Cache** | Page caching | Performance |
| **Smush** | Image optimization | Performance |
| **Redirection** | 301 redirects, URL management | SEO/Maintenance |
| **Duplicate Post** | Clone posts/pages | Productivity |
| **Yoast SEO** | On-page SEO, sitemaps | SEO |
| **JetPack** | Backups, downtime monitoring | Backup/Monitoring |

---

## SUMMARY

**Most Critical Settings:**
1. ✅ **Site URL**: Must be HTTPS
2. ✅ **Search Engine Visibility**: Must be PUBLIC (checked)
3. ✅ **Comment Moderation**: Must be ON to prevent spam
4. ✅ **Permalink Structure**: Must be user-friendly (`/%postname%/`)
5. ✅ **Privacy Policy**: Must be created and legal-compliant

**Easy Wins for Better Performance:**
- Set posts_per_page to 10-15
- Enable RSS feeds (full text)
- Use static homepage instead of blog
- Enable image optimization
- Configure timezone correctly

**Annual Maintenance:**
- Review and update privacy policy
- Audit user roles and permissions
- Check backup settings
- Update SSL certificate
- Review security settings

---

Generated: January 20, 2026
