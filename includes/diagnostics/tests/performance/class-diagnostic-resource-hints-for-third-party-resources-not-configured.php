<?php
/**
 * Resource Hints For Third Party Resources Not Configured Diagnostic
 *
 * Checks if resource hints are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resource Hints For Third Party Resources Not Configured Diagnostic Class
 *
 * Detects missing resource hints.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Resource_Hints_For_Third_Party_Resources_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'resource-hints-for-third-party-resources-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Resource Hints For Third Party Resources Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if resource hints are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for resource hints
		if ( ! has_filter( 'wp_head', 'add_resource_hints' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Resource hints are not configured. Use preconnect, prefetch, and preload hints for third-party CDNs and resources to improve performance.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/resource-hints-for-third-party-resources-not-configured',
			);
		}

		return null;
	}
}
