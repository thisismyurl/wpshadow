<?php
/**
 * Wordpress Image Regeneration Diagnostic
 *
 * Wordpress Image Regeneration issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1261.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Image Regeneration Diagnostic Class
 *
 * @since 1.1261.0000
 */
class Diagnostic_WordpressImageRegeneration extends Diagnostic_Base {

	protected static $slug = 'wordpress-image-regeneration';
	protected static $title = 'Wordpress Image Regeneration';
	protected static $description = 'Wordpress Image Regeneration issue detected';
	protected static $family = 'functionality';

	public static function check() {
		global $wpdb;
		$issues = array();
		
		// Check 1: Get registered image sizes
		$image_sizes = get_intermediate_image_sizes();
		$custom_sizes = array();
		
		foreach ( $image_sizes as $size ) {
			if ( ! in_array( $size, array( 'thumbnail', 'medium', 'medium_large', 'large' ), true ) ) {
				$custom_sizes[] = $size;
			}
		}
		
		if ( count( $custom_sizes ) > 10 ) {
			$issues[] = sprintf( __( '%d custom image sizes registered (storage bloat)', 'wpshadow' ), count( $custom_sizes ) );
		}
		
		// Check 2: Images missing sizes
		$total_attachments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'"
		);
		
		if ( $total_attachments > 100 ) {
			// Sample check for missing metadata
			$missing_meta = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} p
					 LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
					 WHERE p.post_type = %s 
					 AND p.post_mime_type LIKE 'image/%%'
					 AND pm.meta_id IS NULL",
					'_wp_attachment_metadata',
					'attachment'
				)
			);
			
			if ( $missing_meta > 10 ) {
				$issues[] = sprintf( __( '%d images missing metadata (regeneration needed)', 'wpshadow' ), $missing_meta );
			}
		}
		
		// Check 3: Very large original images
		$large_originals = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}
			 WHERE meta_key = '_wp_attached_file'
			 AND meta_value REGEXP '\\.(jpg|jpeg|png)$'
			 LIMIT 1000"
		);
		
		if ( $large_originals > 0 ) {
			$big_image_threshold = apply_filters( 'big_image_size_threshold', 2560 );
			if ( $big_image_threshold > 3000 ) {
				$issues[] = sprintf( __( 'Big image threshold: %dpx (original files very large)', 'wpshadow' ), $big_image_threshold );
			}
		}
		
		// Check 4: Regeneration plugins installed
		$regen_plugins = array(
			'regenerate-thumbnails/regenerate-thumbnails.php',
			'force-regenerate-thumbnails/force-regenerate-thumbnails.php',
		);
		
		$has_regen_tool = false;
		foreach ( $regen_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_regen_tool = true;
				break;
			}
		}
		
		if ( ! $has_regen_tool && $total_attachments > 500 ) {
			$issues[] = __( 'No thumbnail regeneration plugin (manual process)', 'wpshadow' );
		}
		
		// Check 5: Upload directory permissions
		$upload_dir = wp_upload_dir();
		if ( ! wp_is_writable( $upload_dir['basedir'] ) ) {
			$issues[] = __( 'Upload directory not writable (regeneration will fail)', 'wpshadow' );
		}
		
		
		// Check 6: Feature initialization
		if ( ! (get_option( "features_init" ) !== false) ) {
			$issues[] = __( 'Feature initialization', 'wpshadow' );
		}

		// Check 7: Database tables
		if ( ! (! empty( $GLOBALS["wpdb"] )) ) {
			$issues[] = __( 'Database tables', 'wpshadow' );
		}

		// Check 8: Hook registration
		if ( ! (has_action( "init" )) ) {
			$issues[] = __( 'Hook registration', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = (40 + min(35, count($issues) * 8));
		if ( count( $issues ) >= 4 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of image regeneration issues */
				__( 'WordPress image regeneration has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wordpress-image-regeneration',
		);
	}
}
