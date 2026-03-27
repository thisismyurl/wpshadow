<?php
/**
 * Mobile Form Auto-fill Support Treatment
 *
 * Validates that form inputs have appropriate autocomplete attributes for
 * mobile auto-fill functionality.
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Form Auto-fill Support Treatment Class
 *
 * Checks that forms use HTML5 autocomplete attributes to enable mobile
 * browsers' auto-fill features, dramatically improving form completion speed.
 *
 * WCAG Reference:1.0 Identify Input Purpose (Level AA)
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Form_Autofill_Support extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-form-autofill-support';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Form Auto-fill Support';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that form inputs have appropriate autocomplete attributes for mobile auto-fill functionality';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Form_Autofill_Support' );
	}
}
