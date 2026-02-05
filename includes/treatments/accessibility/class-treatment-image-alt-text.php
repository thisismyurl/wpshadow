<?php
/**
 * Treatment: Add Image Alt Text
 *
 * Adds descriptive alt text to images missing it in the media library.
 * Essential for screen reader accessibility and WCAG compliance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Treatment_Image_Alt_Text Class
 *
 * Adds alt text to images for screen reader accessibility.
 * WCAG 2.1 Level A Success Criterion 1.1.1 (Non-text Content).
 *
 * @since 1.6050.1300
 */
class Treatment_Image_Alt_Text extends Treatment_Base {

/**
 * Get the finding ID this treatment addresses.
 *
 * @since  1.6050.1300
 * @return string Finding ID.
 */
public static function get_finding_id() {
return 'image_alt_text';
}

/**
 * Apply the treatment.
 *
 * Adds alt text to images missing it in the media library.
 *
 * @since  1.6050.1300
 * @return array {
 *     Result array.
 *
 *     @type bool   $success Whether treatment succeeded.
 *     @type string $message Human-readable result message.
 *     @type array  $details Additional details about changes made.
 * }
 */
public static function apply() {
global $wpdb;

// Find images without alt text.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$images_without_alt = $wpdb->get_results(
$wpdb->prepare(
"SELECT p.ID, p.post_title, p.guid
FROM {$wpdb->posts} p
LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
WHERE p.post_type = 'attachment' 
AND p.post_mime_type LIKE 'image/%%'
AND (pm.meta_value IS NULL OR pm.meta_value = '')
LIMIT 500",
'_wp_attachment_image_alt'
)
);

if ( empty( $images_without_alt ) ) {
return array(
'success' => true,
'message' => __( 'All images already have alt text', 'wpshadow' ),
'details' => array(
'updated_count' => 0,
),
);
}

$updated_count = 0;
$skipped_count = 0;
$sample_updates = array();

foreach ( $images_without_alt as $image ) {
$alt_text = self::generate_alt_text( $image );

if ( empty( $alt_text ) ) {
$skipped_count++;
continue;
}

// Update alt text.
$result = update_post_meta( $image->ID, '_wp_attachment_image_alt', $alt_text );

if ( $result ) {
$updated_count++;

// Store first 5 for reporting.
if ( count( $sample_updates ) < 5 ) {
$sample_updates[] = array(
'id'       => $image->ID,
'filename' => basename( $image->guid ),
'alt_text' => $alt_text,
);
}
}
}

if ( $updated_count === 0 ) {
return array(
'success' => false,
'message' => __( 'Could not generate alt text for any images', 'wpshadow' ),
'details' => array(
'attempted' => count( $images_without_alt ),
'skipped'   => $skipped_count,
),
);
}

return array(
'success' => true,
'message' => sprintf(
/* translators: %d: number of images updated */
_n(
'Added alt text to %d image',
'Added alt text to %d images',
$updated_count,
'wpshadow'
),
$updated_count
),
'details' => array(
'updated_count'  => $updated_count,
'skipped_count'  => $skipped_count,
'total_processed' => count( $images_without_alt ),
'sample_updates'  => $sample_updates,
'wcag_compliance' => 'WCAG 2.1 Level A - 1.1.1 Non-text Content',
'impact'          => __( 'Screen readers can now describe these images to visually impaired users', 'wpshadow' ),
'seo_benefit'     => __( 'Alt text improves image SEO and helps Google understand your content', 'wpshadow' ),
),
);
}

/**
 * Generate alt text from image data.
 *
 * @since  1.6050.1300
 * @param  object $image Image post object.
 * @return string Generated alt text.
 */
private static function generate_alt_text( $image ) {
// Strategy 1: Use post title if it's descriptive.
$title = trim( $image->post_title );

// Remove common default patterns.
$default_patterns = array(
'/^IMG[-_]\d+$/i',           // IMG-1234
'/^DSC[-_]\d+$/i',           // DSC-5678
'/^Screenshot[-_]/',         // Screenshot-at-...
'/^Untitled[-_]?\d*$/i',     // Untitled or Untitled-1
'/^Image[-_]?\d*$/i',        // Image or Image-1
'/^\d{8,}$/i',               // Just numbers (timestamps)
);

$is_default = false;
foreach ( $default_patterns as $pattern ) {
if ( preg_match( $pattern, $title ) ) {
$is_default = true;
break;
}
}

// If title is not a default pattern and has substance, use it.
if ( ! $is_default && strlen( $title ) > 3 ) {
// Clean up the title.
$alt = $title;

// Remove file extensions.
$alt = preg_replace( '/\.(jpe?g|png|gif|webp|svg)$/i', '', $alt );

// Replace hyphens and underscores with spaces.
$alt = str_replace( array( '-', '_' ), ' ', $alt );

// Remove multiple spaces.
$alt = preg_replace( '/\s+/', ' ', $alt );

// Capitalize first letter.
$alt = ucfirst( trim( $alt ) );

return $alt;
}

// Strategy 2: Generate from filename.
$filename = basename( $image->guid );

// Remove extension.
$filename = preg_replace( '/\.(jpe?g|png|gif|webp|svg)$/i', '', $filename );

// Check if filename is auto-generated.
foreach ( $default_patterns as $pattern ) {
if ( preg_match( $pattern, $filename ) ) {
// Can't generate meaningful alt from auto-generated filename.
return '';
}
}

// Convert filename to readable text.
$alt = $filename;

// Replace hyphens and underscores with spaces.
$alt = str_replace( array( '-', '_' ), ' ', $alt );

// Remove multiple spaces.
$alt = preg_replace( '/\s+/', ' ', $alt );

// Capitalize first letter of each word.
$alt = ucwords( trim( $alt ) );

// Only return if it's at least 3 characters.
return strlen( $alt ) > 3 ? $alt : '';
}
}
