<?php
/**
 * Query String Tracking Not Configured Diagnostic
 *
 * Checks if query string tracking is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query String Tracking Not Configured Diagnostic Class
 *
 * Detects missing query string tracking.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Query_String_Tracking_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'query-string-tracking-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Query String Tracking Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if query string tracking is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for UTM parameter tracking
		if ( ! has_filter( 'init', 'track_utm_parameters' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Query string tracking is not configured. Implement UTM parameter tracking to identify traffic sources and attribute conversions to specific campaigns.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/query-string-tracking-not-configured',
			);
		}

		return null;
	}
}
