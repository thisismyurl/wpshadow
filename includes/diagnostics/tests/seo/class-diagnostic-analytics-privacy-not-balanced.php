<?php
/**
 * Analytics Privacy Not Balanced Diagnostic
 *
 * Checks analytics balance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Analytics_Privacy_Not_Balanced Class
 *
 * Performs diagnostic check for Analytics Privacy Not Balanced.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Analytics_Privacy_Not_Balanced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'analytics-privacy-not-balanced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Analytics Privacy Not Balanced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks analytics balance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !get_option('analytics_privacy_mode' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Analytics privacy not balanced. Use privacy-first analytics (Plausible,
						'severity'   =>   'medium',
						'threat_level'   =>   35,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/analytics-privacy-not-balanced'
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
