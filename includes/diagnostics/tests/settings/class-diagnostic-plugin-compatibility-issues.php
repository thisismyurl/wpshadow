<?php
/**
 * Plugin Compatibility Issues Diagnostic
 *
 * Detects common plugin conflict pairs and incompatibilities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Compatibility Issues Diagnostic
 *
 * Checks for known conflict pairs among active plugins.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Plugin_Compatibility_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-compatibility-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Compatibility Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects common plugin conflict pairs and incompatibilities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );
		$issues = array();

		$conflicts = array(
			array( 'wp-rocket/wp-rocket.php', 'w3-total-cache/w3-total-cache.php', __( 'Multiple cache plugins detected', 'wpshadow' ) ),
			array( 'wp-super-cache/wp-cache.php', 'w3-total-cache/w3-total-cache.php', __( 'Multiple cache plugins detected', 'wpshadow' ) ),
			array( 'wordpress-seo/wp-seo.php', 'seo-by-rank-math/rank-math.php', __( 'Multiple SEO plugins detected', 'wpshadow' ) ),
			array( 'wordfence/wordfence.php', 'sucuri-scanner/sucuri.php', __( 'Multiple security plugins may conflict', 'wpshadow' ) ),
			array( 'elementor/elementor.php', 'beaver-builder-lite-version/fl-builder.php', __( 'Multiple page builders active', 'wpshadow' ) ),
		);

		foreach ( $conflicts as $conflict ) {
			list( $plugin_a, $plugin_b, $message ) = $conflict;
			if ( in_array( $plugin_a, $active_plugins, true ) && in_array( $plugin_b, $active_plugins, true ) ) {
				$issues[] = $message;
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Potential plugin compatibility conflicts detected', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/plugin-compatibility-issues?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'issues' => $issues,
			),
		);
	}
}
