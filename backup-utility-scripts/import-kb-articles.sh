#!/bin/bash
# Auto-generated KB article import script
# Creates 175 knowledge base articles

cd /workspaces/wpshadow

echo 'Creating KB articles...'
CREATED=0

# Article 1/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Live customizer" \
  --post_content="<p>Preview changes to colors, fonts, and layout in real-time before publishing.</p>

<h2>Related Features</h2>
<ul>
<li>Live customizer</li>
<li>Live customizer</li>
</ul>" \
  --post_name="customize-live-customizer" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 2/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Site/Theme Editor" \
  --post_content="<p>Edit your site design, templates, and styles with a visual editor. Make changes with live preview before publishing.</p>

<h2>Related Features</h2>
<ul>
<li>Site/Theme Editor</li>
<li>Site/Theme Editor</li>
</ul>" \
  --post_name="customize-site-theme-editor" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 3/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="All Pages" \
  --post_content="<p>See all your published and draft pages.</p>

<h2>Related Features</h2>
<ul>
<li>All Pages</li>
<li>All Pages</li>
</ul>" \
  --post_name="edit-pages-all-pages" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 4/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="All Posts" \
  --post_content="<p>See all your published and draft posts in a list.</p>

<h2>Related Features</h2>
<ul>
<li>All Posts</li>
<li>All Posts</li>
</ul>" \
  --post_name="edit-posts-all-posts" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 5/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Activate plugin" \
  --post_content="<p>Turn on this plugin to enable its features on your site.</p>

<h2>Related Features</h2>
<ul>
<li>Activate plugin</li>
<li>Activate plugin</li>
</ul>" \
  --post_name="general-activate-plugin" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 6/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Activate theme" \
  --post_content="<p>Set this as your active theme and apply it to your site.</p>

<h2>Related Features</h2>
<ul>
<li>Activate theme</li>
<li>Activate theme</li>
</ul>" \
  --post_name="general-activate-theme" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 7/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Add New" \
  --post_content="<p>Upload new images, videos, or files to your media library.</p>

<h2>Related Features</h2>
<ul>
<li>Add New</li>
<li>Add New</li>
</ul>" \
  --post_name="general-add-new" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 8/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Add tags" \
  --post_content="<p>Use tags to label your posts for better discovery.</p>

<h2>Related Features</h2>
<ul>
<li>Add tags</li>
<li>Add tags</li>
<li>Add tags</li>
<li>Add tags</li>
</ul>" \
  --post_name="general-add-tags" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 9/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Admin bar" \
  --post_content="<p>Show the WordPress admin bar when viewing the site.</p>

<h2>Related Features</h2>
<ul>
<li>Admin bar</li>
<li>Admin bar</li>
</ul>" \
  --post_name="general-admin-bar" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 10/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Admin color scheme" \
  --post_content="<p>Choose your preferred color scheme for the admin interface.</p>

<h2>Related Features</h2>
<ul>
<li>Admin color scheme</li>
<li>Admin color scheme</li>
</ul>" \
  --post_name="general-admin-color-scheme" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 11/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Admin email" \
  --post_content="<p>All notifications and site updates are sent to this email address.</p>" \
  --post_name="general-admin-email" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 12/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Allow pingbacks" \
  --post_content="<p>Allow other sites to notify you when they link to your content.</p>

<h2>Related Features</h2>
<ul>
<li>Allow pingbacks</li>
<li>Allow pingbacks</li>
</ul>" \
  --post_name="general-allow-pingbacks" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 13/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Allow registrations" \
  --post_content="<p>When checked, visitors can create user accounts. When unchecked, only administrators can create users.</p>

<h2>Related Features</h2>
<ul>
<li>Allow registrations</li>
<li>Allow registrations</li>
</ul>" \
  --post_name="general-allow-registrations" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 14/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Author" \
  --post_content="<p>Click to see all content by this author.</p>

<h2>Related Features</h2>
<ul>
<li>Author</li>
<li>Author</li>
</ul>" \
  --post_name="general-author" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 15/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Available tools" \
  --post_content="<p>Browse available tools for site maintenance and optimization.</p>

<h2>Related Features</h2>
<ul>
<li>Available tools</li>
<li>Available tools</li>
</ul>" \
  --post_name="general-available-tools" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 16/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Avatar rating" \
  --post_content="<p>Choose the maximum content rating for displayed avatars.</p>

<h2>Related Features</h2>
<ul>
<li>Avatar rating</li>
<li>Avatar rating</li>
</ul>" \
  --post_name="general-avatar-rating" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 17/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Blog posts" \
  --post_content="<p>Manage your blog posts, or add a new one.</p>

<h2>Related Features</h2>
<ul>
<li>Blog posts</li>
<li>Blog posts</li>
</ul>" \
  --post_name="general-blog-posts" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 18/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Bulk actions" \
  --post_content="<p>Select an action and check boxes above to perform the same action on multiple items.</p>

<h2>Related Features</h2>
<ul>
<li>Bulk actions</li>
<li>Bulk actions</li>
</ul>" \
  --post_name="general-bulk-actions" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 19/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Cancel" \
  --post_content="<p>Go back without saving changes.</p>

<h2>Related Features</h2>
<ul>
<li>Cancel</li>
<li>Cancel</li>
</ul>" \
  --post_name="general-cancel" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 20/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Categories" \
  --post_content="<p>Organize this post by selecting one or more categories.</p>

<h2>Related Features</h2>
<ul>
<li>Categories</li>
<li>Categories</li>
</ul>" \
  --post_name="general-categories" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 21/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Category" \
  --post_content="<p>Click to view all posts in this category.</p>

<h2>Related Features</h2>
<ul>
<li>Category</li>
<li>Category</li>
</ul>" \
  --post_name="general-category" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 22/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Category prefix" \
  --post_content="<p>The URL prefix for category pages. Default: category</p>

<h2>Related Features</h2>
<ul>
<li>Category prefix</li>
<li>Category prefix</li>
</ul>" \
  --post_name="general-category-prefix" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 23/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Comment blocklist" \
  --post_content="<p>Comments containing any of these words will be marked as spam or blocked.</p>

<h2>Related Features</h2>
<ul>
<li>Comment blocklist</li>
<li>Comment blocklist</li>
</ul>" \
  --post_name="general-comment-blocklist" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 24/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Comment pagination" \
  --post_content="<p>Split comments into multiple pages for long threads and choose which page shows first.</p>" \
  --post_name="general-comment-pagination" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 25/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Comment shortcuts" \
  --post_content="<p>Enable keyboard shortcuts for comment moderation.</p>

<h2>Related Features</h2>
<ul>
<li>Comment shortcuts</li>
<li>Comment shortcuts</li>
</ul>" \
  --post_name="general-comment-shortcuts" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 26/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Comments" \
  --post_content="<p>Moderate, approve, and respond to comments from your visitors.</p>

<h2>Related Features</h2>
<ul>
<li>Comments</li>
<li>Comments</li>
</ul>" \
  --post_name="general-comments" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 27/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Content settings" \
  --post_content="<p>Adjust post settings like categories, tags, featured image, and more.</p>

<h2>Related Features</h2>
<ul>
<li>Content settings</li>
<li>Content settings</li>
</ul>" \
  --post_name="general-content-settings" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 28/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Create a new Privacy Policy page" \
  --post_content="<p>Generate a new Privacy Policy page using WordPress defaults, then customize it for your site.</p>" \
  --post_name="general-create-a-new-privacy-policy-page" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 29/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Crop thumbnails" \
  --post_content="<p>Crop images to exact size or scale and letterbox them.</p>

<h2>Related Features</h2>
<ul>
<li>Crop thumbnails</li>
<li>Crop thumbnails</li>
</ul>" \
  --post_name="general-crop-thumbnails" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 30/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Dashboard home" \
  --post_content="<p>Visit the Dashboard for health, updates, and a high-level view of your site.</p>

<h2>Related Features</h2>
<ul>
<li>Dashboard home</li>
<li>Dashboard home</li>
</ul>" \
  --post_name="general-dashboard-home" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 31/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Date" \
  --post_content="<p>The date the item was created or published.</p>

<h2>Related Features</h2>
<ul>
<li>Date</li>
<li>Date</li>
</ul>" \
  --post_name="general-date" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 32/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Date format" \
  --post_content="<p>Choose how dates appear throughout your site. Select a preset format or create a custom one using PHP date codes.</p>

<h2>Related Features</h2>
<ul>
<li>Date format</li>
<li>Date format</li>
</ul>" \
  --post_name="general-date-format" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 33/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Deactivate plugin" \
  --post_content="<p>Turn off this plugin. Its features will no longer be available.</p>

<h2>Related Features</h2>
<ul>
<li>Deactivate plugin</li>
<li>Deactivate plugin</li>
</ul>" \
  --post_name="general-deactivate-plugin" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 34/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Default Avatar" \
  --post_content="<p>Select the placeholder avatar shown for users without a custom image.</p>" \
  --post_name="general-default-avatar" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 35/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Default mail category" \
  --post_content="<p>Category automatically applied to posts received via email.</p>" \
  --post_name="general-default-mail-category" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 36/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Delete item" \
  --post_content="<p>Permanently remove this item. This action cannot be undone.</p>

<h2>Related Features</h2>
<ul>
<li>Delete item</li>
<li>Delete item</li>
</ul>" \
  --post_name="general-delete-item" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 37/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Disallowed Comment Keys" \
  --post_content="<p>Words, phrases, IPs, or email addresses here will force comments to be blocked or sent to spam.</p>" \
  --post_name="general-disallowed-comment-keys" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 38/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Edit item" \
  --post_content="<p>Open this item for editing.</p>

<h2>Related Features</h2>
<ul>
<li>Edit item</li>
<li>Edit item</li>
</ul>" \
  --post_name="general-edit-item" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 39/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Edit URL" \
  --post_content="<p>Change the web address (URL) of this post or page.</p>

<h2>Related Features</h2>
<ul>
<li>Edit URL</li>
<li>Edit URL</li>
</ul>" \
  --post_name="general-edit-url" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 40/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Erase Personal Data" \
  --post_content="<p>Remove all personal data associated with a user account, complying with privacy deletion requests and regulations like GDPR.</p>

<h2>Related Features</h2>
<ul>
<li>Erase Personal Data</li>
<li>Erase Personal Data</li>
</ul>" \
  --post_name="general-erase-personal-data" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 41/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Export content" \
  --post_content="<p>Back up your posts, pages, and other content as an XML file.</p>

<h2>Related Features</h2>
<ul>
<li>Export content</li>
<li>Export content</li>
</ul>" \
  --post_name="general-export-content" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 42/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Export Personal Data" \
  --post_content="<p>Generate and download a data export file containing all personal information for a user account. Required by GDPR and privacy regulations.</p>

<h2>Related Features</h2>
<ul>
<li>Export Personal Data</li>
<li>Export Personal Data</li>
</ul>" \
  --post_name="general-export-personal-data" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 43/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Extend functionality" \
  --post_content="<p>Browse, install, and manage plugins to add features to your site.</p>

<h2>Related Features</h2>
<ul>
<li>Extend functionality</li>
<li>Extend functionality</li>
</ul>" \
  --post_name="general-extend-functionality" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 44/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Featured image" \
  --post_content="<p>Set or change the main image for this post.</p>

<h2>Related Features</h2>
<ul>
<li>Featured image</li>
<li>Featured image</li>
</ul>" \
  --post_name="general-featured-image" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 45/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Filter" \
  --post_content="<p>Apply the selected filters to update the list.</p>

<h2>Related Features</h2>
<ul>
<li>Filter</li>
<li>Filter</li>
</ul>" \
  --post_name="general-filter" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 46/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Filter by category" \
  --post_content="<p>Show only items in a specific category.</p>

<h2>Related Features</h2>
<ul>
<li>Filter by category</li>
<li>Filter by category</li>
</ul>" \
  --post_name="general-filter-by-category" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 47/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Filter by date" \
  --post_content="<p>Show only items from a specific month or year.</p>

<h2>Related Features</h2>
<ul>
<li>Filter by date</li>
<li>Filter by date</li>
</ul>" \
  --post_name="general-filter-by-date" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 48/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Filter by status" \
  --post_content="<p>Show items with a specific status (Published, Draft, Scheduled, etc).</p>

<h2>Related Features</h2>
<ul>
<li>Filter by status</li>
<li>Filter by status</li>
</ul>" \
  --post_name="general-filter-by-status" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 49/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="For each post in a feed, include" \
  --post_content="<p>Decide whether RSS feeds include full text or just excerpts for each post.</p>" \
  --post_name="general-for-each-post-in-a-feed-include" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 50/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="General Settings" \
  --post_content="<p>Configure your site's basic information: title, URL, timezone, and language. These are core settings that affect your entire site.</p>

<h2>Related Features</h2>
<ul>
<li>General Settings</li>
<li>General Settings</li>
</ul>" \
  --post_name="general-general-settings" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 51/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Get help" \
  --post_content="<p>Learn more about this WordPress admin page and its features.</p>

<h2>Related Features</h2>
<ul>
<li>Get help</li>
<li>Get help</li>
</ul>" \
  --post_name="general-get-help" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 52/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Help & Guidance" \
  --post_content="<p>Learn tips, configure tooltips, and access helpful resources for your site.</p>

<h2>Related Features</h2>
<ul>
<li>Help & Guidance</li>
<li>Help & Guidance</li>
</ul>" \
  --post_name="general-help-guidance" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 53/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Hold for review" \
  --post_content="<p>Hold comments for moderation if they contain more than this many links.</p>

<h2>Related Features</h2>
<ul>
<li>Hold for review</li>
<li>Hold for review</li>
</ul>" \
  --post_name="general-hold-for-review" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 54/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Import content" \
  --post_content="<p>Import posts, pages, and other content from another site.</p>

<h2>Related Features</h2>
<ul>
<li>Import content</li>
<li>Import content</li>
</ul>" \
  --post_name="general-import-content" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 55/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Item title" \
  --post_content="<p>Click to edit the title or view the full item details.</p>

<h2>Related Features</h2>
<ul>
<li>Item title</li>
<li>Item title</li>
</ul>" \
  --post_name="general-item-title" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 56/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Items per page" \
  --post_content="<p>Shows how many items are displayed per page. Adjust in Screen Options.</p>

<h2>Related Features</h2>
<ul>
<li>Items per page</li>
<li>Items per page</li>
</ul>" \
  --post_name="general-items-per-page" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 57/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Keyboard shortcuts" \
  --post_content="<p>Enable keyboard shortcuts for faster navigation.</p>

<h2>Related Features</h2>
<ul>
<li>Keyboard shortcuts</li>
<li>Keyboard shortcuts</li>
</ul>" \
  --post_name="general-keyboard-shortcuts" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 58/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Language" \
  --post_content="<p>Choose the language for your admin interface. This overrides the site-wide language setting for your account.</p>" \
  --post_name="general-language" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 59/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Library" \
  --post_content="<p>View and manage all your uploaded images, videos, and media files.</p>

<h2>Related Features</h2>
<ul>
<li>Library</li>
<li>Library</li>
</ul>" \
  --post_name="general-library" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 60/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Live preview" \
  --post_content="<p>Preview changes to this theme in real-time before applying.</p>

<h2>Related Features</h2>
<ul>
<li>Live preview</li>
<li>Live preview</li>
</ul>" \
  --post_name="general-live-preview" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 61/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Log out" \
  --post_content="<p>End your session and log out from the WordPress admin.</p>

<h2>Related Features</h2>
<ul>
<li>Log out</li>
<li>Log out</li>
</ul>" \
  --post_name="general-log-out" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 62/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Mail login name" \
  --post_content="<p>Username for the secret email account that receives posts.</p>" \
  --post_name="general-mail-login-name" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 63/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Mail password" \
  --post_content="<p>Password for the post-by-email inbox. Keep this secure and change if exposed.</p>" \
  --post_name="general-mail-password" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 64/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Mail server" \
  --post_content="<p>POP3 server used for post-by-email. Keep this account secret to prevent unwanted posts.</p>" \
  --post_name="general-mail-server" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 65/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Mail server port" \
  --post_content="<p>POP3 port for the post-by-email inbox (usually 110 or 995 for SSL).</p>" \
  --post_name="general-mail-server-port" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 66/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Manage team" \
  --post_content="<p>Add users and manage their roles to control site access.</p>

<h2>Related Features</h2>
<ul>
<li>Manage team</li>
<li>Manage team</li>
</ul>" \
  --post_name="general-manage-team" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 67/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Media" \
  --post_content="<p>Upload and manage images, videos, and files for your site.</p>

<h2>Related Features</h2>
<ul>
<li>Media</li>
<li>Media</li>
</ul>" \
  --post_name="general-media" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 68/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Membership" \
  --post_content="<p>Allow new users to register and create accounts on your site.</p>

<h2>Related Features</h2>
<ul>
<li>Membership</li>
<li>Membership</li>
</ul>" \
  --post_name="general-membership" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 69/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Navigate pages" \
  --post_content="<p>Click to go to a different page of items.</p>

<h2>Related Features</h2>
<ul>
<li>Navigate pages</li>
<li>Navigate pages</li>
</ul>" \
  --post_name="general-navigate-pages" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 70/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Nesting levels" \
  --post_content="<p>How many levels deep comments can be nested.</p>

<h2>Related Features</h2>
<ul>
<li>Nesting levels</li>
<li>Nesting levels</li>
</ul>" \
  --post_name="general-nesting-levels" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 71/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="New Application Password Name" \
  --post_content="<p>Create an application-specific password for logging in to WordPress via apps or scripts without exposing your main password.</p>" \
  --post_name="general-new-application-password-name" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 72/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="New User Default Role" \
  --post_content="<p>The permission level automatically assigned to newly registered users. Choose Subscriber for read-only access.</p>

<h2>Related Features</h2>
<ul>
<li>New User Default Role</li>
<li>New User Default Role</li>
</ul>" \
  --post_name="general-new-user-default-role" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 73/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Notify on comments" \
  --post_content="<p>Send email when someone posts a new comment.</p>

<h2>Related Features</h2>
<ul>
<li>Notify on comments</li>
<li>Notify on comments</li>
</ul>" \
  --post_name="general-notify-on-comments" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 74/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Notify on moderation" \
  --post_content="<p>Send email when a comment is held for moderation.</p>

<h2>Related Features</h2>
<ul>
<li>Notify on moderation</li>
<li>Notify on moderation</li>
</ul>" \
  --post_name="general-notify-on-moderation" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 75/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Organize by month/year" \
  --post_content="<p>Organize uploads into folders by year and month.</p>

<h2>Related Features</h2>
<ul>
<li>Organize by month/year</li>
<li>Organize by month/year</li>
</ul>" \
  --post_name="general-organize-by-month-year" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 76/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Organize posts" \
  --post_content="<p>Create and manage categories to organize your posts.</p>

<h2>Related Features</h2>
<ul>
<li>Organize posts</li>
<li>Organize posts</li>
</ul>" \
  --post_name="general-organize-posts" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 77/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Page template" \
  --post_content="<p>Choose a custom layout template for this page if available.</p>

<h2>Related Features</h2>
<ul>
<li>Page template</li>
<li>Page template</li>
</ul>" \
  --post_name="general-page-template" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 78/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Pages" \
  --post_content="<p>Create and manage static pages like Home, About, and Contact.</p>

<h2>Related Features</h2>
<ul>
<li>Pages</li>
<li>Pages</li>
</ul>" \
  --post_name="general-pages" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 79/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Permalink structure" \
  --post_content="<p>Choose how your post URLs are formatted. Important for SEO.</p>

<h2>Related Features</h2>
<ul>
<li>Permalink structure</li>
<li>Permalink structure</li>
</ul>" \
  --post_name="general-permalink-structure" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 80/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Post via email" \
  --post_content="<p>Allow posting new content by sending emails to a special address.</p>

<h2>Related Features</h2>
<ul>
<li>Post via email</li>
<li>Post via email</li>
</ul>" \
  --post_name="general-post-via-email" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 81/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Preview" \
  --post_content="<p>See how your post or page will look before publishing.</p>

<h2>Related Features</h2>
<ul>
<li>Preview</li>
<li>Preview</li>
</ul>" \
  --post_name="general-preview" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 82/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Privacy policy" \
  --post_content="<p>Select the page to use as your privacy policy.</p>

<h2>Related Features</h2>
<ul>
<li>Privacy policy</li>
<li>Privacy policy</li>
</ul>" \
  --post_name="general-privacy-policy" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 83/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Profile Picture" \
  --post_content="<p>Your profile picture is managed through Gravatar. Visit Gravatar.com to upload or change your image.</p>" \
  --post_name="general-profile-picture" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 84/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Publish" \
  --post_content="<p>Click to publish your post. You can edit it anytime after publishing.</p>

<h2>Related Features</h2>
<ul>
<li>Publish</li>
<li>Publish</li>
</ul>" \
  --post_name="general-publish" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 85/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Quick add" \
  --post_content="<p>Quickly create posts, pages, or upload media from anywhere in admin.</p>

<h2>Related Features</h2>
<ul>
<li>Quick add</li>
<li>Quick add</li>
</ul>" \
  --post_name="general-quick-add" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 86/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="RSS content" \
  --post_content="<p>Choose whether to show full post or excerpt in RSS feeds.</p>

<h2>Related Features</h2>
<ul>
<li>RSS content</li>
<li>RSS content</li>
</ul>" \
  --post_name="general-rss-content" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 87/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="RSS posts" \
  --post_content="<p>Number of posts to include in your RSS feed.</p>

<h2>Related Features</h2>
<ul>
<li>RSS posts</li>
<li>RSS posts</li>
</ul>" \
  --post_name="general-rss-posts" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 88/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Save changes" \
  --post_content="<p>Submit this form and save your changes.</p>

<h2>Related Features</h2>
<ul>
<li>Save changes</li>
<li>Save changes</li>
</ul>" \
  --post_name="general-save-changes" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 89/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Save draft" \
  --post_content="<p>Save your work without publishing. You can finish editing later.</p>

<h2>Related Features</h2>
<ul>
<li>Save draft</li>
<li>Save draft</li>
</ul>" \
  --post_name="general-save-draft" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 90/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Screen options" \
  --post_content="<p>Customize what columns and items appear on this page.</p>

<h2>Related Features</h2>
<ul>
<li>Screen options</li>
<li>Screen options</li>
</ul>" \
  --post_name="general-screen-options" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 91/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Search" \
  --post_content="<p>Click to search for items matching your keywords.</p>

<h2>Related Features</h2>
<ul>
<li>Search</li>
<li>Search</li>
</ul>" \
  --post_name="general-search" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 92/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Search admin" \
  --post_content="<p>Quickly search for posts, pages, settings, and more from anywhere in the admin.</p>

<h2>Related Features</h2>
<ul>
<li>Search admin</li>
<li>Search admin</li>
</ul>" \
  --post_name="general-search-admin" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 93/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Search items" \
  --post_content="<p>Type to search for items by title or content.</p>

<h2>Related Features</h2>
<ul>
<li>Search items</li>
<li>Search items</li>
</ul>" \
  --post_name="general-search-items" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 94/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Select items" \
  --post_content="<p>Check these to select items for bulk actions.</p>

<h2>Related Features</h2>
<ul>
<li>Select items</li>
<li>Select items</li>
</ul>" \
  --post_name="general-select-items" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 95/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Sessions" \
  --post_content="<p>View and manage where you are currently logged in. You can log out of other devices for security.</p>" \
  --post_name="general-sessions" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 96/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Show avatars" \
  --post_content="<p>Display user profile images next to their comments and posts.</p>

<h2>Related Features</h2>
<ul>
<li>Show avatars</li>
<li>Show avatars</li>
</ul>" \
  --post_name="general-show-avatars" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 97/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Site design" \
  --post_content="<p>Customize your site appearance with themes, colors, menus, and widgets.</p>

<h2>Related Features</h2>
<ul>
<li>Site design</li>
<li>Site design</li>
</ul>" \
  --post_name="general-site-design" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 98/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Site health" \
  --post_content="<p>Check your site status and get recommendations for improvements.</p>

<h2>Related Features</h2>
<ul>
<li>Site health</li>
<li>Site health</li>
</ul>" \
  --post_name="general-site-health" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 99/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Site icon" \
  --post_content="<p>Upload a favicon (icon) that appears in browser tabs and bookmarks. Recommended size is 512x512 pixels.</p>" \
  --post_name="general-site-icon" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 100/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Site Language" \
  --post_content="<p>Select the language for the WordPress admin interface and site. Change requires language packs to be installed.</p>

<h2>Related Features</h2>
<ul>
<li>Site Language</li>
<li>Site Language</li>
</ul>" \
  --post_name="general-site-language" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 101/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Site name" \
  --post_content="<p>Visit your site home page or access site-level settings.</p>

<h2>Related Features</h2>
<ul>
<li>Site name</li>
<li>Site name</li>
</ul>" \
  --post_name="general-site-name" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 102/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Site settings" \
  --post_content="<p>Configure your site name, timezone, and other important settings.</p>

<h2>Related Features</h2>
<ul>
<li>Site settings</li>
<li>Site settings</li>
<li>Site settings</li>
</ul>" \
  --post_name="general-site-settings" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 103/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Site tools" \
  --post_content="<p>Access tools for importing, exporting, and analyzing your site.</p>

<h2>Related Features</h2>
<ul>
<li>Site tools</li>
<li>Site tools</li>
</ul>" \
  --post_name="general-site-tools" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 104/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Site visibility" \
  --post_content="<p>Control whether search engines can index your site.</p>

<h2>Related Features</h2>
<ul>
<li>Site visibility</li>
<li>Site visibility</li>
</ul>" \
  --post_name="general-site-visibility" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 105/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Sort column" \
  --post_content="<p>Click to sort items by this column. Click again to reverse order.</p>

<h2>Related Features</h2>
<ul>
<li>Sort column</li>
<li>Sort column</li>
</ul>" \
  --post_name="general-sort-column" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 106/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Status" \
  --post_content="<p>Shows whether the item is published, draft, or has other status.</p>

<h2>Related Features</h2>
<ul>
<li>Status</li>
<li>Status</li>
</ul>" \
  --post_name="general-status" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 107/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Syntax Highlighting" \
  --post_content="<p>Enable syntax highlighting in the code editor for easier reading and debugging.</p>" \
  --post_name="general-syntax-highlighting" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 108/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Tag prefix" \
  --post_content="<p>The URL prefix for tag pages. Default: tag</p>

<h2>Related Features</h2>
<ul>
<li>Tag prefix</li>
<li>Tag prefix</li>
</ul>" \
  --post_name="general-tag-prefix" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 109/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Time format" \
  --post_content="<p>Choose how times appear throughout your site. Select 12-hour, 24-hour, or create a custom format.</p>

<h2>Related Features</h2>
<ul>
<li>Time format</li>
<li>Time format</li>
</ul>" \
  --post_name="general-time-format" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 110/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Timezone" \
  --post_content="<p>Select your timezone. This affects when scheduled posts are published and when cron jobs run.</p>

<h2>Related Features</h2>
<ul>
<li>Timezone</li>
<li>Timezone</li>
</ul>" \
  --post_name="general-timezone" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 111/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Toggle menu" \
  --post_content="<p>Collapse or expand the sidebar menu for more screen space.</p>

<h2>Related Features</h2>
<ul>
<li>Toggle menu</li>
<li>Toggle menu</li>
</ul>" \
  --post_name="general-toggle-menu" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 112/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Update Services" \
  --post_content="<p>Services to notify when you publish new posts. Enter one service URL per line.</p>" \
  --post_name="general-update-services" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 113/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Updates" \
  --post_content="<p>Check and install updates for WordPress, plugins, and themes to keep your site secure and running smoothly.</p>

<h2>Related Features</h2>
<ul>
<li>Updates</li>
<li>Updates</li>
</ul>" \
  --post_name="general-updates" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 114/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Updates available" \
  --post_content="<p>Install WordPress, plugin, and theme updates to stay secure.</p>

<h2>Related Features</h2>
<ul>
<li>Updates available</li>
<li>Updates available</li>
</ul>" \
  --post_name="general-updates-available" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 115/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Upload folder" \
  --post_content="<p>Where media files are stored on your server. Default: wp-content/uploads</p>

<h2>Related Features</h2>
<ul>
<li>Upload folder</li>
<li>Upload folder</li>
</ul>" \
  --post_name="general-upload-folder" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 116/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Workflow Manager" \
  --post_content="<p>Create and manage automated workflows to standardize site maintenance tasks.</p>

<h2>Related Features</h2>
<ul>
<li>Workflow Manager</li>
<li>Workflow Manager</li>
</ul>" \
  --post_name="general-workflow-manager" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 117/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="WPShadow" \
  --post_content="<p>Central hub for site health monitoring, analysis tools, and optimization features.</p>

<h2>Related Features</h2>
<ul>
<li>WPShadow</li>
<li>WPShadow</li>
</ul>" \
  --post_name="general-wpshadow" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 118/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="WPShadow Dark Mode" \
  --post_content="<p>Control dark mode for WPShadow admin pages. Choose Auto to follow your system preference, or manually select Light or Dark.</p>" \
  --post_name="general-wpshadow-dark-mode" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 119/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="WPShadow Dashboard" \
  --post_content="<p>View your site health status, recent findings, and quick improvement recommendations.</p>

<h2>Related Features</h2>
<ul>
<li>WPShadow Dashboard</li>
<li>WPShadow Dashboard</li>
</ul>" \
  --post_name="general-wpshadow-dashboard" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 120/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="WPShadow Tools" \
  --post_content="<p>Access specialized analysis tools including accessibility audits, link checks, and color contrast analysis.</p>

<h2>Related Features</h2>
<ul>
<li>WPShadow Tools</li>
<li>WPShadow Tools</li>
</ul>" \
  --post_name="general-wpshadow-tools" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 121/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Your profile" \
  --post_content="<p>Access your account settings, edit your profile, or log out.</p>

<h2>Related Features</h2>
<ul>
<li>Your profile</li>
<li>Your profile</li>
</ul>" \
  --post_name="general-your-profile" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 122/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Edit menus" \
  --post_content="<p>Create and organize navigation menus for your site.</p>

<h2>Related Features</h2>
<ul>
<li>Edit menus</li>
<li>Edit menus</li>
</ul>" \
  --post_name="menus-edit-menus" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 123/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Edit code" \
  --post_content="<p>Edit plugin or theme files directly. Be careful—errors can break your site.</p>

<h2>Related Features</h2>
<ul>
<li>Edit code</li>
<li>Edit code</li>
</ul>" \
  --post_name="plugin-editor-edit-code" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 124/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Plugin File Editor" \
  --post_content="<p>Edit plugin PHP files directly. Be careful—syntax errors can break your site and disable the plugin.</p>

<h2>Related Features</h2>
<ul>
<li>Plugin File Editor</li>
<li>Plugin File Editor</li>
</ul>" \
  --post_name="plugin-editor-plugin-file-editor" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 125/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Add new plugins" \
  --post_content="<p>Search and install plugins from the WordPress plugin directory.</p>

<h2>Related Features</h2>
<ul>
<li>Add new plugins</li>
<li>Add new plugins</li>
</ul>" \
  --post_name="plugin-install-add-new-plugins" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 126/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Installed plugins" \
  --post_content="<p>See all your installed plugins, activate, deactivate, or delete them.</p>

<h2>Related Features</h2>
<ul>
<li>Installed plugins</li>
<li>Installed plugins</li>
</ul>" \
  --post_name="plugins-installed-plugins" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 127/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Add a new post" \
  --post_content="<p>Create and publish a new blog post.</p>

<h2>Related Features</h2>
<ul>
<li>Add a new post</li>
<li>Add a new post</li>
</ul>" \
  --post_name="post-new-add-a-new-post" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 128/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Create new page" \
  --post_content="<p>Add a new static page to your site.</p>

<h2>Related Features</h2>
<ul>
<li>Create new page</li>
<li>Create new page</li>
</ul>" \
  --post_name="post-new-create-new-page" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 129/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Biographical info" \
  --post_content="<p>Your bio or about information displayed on author pages.</p>

<h2>Related Features</h2>
<ul>
<li>Biographical info</li>
<li>Biographical info</li>
</ul>" \
  --post_name="profile-biographical-info" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 130/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Display name" \
  --post_content="<p>How your name appears when you publish or comment.</p>

<h2>Related Features</h2>
<ul>
<li>Display name</li>
<li>Display name</li>
</ul>" \
  --post_name="profile-display-name" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 131/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Edit profile" \
  --post_content="<p>Update your personal information, email, and password.</p>

<h2>Related Features</h2>
<ul>
<li>Edit profile</li>
<li>Edit profile</li>
</ul>" \
  --post_name="profile-edit-profile" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 132/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Email address" \
  --post_content="<p>Your email address for account recovery and notifications.</p>

<h2>Related Features</h2>
<ul>
<li>Email address</li>
<li>Email address</li>
</ul>" \
  --post_name="profile-email-address" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 133/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="First name" \
  --post_content="<p>Your first name (optional, displayed in some areas).</p>

<h2>Related Features</h2>
<ul>
<li>First name</li>
<li>First name</li>
</ul>" \
  --post_name="profile-first-name" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 134/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Last name" \
  --post_content="<p>Your last name (optional, displayed in some areas).</p>

<h2>Related Features</h2>
<ul>
<li>Last name</li>
<li>Last name</li>
</ul>" \
  --post_name="profile-last-name" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 135/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Nickname" \
  --post_content="<p>How your name is displayed publicly.</p>

<h2>Related Features</h2>
<ul>
<li>Nickname</li>
<li>Nickname</li>
</ul>" \
  --post_name="profile-nickname" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 136/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Website" \
  --post_content="<p>Your personal website URL (optional).</p>

<h2>Related Features</h2>
<ul>
<li>Website</li>
<li>Website</li>
</ul>" \
  --post_name="profile-website" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 137/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Your profile" \
  --post_content="<p>Update your personal information, password, and settings.</p>

<h2>Related Features</h2>
<ul>
<li>Your profile</li>
<li>Your profile</li>
</ul>" \
  --post_name="profile-your-profile" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 138/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Allow comments" \
  --post_content="<p>Allow visitors to post comments on new posts by default.</p>

<h2>Related Features</h2>
<ul>
<li>Allow comments</li>
<li>Allow comments</li>
</ul>" \
  --post_name="settings-discussion-allow-comments" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 139/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Comment moderation" \
  --post_content="<p>Hold comments for review before they appear on your site.</p>

<h2>Related Features</h2>
<ul>
<li>Comment moderation</li>
<li>Comment moderation</li>
</ul>" \
  --post_name="settings-discussion-comment-moderation" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 140/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Comments settings" \
  --post_content="<p>Control comment moderation, notifications, and avatar settings.</p>

<h2>Related Features</h2>
<ul>
<li>Comments settings</li>
<li>Comments settings</li>
</ul>" \
  --post_name="settings-discussion-comments-settings" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 141/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Enable nesting" \
  --post_content="<p>Allow replies to specific comments to be nested under them.</p>

<h2>Related Features</h2>
<ul>
<li>Enable nesting</li>
<li>Enable nesting</li>
</ul>" \
  --post_name="settings-discussion-enable-nesting" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 142/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Require name and email" \
  --post_content="<p>Commenters must provide a name and email address.</p>

<h2>Related Features</h2>
<ul>
<li>Require name and email</li>
<li>Require name and email</li>
</ul>" \
  --post_name="settings-discussion-require-name-and-email" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 143/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Admin email" \
  --post_content="<p>All notifications and site updates are sent to this email address.</p>" \
  --post_name="settings-general-admin-email" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 144/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="General settings" \
  --post_content="<p>Set site title, tagline, timezone, and date format.</p>

<h2>Related Features</h2>
<ul>
<li>General settings</li>
<li>General settings</li>
</ul>" \
  --post_name="settings-general-general-settings" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 145/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Home URL" \
  --post_content="<p>The main page of your site. Usually the same as your Site URL.</p>

<h2>Related Features</h2>
<ul>
<li>Home URL</li>
<li>Home URL</li>
</ul>" \
  --post_name="settings-general-home-url" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 146/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Site tagline" \
  --post_content="<p>A short description of your site. Leave blank if you don't want one.</p>

<h2>Related Features</h2>
<ul>
<li>Site tagline</li>
<li>Site tagline</li>
</ul>" \
  --post_name="settings-general-site-tagline" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 147/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Site title" \
  --post_content="<p>The name of your website, displayed in browser tabs and search results.</p>

<h2>Related Features</h2>
<ul>
<li>Site title</li>
<li>Site title</li>
</ul>" \
  --post_name="settings-general-site-title" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 148/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Site URL" \
  --post_content="<p>The URL where your site is located. Changing this requires careful attention.</p>

<h2>Related Features</h2>
<ul>
<li>Site URL</li>
<li>Site URL</li>
</ul>" \
  --post_name="settings-general-site-url" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 149/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Week starts on" \
  --post_content="<p>Set which day the week starts on in post calendars and scheduling interfaces. Affects calendar display only.</p>

<h2>Related Features</h2>
<ul>
<li>Week starts on</li>
<li>Week starts on</li>
</ul>" \
  --post_name="settings-general-week-starts-on" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 150/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Image settings" \
  --post_content="<p>Configure automatic image sizes and thumbnail settings.</p>

<h2>Related Features</h2>
<ul>
<li>Image settings</li>
<li>Image settings</li>
</ul>" \
  --post_name="settings-media-image-settings" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 151/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Large size height" \
  --post_content="<p>Height of large-sized images (pixels).</p>

<h2>Related Features</h2>
<ul>
<li>Large size height</li>
<li>Large size height</li>
</ul>" \
  --post_name="settings-media-large-size-height" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 152/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Large size width" \
  --post_content="<p>Width of large-sized images (pixels).</p>

<h2>Related Features</h2>
<ul>
<li>Large size width</li>
<li>Large size width</li>
</ul>" \
  --post_name="settings-media-large-size-width" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 153/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Medium size height" \
  --post_content="<p>Height of medium-sized images (pixels).</p>

<h2>Related Features</h2>
<ul>
<li>Medium size height</li>
<li>Medium size height</li>
</ul>" \
  --post_name="settings-media-medium-size-height" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 154/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Medium size width" \
  --post_content="<p>Width of medium-sized images (pixels).</p>

<h2>Related Features</h2>
<ul>
<li>Medium size width</li>
<li>Medium size width</li>
</ul>" \
  --post_name="settings-media-medium-size-width" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 155/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Thumbnail height" \
  --post_content="<p>Height of automatically generated thumbnail images (pixels).</p>

<h2>Related Features</h2>
<ul>
<li>Thumbnail height</li>
<li>Thumbnail height</li>
</ul>" \
  --post_name="settings-media-thumbnail-height" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 156/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Thumbnail width" \
  --post_content="<p>Width of automatically generated thumbnail images (pixels).</p>

<h2>Related Features</h2>
<ul>
<li>Thumbnail width</li>
<li>Thumbnail width</li>
</ul>" \
  --post_name="settings-media-thumbnail-width" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 157/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Permalink structure" \
  --post_content="<p>Set how your post URLs are formatted for better SEO.</p>

<h2>Related Features</h2>
<ul>
<li>Permalink structure</li>
<li>Permalink structure</li>
</ul>" \
  --post_name="settings-permalink-permalink-structure" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 158/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Privacy settings" \
  --post_content="<p>Configure your site privacy and search engine visibility.</p>

<h2>Related Features</h2>
<ul>
<li>Privacy settings</li>
<li>Privacy settings</li>
</ul>" \
  --post_name="settings-privacy-privacy-settings" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 159/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Homepage" \
  --post_content="<p>Select the page to display as your site homepage.</p>

<h2>Related Features</h2>
<ul>
<li>Homepage</li>
<li>Homepage</li>
</ul>" \
  --post_name="settings-reading-homepage" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 160/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Homepage displays" \
  --post_content="<p>Choose to show your latest posts or a static page as the homepage.</p>

<h2>Related Features</h2>
<ul>
<li>Homepage displays</li>
<li>Homepage displays</li>
</ul>" \
  --post_name="settings-reading-homepage-displays" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 161/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Posts page" \
  --post_content="<p>Select the page to display your blog posts list.</p>

<h2>Related Features</h2>
<ul>
<li>Posts page</li>
<li>Posts page</li>
</ul>" \
  --post_name="settings-reading-posts-page" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 162/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Posts per page" \
  --post_content="<p>Number of posts to display on each page of your blog.</p>

<h2>Related Features</h2>
<ul>
<li>Posts per page</li>
<li>Posts per page</li>
</ul>" \
  --post_name="settings-reading-posts-per-page" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 163/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Reading settings" \
  --post_content="<p>Set your homepage and how many posts to display.</p>

<h2>Related Features</h2>
<ul>
<li>Reading settings</li>
<li>Reading settings</li>
</ul>" \
  --post_name="settings-reading-reading-settings" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 164/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Default post category" \
  --post_content="<p>Posts are automatically assigned to this category if none is selected.</p>

<h2>Related Features</h2>
<ul>
<li>Default post category</li>
<li>Default post category</li>
</ul>" \
  --post_name="settings-writing-default-post-category" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 165/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Default post format" \
  --post_content="<p>Choose the default format for new posts (Standard, Aside, Gallery, etc).</p>

<h2>Related Features</h2>
<ul>
<li>Default post format</li>
<li>Default post format</li>
</ul>" \
  --post_name="settings-writing-default-post-format" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 166/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Writing settings" \
  --post_content="<p>Configure default post format, category, and post settings.</p>

<h2>Related Features</h2>
<ul>
<li>Writing settings</li>
<li>Writing settings</li>
</ul>" \
  --post_name="settings-writing-writing-settings" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 167/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Theme File Editor" \
  --post_content="<p>Edit theme template and CSS files directly. Be careful—errors can break your site. Consider using a child theme instead.</p>

<h2>Related Features</h2>
<ul>
<li>Theme File Editor</li>
<li>Theme File Editor</li>
</ul>" \
  --post_name="theme-editor-theme-file-editor" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 168/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Choose a theme" \
  --post_content="<p>Browse and activate themes to change your site layout and style.</p>

<h2>Related Features</h2>
<ul>
<li>Choose a theme</li>
<li>Choose a theme</li>
</ul>" \
  --post_name="themes-choose-a-theme" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 169/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Invite user" \
  --post_content="<p>Add a new user to your site with a specific role.</p>

<h2>Related Features</h2>
<ul>
<li>Invite user</li>
<li>Invite user</li>
</ul>" \
  --post_name="user-new-invite-user" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 170/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Password" \
  --post_content="<p>Your login password. Use a strong password with letters, numbers, and symbols.</p>

<h2>Related Features</h2>
<ul>
<li>Password</li>
<li>Password</li>
</ul>" \
  --post_name="user-new-password" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 171/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Send user notification" \
  --post_content="<p>Email the new user their login credentials and welcome message.</p>" \
  --post_name="user-new-send-user-notification" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 172/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="User role" \
  --post_content="<p>The permission level determines what this user can do on the site.</p>

<h2>Related Features</h2>
<ul>
<li>User role</li>
<li>User role</li>
</ul>" \
  --post_name="user-new-user-role" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 173/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Username" \
  --post_content="<p>Your login username (cannot be changed).</p>

<h2>Related Features</h2>
<ul>
<li>Username</li>
<li>Username</li>
</ul>" \
  --post_name="user-new-username" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"beginner","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 174/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="All users" \
  --post_content="<p>View, edit, or remove user accounts from your site.</p>

<h2>Related Features</h2>
<ul>
<li>All users</li>
<li>All users</li>
</ul>" \
  --post_name="users-all-users" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

# Article 175/175
docker-compose exec -T wordpress-test wp --allow-root post create \
  --post_type=wpshadow_kb \
  --post_title="Manage widgets" \
  --post_content="<p>Add and arrange widgets in your site sidebar and other areas.</p>

<h2>Related Features</h2>
<ul>
<li>Manage widgets</li>
<li>Manage widgets</li>
</ul>" \
  --post_name="widgets-manage-widgets" \
  --post_status=publish \
  --meta_input='{"_wpshadow_difficulty":"intermediate","_wpshadow_read_time":"3"}'
 && CREATED=$((CREATED+1))

echo ""
echo "Created $CREATED KB articles!"
