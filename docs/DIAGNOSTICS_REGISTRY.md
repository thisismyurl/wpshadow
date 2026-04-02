# Diagnostics Registry

Quick-reference for all diagnostic tests in `includes/diagnostics/tests/`.
**193 diagnostics** across 12 families.

Status key: ✅ Implemented | 🔧 Stub (check returns null / TODO)

---

## Accessibility (12)

| Slug | Title | Status |
|-|-|-|
| `button-text-specific` | Button Text Specific | ✅ |
| `focus-outline-preserved` | Focus Outline Preserved | ✅ |
| `form-error-messaging-reviewed` | Form Error Messaging | ✅ |
| `heading-structure-reviewable` | Heading Structure Reviewable | ✅ |
| `image-alt-process-reviewed` | Image Alt Text Process | ✅ |
| `lang-attribute-correct` | HTML Lang Attribute Correct | ✅ |
| `motion-reduction-reviewed` | Reduced Motion Support | ✅ |
| `nav-menu-accessible-name` | Navigation Accessible Name | ✅ |
| `search-form-accessible-name` | Search Form Accessible Name | ✅ |
| `skip-link-present` | Skip Link Present | ✅ |
| `underlines-or-link-distinction-reviewed` | Link Distinction | ✅ |
| `viewport-meta-reviewed` | Viewport Meta | ✅ |

---

## Code Quality (7)

| Slug | Title | Status |
|-|-|-|
| `default-comment-removed` | Default WordPress Sample Comment Not Removed | ✅ |
| `default-page-removed` | Default Sample Page Not Removed | ✅ |
| `default-page-slug-updated` | Default Sample Page Slug or Title Not Updated | ✅ |
| `default-post-removed` | Default Hello World Post Not Removed | ✅ |
| `default-post-slug-updated` | Default Hello World Post Slug or Title Not Updated | ✅ |
| `demo-media-removed` | Demo or Placeholder Media Detected in Library | ✅ |
| `sample-content-removed` | Placeholder Text Detected in Published Content | ✅ |

---

## Database (10)

| Slug | Title | Status |
|-|-|-|
| `duplicate-post-meta-keys` | No High-Frequency Orphaned Post Meta Keys | 🔧 |
| `myisam-tables-detected` | No MyISAM Tables in Use | 🔧 |
| `orphaned-user-meta` | No Orphaned User Meta | 🔧 |
| `post-meta-bloat-detected` | wp_postmeta Not Excessively Bloated | 🔧 |
| `stale-sessions-cleared` | Stale User Sessions Not Accumulating | 🔧 |
| `tables-without-primary-key` | All Tables Have a Primary Key | 🔧 |
| `user-meta-bloat-detected` | wp_usermeta Not Excessively Bloated | 🔧 |
| `woocommerce-session-table-size` | WooCommerce Session Table Not Bloated | 🔧 |
| `wp-options-autoload-size` | Autoloaded Options Total Size Under Limit | 🔧 |
| `wp-options-row-count-reasonable` | wp_options Table Not Bloated | 🔧 |

---

## Design (16)

| Slug | Title | Status |
|-|-|-|
| `about-page-published` | About Page Published | ✅ |
| `child-theme-active` | Child Theme In Use | 🔧 |
| `contact-page-has-form` | Contact Page Has a Form | 🔧 |
| `contact-page-published` | Contact Page Published | ✅ |
| `copyright-year-current` | Copyright Year Current | 🔧 |
| `custom-logo-set` | Custom Logo Set | ✅ |
| `default-image-size-reviewed` | Default Image Size | ✅ |
| `draft-pages-accumulation` | Draft Pages Not Accumulating | 🔧 |
| `footer-menu-reviewed` | Footer Menu | ✅ |
| `homepage-displays-intentional` | Homepage Displays Intentional | ✅ |
| `homepage-page-published` | Homepage Page Published | ✅ |
| `mobile-menu-reviewed` | Mobile Menu | ✅ |
| `posts-have-featured-images` | Recent Posts Have Featured Images | 🔧 |
| `posts-page-published` | Posts Page Published | ✅ |
| `primary-navigation-assigned` | Primary Navigation Assigned | ✅ |
| `search-enabled-intentional` | Search Enabled Intentional | ✅ |

---

## Monitoring (8)

| Slug | Title | Status |
|-|-|-|
| `404-monitoring-reviewed` | 404 Monitoring | ✅ |
| `analytics-installed-intentional` | Analytics Installed Intentional | ✅ |
| `application-health-checks-registered` | Application Health Checks Registered | ✅ |
| `backups-automated` | Backups Automated | ✅ |
| `error-logging-reviewed` | Error Logging | ✅ |
| `scheduled-posts-not-stuck` | Scheduled Posts Not Stuck | 🔧 |
| `system-cron-configured-production` | System Cron In Production | ✅ |
| `wp-cron-reliable` | WP Cron Reliable | ✅ |

---

## Performance (41)

| Slug | Title | Status |
|-|-|-|
| `active-plugin-count-reasonable` | Active Plugin Count Reasonable | 🔧 |
| `autoloaded-options-reviewed` | Autoloaded Options | ✅ |
| `autosave-interval-optimized` | Autosave Interval Optimized | ✅ |
| `browser-caching-headers` | Browser Caching Headers | ✅ |
| `caching-plugin-active` | Caching Plugin Active | ✅ |
| `cdn-configured-for-static-assets` | CDN For Static Assets | ✅ |
| `compression-enabled` | Compression Enabled | ✅ |
| `critical-css-strategy-reviewed` | Critical CSS Strategy | ✅ |
| `critical-resources-preloaded` | Critical Resources Preloaded | ✅ |
| `css-minification-reviewed` | CSS Minification | ✅ |
| `database-indexes-missing` | Database Indexes Missing | ✅ |
| `database-optimization-reviewed` | Database Optimization | ✅ |
| `database-version-supported` | Database Version Supported | ✅ |
| `db-charset-collation-correct` | DB Charset and Collation Correct | ✅ |
| `embed-assets-reviewed` | Embed Assets | ✅ |
| `emoji-assets-reviewed` | Emoji Assets | ✅ |
| `expired-transients-cleared` | Expired Transients Cleared | ✅ |
| `extra-image-sizes-trimmed` | Extra Image Sizes Trimmed | ✅ |
| `font-loading-reviewed` | Font Loading | ✅ |
| `heartbeat-usage-reviewed` | Heartbeat Usage | ✅ |
| `http2-or-http3-enabled` | HTTP/2 or HTTP/3 Enabled | ✅ |
| `image-compression-pipeline-active` | Image Compression Pipeline Active | ✅ |
| `image-dimensions-not-set-causing-layout-shift` | Image Dimensions Not Set Causing Layout Shift | ✅ |
| `image-lazy-loading-reviewed` | Image Lazy Loading | ✅ |
| `implements-lazy-loading` | Lazy Loading Implemented | ✅ |
| `innodb-storage-engine-used` | InnoDB Storage Engine Used | ✅ |
| `jpeg-quality-configured` | JPEG Quality | ✅ |
| `js-minification-reviewed` | JavaScript Minification | ✅ |
| `large-image-threshold-reviewed` | Media Scaling Threshold | ✅ |
| `noncritical-js-deferred` | Non-Critical JS Deferred | ✅ |
| `object-cache-reviewed` | Object Cache | ✅ |
| `opcache-enabled` | OPcache Enabled | ✅ |
| `orphaned-comments-reviewed` | Orphaned Comments | ✅ |
| `orphaned-post-meta-reviewed` | Orphaned Post Meta | ✅ |
| `orphaned-term-relationships-reviewed` | Orphaned Term Relationships | ✅ |
| `page-cache-enabled` | Page Cache Enabled | ✅ |
| `php-memory-limit-optimized` | PHP Memory Limit Optimized | ✅ |
| `post-revision-limit-set` | Post Revision Limit Set | ✅ |
| `responsive-images-enabled` | Responsive Images Enabled | ✅ |
| `transients-cleanup-reviewed` | Transient Cleanup | ✅ |
| `webp-support-reviewed` | Modern Image Format Support | ✅ |

---

## Security (45)

| Slug | Title | Status |
|-|-|-|
| `admin-account-count-minimized` | Admin Account Count Minimized | ✅ |
| `admin-session-expiration-hardened` | Admin Session Expiration Hardened | ✅ |
| `auth-keys-and-salts-set` | Auth Keys And Salts Set | ✅ |
| `auto-update-policy-reviewed` | Auto Update Policy | ✅ |
| `backup-files-not-public` | Backup Files Not Public | ✅ |
| `comment-link-limit-set` | Comment Link Limit Set | ✅ |
| `comment-moderation-enabled` | Comment Moderation Enabled | ✅ |
| `core-updated` | WordPress Core Updated | ✅ |
| `database-prefix-intentional` | Database Prefix Intentional | ✅ |
| `db-credentials-not-exposed` | DB Credentials Not Exposed | ✅ |
| `default-admin-username-removed` | Default Admin Username Removed | ✅ |
| `default-role-subscriber` | Default Role is Subscriber | ✅ |
| `directory-listing-disabled` | Directory Listing Disabled | ✅ |
| `file-editor-disabled` | Theme and Plugin Editor Disabled | ✅ |
| `file-mods-policy-defined` | File Modifications Policy Defined | ✅ |
| `file-permissions-reviewed` | File Permissions | ✅ |
| `force-ssl-admin` | Force SSL Admin | ✅ |
| `form-rate-limiting-active` | Form Rate Limiting Active | ✅ |
| `https-enabled` | HTTPS Enabled | ✅ |
| `login-throttling-active` | Login Throttling Active | ✅ |
| `login-url-hardening-reviewed` | Login URL Hardening | ✅ |
| `mixed-content-eliminated` | Mixed Content Eliminated | ✅ |
| `plugin-auto-updates-reviewed` | Plugin Auto Updates | ✅ |
| `plugins-updated` | Plugins Updated | ✅ |
| `privacy-policy-links-visible` | Privacy Links Visible | ✅ |
| `privacy-policy-page-set` | Privacy Policy Page Set | ✅ |
| `query-debug-logging-disabled-production` | Query Debug Logging Disabled In Production | ✅ |
| `rest-api-sensitive-routes-protected` | REST API Sensitive Routes Protected | ✅ |
| `security-headers-present` | Security Headers Present | ✅ |
| `sensitive-files-protected` | Sensitive Files Protected | ✅ |
| `spam-protection-enabled` | Spam Protection Enabled | ✅ |
| `ssl-certificate-valid` | SSL Certificate Valid | 🔧 |
| `themes-updated` | Themes Updated | ✅ |
| `two-factor-admin-enabled` | Two-Factor for Admin Enabled | ✅ |
| `unused-plugins-removed` | Unused Plugins Removed | ✅ |
| `unused-themes-removed` | Unused Themes Removed | ✅ |
| `uploads-php-execution-blocked` | Uploads PHP Execution Blocked | ✅ |
| `user-enumeration-reduced` | User Enumeration Reduced | ✅ |
| `wp-config-location-reviewed` | wp-config Location | ✅ |
| `wp-config-permissions-hardened` | wp-config Permissions Hardened | ✅ |
| `wp-content-write-scope-minimized` | wp-content Write Scope Minimized | ✅ |
| `wp-debug-display-off` | WP Debug Display Off | ✅ |
| `wp-debug-log-private` | WP Debug Log Private | ✅ |
| `xmlrpc-policy-intentional` | XML-RPC Policy Intentional | ✅ |

---

## SEO (28)

| Slug | Title | Status |
|-|-|-|
| `author-archives-intentional` | Author Archives Intentional | ✅ |
| `canonical-urls-reviewed` | Canonical URLs | ✅ |
| `category-strategy-reviewed` | Category Strategy | ✅ |
| `custom-404-strategy-present` | Custom 404 Strategy Present | ✅ |
| `document-title-format-reviewed` | Document Title Format | ✅ |
| `homepage-has-one-h1` | Homepage Has a Single H1 | 🔧 |
| `homepage-meta-reviewed` | Homepage Meta | ✅ |
| `image-link-default-reviewed` | Image Link Default | ✅ |
| `media-attachment-pages-reviewed` | Attachment Pages | ✅ |
| `meta-descriptions-managed` | Meta Descriptions Managed | ✅ |
| `meta-titles-managed` | Meta Titles Managed | ✅ |
| `noindex-policy-reviewed` | Noindex Policy | ✅ |
| `open-graph-defaults-set` | Open Graph Defaults Set | ✅ |
| `organization-schema-reviewed` | Organization Schema | ✅ |
| `permalink-structure-meaningful` | Permalink Structure Meaningful | ✅ |
| `redirect-management-reviewed` | Redirect Management | ✅ |
| `robots-policy-configured` | Robots Policy | ✅ |
| `rss-feed-summary-reviewed` | RSS Feed Summary | ✅ |
| `schema-basics-reviewed` | Schema Basics | ✅ |
| `search-engine-visibility-intentional` | Search Engine Visibility Intentional | ✅ |
| `search-page-indexing-reviewed` | Search Page Indexing | ✅ |
| `seo-plugin-config-intentional` | SEO Plugin Configuration Intentional | ✅ |
| `site-icon-configured` | Site Icon | ✅ |
| `social-profile-links-reviewed` | Social Profile Links | ✅ |
| `tag-archives-intentional` | Tag Archives Intentional | ✅ |
| `twitter-card-reviewed` | Twitter Card | ✅ |
| `uncategorized-usage-reviewed` | Uncategorized Usage | ✅ |
| `xml-sitemap-enabled` | XML Sitemap Enabled | ✅ |

---

## Settings (18)

| Slug | Title | Status |
|-|-|-|
| `admin-email-deliverable` | Admin Email Deliverable | ✅ |
| `auto-update-policy-configured` | Auto-Update Policy | ✅ |
| `comment-policy-intentional` | Comment Policy Intentional | ✅ |
| `comment-spam-backlog` | Comment Spam Backlog Managed | 🔧 |
| `cookie-consent-plugin-active` | Cookie Consent Plugin Active | 🔧 |
| `default-user-role-reviewed` | Default User Role | ✅ |
| `discussion-defaults-reviewed` | Discussion Defaults | ✅ |
| `front-page-configured` | Front Page | ✅ |
| `legal-pages-linked-footer` | Legal Pages Linked in Footer | ✅ |
| `mail-sender-configured` | Mail Sender | ✅ |
| `maintenance-mode-off` | Maintenance Mode Off | ✅ |
| `media-sizes-reviewed` | Media Sizes | ✅ |
| `pingbacks-trackbacks-configured` | Pingbacks and Trackbacks | ✅ |
| `registration-setting-intentional` | Registration Setting Intentional | ✅ |
| `site-title-tagline-intentional` | Site Title And Tagline Intentional | ✅ |
| `site-urls-configured-correctly` | Site URLs Correctly | ✅ |
| `smtp-configured` | SMTP | ✅ |
| `timezone-configured` | Timezone | ✅ |

---

## WordPress Health (2)

| Slug | Title | Status |
|-|-|-|
| `php-version-reviewed` | PHP Version | ✅ |
| `site-health-criticals-addressed` | Site Health Criticals Addressed | ✅ |

---

## Workflows (5)

| Slug | Title | Status |
|-|-|-|
| `cron-health-reviewed` | Cron Health | ✅ |
| `cron-overlap-protection-enabled` | Cron Overlap Protection Enabled | ✅ |
| `cron-traffic-dependence-reviewed` | WP Cron Traffic Dependence | ✅ |
| `external-cron-reviewed` | External Cron Configured | ✅ |
| `system-cron-offload-configured` | System Cron Offload | ✅ |

---

## Proposed / Pending Approval

_No diagnostics currently pending._
