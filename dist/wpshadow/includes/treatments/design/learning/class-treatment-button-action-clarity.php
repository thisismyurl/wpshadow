<?php
/**
 * Button Action Clarity Treatment
 *
 * Issue #4789: Buttons Don't Explain What Happens Next
 * Family: learning (Commandment #8: Inspire Confidence)
 *
 * Checks if button labels clearly explain what will happen.
 * Vague buttons like "Submit" or "Continue" cause uncertainty.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Button_Action_Clarity Class
 *
 * Checks if button labels are specific and clear.
 *
 * @since 1.6093.1200
 */
class Treatment_Button_Action_Clarity extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'button-action-clarity';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Buttons Don\'t Explain What Happens Next';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if buttons use clear, specific action labels instead of generic terms';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Button_Action_Clarity' );
	}
}
