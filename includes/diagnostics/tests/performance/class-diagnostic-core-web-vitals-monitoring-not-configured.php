<?php
/**
 * Core Web Vitals Monitoring Not Configured Diagnostic
 *
 * Checks if Core Web Vitals are monitored.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2335
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core Web Vitals Monitoring Not Configured Diagnostic Class
 *
 * Detects missing Core Web Vitals monitoring.
 *
 * @since 1.2601.2335
 */
class Diagnostic_Core_Web_Vitals_Monitoring_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'core-web-vitals-monitoring-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Core Web Vitals Monitoring Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Core Web Vitals are monitored';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2335
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for Core Web Vitals monitoring plugins
		$cwv_plugins = array(
			'google-pagespeed-insights/pagespeed-insights.php',
			'web-vitals/web-vitals.php',
		);

		$cwv_active = false;
		foreach ( $cwv_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$cwv_active = true;
				break;
			}
		}

		if ( ! $cwv_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Core Web Vitals (LCP, FID, CLS) are not being monitored. Track these metrics to optimize for search ranking.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/core-web-vitals-monitoring-not-configured',
			);
		}

		return null;
	}
}
