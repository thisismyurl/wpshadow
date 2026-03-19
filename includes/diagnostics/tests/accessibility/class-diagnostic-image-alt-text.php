<?php
/**
 * Image Alt Text Diagnostic
 *
 * Checks for missing alt attributes on images throughout the site,
 * ensuring screen reader accessibility and WCAG compliance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Alt Text Diagnostic Class
 *
 * Verifies images have descriptive alt text for screen readers.
 * WCAG 2.1 Level A Success Criterion1.0 (Non-text Content).
 *
 * @since 1.6093.1200
 */
class Diagnostic_Image_Alt_Text extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'image_alt_text';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Image Alt Text';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies images have descriptive alt text for screen readers';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$stats    = array();
		$issues   = array();
		$warnings = array();

		// Check for accessibility plugins that help with alt text.
		$a11y_plugins = array(
			'accessibility-checker/accessibility-checker.php' => 'Accessibility Checker',
			'wp-accessibility/wp-accessibility.php'           => 'WP Accessibility',
			'one-click-accessibility/one-click-accessibility.php' => 'One Click Accessibility',
		);

		$active_a11y = array();
		foreach ( $a11y_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_a11y[] = $plugin_name;
			}
		}

		if ( count( $active_a11y ) > 0 ) {
			$stats['accessibility_plugins'] = implode( ', ', $active_a11y );
		}

		// Count total images in media library.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$total_images = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type LIKE 'image/%'"
		);

		// Count images without alt text.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$no_alt = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
				WHERE p.post_type = 'attachment' 
				AND p.post_mime_type LIKE 'image/%%'
				AND (pm.meta_value IS NULL OR pm.meta_value = '')",
				'_wp_attachment_image_alt'
			)
		);

		$total_images = absint( $total_images );
		$no_alt       = absint( $no_alt );

		$stats['total_images']       = $total_images;
		$stats['images_without_alt'] = $no_alt;

		if ( $total_images > 0 ) {
			$alt_percentage                = ( ( $total_images - $no_alt ) / $total_images ) * 100;
			$stats['alt_text_percentage']  = round( $alt_percentage, 1 ) . '%';

			if ( $no_alt > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of images */
					_n(
						'%d image is missing alt text',
						'%d images are missing alt text',
						$no_alt,
						'wpshadow'
					),
					$no_alt
				);
			}
		} else {
			$warnings[] = 'No images found in media library';
		}

		// Return finding if significant issues detected.
		if ( $no_alt > 5 || ( $total_images > 0 && ( $no_alt / $total_images ) > 0.2 ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of images without alt text, 2: percentage */
					__( 'Found %1$d images without alt text (%2$s of your images). Alt text is like verbal descriptions for people who can\'t see images—essential for screen reader users. Without it, blind visitors miss important visual information. Adding alt text helps 2%% of users who are blind or have low vision, plus it improves your SEO.', 'wpshadow' ),
					$no_alt,
					isset( $stats['alt_text_percentage'] ) ? ( 100 - round( ( ( $total_images - $no_alt ) / $total_images ) * 100, 1 ) ) . '%' : '0%'
				),
				'severity'     => $no_alt > 20 ? 'high' : 'medium',
				'threat_level' => $no_alt > 20 ? 70 : 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-alt-text',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
					'wcag_criterion' => 'WCAG 2.1 Level A -1.0 Non-text Content',
				),
			);
		}

		return null;
	}
}
