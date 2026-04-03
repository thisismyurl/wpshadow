# Diagnostics Registry

Quick-reference for all diagnostic tests in `includes/diagnostics/tests/`.
**192 diagnostics** across 12 families.

Status key: â Implemented | ð§ Stub (check returns null / TODO)

---

## Accessibility (12)

| Slug | Title | Description | Status |
|-|-|-|-|
| `button-text-specific` | Button Text Specific | Checks buttons and links for vague text like "Click Here" or "Read More" that gives screen-reader users no context about where the control leads. | â |
| `focus-outline-preserved` | Focus Outline Preserved | Checks the active theme hasn't removed the keyboard focus ring that lets people navigating by keyboard see which element is currently selected. | â |
| `form-error-messaging-reviewed` | Form Error Messaging | Checks whether active form plugins display error messages next to the fields they apply to, so all users know exactly what went wrong and where. | â |
| `heading-structure-reviewable` | Heading Structure Reviewable | Scans published content for heading problems â like a duplicate H1 inside the post body, or skipped heading levels â that disrupt the document outline used by screen readers. | â |
| `image-alt-process-reviewed` | Image Alt Text Process | Checks whether a process is in place for adding meaningful alternative text to images so visitors using screen readers understand every visual on the page. | â |
| `lang-attribute-correct` | HTML Lang Attribute Correct | Checks the HTML lang attribute is set to the correct language so screen readers choose the right voice engine and dictionary for your content. | â |
| `motion-reduction-reviewed` | Reduced Motion Support | Checks whether the active theme respects the "reduce motion" preference that visitors with vestibular conditions or motion sensitivity can set on their device. | â |
| `nav-menu-accessible-name` | Navigation Accessible Name | Checks that navigation menus have descriptive labels so screen-reader users can tell them apart when a page has more than one navigation area. | â |
| `search-form-accessible-name` | Search Form Accessible Name | Checks the search form has a visible or programmatic label so all users â including those relying on screen readers â understand what it does. | â |
| `skip-link-present` | Skip Link Present | Checks a "skip to main content" link exists at the top of the page so keyboard users can jump past repeated headers and navigation without pressing Tab many times. | â |
| `underlines-or-link-distinction-reviewed` | Link Distinction | Checks that links are visually distinct from surrounding text in a way that doesn't rely on colour alone, so users who cannot differentiate colours can still spot them. | â |
| `viewport-meta-reviewed` | Viewport Meta | Checks the viewport meta tag doesn't disable pinch-to-zoom so visitors who need to enlarge text to read comfortably are free to do so. | â |

---

## Code Quality (7)

| Slug | Title | Description | Status |
|-|-|-|-|
| `default-comment-removed` | Default WordPress Sample Comment Not Removed | Checks the WordPress sample comment that ships with every new installation has been deleted so visitors don't see leftover placeholder content. | â |
| `default-page-removed` | Default Sample Page Not Removed | Checks the "Sample Page" that WordPress installs on every new site has been removed or replaced with real content, so visitors never land on a template placeholder. | â |
| `default-page-slug-updated` | Default Sample Page Slug or Title Not Updated | Checks the Sample Page slug and title have been changed from their defaults so the page doesn't retain generic wording that signals an unfinished site. | â |
| `default-post-removed` | Default Hello World Post Not Removed | Checks the "Hello world!" post that comes with every new WordPress installation has been removed or replaced so visitors don't encounter filler content. | â |
| `default-post-slug-updated` | Default Hello World Post Slug or Title Not Updated | Checks the Hello World post slug and title have been updated from their defaults so no generic placeholder content remains. | â |
| `demo-media-removed` | Demo or Placeholder Media Detected in Library | Checks the media library for placeholder or demo images that were never replaced with real content, which can look unprofessional and confuse visitors. | â |
| `sample-content-removed` | Placeholder Text Detected in Published Content | Checks published pages for placeholder text like "Lorem ipsum" that was put in during site building and never replaced with real copy. | â |

---

## Database (10)

| Slug | Title | Description | Status |
|-|-|-|-|
| `duplicate-post-meta-keys` | No High-Frequency Orphaned Post Meta Keys | Checks for post extra-data rows written by plugins that are no longer active. These orphaned rows waste database space and can slow down queries that look up post information. | ð§ |
| `myisam-tables-detected` | No MyISAM Tables in Use | Checks all database tables use the modern InnoDB format, which supports crash recovery and faster queries â the older MyISAM format does not. | ð§ |
| `orphaned-user-meta` | No Orphaned User Meta | Checks that all user profile data rows belong to an account that still exists. Rows left behind after a user is deleted waste space and can cause unexpected query results. | ð§ |
| `post-meta-bloat-detected` | wp_postmeta Not Excessively Bloated | Checks the ratio of post extra-data rows to published posts is reasonable. A very high ratio usually means old plugin data is sitting in the database unused. | ð§ |
| `stale-sessions-cleared` | Stale User Sessions Not Accumulating | Checks that expired login session records are being cleaned up regularly. Accumulated stale sessions waste database space and can slow down session lookups. | ð§ |
| `tables-without-primary-key` | All Tables Have a Primary Key | Checks every database table has a primary key. Tables without one can degrade performance and cause problems with database replication. | ð§ |
| `user-meta-bloat-detected` | wp_usermeta Not Excessively Bloated | Checks the ratio of user profile data rows to registered user accounts is reasonable. A very high ratio suggests old plugin data was left behind when plugins were removed. | ð§ |
| `woocommerce-session-table-size` | WooCommerce Session Table Not Bloated | Checks the WooCommerce session table (when present) hasn't accumulated too many rows, which is a sign that its scheduled cleanup routines aren't running correctly. | ð§ |
| `wp-options-autoload-size` | Autoloaded Options Total Size Under Limit | Checks that options loaded on every single page request stay under a safe total size. A large autoload payload slows every page on your site. | ð§ |
| `wp-options-row-count-reasonable` | wp_options Table Not Bloated | Checks the WordPress options table hasn't grown to an unusual size, which is a common symptom of plugin data accumulating over time. | ð§ |

---

## Design (16)

| Slug | Title | Description | Status |
|-|-|-|-|
| `about-page-published` | About Page Published | Checks an About page is published and live so visitors can learn who is behind the site â a key part of building the trust that turns browsers into customers. | â |
| `child-theme-active` | Child Theme In Use | Checks whether a child theme is in use so any visual customisations you've made survive future theme updates without being overwritten. | ð§ |
| `contact-page-has-form` | Contact Page Has a Form | Checks the contact page includes an actual form so visitors can reach you directly without needing to know your email address. | ð§ |
| `contact-page-published` | Contact Page Published | Checks a Contact page is published so visitors and potential customers always have a clear way to reach you. | â |
| `copyright-year-current` | Copyright Year Current | Checks the copyright year shown in your site footer matches the current year so the site looks actively maintained rather than neglected. | ð§ |
| `custom-logo-set` | Custom Logo Set | Checks a custom logo image has been uploaded so your site header shows your brand rather than a plain text fallback. | â |
| `default-image-size-reviewed` | Default Image Size | Checks the thumbnail, medium, and large image size settings have been reviewed so WordPress generates image sizes that match your theme's actual layout. | â |
| `draft-pages-accumulation` | Draft Pages Not Accumulating | Checks for pages that have been sitting in draft status for more than 90 days, which often signals site work that was started but never completed. | ð§ |
| `footer-menu-reviewed` | Footer Menu | Checks a footer navigation menu has been assigned so visitors can find important links like Contact, Privacy Policy, and About from every page. | â |
| `homepage-displays-intentional` | Homepage Displays | Checks the homepage display setting matches what you actually intended â whether that is a static page or your latest posts â so visitors always see the right content. | â |
| `homepage-page-published` | Homepage Page Published | Checks a published page is assigned as the static homepage so visitors see real content rather than a blank or unexpected default page. | â |
| `mobile-menu-reviewed` | Mobile Menu | Checks the active theme includes a responsive mobile menu so visitors on phones and small screens can navigate comfortably without fighting a full desktop layout. | â |
| `posts-have-featured-images` | Recent Posts Have Featured Images | Checks recent posts have a featured image set so blog listings, social media previews, and RSS readers show an image alongside your content. | ð§ |
| `posts-page-published` | Posts Page Published | Checks a page is published and assigned to display your blog so the posts archive has an accessible home and isn't just floating at a raw URL. | â |
| `primary-navigation-assigned` | Primary Navigation Assigned | Checks a menu has been assigned to your theme's primary navigation location so visitors can find their way around the site. | â |
| `search-enabled-intentional` | Search Enabled | Checks whether site search is turned on or off as a deliberate decision, since many small sites don't benefit from it and it can create thin-content search result pages. | â |

---

## Monitoring (8)

| Slug | Title | Description | Status |
|-|-|-|-|
| `404-monitoring-reviewed` | 404 Monitoring | Checks whether a tool is active to track broken URLs on your site so you can catch and fix links that are silently losing visitors and search engine ranking. | â |
| `analytics-installed-intentional` | Analytics Installed | Checks whether an analytics tool is active so you have visibility into who visits your site, where they come from, and what they do. | â |
| `application-health-checks-registered` | Application Health Checks Registered | Checks whether custom Site Health tests have been registered so your plugins and themes can report their own status alongside WordPress's built-in checks. | â |
| `backups-automated` | Backups Automated | Checks an automated backup plugin is active so your site can be fully restored if it is ever compromised, hit by a bad update, or suffers data loss. | â |
| `error-logging-reviewed` | Error Logging | Checks error logging is configured safely so PHP errors stay out of sight of visitors and are quietly captured somewhere you can review them. | â |
| `scheduled-posts-not-stuck` | Scheduled Posts Not Stuck | Checks for posts that were set to publish automatically but never went live â a reliable sign that the WordPress background task scheduler is not working. | ð§ |
| `system-cron-configured-production` | System Cron In Production | Checks whether a real server-level task scheduler is handling timed jobs rather than relying on visitor page loads, which is much more reliable on production sites. | â |
| `wp-cron-reliable` | WP Cron Reliable | Checks the WordPress background task system (WP-Cron) is configured to fire reliably so scheduled posts, email notifications, and plugin tasks run on time. | â |

---

## Performance (42)

| Slug | Title | Description | Status |
|-|-|-|-|
| `active-plugin-count-reasonable` | Active Plugin Count Reasonable | Checks the number of active plugins is not excessive. Too many plugins slow every page load, increase maintenance burden, and widen the security attack surface. | ð§ |
| `autoloaded-options-reviewed` | Autoloaded Options | Checks the total amount of data WordPress loads into memory on every single page request is within a sensible limit. | â |
| `autosave-interval-optimized` | Autosave Interval Optimized | Checks the post editor autosave interval has been reviewed so it doesn't fire too frequently and add unnecessary server load while you're writing. | â |
| `browser-caching-headers` | Browser Caching Headers | Checks your server sends caching instructions to browsers so repeat visitors load previously-seen images and files from their local device instead of downloading them again. | â |
| `caching-plugin-active` | Caching Plugin Active | Checks a page caching plugin is active â the single most impactful speed improvement for WordPress, typically cutting server response times by 50â90%. | â |
| `cdn-configured-for-static-assets` | CDN For Static Assets | Checks whether images, CSS, and JavaScript files are served from a content delivery network so visitors load them from a server geographically close to them. | â |
| `compression-enabled` | Compression Enabled | Checks your server compresses files before sending them to browsers, which typically reduces page weight by 60â80% and makes pages load noticeably faster. | â |
| `critical-css-strategy-reviewed` | Critical CSS Strategy | Checks whether the CSS needed to display the visible part of a page (above the fold) is separated so the page can paint quickly without waiting for all stylesheets. | â |
| `critical-resources-preloaded` | Critical Resources Preloaded | Checks whether important assets like fonts, hero images, and key scripts are told to load early so the browser isn't left waiting for them mid-render. | â |
| `css-minification-reviewed` | CSS Minification | Checks whether CSS stylesheets have whitespace and comments stripped out so they transfer faster to the browser. | â |
| `database-indexes-missing` | Database Indexes Missing | Checks for slow database queries caused by columns that are searched frequently but don't have an index to speed lookups up. | â |
| `database-optimization-reviewed` | Database Optimization | Checks whether routine database maintenance â like clearing table overhead and optimising fragmented tables â has been reviewed. | â |
| `database-version-supported` | Database Version Supported | Checks the MySQL or MariaDB version running on your server is current enough to support the performance and query features that WordPress relies on. | â |
| `db-charset-collation-correct` | DB Charset and Collation Correct | Checks the database character set is configured for Unicode so emoji, accented characters, and multilingual content all save and display correctly. | â |
| `embed-assets-reviewed` | Embed Assets | Checks whether oEmbed scripts that let other sites embed your content are loading on every page even if you don't use this feature, adding unnecessary page weight. | â |
| `emoji-assets-reviewed` | Emoji Assets | Checks whether the WordPress emoji detection scripts are still loading when modern browsers handle emoji natively, adding unnecessary HTTP requests to every page. | â |
| `expired-transients-cleared` | Expired Transients Cleared | Checks for expired temporary data (transients) that wasn't automatically cleaned up and is unnecessarily occupying database space. | â |
| `extra-image-sizes-trimmed` | Extra Image Sizes Trimmed | Checks whether unnecessary image size variants are being generated on every upload, wasting server storage and upload processing time. | â |
| `font-loading-reviewed` | Font Loading | Checks whether web font loading has been reviewed so fonts don't block page rendering or cause a flash of invisible text while they download. | â |
| `heartbeat-usage-reviewed` | Heartbeat Usage | Checks the WordPress Heartbeat API â which sends regular background requests from the browser â has been configured to a sensible polling interval for your site type. | â |
| `http2-or-http3-enabled` | HTTP/2 or HTTP/3 Enabled | Checks your server supports modern connection technology that sends multiple files at once, significantly speeding up how quickly pages reach your visitors. | â |
| `image-compression-pipeline-active` | Image Compression Pipeline Active | Checks an image optimisation plugin is active so uploaded images are automatically compressed before they are served to visitors. | â |
| `image-dimensions-not-set-causing-layout-shift` | Image Dimensions Not Set Causing Layout Shift | Checks images have explicit width and height set so browsers reserve the correct space for them, preventing the page from jumping around as images load. | â |
| `image-lazy-loading-reviewed` | Image Lazy Loading | Checks whether images below the visible area of the screen load lazily (only when needed) so the initial page load isn't slowed by images a visitor hasn't scrolled to yet. | â |
| `innodb-storage-engine-used` | InnoDB Storage Engine Used | Checks the database is using the InnoDB storage engine, which offers better performance, crash recovery, and row-level locking compared to older alternatives. | â |
| `jpeg-quality-configured` | JPEG Quality | Checks the JPEG compression setting has been reviewed so images are a sensible balance between visual quality and file size. | â |
| `js-minification-reviewed` | JavaScript Minification | Checks whether JavaScript files have whitespace and comments removed so they transfer faster to the browser. | â |
| `large-image-threshold-reviewed` | Media Scaling Threshold | Checks WordPress is allowed to automatically scale down oversized uploaded images, preventing multi-megabyte originals from being served directly to visitors. | â |
| `noncritical-js-deferred` | Non-Critical JS Deferred | Checks that scripts not needed for the initial page display are loading afterward so they don't hold up the visible content reaching visitors. | â |
| `object-cache-reviewed` | Object Cache | Checks whether a persistent object cache is active so database results are remembered between page requests instead of being re-queried every time. | â |
| `opcache-enabled` | OPcache Enabled | Checks PHP OPcache is enabled so PHP files are compiled once and run from memory on repeat, making PHP execution significantly faster. | â |
| `orphaned-comments-reviewed` | Orphaned Comments | Checks for comments attached to posts that no longer exist, which take up database space without serving any purpose. | â |
| `orphaned-post-meta-reviewed` | Orphaned Post Meta | Checks for post extra-data (meta) attached to posts that were deleted, which accumulates in the database over time without serving any purpose. | â |
| `orphaned-term-relationships-reviewed` | Orphaned Term Relationships | Checks for category and tag assignments linked to posts that no longer exist, which add unnecessary rows to the database. | â |
| `page-cache-enabled` | Page Cache Enabled | Checks page caching is active so fully-built HTML pages are served instantly from a cache instead of being rebuilt from scratch on every single visit. | ✅ |
| `php-memory-limit-optimized` | PHP Memory Limit Optimized | Checks the PHP memory limit is set high enough for WordPress and its plugins to run without hitting memory errors on page load or admin actions. | ✅ |
| `pingback-head-link` | Pingback Endpoint Disclosed in Head and Headers | Checks whether WordPress is advertising your xmlrpc.php endpoint via a `<link rel="pingback">` tag in every page's head and via an X-Pingback HTTP response header — both active even when pingbacks are disabled for new posts. | ✅ |
| `post-revision-limit-set` | Post Revision Limit Set | Checks a limit is set on the number of saved content revisions (older versions of your posts) so the database doesn't grow indefinitely with historical copies. | â |
| `responsive-images-enabled` | Responsive Images Enabled | Checks WordPress is generating responsive image code so browsers automatically download the right image size for the visitor's screen, not an oversized one. | ✅ |
| `rss-head-links` | RSS Feed Autodiscovery Links in Head | Checks whether WordPress is injecting RSS autodiscovery link tags into every page's head that broadcast feed URLs to browsers and feed readers — unnecessary for sites that do not actively promote an RSS subscription audience. | ✅ |
| `transients-cleanup-reviewed` | Transient Cleanup | Checks whether temporary database data (transients) is being cleaned up regularly so expired entries don't accumulate over time. | â |
| `webp-support-reviewed` | Modern Image Format Support | Checks whether your server and image pipeline support modern image formats that are smaller in file size without any visible drop in quality. | â |

---

## Security (44)

| Slug | Title | Description | Status |
|-|-|-|-|
| `admin-account-count-minimized` | Admin Account Count Minimized | Checks the number of administrator-level accounts is kept as low as possible, since every admin account is a potential target for attackers trying to take over your site. | â |
| `admin-session-expiration-hardened` | Admin Session Expiration Hardened | Checks admin login sessions are set to expire within a reasonable timeframe so unattended or forgotten sessions don't stay open indefinitely. | â |
| `auth-keys-and-salts-set` | Auth Keys And Salts Set | Checks the WordPress authentication keys and salts are set to unique, strong values â they cryptographically sign login cookies, and weak values make sessions easier to forge. | â |
| `auto-update-policy-reviewed` | Auto Update Policy | Checks a deliberate automatic update policy has been configured rather than leaving WordPress to use its defaults without a conscious decision. | â |
| `backup-files-not-public` | Backup Files Not Public | Checks backup files aren't stored in a web-accessible location where anyone could download a full copy of your site data and database. | â |
| `comment-link-limit-set` | Comment Link Limit Set | Checks a limit is set on how many links a comment can contain before WordPress holds it for moderation, since high-link-count comments are a classic spam pattern. | â |
| `comment-moderation-enabled` | Comment Moderation Enabled | Checks new comments require your approval before appearing publicly so spam and harmful content can't go live on your site without your review. | â |
| `core-updated` | WordPress Core Updated | Checks WordPress is running the latest available version so known security vulnerabilities are patched on your site. | â |
| `database-prefix-intentional` | Database Prefix | Checks the database table prefix has been changed from the widely-known default "wp_" that automated SQL injection tools specifically look for. | â |
| `db-credentials-not-exposed` | DB Credentials Not Exposed | Checks your database login details aren't accessible through a public URL that an attacker or curious visitor could simply open in their browser. | â |
| `default-admin-username-removed` | Default Admin Username Removed | Checks no account with the login name "admin" exists, since it is the very first username automated brute-force tools try when attacking a WordPress login. | â |
| `default-role-subscriber` | Default Role is Subscriber | Checks the role assigned to new user registrations is the lowest-privilege option (Subscriber) so nobody accidentally gets more access than they should. | â |
| `directory-listing-disabled` | Directory Listing Disabled | Checks your web server doesn't show a browseable file listing when a visitor goes to a folder URL, which would expose your file structure to anyone curious enough to look. | â |
| `file-editor-disabled` | Theme and Plugin Editor Disabled | Checks the built-in WordPress code editor is disabled so even if an admin account is compromised, an attacker can't use it to execute custom code without server access. | â |
| `file-mods-policy-defined` | File Modifications Policy Defined | Checks a clear policy is set for whether WordPress is allowed to modify files, so all file-level changes happen through a controlled, deliberate process. | â |
| `file-permissions-reviewed` | File Permissions | Checks WordPress file and folder permissions are restrictive enough that only the web server process can read sensitive files like wp-config.php. | â |
| `force-ssl-admin` | Force SSL Admin | Checks the WordPress admin area is set to always load over HTTPS so login credentials are never sent over an unencrypted connection. | â |
| `form-rate-limiting-active` | Form Rate Limiting Active | Checks public-facing forms are protected by anti-spam or rate-limiting measures so bots can't flood them with automated submissions. | â |
| `https-enabled` | HTTPS Enabled | Checks your site loads over an encrypted HTTPS connection so all data exchanged with visitors â including login details and form submissions â is protected. | â |
| `login-throttling-active` | Login Throttling Active | Checks a plugin is actively limiting failed login attempts so automated tools can't endlessly cycle through password guesses targeting your accounts. | â |
| `login-url-hardening-reviewed` | Login URL Hardening | Checks whether the standard WordPress login page (wp-login.php) has been reviewed for protections against the automated bots that continuously probe it. | â |
| `mixed-content-eliminated` | Mixed Content Eliminated | Checks all resources on your pages load over HTTPS rather than HTTP so browsers don't block them or show visitors a security warning. | â |
| `plugin-auto-updates-reviewed` | Plugin Auto Updates | Checks a deliberate policy is in place for keeping plugins updated â either automatically or through a regular manual schedule â so security patches are applied promptly. | â |
| `plugins-updated` | Plugins Updated | Checks all installed plugins are running the latest available version so known security vulnerabilities in older versions are not present on your site. | â |
| `privacy-policy-links-visible` | Privacy Links Visible | Checks a link to your privacy policy is easy for visitors to find so they know how their data is used â a basic requirement under most privacy laws. | â |
| `privacy-policy-page-set` | Privacy Policy Page Set | Checks a published privacy policy page is assigned in WordPress settings, as required by GDPR, CCPA, and most other privacy regulations. | â |
| `query-debug-logging-disabled-production` | Query Debug Logging Disabled In Production | Checks the SAVEQUERIES development tool is turned off on a live site, since recording every database query in memory is wasteful and can expose internal details. | â |
| `rest-api-sensitive-routes-protected` | REST API Sensitive Routes Protected | Checks the WordPress REST API isn't publicly exposing the list of usernames on your site, which attackers can use as a starting point for password attacks. | â |
| `security-headers-present` | Security Headers Present | Checks your server sends security headers that give browsers instructions for protecting visitors from common attacks like cross-site scripting and clickjacking. | â |
| `sensitive-files-protected` | Sensitive Files Protected | Checks that configuration and credential files (like .env) are not accessible via a web browser to anyone who knows â or guesses â their filename. | â |
| `spam-protection-enabled` | Spam Protection Enabled | Checks a spam protection plugin is active so comment sections, contact forms, and registration pages are defended against automated bot submissions. | â |
| `ssl-certificate-valid` | SSL Certificate Valid | Checks your HTTPS security certificate is valid, trusted, and not close to expiry so visitors never see a browser security warning when they open your site. | ð§ |
| `themes-updated` | Themes Updated | Checks all installed themes are running the latest available version so known security fixes are applied even for themes you aren't actively using. | â |
| `two-factor-admin-enabled` | Two-Factor for Admin Enabled | Checks administrator accounts are protected by two-factor authentication so a stolen password alone isn't enough to take over an admin login. | â |
| `unused-plugins-removed` | Unused Plugins Removed | Checks deactivated plugins have been deleted from the server, since inactive plugin code is still a security exposure even when it isn't running. | â |
| `unused-themes-removed` | Unused Themes Removed | Checks unused themes have been removed from the server. Inactive themes still receive less scrutiny and their unpatched vulnerabilities remain present on your site. | â |
| `uploads-php-execution-blocked` | Uploads PHP Execution Blocked | Checks the uploads folder is configured to block PHP files from running so a maliciously uploaded file can't be used to execute code on your server. | â |
| `user-enumeration-reduced` | User Enumeration Reduced | Checks measures are in place to prevent attackers from easily discovering the usernames on your site through author URLs or REST API endpoints. | â |
| `wp-config-location-reviewed` | wp-config Location | Checks the location and visibility of wp-config.php â the file containing your database credentials â has been reviewed to reduce the risk of it being accessed directly. | â |
| `wp-config-permissions-hardened` | wp-config Permissions Hardened | Checks wp-config.php has restrictive file permissions so only the web server process can read the database credentials and secret keys it contains. | â |
| `wp-content-write-scope-minimized` | wp-content Write Scope Minimized | Checks file write permissions inside wp-content are limited to only the directories that genuinely need to accept new files, reducing the impact of a compromised plugin. | â |
| `wp-debug-display-off` | WP Debug Display Off | Checks WP_DEBUG_DISPLAY is turned off on your live site so PHP errors stay hidden from visitors and don't leak file paths or server details. | â |
| `wp-debug-log-private` | WP Debug Log Private | Checks the WordPress debug log isn't stored in a publicly accessible web location where its file paths, query data, and error details could be read by anyone. | â |
| `xmlrpc-policy-intentional` | XML-RPC Policy | Checks whether the legacy XML-RPC API is enabled or disabled as a deliberate choice, since it is rarely needed today and is a frequent target for brute-force and flood attacks. | â |

---

## SEO (28)

| Slug | Title | Description | Status |
|-|-|-|-|
| `author-archives-intentional` | Author Archives | Checks author archive pages are configured intentionally since single-author sites produce thin, duplicate-content archives that can dilute search rankings. | â |
| `canonical-urls-reviewed` | Canonical URLs | Checks canonical URL tags are being output so search engines know the preferred version of each page and don't split ranking credit across duplicate URLs. | â |
| `category-strategy-reviewed` | Category Strategy | Checks posts aren't all sitting in the default "Uncategorized" category so your site has a meaningful structure that helps search engines understand your topics. | â |
| `custom-404-strategy-present` | Custom 404 Strategy Present | Checks a custom 404 page is in place so visitors who reach a broken link are offered helpful navigation rather than a generic error message. | â |
| `document-title-format-reviewed` | Document Title Format | Checks your theme supports proper title tag management so SEO plugins can control the format and keywords of every page's browser tab title. | â |
| `homepage-has-one-h1` | Homepage Has a Single H1 | Checks the homepage has exactly one H1 heading so search engines can clearly identify the primary topic of your most important page. | ð§ |
| `homepage-meta-reviewed` | Homepage Meta | Checks the homepage has a reviewed meta title and description so search results show relevant, intentional text rather than whatever Google decides to pull. | â |
| `image-link-default-reviewed` | Image Link Default | Checks the default image insertion setting doesn't link images to thin attachment pages that add no value and can dilute your search ranking. | â |
| `media-attachment-pages-reviewed` | Attachment Pages | Checks attachment pages (automatically created for every uploaded file) are disabled or handled correctly since they are thin-content pages that waste crawl budget. | â |
| `meta-descriptions-managed` | Meta Descriptions Managed | Checks a strategy is in place for controlling meta descriptions so search results show intentional, compelling text rather than randomly extracted content. | â |
| `meta-titles-managed` | Meta Titles Managed | Checks an SEO plugin is managing page title templates so every page has a keyword-intentional, correctly formatted browser tab title. | â |
| `noindex-policy-reviewed` | Noindex Policy | Checks a deliberate policy is in place for hiding low-value pages (like date archives and search results) from search engines to avoid diluting your ranking signal. | â |
| `open-graph-defaults-set` | Open Graph Defaults Set | Checks Open Graph tags are configured so links shared on Facebook, LinkedIn, and similar platforms display with the right title, description, and image preview. | â |
| `organization-schema-reviewed` | Organization Schema | Checks structured data describing your organisation is present so search engines can display accurate business details in rich search results. | â |
| `permalink-structure-meaningful` | Permalink Structure Meaningful | Checks your URLs contain readable words rather than numbers like "?p=123" so they are meaningful to both visitors and search engines. | â |
| `redirect-management-reviewed` | Redirect Management | Checks a redirect management tool is active so when URLs change, visitors and search engines are automatically sent to the right destination. | â |
| `robots-policy-configured` | Robots Policy | Checks a clear robots.txt file is in place with deliberate instructions for which pages search engine crawlers are allowed to visit. | â |
| `rss-feed-summary-reviewed` | RSS Feed Summary | Checks RSS feeds are set to output summaries rather than full post content so content scrapers can't republish your articles verbatim. | â |
| `schema-basics-reviewed` | Schema Basics | Checks structured data markup is active so search engines understand your content types and can show rich results like star ratings and FAQ panels. | â |
| `search-engine-visibility-intentional` | Search Engine Visibility | Checks the "Discourage search engines" setting is intentionally on or off â accidentally leaving it on prevents your site from appearing in Google at all. | â |
| `search-page-indexing-reviewed` | Search Page Indexing | Checks internal search result pages are blocked from being indexed by search engines since they are thin, auto-generated pages that can harm your rankings. | â |
| `seo-plugin-config-intentional` | SEO Plugin Configuration | Checks a recognised SEO plugin is active and configured so key elements like meta titles, descriptions, sitemaps, and schema markup are being managed. | â |
| `site-icon-configured` | Site Icon | Checks a site icon (favicon) is set so your site is recognisable in browser tabs, bookmarks, mobile home screens, and Google Search results. | â |
| `social-profile-links-reviewed` | Social Profile Links | Checks your SEO plugin has social profile URLs configured so search engines can connect your site's content to your social media presence. | â |
| `tag-archives-intentional` | Tag Archives | Checks tag archive pages are configured deliberately since tags used without a strategy tend to create many thin archive pages that compete with each other in search results. | â |
| `twitter-card-reviewed` | Twitter Card | Checks Twitter/X card meta tags are in place so links shared on X display with a title, description, and image rather than a plain bare URL. | â |
| `uncategorized-usage-reviewed` | Uncategorized Usage | Checks the default "Uncategorized" category is handled intentionally so your site structure signals topical relevance to search engines. | â |
| `xml-sitemap-enabled` | XML Sitemap Enabled | Checks an XML sitemap is being generated so search engines can discover and index all your pages efficiently. | â |

---

## Settings (18)

| Slug | Title | Description | Status |
|-|-|-|-|
| `admin-email-deliverable` | Admin Email Deliverable | Checks the admin email address is a real, reachable account so WordPress password resets, update notifications, and comment alerts actually reach you. | â |
| `auto-update-policy-configured` | Auto-Update Policy | Checks a deliberate policy is set for automatic WordPress core updates so your site stays protected between manual maintenance visits. | â |
| `comment-policy-intentional` | Comment Policy | Checks the comment settings â whether comments are open, moderated, and auto-closed â reflect a deliberate policy rather than the WordPress installation defaults. | â |
| `comment-spam-backlog` | Comment Spam Backlog Managed | Checks the spam comment queue is regularly cleared so it doesn't grow indefinitely and slow down the comments area of your admin dashboard. | ð§ |
| `cookie-consent-plugin-active` | Cookie Consent Plugin Active | Checks a cookie consent solution is active so visitors are informed about tracking cookies and given a choice â a requirement under GDPR and similar privacy laws. | ð§ |
| `default-user-role-reviewed` | Default User Role | Checks the role automatically assigned to new user accounts has been reviewed and set to the minimum privilege level your site requires. | â |
| `discussion-defaults-reviewed` | Discussion Defaults | Checks the WordPress discussion settings (comments, pingbacks, moderation) have been reviewed and don't simply remain at the installation defaults. | â |
| `front-page-configured` | Front Page | Checks the reading settings are configured so the front page shows the content you intend â whether a static page or the latest posts. | â |
| `legal-pages-linked-footer` | Legal Pages Linked in Footer | Checks privacy policy, terms, and other legal pages are linked in a footer menu so every visitor can find them from any page, as most privacy laws require. | â |
| `mail-sender-configured` | Mail Sender | Checks WordPress emails are sent with a real sender name and address rather than the generic default, which improves deliverability and presents a professional identity. | â |
| `maintenance-mode-off` | Maintenance Mode Off | Checks maintenance or coming-soon mode is not blocking real visitors from accessing your site when it should be live. | â |
| `media-sizes-reviewed` | Media Sizes | Checks the thumbnail, medium, and large image size settings have been purposefully set for your theme layout so WordPress generates only the sizes you actually use. | â |
| `pingbacks-trackbacks-configured` | Pingbacks and Trackbacks | Checks pingbacks and trackbacks are configured deliberately since they are rarely useful on modern sites and are frequently exploited for spam and server abuse. | â |
| `registration-setting-intentional` | Registration Setting | Checks whether open user registration is enabled by design (for example, on a membership site) rather than as an overlooked leftover from the installation defaults. | â |
| `site-title-tagline-intentional` | Site Title And Tagline | Checks the site title and tagline have been set to something meaningful since they appear in browser tabs, search results, and social media previews. | â |
| `site-urls-configured-correctly` | Site URLs Correctly | Checks the WordPress URL settings are correct and consistent â mismatched or HTTP URLs cause redirect loops, canonical issues, and security exposure. | â |
| `smtp-configured` | SMTP | Checks WordPress email is sent through a proper mail service rather than PHP mail(), which is frequently blocked by hosting providers and marked as spam. | â |
| `timezone-configured` | Timezone | Checks the site timezone is set to a named location rather than the UTC default so scheduled posts, timestamps, and event plugins show the correct time for your audience. | â |

---

## WordPress Health (2)

| Slug | Title | Description | Status |
|-|-|-|-|
| `php-version-reviewed` | PHP Version | Checks the PHP version running your site is current and actively maintained so you have access to performance improvements and security patches. | â |
| `site-health-criticals-addressed` | Site Health Criticals Addressed | Checks there are no outstanding critical issues flagged in the WordPress Site Health tool that could affect your site's security, performance, or reliability. | â |

---

## Workflows (5)

| Slug | Title | Description | Status |
|-|-|-|-|
| `cron-health-reviewed` | Cron Health | Checks the WordPress background task system (WP-Cron) is running without errors so scheduled actions like publishing posts and sending emails fire correctly. | â |
| `cron-overlap-protection-enabled` | Cron Overlap Protection Enabled | Checks the WP-Cron lock mechanism is healthy so a stale lock from a crashed task run doesn't prevent new scheduled tasks from starting. | â |
| `cron-traffic-dependence-reviewed` | WP Cron Traffic Dependence | Checks whether the site is relying on visitor page loads to trigger scheduled tasks â on low-traffic sites this means tasks can be delayed or skipped entirely. | â |
| `external-cron-reviewed` | External Cron Configured | Checks whether a real server-level cron job has been configured as the trigger for WordPress scheduled tasks rather than the visitor-dependent default. | â |
| `system-cron-offload-configured` | System Cron Offload | Checks WP-Cron is offloaded to a proper server-level cron so scheduled tasks run reliably on a fixed schedule rather than waiting for the next visitor to arrive. | â |

---

## Proposed / Pending Approval

### Treatment Planning Categories

Current review of missing treatments: 138 diagnostics are missing a treatment implementation.

Category definitions:
- Better automation: deterministic changes WPShadow can apply directly with high confidence.
- Scripted remediation: code-driven or batch-driven fixes that need environment checks, verification, or multi-step workflows.
- User interaction / form: fixes that need user intent, credentials, content, policy decisions, or other guided input.

#### Better Automation (10)

These are strong candidates for direct treatment implementations because the fix is mostly a setting, constant, metadata, or reversible file/config adjustment.

`admin-ampdevmode-assets`, `copyright-year-current`, `db-credentials-not-exposed`, `diagnostic-metadata-test`, `lang-attribute-correct`, `responsive-images-enabled`, `timezone`, `treatment-maturity-test`, `upload-size-configured`, `xmlrpc-policy-intentional`

#### Scripted Remediation (39)

These are good treatment candidates, but they likely need safer orchestration: environment detection, previews, backups, verifications, or post-apply rechecks.

`admin-excessive-inline-scripts`, `admin-excessive-inline-styles`, `admin-protocol-relative-assets`, `admin-scripts-in-head-blocking`, `admin-unminified-plugin-assets`, `application-health-checks-registered`, `autoloaded-options`, `backup-files-not-public`, `comment-spam-backlog`, `core-updated`, `critical-css-strategy`, `critical-resources-preloaded`, `cron-health`, `css-minification`, `database-optimization`, `default-admin-username-removed`, `demo-media-removed`, `duplicate-post-meta-keys`, `extra-image-sizes-trimmed`, `js-minification`, `myisam-tables-detected`, `post-meta-bloat-detected`, `rest-api-sensitive-routes-protected`, `sample-content-removed`, `scheduled-posts-not-stuck`, `site-urls-correctly`, `stale-sessions-cleared`, `tables-without-primary-key`, `themes-updated`, `unused-plugins-removed`, `unused-themes-removed`, `user-enumeration-reduced`, `user-meta-bloat-detected`, `user-table-large`, `woocommerce-session-table-size`, `wp-content-write-scope-minimized`, `wp-cron-reliable`, `wp-options-autoload-size`, `wp-options-row-count-reasonable`

#### User Interaction / Form (86)

These need user intent, site-specific content, plugin/service choice, business/legal judgment, credentials, or theme/design decisions. Treatments here should usually start as guided forms, setup flows, or checklists rather than blind automation.

`404-monitoring`, `about-page-published`, `active-plugin-count-reasonable`, `admin-account-count-minimized`, `admin-email-deliverable`, `admin-email-domain-match`, `analytics-installed-intentional`, `application-passwords-intentional`, `author-archives-intentional`, `auto-update-policy`, `auto-update-policy-reviewed`, `backups-automated`, `button-text-specific`, `caching-plugin-active`, `canonical-urls`, `category-strategy`, `cdn-for-static-assets`, `child-theme-active`, `comment-policy-intentional`, `contact-page-has-form`, `contact-page-published`, `cookie-consent-plugin-active`, `cron-traffic-dependence`, `custom-404-strategy-present`, `custom-logo-set`, `date-time-format-intentional`, `document-title-format`, `draft-pages-accumulation`, `external-cron`, `focus-outline-preserved`, `font-loading`, `footer-menu`, `form-error-messaging`, `front-page`, `heading-structure-reviewable`, `homepage-displays-intentional`, `homepage-has-one-h1`, `homepage-meta`, `homepage-page-published`, `image-alt-process`, `image-compression-pipeline-active`, `image-dimensions-not-set-causing-layout-shift`, `legal-pages-linked-footer`, `mail-sender`, `media-sizes`, `meta-descriptions-managed`, `meta-titles-managed`, `mobile-menu`, `motion-reduction`, `nav-menu-accessible-name`, `noindex-policy`, `object-cache`, `open-graph-defaults-set`, `organization-schema`, `page-cache-enabled`, `php-extensions-required`, `plugins-updated`, `posts-have-featured-images`, `posts-page-published`, `primary-navigation-assigned`, `privacy-policy-links-visible`, `redirect-management`, `registration-setting-intentional`, `robots-policy`, `schema-basics`, `search-enabled-intentional`, `search-form-accessible-name`, `seo-plugin-config-intentional`, `site-health-criticals-addressed`, `site-icon`, `site-language-intentional`, `site-title-tagline-intentional`, `skip-link-present`, `smtp`, `social-profile-links`, `spam-protection-enabled`, `system-cron-offload`, `system-cron-production`, `tag-archives-intentional`, `terms-of-service-page`, `twitter-card`, `two-factor-admin-enabled`, `underlines-or-link-distinction`, `viewport-meta`, `webp-support`, `xml-sitemap-enabled`

#### First Implementation Candidates

These are good early targets because they are high impact and relatively clear to implement:

1. `upload-size-configured`
2. `timezone`
3. `comment-spam-backlog`
4. `sample-content-removed`
5. `stale-sessions-cleared`
6. `wp-options-autoload-size`
7. `user-enumeration-reduced`
8. `default-admin-username-removed`

#### Notes

- Some diagnostics may eventually span more than one category. For example, a form-driven setup flow may collect inputs first and then hand off to a scripted remediation.
- Categories are implementation defaults, not hard limits. If the product adds richer setup wizards, some User Interaction items can later move into hybrid guided automation.
