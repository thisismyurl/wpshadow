<?php
/**
 * GDPR Privacy Policy Treatment
 *
 * Checks if GDPR privacy policy is present and accessible.
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
 * GDPR Privacy Policy Treatment Class
 *
 * Verifies that a GDPR-compliant privacy policy is present and
 * that customer data handling is documented.
 *
 * @since 1.6093.1200
 */
class Treatment_GDPR_Privacy_Policy extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-privacy-policy';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Privacy Policy';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if GDPR privacy policy is present and accessible';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the GDPR privacy policy treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if GDPR issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_GDPR_Privacy_Policy' );
	}
}
