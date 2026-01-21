<?php
// Simple SQL insertion approach
$article_file = '/workspaces/wpshadow/docs/KB_ARTICLE_PLUGIN_ACTIVATION_REWRITE.md';

if (!file_exists($article_file)) {
    echo "ERROR: Article file not found at $article_file\n";
    exit(1);
}

$article_content = file_get_contents($article_file);
if (!$article_content) {
    echo "ERROR: Could not read article file\n";
    exit(1);
}

// Load WordPress
define('WP_USE_THEMES', false);
require('/var/www/html/wp-load.php');

// Escape content for SQL
$title = 'How to Activate a WordPress Plugin Safely';
$excerpt = 'Learn the safe way to activate WordPress plugins: compatibility checks, backups, and troubleshooting.';
$post_name = 'how-to-activate-wordpress-plugin-safely';
$guid = 'http://localhost:9000/?p=207';

// Create post array
$post_args = array(
    'ID' => 207,
    'post_type' => 'wpshadow_kb',
    'post_status' => 'publish',
    'post_title' => $title,
    'post_content' => $article_content,
    'post_excerpt' => $excerpt,
    'post_name' => $post_name,
);

// Check if post exists
$existing = get_post(207);
if ($existing) {
    echo "Post 207 exists, updating...\n";
} else {
    echo "Post 207 does not exist, creating...\n";
}

// Insert/update post
$post_id = wp_insert_post($post_args, true);

if (is_wp_error($post_id)) {
    echo "ERROR: " . $post_id->get_error_message() . "\n";
    exit(1);
}

echo "✓ Post 207 created/updated successfully\n";

// Update post meta
update_post_meta($post_id, 'read_time', '5-7 minutes');
update_post_meta($post_id, 'difficulty', 'Beginner');
update_post_meta($post_id, 'category', 'Plugins & Extensions');
update_post_meta($post_id, 'points_available', '135');

echo "✓ Post metadata updated\n";
echo "✓ Content length: " . strlen($article_content) . " bytes\n";
echo "✓ Article is live at: http://localhost:9000/?p=207\n";
