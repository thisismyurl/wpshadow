<?php
/**
 * Form Abandonment Tracking Treatment
 *
 * Checks if form field interactions and abandonment points are tracked.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1035
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Abandonment Tracking Treatment Class
 *
 * If 1,000 people start your form and 500 abandon, you need to know WHERE
 * they quit. Usually 1-2 specific fields cause most dropoff.
 *
 * @since 1.6035.1035
 */
class Treatment_Form_Abandonment_Tracking extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-abandonment-tracking';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Form Abandonment Tracking';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if form field interactions and abandonment points are tracked';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1035
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Form_Abandonment_Tracking' );
	}
}
