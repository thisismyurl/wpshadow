<?php
/**
 * Privacy Policy Content Treatment
 *
 * Analyzes privacy policy content to ensure it covers essential topics
 * required by GDPR and other privacy regulations.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1600
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Policy Content Treatment Class
 *
 * Validates that privacy policy content includes required sections
 * and disclosures for compliance.
 *
 * @since 1.6032.1600
 */
class Treatment_Privacy_Policy_Content extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'privacy-policy-content';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy Content';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates privacy policy content completeness';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the treatment check.
	 *
	 * Checks:
	 * - Privacy policy covers data collection
	 * - Covers data usage and sharing
	 * - Covers user rights (GDPR requirements)
	 * - Has contact information
	 * - Has been recently updated
	 *
	 * @since  1.6032.1600
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Privacy_Policy_Content' );
	}
}
