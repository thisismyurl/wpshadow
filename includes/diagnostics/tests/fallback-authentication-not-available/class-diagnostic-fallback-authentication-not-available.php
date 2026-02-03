<?php
/**
 * Fallback Authentication Not Available Diagnostic
 *
 * Checks fallback auth.
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
 * Diagnostic_Fallback_Authentication_Not_Available Class
 *
 * Performs diagnostic check for Fallback Authentication Not Available.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Fallback_Authentication_Not_Available extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'fallback-authentication-not-available';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Fallback Authentication Not Available';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks fallback auth';

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
						'provide_fallback_authentication' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Fallback authentication not available. Provide backup authentication method when primary service is unavailable.',
						'severity'   =>   'medium',
						'threat_level'   =>   45,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/fallback-authentication-not-available'
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
