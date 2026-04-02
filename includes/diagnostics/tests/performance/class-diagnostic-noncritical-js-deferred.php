<?php
/**
 * Non-Critical JS Deferred Diagnostic
 *
 * Checks whether a performance plugin handles JavaScript deferral, or whether
 * the active theme avoids registering a high number of render-blocking scripts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Non-Critical JS Deferred Diagnostic Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Noncritical_Js_Deferred extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'noncritical-js-deferred';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Non-Critical JS Deferred';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether a performance plugin is active to defer non-critical JavaScript, reducing render-blocking resources that delay page paint.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Detects JS deferral plugins first, then checks the registered script queue
	 * for scripts without defer/async attributes that may block rendering.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when blocking scripts are detected, null when healthy.
	 */
	public static function check() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		// Performance plugins that handle script deferral.
		$perf_plugins = array(
			'w3-total-cache/w3-total-cache.php'             => 'W3 Total Cache',
			'wp-rocket/wp-rocket.php'                       => 'WP Rocket',
			'wp-optimize/wp-optimize.php'                   => 'WP-Optimize',
			'autoptimize/autoptimize.php'                   => 'Autoptimize',
			'litespeed-cache/litespeed-cache.php'           => 'LiteSpeed Cache',
			'sg-cachepress/sg-cachepress.php'               => 'SiteGround Optimizer',
			'hummingbird-performance/wp-hummingbird.php'    => 'Hummingbird',
			'perfmatters/perfmatters.php'                   => 'Perfmatters',
			'flying-scripts/flying-scripts.php'             => 'Flying Scripts',
			'async-javascript/async-javascript.php'         => 'Async JavaScript',
		);

		foreach ( $perf_plugins as $plugin_file => $plugin_name ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				return null; // Script optimisation is delegated to a performance plugin.
			}
		}

		// No performance plugin: scan the homepage for blocking script tags.
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url, array(
			'timeout'    => 7,
			'user-agent' => 'WPShadow-Diagnostic/1.0',
			'sslverify'  => false,
		) );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );

		// Count <script src="..."> tags without defer or async.
		$blocking_count = preg_match_all( '/<script\s[^>]*src=[^>]*>/i', $body, $matches );
		if ( false === $blocking_count ) {
			return null;
		}

		$total_scripts   = $blocking_count;
		$deferred_count  = 0;
		foreach ( $matches[0] as $tag ) {
			if ( preg_match( '/\bdefer\b|\basync\b/i', $tag ) ) {
				$deferred_count++;
			}
		}

		$blocking_scripts = $total_scripts - $deferred_count;

		if ( $blocking_scripts <= 3 ) {
			return null; // Acceptable number of blocking scripts.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of blocking scripts */
				__( '%d render-blocking JavaScript files were detected on the homepage without defer or async attributes. Blocking scripts pause HTML parsing until they are downloaded and executed, delaying page rendering. Install a performance plugin such as WP Rocket, Autoptimize, or Async JavaScript to defer non-critical scripts.', 'wpshadow' ),
				$blocking_scripts
			),
			'severity'     => 'medium',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/noncritical-js-deferred?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'total_scripts'    => $total_scripts,
				'blocking_scripts' => $blocking_scripts,
				'deferred_scripts' => $deferred_count,
			),
		);
	}
}
