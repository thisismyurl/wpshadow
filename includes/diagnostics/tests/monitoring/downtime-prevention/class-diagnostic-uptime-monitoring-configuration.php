<?php
/**
 * Uptime Monitoring Configuration Diagnostic
 *
 * Checks if uptime monitoring is configured for downtime alerts.
 *
 * @package WPShadow\Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Uptime Monitoring Configuration
 *
 * Detects whether uptime monitoring is set up for business continuity.
 */
class Diagnostic_Uptime_Monitoring_Configuration extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'uptime-monitoring-configuration';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Uptime Monitoring Configuration';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for uptime monitoring and alerting';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'downtime-prevention';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$plugins = array(
			'uptime-monitor/uptime-monitor.php'              => 'Uptime Monitor',
			'site-offline/site-offline.php'                 => 'Site Offline',
			'jetpack/jetpack.php'                            => 'Jetpack',
		);

		$active = array();
		foreach ( $plugins as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$active[] = $name;
			}
		}

		$stats['uptime_monitoring_active'] = count( $active );
		$stats['uptime_plugins'] = $active;

		// Check if WordPress ping services are configured
		$ping_services = get_option( 'blog_public' );
		$stats['blog_public'] = (bool) $ping_services;

		if ( empty( $active ) ) {
			$issues[] = __( 'No uptime monitoring service detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Uptime monitoring tracks your site availability and alerts you to downtime immediately. Every minute of downtime costs lost revenue, damaged reputation, and SEO penalties. Proactive monitoring minimizes impact from unexpected outages.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/uptime-monitoring',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
