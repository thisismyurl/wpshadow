<?php
/**
 * Accept-Language Header Abuse Not Prevented Diagnostic
 *
 * Checks Accept-Language abuse.
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
 * Diagnostic_Accept_Language_Header_Abuse_Not_Prevented Class
 *
 * Performs diagnostic check for Accept Language Header Abuse Not Prevented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Accept_Language_Header_Abuse_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'accept-language-header-abuse-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Accept-Language Header Abuse Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks Accept-Language abuse';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'validate_accept_language' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Accept-Language header abuse not prevented. Validate language codes and prevent header injection attacks.',
						'severity'   =>   'medium',
						'threat_level'   =>   30,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/accept-language-header-abuse-not-prevented'
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
