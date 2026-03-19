<?php
/**
 * Design Tool Integration Missing Diagnostic
 *
 * Detects when design tools (Canva, Figma, Adobe Express) are not
 * integrated, requiring manual export and upload workflows.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Design Tool Integration Missing Diagnostic Class
 *
 * Checks if design tools are integrated. Manual export/upload from
 * Canva, Figma, or Adobe wastes time and breaks workflows.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Design_Tool_Integration_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'design-tool-integration-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Design Tool Integration (Canva, Figma, Adobe)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing direct integrations with design platforms';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'workflow';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if design tools are integrated. Direct integration saves
	 * 15-30 minutes per image workflow.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Don't flag if Integration module is already active.
		if ( Upgrade_Path_Helper::has_pro_product( 'wpadmin-integration' ) ) {
			return null;
		}

		// Check for existing design tool integrations.
		if ( self::has_design_integration() ) {
			return null;
		}

		// Detect design tool usage from image EXIF data.
		$detected_tools = self::detect_design_tools();

		// Don't flag if no design tool signatures detected.
		if ( empty( $detected_tools ) ) {
			return null;
		}

		// Estimate manual uploads per week (admins uploading images).
		$manual_uploads_per_week = self::estimate_manual_uploads();

		// Don't flag if very few uploads (<5/week).
		if ( $manual_uploads_per_week < 5 ) {
			return null;
		}

		return array(
			'id'                      => self::$slug,
			'title'                   => self::$title,
			'description'             => sprintf(
				/* translators: %s: comma-separated list of detected tools */
				__( 'Your images show signs of manual exports from %s. Direct integration would save 15-30 minutes per image, sync brand templates automatically, and allow designers to publish directly.', 'wpshadow' ),
				implode( ', ', $detected_tools )
			),
			'severity'                => 'low',
			'threat_level'            => 15,
			'auto_fixable'            => false,
			'manual_uploads_per_week' => $manual_uploads_per_week,
			'time_savings_per_image'  => '15-30 minutes',
			'detected_tools'          => $detected_tools,
			'kb_link'                 => 'https://wpshadow.com/kb/design-tool-integration',
		);
	}

	/**
	 * Check if design integration is already enabled.
	 *
	 * @since 1.6093.1200
	 * @return bool True if design integration detected.
	 */
	private static function has_design_integration() {
		$integration_plugins = array(
			'canva/canva.php',
			'figma-wordpress/figma-wordpress.php',
			'adobe-express/adobe-express.php',
		);

		foreach ( $integration_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Detect design tools from image metadata.
	 *
	 * @since 1.6093.1200
	 * @return array Array of detected tool names.
	 */
	private static function detect_design_tools() {
		global $wpdb;
		$detected = array();

		// Check for Canva signatures in image metadata.
		$canva_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type LIKE 'image/%'
			AND (post_title LIKE '%canva%' OR post_name LIKE '%canva%')"
		);

		if ( $canva_count > 0 ) {
			$detected[] = 'Canva';
		}

		// Check for Figma exports (often have 'figma' in filename).
		$figma_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type LIKE 'image/%'
			AND (post_title LIKE '%figma%' OR post_name LIKE '%figma%')"
		);

		if ( $figma_count > 0 ) {
			$detected[] = 'Figma';
		}

		// Check for Adobe Express exports.
		$adobe_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type LIKE 'image/%'
			AND (post_title LIKE '%adobe%' OR post_name LIKE '%spark%')"
		);

		if ( $adobe_count > 0 ) {
			$detected[] = 'Adobe Express';
		}

		return $detected;
	}

	/**
	 * Estimate manual image uploads per week.
	 *
	 * @since 1.6093.1200
	 * @return int Estimated uploads per week.
	 */
	private static function estimate_manual_uploads() {
		global $wpdb;

		// Count images uploaded by admins in last 30 days.
		$thirty_days_ago = date( 'Y-m-d H:i:s', strtotime( '-30 days' ) );

		$recent_uploads = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_type = 'attachment' 
				AND post_mime_type LIKE 'image/%%'
				AND post_date > %s",
				$thirty_days_ago
			)
		);

		// Convert to per-week average.
		return (int) round( $recent_uploads / 4 );
	}
}
