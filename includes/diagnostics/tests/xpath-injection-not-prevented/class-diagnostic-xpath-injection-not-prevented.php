<?php
/**
 * XPATH Injection Not Prevented Diagnostic
 *
 * Checks XPATH injection prevention.
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
 * Diagnostic_XPATH_Injection_Not_Prevented Class
 *
 * Performs diagnostic check for Xpath Injection Not Prevented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_XPATH_Injection_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'xpath-injection-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XPATH Injection Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks XPATH injection prevention';

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
						'sanitize_xpath_queries' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('XPATH injection not prevented. Sanitize all XPATH queries and use parameterized queries to prevent injection attacks on XML processing.',
						'severity'   =>   'high',
						'threat_level'   =>   65,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/xpath-injection-not-prevented'
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
