<?php
/**
 * Abandoned Feature Removal Not Tracked Diagnostic
 *
 * Checks feature removal.
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
 * Diagnostic_Abandoned_Feature_Removal_Not_Tracked Class
 *
 * Performs diagnostic check for Abandoned Feature Removal Not Tracked.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Abandoned_Feature_Removal_Not_Tracked extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'abandoned-feature-removal-not-tracked';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Abandoned Feature Removal Not Tracked';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks feature removal';

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
		if ( ! has_filter( 'init', 'track_abandoned_features' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Abandoned feature removal tracking is not configured yet. Gradual deprecation tracking helps avoid sudden user impact.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 5,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/abandoned-feature-removal-not-tracked',
			);
		}

		return null;
	}
}
