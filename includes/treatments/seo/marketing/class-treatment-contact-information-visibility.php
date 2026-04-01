<?php
/**
 * Contact Information Visibility Treatment
 *
 * Checks whether visitors can easily find contact details or a contact page.
 *
 * @package    WPShadow
 * @subpackage Treatments\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact Information Visibility Treatment Class
 *
 * Verifies that a contact page exists and is discoverable in menus,
 * with at least one contact method available.
 *
 * @since 0.6093.1200
 */
class Treatment_Contact_Information_Visibility extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'contact-information-visibility';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Contact Information Difficult to Find';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if visitors can easily find a contact page or contact method';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Contact_Information_Visibility' );
	}
}
