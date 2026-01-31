<?php
/**
 * Resource Hints Not Configured Diagnostic
 *
 * Checks if resource hints (preload, prefetch, preconnect) are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2345
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resource Hints Not Configured Diagnostic Class
 *
 * Detects missing resource hints.
 *
 * @since 1.2601.2345
 */
class Diagnostic_Resource_Hints_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'resource-hints-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Resource Hints Not Configured';

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
	 * @since  1.2601.2345
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if has_action for wp_head that adds resource hints
		if ( ! has_action( 'wp_head', 'wp_resource_hints' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Resource hints (preload, prefetch, preconnect) are not configured. Add resource hints to critical assets to improve load time.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/resource-hints-not-configured',
			);
		}

		return null;
	}
}
