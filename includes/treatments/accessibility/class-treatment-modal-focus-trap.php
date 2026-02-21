<?php
/**
 * Modal Focus Trap Treatment
 *
 * Checks if modal dialogs properly trap keyboard focus.
 *
 * @since   1.6035.1400
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modal Focus Trap Treatment Class
 *
 * Validates that modals keep keyboard focus inside until closed.
 *
 * @since 1.6035.1400
 */
class Treatment_Modal_Focus_Trap extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'modal-focus-trap';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Modals Don\'t Trap Focus';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if modal dialogs properly trap keyboard focus';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Modal_Focus_Trap' );
	}
}
