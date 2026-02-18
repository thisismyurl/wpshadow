<?php
/**
 * Target Keywords Tracked Diagnostic
 *
 * Tests if keyword rankings are actively monitored.
 *
 * @since   1.6050.0000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Target Keywords Tracked Diagnostic Class
 *
 * Verifies that target keywords are tracked using tools or reports.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Tracks_Target_Keywords extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tracks-target-keywords';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Target Keywords Tracked';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if keyword rankings are actively monitored';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_target_keywords_tracked' );
		if ( $manual_flag ) {
			return null;
		}

		if ( self::has_rank_tracking_plugin() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No keyword rank tracking detected. Track target keywords to measure SEO progress.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/target-keywords-tracked',
			'persona'      => 'publisher',
		);
	}

	/**
	 * Check for rank tracking plugins or integrations.
	 *
	 * @since  1.6050.0000
	 * @return bool True if detected.
	 */
	private static function has_rank_tracking_plugin() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = array(
			'serp-tracker/serp-tracker.php',
			'keyword-rank-checker/keyword-rank-checker.php',
			'wp-rank-tracker/wp-rank-tracker.php',
		);

		foreach ( $plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		$last_report = (int) get_option( 'wpshadow_keyword_rank_report' );
		return $last_report > 0;
	}
}
