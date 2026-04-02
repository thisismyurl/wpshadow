<?php
/**
 * Feature Flags Implementation Not Configured Diagnostic
 *
 * Checks if feature flags are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feature Flags Implementation Not Configured Diagnostic Class
 *
 * Detects missing feature flags.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Feature_Flags_Implementation_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feature-flags-implementation-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feature Flags Implementation Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if feature flags are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if feature flags are used
		if ( ! has_filter( 'wp_loaded', 'wp_check_feature_flags' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Feature flags are not implemented. Use feature flags to safely deploy new features and test them with specific user groups.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/feature-flags-implementation-not-configured',
			);
		}

		return null;
	}
}
