<?php
/**
 * Concatenate Scripts Disabled Diagnostic
 *
 * Checks whether CONCATENATE_SCRIPTS has been explicitly set to false,
 * which forces WordPress to serve every admin script as a separate HTTP
 * request instead of combining them through load-scripts.php.
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
 * Diagnostic_Concatenate_Scripts_Disabled Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Concatenate_Scripts_Disabled extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'concatenate-scripts-disabled';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Admin Script Concatenation Disabled';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether CONCATENATE_SCRIPTS is explicitly set to false. WordPress bundles admin scripts into a single load-scripts.php request by default; disabling this multiplies the number of individual HTTP requests on every admin page load.';

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
	protected static $confidence = 'low';

	/**
	 * Run the diagnostic check.
	 *
	 * WordPress sets CONCATENATE_SCRIPTS to true by default (represented by a
	 * combined load-scripts.php?c=1 request in the browser). If a developer or
	 * plugin has explicitly defined it as false, every bundled admin script is
	 * served as a standalone request, increasing HTTP overhead significantly.
	 *
	 * Note: SCRIPT_DEBUG=true also disables concatenation as a side-effect. The
	 * class-diagnostic-script-debug-production.php diagnostic handles that case.
	 * This diagnostic only flags the explicit CONCATENATE_SCRIPTS=false pattern.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if concatenation is explicitly disabled, null when healthy.
	 */
	public static function check(): ?array {
		// Only flag when the constant is explicitly defined as false.
		// When SCRIPT_DEBUG is true, WordPress internally disables concatenation
		// but that is already caught by the script-debug-production diagnostic.
		if ( ! defined( 'CONCATENATE_SCRIPTS' ) ) {
			return null;
		}

		if ( CONCATENATE_SCRIPTS !== false ) {
			return null;
		}

		// Confirm SCRIPT_DEBUG is not the root cause (avoid double-reporting).
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __(
				'CONCATENATE_SCRIPTS is explicitly set to false in wp-config.php or a plugin. WordPress normally combines all admin JavaScript into a single load-scripts.php request per page, reducing HTTP round-trips. With this disabled, every individual script (jQuery, Backbone, the block editor libraries, and all plugin scripts) is fetched as a separate HTTP request on every admin page load, significantly increasing backend latency for editors and administrators.',
				'wpshadow'
			),
			'severity'     => 'medium',
			'threat_level' => 40,
			'kb_link'      => '',
			'details'      => array(
				'constant'           => 'CONCATENATE_SCRIPTS',
				'current_value'      => 'false',
				'recommended_value'  => 'true (or removed)',
				'note'               => __(
					'WPShadow can comment out the define automatically. Alternatively, remove define(\'CONCATENATE_SCRIPTS\', false) from wp-config.php manually. This setting is intended for debugging only and should not be present on production sites.',
					'wpshadow'
				),
			),
		);
	}
}
