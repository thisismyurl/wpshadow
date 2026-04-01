<?php
/**
 * Comment Cookies Consent Treatment
 *
 * Verifies comment cookie consent is properly configured for GDPR compliance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Cookies Consent Treatment Class
 *
 * Checks comment cookie consent configuration for privacy compliance.
 *
 * @since 0.6093.1200
 */
class Treatment_Comment_Cookies_Consent extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-cookies-consent';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Cookies Consent';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment cookie consent for GDPR';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Cookies_Consent' );
	}
}
