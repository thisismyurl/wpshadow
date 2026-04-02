<?php
/**
 * Mobile Touch Target Feedback Treatment
 *
 * Ensures touch interactions provide visible feedback.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Touch Target Feedback Treatment Class
 *
 * Ensures touch interactions provide visible feedback through :active and :hover
 * states, making interactions feel responsive and intentional.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Touch_Target_Feedback extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-touch-target-feedback';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Touch Target Feedback';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure touch interactions provide visible feedback for responsiveness';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Touch_Target_Feedback' );
	}
}
