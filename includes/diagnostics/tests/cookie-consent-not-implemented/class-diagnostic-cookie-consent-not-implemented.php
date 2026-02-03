<?php
/**
 * Cookie Consent Not Implemented Diagnostic
 *
 * Checks cookie consent.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Cookie_Consent_Not_Implemented Class
 *
 * Performs diagnostic check for Cookie Consent Not Implemented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Cookie_Consent_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cookie-consent-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cookie Consent Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks cookie consent';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !get_option('cookie_consent_enabled' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Cookie consent not implemented. Show consent banner and only load tracking after explicit user consent (GDPR/CCPA).',
						'severity'   =>   'high',
						'threat_level'   =>   80,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/cookie-consent-not-implemented'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
