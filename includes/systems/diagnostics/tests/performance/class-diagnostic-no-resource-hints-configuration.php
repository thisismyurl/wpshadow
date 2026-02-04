<?php
/**
 * No Resource Hints Configuration Diagnostic
 *
 * Detects when resource hints are not configured,
 * missing opportunities to optimize resource loading.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Resource Hints Configuration
 *
 * Checks whether resource hints (preconnect, prefetch)
 * are configured for optimal loading.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Resource_Hints_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-resource-hints-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Resource Hints Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether resource hints are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check homepage for resource hints
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );
		
		// Check for resource hint types
		$has_preconnect = preg_match( '/<link[^>]*rel=["\']preconnect["\']/i', $body );
		$has_prefetch = preg_match( '/<link[^>]*rel=["\']prefetch["\']/i', $body );
		$has_preload = preg_match( '/<link[^>]*rel=["\']preload["\']/i', $body );

		if ( ! $has_preconnect && ! $has_prefetch && ! $has_preload ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Resource hints aren\'t configured, which misses optimization opportunities. Resource hints tell browser what to load early: preconnect (establish connection to external domain), preload (fetch critical resource immediately), prefetch (download resource for next page). This parallelizes loading, reducing wait time. Use: preconnect for fonts/CDNs, preload for critical CSS/fonts, prefetch for likely next page. Saves 20-200ms per resource. Performance plugins often add these automatically.',
					'wpshadow'
				),
				'severity'      => 'low',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Resource Loading Optimization',
					'potential_gain' => 'Save 20-200ms per optimized resource',
					'roi_explanation' => 'Resource hints enable parallel loading and early connections, reducing resource fetch time 20-200ms each.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/resource-hints-configuration',
			);
		}

		return null;
	}
}
