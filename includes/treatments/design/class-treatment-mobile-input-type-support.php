<?php
/**
 * Mobile Input Type Support Treatment
 *
 * Validates that form inputs use appropriate HTML5 input types for mobile
 * keyboard optimization (email, tel, url, number, date, etc.).
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Input Type Support Treatment Class
 *
 * Scans forms to ensure mobile-optimized input types are used instead of
 * generic text inputs. Proper input types trigger optimized mobile keyboards
 * (numeric keypad for tel, email keyboard for email, etc.).
 *
 * WCAG Reference: 3.2.2 On Input (Level A)
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Input_Type_Support extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-input-type-support';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Input Type Support';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that form inputs use appropriate HTML5 input types for mobile keyboard optimization';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Input_Type_Support' );
	}
}
