#!/bin/bash
REPO="thisismyurl/wpshadow"

echo "=== Creating 95 WordPress Settings Diagnostics ==="
echo "Covering EVERY core WordPress setting with diagnostic value"
echo ""

# CATEGORY 1: General Settings (15 diagnostics)
echo "Creating General Settings Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Site Title Configuration" --body "Validates site title is set and not default 'Just Another WordPress Site'. Tests SEO impact. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Tagline Optimization" --body "Checks if tagline is customized and optimized for SEO. Detects generic or missing taglines. Threat: 35" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] WordPress Address URL" --body "Validates WordPress Address (URL) setting matches actual installation. Detects mismatches causing errors. Threat: 75" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Site Address URL" --body "Validates Site Address (URL) matches domain configuration. Critical for site access. Threat: 80" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Admin Email Deliverability" --body "Tests if admin email address receives notifications. Validates email server connectivity. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Membership Settings Security" --body "Checks 'Anyone can register' setting. Validates against spam registration attacks. Threat: 70" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] New User Default Role" --body "Validates default user role is appropriate. Detects overly permissive settings (admin default). Threat: 75" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Site Language Configuration" --body "Tests if site language is properly set. Validates translation file availability. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Timezone Accuracy" --body "Validates timezone setting matches server location. Tests scheduled post timing accuracy. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Date Format Consistency" --body "Checks date format setting consistency. Validates output matches configuration. Threat: 30" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Time Format Consistency" --body "Checks time format setting consistency. Validates 12h vs 24h display. Threat: 25" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Week Starts On Setting" --body "Validates week start day setting for calendar widgets and events. Threat: 20" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] HTTP vs HTTPS Configuration" --body "Detects HTTP/HTTPS mismatches in URLs. Tests SSL certificate validity. Threat: 70" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] www vs non-www Consistency" --body "Checks for www/non-www redirect issues. Validates canonical URL configuration. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Site Icon (Favicon) Presence" --body "Tests if site icon/favicon is set and displays correctly. Validates file format and size. Threat: 30" && sleep 2

echo "✅ General Settings: 15 diagnostics"
sleep 5

# CATEGORY 2: Writing Settings (12 diagnostics)
echo "Creating Writing Settings Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Default Post Category" --body "Validates default category is set appropriately. Detects 'Uncategorized' usage issues. Threat: 35" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Default Post Format" --body "Checks default post format setting. Validates theme support for selected format. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post via Email Configuration" --body "Tests post-by-email functionality if enabled. Validates mail server settings. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Mail Server Connectivity" --body "Tests connection to mail server for post-by-email. Validates credentials and ports. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Update Services Ping" --body "Tests XML-RPC update services functionality. Validates ping services are reachable. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Remote Publishing Security" --body "Checks if XML-RPC and REST API are properly secured. Detects brute force vulnerabilities. Threat: 75" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Emoji Support Configuration" --body "Tests emoji loading and rendering. Validates script loading performance impact. Threat: 30" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Auto-Embeds Functionality" --body "Tests automatic oEmbed functionality. Validates provider whitelist. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Press This Bookmarklet" --body "Tests Press This bookmarklet functionality if used. Validates JavaScript loading. Threat: 25" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Writing Enhancement Conflicts" --body "Detects conflicts between writing enhancement plugins. Tests editor functionality. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Distraction-Free Mode" --body "Tests distraction-free writing mode functionality. Validates fullscreen editor. Threat: 30" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Visual vs Text Editor Balance" --body "Validates both visual and text editors function correctly. Tests mode switching. Threat: 45" && sleep 2

echo "✅ Writing Settings: 12 diagnostics"
sleep 5

# CATEGORY 3: Reading Settings (15 diagnostics)
echo "Creating Reading Settings Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Homepage Display Configuration" --body "Validates homepage displays correctly (posts vs static page). Tests front page selection. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Posts Page Selection" --body "Checks if posts page is configured when using static front page. Validates page selection. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Blog Posts Per Page" --body "Validates posts per page setting. Tests pagination and performance impact. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Syndication Feed Posts Count" --body "Checks RSS feed item count setting. Validates feed performance and reader experience. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Feed Content Type" --body "Validates RSS feed shows full text vs summary. Tests excerpt generation. Threat: 35" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Search Engine Visibility" --body "Critical check if 'Discourage search engines' is enabled on production. Threat: 85" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] RSS Feed Functionality" --body "Tests if RSS feeds are generating correctly. Validates feed XML syntax. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] RSS Feed URL Accessibility" --body "Tests if feed URLs are accessible. Detects 404 errors on feed endpoints. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Atom Feed Support" --body "Tests Atom feed generation and accessibility. Validates alternative feed formats. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Comment Feed Functionality" --body "Tests comment RSS feeds. Validates comment feed generation. Threat: 35" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Category Feed Performance" --body "Tests individual category RSS feeds. Validates feed generation performance. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Feed Caching Configuration" --body "Validates RSS feed caching. Tests cache expiration and updates. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Feed Burner Integration" --body "Tests FeedBurner or feed redirect configuration. Validates feed URL redirects. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Reading Settings Performance Impact" --body "Measures performance impact of reading settings. Tests query optimization. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Archive Page Configuration" --body "Tests archive pages display correctly with reading settings. Validates pagination. Threat: 45" && sleep 2

echo "✅ Reading Settings: 15 diagnostics"
sleep 5

# CATEGORY 4: Discussion Settings (20 diagnostics)
echo "Creating Discussion Settings Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Default Article Comments" --body "Validates default comment status for new posts. Tests setting persistence. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Pingback Trackback Settings" --body "Tests pingback/trackback functionality and spam implications. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Comment Notification Delivery" --body "Tests comment notification email delivery. Validates SMTP configuration. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Moderation Email Delivery" --body "Tests moderation notification emails. Validates moderator email addresses. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Comment Approval Workflow" --body "Validates comment must be manually approved setting. Tests moderation queue. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Comment Author Whitelist" --body "Tests previously approved commenter auto-approval. Validates whitelist functionality. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Comment Moderation Threshold" --body "Validates link count moderation threshold. Tests spam prevention effectiveness. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Comment Blacklist Effectiveness" --body "Tests comment blacklist/disallowed words. Validates filtering accuracy. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Avatar Display Configuration" --body "Tests avatar display settings. Validates Gravatar integration. Threat: 35" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Default Avatar Selection" --body "Validates default avatar setting. Tests fallback avatar display. Threat: 25" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Comment Nesting Depth" --body "Tests maximum comment nesting level. Validates threaded comment display. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Comment Pagination Settings" --body "Tests comment pagination configuration. Validates 'break comments into pages'. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Comments Per Page Configuration" --body "Validates comments per page setting. Tests pagination performance. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Comment Sort Order" --body "Tests comment ordering (newest/oldest first). Validates display logic. Threat: 30" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Comment Form Position" --body "Tests comment form display position (top/bottom). Validates theme compatibility. Threat: 35" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Email Me Settings Accuracy" --body "Validates 'Email me whenever' settings work correctly. Tests notification triggers. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Comment Cookies Consent" --body "Tests comment author cookie consent functionality. Validates GDPR compliance. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Comment Close Automation" --body "Tests automatic comment closing on old posts. Validates close threshold. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Pingback Spam Prevention" --body "Tests pingback spam prevention measures. Validates self-pingback blocking. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Discussion Settings Security" --body "Overall discussion settings security audit. Detects spam-vulnerable configurations. Threat: 70" && sleep 2

echo "✅ Discussion Settings: 20 diagnostics"
sleep 5

# CATEGORY 5: Media Settings (10 diagnostics)
echo "Creating Media Settings Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Thumbnail Size Configuration" --body "Validates thumbnail dimensions are appropriate. Tests regeneration requirements. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Medium Size Configuration" --body "Validates medium image size settings. Tests dimension accuracy. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Large Size Configuration" --body "Validates large image size settings. Tests maximum dimensions. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Image Size Consistency" --body "Tests if all registered image sizes generate correctly. Validates theme image sizes. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Upload Organization Structure" --body "Tests year/month folder organization setting. Validates folder structure integrity. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Settings vs Existing Files" --body "Detects mismatches between settings and existing media. Validates regeneration needs. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Custom Image Sizes Registration" --body "Tests custom image sizes from themes/plugins. Validates add_image_size calls. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Image Dimension Accuracy" --body "Validates actual generated images match configured dimensions. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Crop vs Resize Settings" --body "Tests hard crop vs proportional resize behavior. Validates image quality. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Settings Performance Impact" --body "Measures performance impact of media size generation. Tests optimization opportunities. Threat: 50" && sleep 2

echo "✅ Media Settings: 10 diagnostics"
sleep 5

# CATEGORY 6: Permalinks Settings (15 diagnostics)
echo "Creating Permalinks Settings Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Permalink Structure SEO" --body "Validates permalink structure is SEO-friendly. Detects plain permalink usage. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Custom Permalink Structure" --body "Tests custom permalink structure syntax. Validates structure tags. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Permalink Rewrite Rules" --body "Tests if rewrite rules are generated correctly. Validates .htaccess on Apache. Threat: 75" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] htaccess File Writability" --body "Tests if .htaccess is writable for permalink updates. Validates file permissions. Threat: 70" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Nginx Rewrite Configuration" --body "Detects Nginx servers and provides rewrite config. Tests permalink functionality. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Category Base Configuration" --body "Validates category base setting. Tests category URL structure. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Tag Base Configuration" --body "Validates tag base setting. Tests tag URL structure. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Permalink Trailing Slash" --body "Tests trailing slash consistency. Detects redirect loops. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Permalink 404 Errors" --body "Detects 404 errors from broken permalinks. Tests URL accessibility. Threat: 75" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Flush Rewrite Rules Needed" --body "Detects if rewrite rules need flushing. Tests for stale rules. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Custom Post Type Permalinks" --body "Validates CPT permalink structures. Tests rewrite slug configuration. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Taxonomy Permalink Structure" --body "Tests custom taxonomy permalink structures. Validates URL rewriting. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Permalink Conflict Detection" --body "Detects permalink conflicts between posts/pages/CPTs. Tests slug uniqueness. Threat: 70" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Canonical URL Configuration" --body "Tests canonical URL generation from permalinks. Validates SEO canonical tags. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Permalink Migration Issues" --body "Detects issues after permalink structure changes. Tests redirect setup. Threat: 70" && sleep 2

echo "✅ Permalinks Settings: 15 diagnostics"
sleep 5

# CATEGORY 7: Privacy Settings (8 diagnostics)
echo "Creating Privacy Settings Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Privacy Policy Page Setup" --body "Validates privacy policy page is configured. Tests page accessibility. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Privacy Policy Content" --body "Tests if privacy policy page has actual content. Detects default/empty policies. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Privacy Policy Link Visibility" --body "Tests if privacy policy link displays correctly. Validates footer/form integration. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Personal Data Export Functionality" --body "Tests personal data export tool. Validates GDPR compliance features. Threat: 70" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Personal Data Erasure Functionality" --body "Tests personal data erasure requests. Validates right to be forgotten. Threat: 70" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Data Retention Settings" --body "Tests data retention configuration. Validates auto-deletion settings. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Cookie Consent Integration" --body "Tests cookie consent banner integration. Validates GDPR cookie compliance. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Privacy Request Notifications" --body "Tests privacy request email notifications. Validates admin notification delivery. Threat: 50" && sleep 2

echo "✅ Privacy Settings: 8 diagnostics"
sleep 5

echo ""
echo "=== WordPress Settings Diagnostics Creation Complete ==="
echo "Total Created: 95 diagnostics covering ALL WordPress core settings"
echo ""
echo "Categories:"
echo "  • General Settings: 15"
echo "  • Writing Settings: 12"
echo "  • Reading Settings: 15"
echo "  • Discussion Settings: 20"
echo "  • Media Settings: 10"
echo "  • Permalinks Settings: 15"
echo "  • Privacy Settings: 8"
echo ""
echo "Every WordPress setting reviewed for diagnostic value!"
