<?php
/**
 * Feature Flag System Not Implemented Diagnostic
 *
 * Checks if feature flags are implemented.
 *
 * @package    WPShadow
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
 * Feature Flag System Not Implemented Diagnostic Class
 *
 * Detects missing feature flags.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Feature_Flag_System_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feature-flag-system-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feature Flag System Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if feature flags are implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for feature flag system
		if ( ! has_filter( 'init', 'check_feature_flag' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Feature flag system is not implemented. Use feature flags to enable/disable features by user segment or percentage to enable gradual rollouts and A/B testing.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/feature-flag-system-not-implemented?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
