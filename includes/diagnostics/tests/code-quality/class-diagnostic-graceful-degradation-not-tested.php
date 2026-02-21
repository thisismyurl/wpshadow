<?php
/**
 * Graceful Degradation Not Tested Diagnostic
 *
 * Checks graceful degradation.
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
 * Diagnostic_Graceful_Degradation_Not_Tested Class
 *
 * Performs diagnostic check for Graceful Degradation Not Tested.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Graceful_Degradation_Not_Tested extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'graceful-degradation-not-tested';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Graceful Degradation Not Tested';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks graceful degradation';

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
		if ( ! get_option( 'graceful_degradation_tested' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Graceful degradation testing is not completed yet. Testing with reduced browser capabilities helps ensure core functionality remains usable.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/graceful-degradation-not-tested',
			);
		}

		return null;
	}
}
