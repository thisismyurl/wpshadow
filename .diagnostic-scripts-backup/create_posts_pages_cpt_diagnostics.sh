#!/bin/bash
REPO="thisismyurl/wpshadow"

echo "=== Creating 50 Posts/Pages/CPT Diagnostics ==="
echo ""

# CATEGORY 1: Content Editor Health (10 diagnostics)
echo "Creating Content Editor Health Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Block Editor (Gutenberg) Functionality" --body "Tests if block editor loads correctly, blocks render properly, and no JavaScript errors occur. Critical for content creation workflow. Threat: 65" --label "diagnostic,posts,editor,gutenberg,functionality" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Classic Editor Availability" --body "Verifies Classic Editor plugin is active and switching between editors works properly. Threat: 50" --label "diagnostic,posts,editor,compatibility" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Auto-Save Functionality" --body "Tests auto-save triggers at proper intervals (60s) via heartbeat API. Prevents content loss. Threat: 70" --label "diagnostic,posts,editor,data,integrity" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Lock/Concurrent Editing" --body "Verifies post lock mechanism prevents simultaneous editing conflicts. Multi-author sites critical. Threat: 60" --label "diagnostic,posts,workflow,concurrency" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Revision History Integrity" --body "Tests revision saving and restore functionality work correctly. Undo capability essential. Threat: 55" --label "diagnostic,posts,revisions,data" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Media Attachment in Editor" --body "Verifies media library loads and images insert correctly into content. Threat: 60" --label "diagnostic,posts,editor,media" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Content Validation on Save" --body "Tests content sanitization and XSS prevention when saving posts. Security critical. Threat: 75" --label "diagnostic,posts,security,xss" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Editor Performance Load Time" --body "Measures editor load time (<2s target). Impacts daily productivity. Threat: 50" --label "diagnostic,posts,performance,editor" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Custom Field Editor Access" --body "Verifies custom fields meta box is visible and editable when needed. Threat: 45" --label "diagnostic,posts,customfields,usability" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Preview Functionality" --body "Tests preview generation creates correct URL and displays accurately. Threat: 50" --label "diagnostic,posts,preview,functionality" & sleep 2

echo "✅ Content Editor Health: 10 diagnostics"
sleep 5

# CATEGORY 2: Publishing Workflow (10 diagnostics)
echo "Creating Publishing Workflow Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Publish Button Accessibility" --body "Tests publish button is visible, clickable, and responds correctly. Business-critical functionality. Threat: 70" --label "diagnostic,posts,publishing,workflow" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Scheduling Accuracy" --body "Verifies scheduled posts publish at exact scheduled time (±1 min tolerance). Threat: 65" --label "diagnostic,posts,scheduling,cron" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Status Transition Validation" --body "Tests Draft→Pending→Published status transitions work correctly. Editorial workflow. Threat: 60" --label "diagnostic,posts,workflow,status" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Permalink Generation" --body "Verifies permalinks generate correctly with no duplicates. SEO critical. Threat: 70" --label "diagnostic,posts,seo,permalinks" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Slug Conflict Detection" --body "Detects duplicate post slugs within post types. Prevents URL conflicts and 404 errors. Threat: 60" --label "diagnostic,posts,seo,slugs" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Featured Image Assignment" --body "Tests featured images save and display correctly. Visual content integrity. Threat: 50" --label "diagnostic,posts,media,featured" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Category Assignment" --body "Verifies categories assign and persist correctly. Content organization. Threat: 45" --label "diagnostic,posts,taxonomy,categories" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Tag Assignment" --body "Tests tags save correctly with no orphaned terms. Taxonomy integrity. Threat: 40" --label "diagnostic,posts,taxonomy,tags" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Excerpt Generation" --body "Verifies auto-excerpts generate when manual excerpt missing. Meta description backup. Threat: 35" --label "diagnostic,posts,seo,excerpts" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Format Support" --body "Tests post formats (aside, gallery, video) work if theme supports them. Threat: 30" --label "diagnostic,posts,formats,theme" & sleep 2

echo "✅ Publishing Workflow: 10 diagnostics"
sleep 5

# CATEGORY 3: Custom Post Types (10 diagnostics)
echo "Creating Custom Post Types Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] CPT Registration Validation" --body "Verifies all custom post types register correctly without errors. CPT foundation. Threat: 70" --label "diagnostic,cpt,registration,functionality" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT Menu Visibility" --body "Tests custom post types appear in admin menu. Access critical. Threat: 65" --label "diagnostic,cpt,menu,usability" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT Capability Mapping" --body "Verifies user capabilities correctly assigned to CPTs. Security critical. Threat: 75" --label "diagnostic,cpt,security,capabilities" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT Archive Page Functionality" --body "Tests CPT archives display correctly. Content discoverability. Threat: 55" --label "diagnostic,cpt,archives,functionality" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT Single Post Template" --body "Verifies single CPT posts use correct template hierarchy. Layout integrity. Threat: 50" --label "diagnostic,cpt,templates,theme" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT REST API Support" --body "Tests CPTs accessible via REST API if enabled. Headless CMS support. Threat: 60" --label "diagnostic,cpt,api,rest" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT Rewrite Rule Conflicts" --body "Detects CPT permalink conflicts with other routes. Prevents 404 errors. Threat: 65" --label "diagnostic,cpt,permalinks,rewrites" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT Taxonomy Association" --body "Verifies custom taxonomies properly assigned to CPTs. Organization system. Threat: 55" --label "diagnostic,cpt,taxonomy,registration" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT Hierarchical Support" --body "Tests hierarchical CPTs (like pages) maintain parent/child relationships. Threat: 50" --label "diagnostic,cpt,hierarchy,functionality" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT Query Optimization" --body "Detects inefficient CPT queries, N+1 problems. Performance impact. Threat: 55" --label "diagnostic,cpt,performance,queries" & sleep 2

echo "✅ Custom Post Types: 10 diagnostics"
sleep 5

# CATEGORY 4: Data Integrity (10 diagnostics)
echo "Creating Data Integrity Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Orphaned Post Detection" --body "Finds posts with invalid parent IDs or term relationships. Data consistency. Threat: 50" --label "diagnostic,posts,data,integrity,orphans" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Missing Featured Image Detection" --body "Identifies posts that should have featured images but don't. Content completeness. Threat: 45" --label "diagnostic,posts,media,integrity" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Duplicate Slug Detection" --body "Finds multiple posts with same slug (post_name). SEO and URL conflicts. Threat: 65" --label "diagnostic,posts,seo,duplicates" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Meta Corruption" --body "Detects malformed or broken post meta data. Functionality preservation. Threat: 60" --label "diagnostic,posts,data,corruption" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Revision Bloat Detection" --body "Identifies excessive revisions per post. Database optimization. Threat: 45" --label "diagnostic,posts,revisions,bloat,performance" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Broken Post Relationships" --body "Validates post-to-post relationships (ACF, related posts). Data integrity. Threat: 55" --label "diagnostic,posts,relationships,data" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Missing Required Custom Fields" --body "Finds posts missing required custom field data. Content completeness. Threat: 50" --label "diagnostic,posts,customfields,integrity" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Date Inconsistencies" --body "Detects post_date vs post_modified logic errors. Reporting accuracy. Threat: 40" --label "diagnostic,posts,data,dates" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Author Attribution Accuracy" --body "Validates post authors exist and have correct permissions. Content ownership. Threat: 50" --label "diagnostic,posts,authors,integrity" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Content Encoding Issues" --body "Checks UTF-8 encoding correct, no corruption. International sites critical. Threat: 55" --label "diagnostic,posts,encoding,internationalization" & sleep 2

echo "✅ Data Integrity: 10 diagnostics"
sleep 5

# CATEGORY 5: Performance (5 diagnostics)
echo "Creating Performance Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Large Post Content Performance" --body "Tests posts with >10K words load efficiently. Long-form content sites. Threat: 50" --label "diagnostic,posts,performance,content" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Query Optimization" --body "Verifies WP_Query used efficiently, no unnecessary queries. Site performance. Threat: 60" --label "diagnostic,posts,performance,queries" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Search Performance" --body "Tests post search queries execute quickly. User experience. Threat: 50" --label "diagnostic,posts,search,performance" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Pagination Efficiency" --body "Verifies pagination doesn't cause slow queries. Archive performance. Threat: 45" --label "diagnostic,posts,pagination,performance" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Cache Effectiveness" --body "Tests object cache used for post queries. High-traffic optimization. Threat: 55" --label "diagnostic,posts,cache,performance" & sleep 2

echo "✅ Performance: 5 diagnostics"
sleep 5

# CATEGORY 6: Security & Permissions (5 diagnostics)
echo "Creating Security & Permissions Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Post Edit Capability Checks" --body "Verifies users can only edit posts they have permission for. Authorization critical. Threat: 80" --label "diagnostic,posts,security,capabilities" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Deletion Protection" --body "Tests posts require proper capability to delete. Content protection. Threat: 75" --label "diagnostic,posts,security,deletion" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Status Visibility Control" --body "Verifies private/draft posts not visible to unauthorized users. Information security. Threat: 70" --label "diagnostic,posts,security,visibility" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post XSS Prevention" --body "Tests post content properly sanitized/escaped. Critical security vulnerability prevention. Threat: 85" --label "diagnostic,posts,security,xss" & sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post REST API Authentication" --body "Verifies REST API post endpoints require authentication. API security. Threat: 75" --label "diagnostic,posts,security,api,rest" & sleep 2

echo "✅ Security & Permissions: 5 diagnostics"
sleep 5

echo ""
echo "=== Posts/Pages/CPT Diagnostics Creation Complete ==="
echo "Total Created: 50 diagnostics"
echo "Categories:"
echo "  • Content Editor Health: 10"
echo "  • Publishing Workflow: 10"
echo "  • Custom Post Types: 10"
echo "  • Data Integrity: 10"
echo "  • Performance: 5"
echo "  • Security & Permissions: 5"
echo ""
echo "All diagnostics cover core WordPress content management!"
