<?php
/**
 * GDPR Tools Privacy Policy Integration Treatment
 *
 * Detects whether GDPR tool pages link to site privacy policy and
 * explain user rights.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.1900
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GDPR Privacy Policy Integration Treatment Class
 *
 * Ensures GDPR tools are properly integrated with privacy policy
 * and explain user rights for compliance.
 *
 * @since 1.6033.1900
 */
class Treatment_GDPR_Privacy_Policy_Integration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-privacy-policy-integration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Tools Privacy Policy Integration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies GDPR tools link to privacy policy and explain user rights';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the treatment check.
	 *
	 * Checks:
	 * - Privacy policy page is set
	 * - Privacy policy page is published
	 * - GDPR request pages link to privacy policy
	 * - User rights are explained
	 * - GDPR article references present
	 * - Contact information for privacy requests
	 *
	 * @since  1.6033.1900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_GDPR_Privacy_Policy_Integration' );
	}
}
