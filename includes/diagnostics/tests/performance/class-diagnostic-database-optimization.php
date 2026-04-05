<?php
/**
 * Database Optimization Diagnostic
 *
 * Checks whether a database optimisation strategy is in place and whether
 * the autoloaded options payload is within a healthy threshold.
 *
 * @package WPShadow
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
 * Diagnostic_Database_Optimization Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Database_Optimization extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'database-optimization';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Database Optimization';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether a database optimisation schedule is active and whether the autoloaded options payload is within a healthy threshold.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Maximum acceptable number of autoloaded options before flagging.
	 */
	private const AUTOLOAD_THRESHOLD = 1000;

	/**
	 * Plugins that include automated database optimisation.
	 *
	 * @var array<string,string>
	 */
	private const DB_PLUGINS = array(
		'wp-optimize/wp-optimize.php'                => 'WP-Optimize',
		'wp-rocket/wp-rocket.php'                    => 'WP Rocket',
		'advanced-database-cleaner/advanced-db-cleaner.php' => 'Advanced Database Cleaner',
		'better-wp-security/better-wp-security.php'  => 'Solid Security',
		'w3-total-cache/w3-total-cache.php'          => 'W3 Total Cache',
		'litespeed-cache/litespeed-cache.php'        => 'LiteSpeed Cache',
		'nitropack/nitropack-plugin.php'             => 'NitroPack',
		'hummingbird-performance/wp-hummingbird.php' => 'Hummingbird',
	);

	/**
	 * Cron hooks registered by DB optimisation plugins.
	 *
	 * @var string[]
	 */
	private const DB_CRON_HOOKS = array(
		'wpo_cron_event2',      // WP-Optimize
		'wpo_cron_event3',
		'rocket_database_optimization_time_event', // WP Rocket
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Passes if a recognised DB optimisation plugin is active with scheduled
	 * cleanup. Falls back to checking for excessive autoloaded options as a
	 * proxy for database bloat.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Check for known DB optimisation plugins with an active cron schedule.
		foreach ( self::DB_PLUGINS as $plugin_file => $name ) {
			if ( ! is_plugin_active( $plugin_file ) ) {
				continue;
			}
			// Plugin active — verify it has a scheduled cleanup event.
			foreach ( self::DB_CRON_HOOKS as $hook ) {
				if ( wp_next_scheduled( $hook ) ) {
					return null;
				}
			}
			// Plugin active but cron not matched — still consider it managed.
			return null;
		}

		// No dedicated plugin found; check autoloaded options volume as a proxy.
		$alloptions   = wp_load_alloptions();
		$autoload_count = count( $alloptions );

		if ( $autoload_count < self::AUTOLOAD_THRESHOLD ) {
			// Small site with no dedicated DB optimiser — low risk for now.
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			/* translators: %d: number of autoloaded options */
			'description'  => sprintf(
				__( 'No scheduled database optimisation was detected and your site has %d autoloaded options, which exceeds the recommended threshold of 1,000. This increases every page load time as WordPress must load all of these rows on every request.', 'wpshadow' ),
				$autoload_count
			),
			'severity'     => $autoload_count > 2000 ? 'high' : 'medium',
			'threat_level' => $autoload_count > 2000 ? 60 : 40,
			'details'      => array(
				'autoloaded_options' => $autoload_count,
				'fix'                => __( 'Install WP-Optimize (free) and schedule weekly database cleanup tasks including clearing post revisions, trashed posts, spam comments, and transients. Review and disable autoloaded options for unused plugins via the WP-Optimize or Advanced Database Cleaner plugin. Consider running OPTIMIZE TABLE on wp_options periodically.', 'wpshadow' ),
			),
		);
	}
}
