<?php
/**
 * Graceful Feature Degradation Not Tested Diagnostic
 *
 * Checks feature degradation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Graceful_Feature_Degradation_Not_Tested Class
 *
 * Performs diagnostic check for Graceful Feature Degradation Not Tested.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Graceful_Feature_Degradation_Not_Tested extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'graceful-feature-degradation-not-tested';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Graceful Feature Degradation Not Tested';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks feature degradation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'test_feature_degradation' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Graceful feature degradation testing is not in place yet. Verifying fallback behavior helps keep experiences stable when dependencies are unavailable.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/graceful-feature-degradation-not-tested',
			);
		}

		return null;
	}
}
