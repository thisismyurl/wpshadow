#!/bin/bash
REPO="thisismyurl/wpshadow"

echo "=== Creating 50 Posts/Pages/CPT Diagnostics (No Labels) ==="
echo ""

# CATEGORY 1: Post/Page Creation & Editing (10 diagnostics)
echo "Creating Post/Page Creation & Editing Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Post Save Failures" --body "Detects posts failing to save properly. Monitors save operations and identifies causes of failures (permissions, database, hooks). Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Auto-Save Functionality" --body "Verifies WordPress auto-save is working correctly. Tests auto-save intervals and data persistence. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Revision Bloat" --body "Checks if post revisions are accumulating excessively. Measures revision counts per post and database impact. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Draft Recovery Capability" --body "Tests if users can recover unsaved drafts after crashes or timeouts. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Lock Issues" --body "Detects posts stuck in editing lock state. Identifies lock timeouts and concurrent editing conflicts. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Featured Image Upload Failures" --body "Monitors featured image uploads for failures. Tests upload process and thumbnail generation. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Excerpt Truncation" --body "Verifies post excerpts aren't being truncated incorrectly. Checks excerpt length limits and encoding. Threat: 30" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Format Support" --body "Checks if post formats (aside, gallery, video, etc.) are properly supported by theme. Threat: 35" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Quick Edit Functionality" --body "Tests quick edit feature in post list for reliability. Verifies data saves correctly. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Bulk Edit Reliability" --body "Validates bulk edit operations on multiple posts. Tests for data loss or corruption. Threat: 50" && sleep 2

echo "✅ Post/Page Creation: 10 diagnostics"
sleep 5

# CATEGORY 2: Post Status & Workflow (8 diagnostics)
echo "Creating Post Status & Workflow Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Post Status Transitions" --body "Monitors post status changes (draft→pending→publish). Detects stuck or failed transitions. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Scheduled Post Reliability" --body "Verifies scheduled posts publish at correct time. Detects missed schedules and cron issues. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Visibility Settings" --body "Checks if post visibility (public/private/password) works correctly. Tests access control. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Pending Review Notifications" --body "Verifies authors/editors get notified of pending posts. Tests notification delivery. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Trash Recovery" --body "Tests if trashed posts can be restored properly. Verifies data integrity after restore. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Deletion Permanence" --body "Ensures permanently deleted posts are removed from database. Checks for orphaned data. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Date Backdating" --body "Verifies backdating posts works correctly. Tests post_date vs post_date_gmt handling. Threat: 35" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Sticky Post Functionality" --body "Tests if sticky posts remain at top of listings. Verifies sticky flag persistence. Threat: 30" && sleep 2

echo "✅ Post Status & Workflow: 8 diagnostics"
sleep 5

# CATEGORY 3: Custom Post Types (10 diagnostics)
echo "Creating Custom Post Type Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] CPT Registration Validation" --body "Validates all custom post types are registered correctly. Checks for registration errors and conflicts. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT Menu Visibility" --body "Verifies custom post types appear in admin menu. Tests show_in_menu and menu_position settings. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT Permalink Structure" --body "Checks if CPT permalinks work correctly. Tests rewrite rules and URL structure. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT Archive Page Support" --body "Verifies CPT archive pages display correctly. Tests has_archive functionality. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT Taxonomy Association" --body "Validates taxonomies properly associated with CPTs. Tests taxonomy registration and relationships. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT REST API Exposure" --body "Checks if CPTs are exposed to REST API when intended. Tests show_in_rest setting. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT Gutenberg Support" --body "Verifies CPTs support Gutenberg editor. Tests show_in_rest and editor compatibility. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT Query Performance" --body "Measures query performance for custom post type listings. Detects slow or inefficient queries. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT Capability Mapping" --body "Validates capability mapping for CPTs. Tests if users have correct permissions. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] CPT Meta Box Support" --body "Checks if CPTs support meta boxes correctly. Tests add_meta_box functionality. Threat: 45" && sleep 2

echo "✅ Custom Post Types: 10 diagnostics"
sleep 5

# CATEGORY 4: Post Meta & Custom Fields (8 diagnostics)
echo "Creating Post Meta & Custom Fields Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Post Meta Save Reliability" --body "Verifies post meta data saves correctly. Tests update_post_meta and data persistence. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Custom Field Visibility" --body "Checks if custom fields display in post editor. Tests custom field UI availability. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Meta Serialization Issues" --body "Detects improperly serialized post meta. Tests for serialization errors and data corruption. Threat: 65" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Hidden Meta Field Bloat" --body "Identifies excessive hidden meta fields. Measures meta table bloat from plugins. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Meta Key Naming Conflicts" --body "Detects meta key naming conflicts between plugins. Tests for duplicate or conflicting keys. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Meta Query Performance" --body "Measures performance of meta_query operations. Detects slow meta queries. Threat: 60" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Advanced Custom Fields Compatibility" --body "Tests ACF field group registration and display. Verifies ACF data integrity. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Meta Box Plugin Conflicts" --body "Detects conflicts between meta box plugins. Tests for UI collisions and data conflicts. Threat: 50" && sleep 2

echo "✅ Post Meta & Custom Fields: 8 diagnostics"
sleep 5

# CATEGORY 5: Post Relationships & Hierarchy (6 diagnostics)
echo "Creating Post Relationships & Hierarchy Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Post Parent-Child Relationships" --body "Validates hierarchical post relationships (pages). Tests parent/child data integrity. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Page Template Assignment" --body "Verifies page templates are assigned and loading correctly. Tests template file availability. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post-to-Post Relationships" --body "Checks if post-to-post connections work (plugins like ACF Relationship). Tests relationship integrity. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Menu Item Post Associations" --body "Validates menu items correctly link to posts/pages. Tests menu-post relationship integrity. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Orphan Detection" --body "Detects orphaned posts with invalid parent IDs. Tests for broken hierarchies. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Cross-Post Type References" --body "Validates references between different post types. Tests relationship plugin compatibility. Threat: 45" && sleep 2

echo "✅ Post Relationships: 6 diagnostics"
sleep 5

# CATEGORY 6: Content Display & Output (8 diagnostics)
echo "Creating Content Display & Output Diagnostics..."

gh issue create --repo "$REPO" --title "[Diagnostic] Post Content Encoding Issues" --body "Detects character encoding problems in post content. Tests for UTF-8 issues and special characters. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Excerpt Generation" --body "Verifies auto-generated excerpts work correctly. Tests excerpt filters and length. Threat: 35" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Thumbnail Display" --body "Checks if post thumbnails display correctly on frontend. Tests image size generation. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Pagination Functionality" --body "Tests if paginated posts (<!--nextpage-->) work correctly. Verifies navigation and display. Threat: 40" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Content Filtering" --body "Validates the_content filter chain. Detects conflicts in content filters. Threat: 50" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Shortcode Rendering" --body "Tests if shortcodes in posts render correctly. Detects unprocessed or broken shortcodes. Threat: 55" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Embed Functionality" --body "Verifies oEmbed embeds work in posts (YouTube, Twitter, etc.). Tests embed providers. Threat: 45" && sleep 2
gh issue create --repo "$REPO" --title "[Diagnostic] Post Read More Tag" --body "Checks if <!--more--> tag works correctly. Tests excerpt vs full content display. Threat: 30" && sleep 2

echo "✅ Content Display: 8 diagnostics"
sleep 5

echo ""
echo "=== Posts/Pages/CPT Diagnostics Creation Complete ==="
echo "Total Created: 50 diagnostics"
echo "Categories:"
echo "  • Post/Page Creation & Editing: 10"
echo "  • Post Status & Workflow: 8"
echo "  • Custom Post Types: 10"
echo "  • Post Meta & Custom Fields: 8"
echo "  • Post Relationships & Hierarchy: 6"
echo "  • Content Display & Output: 8"
echo ""
echo "All diagnostics cover core WordPress content management functionality!"
