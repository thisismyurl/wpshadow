<?php
/**
 * Broken Links Monitored Diagnostic
 *
 * Tests if broken links are monitored and fixed.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Broken Links Monitored Diagnostic Class
 *
 * Verifies that a broken link monitoring system is in place.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Monitors_Broken_Links extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'monitors-broken-links';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Broken Links Monitored';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if broken links are monitored and fixed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$checker_plugins = array(
			'broken-link-checker/broken-link-checker.php',
			'broken-link-checker-pro/broken-link-checker.php',
		);

		foreach ( $checker_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return null;
			}
		}

		$last_scan = (int) get_option( 'wpshadow_broken_links_last_scan' );
		if ( $last_scan ) {
			$days = floor( ( time() - $last_scan ) / DAY_IN_SECONDS );
			if ( $days <= 30 ) {
				return null;
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No broken link monitoring detected. Check for broken links monthly to protect SEO and user experience.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/broken-links-monitored?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'persona'      => 'publisher',
		);
	}
}
