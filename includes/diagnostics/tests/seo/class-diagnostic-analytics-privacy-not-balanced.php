<?php
/**
 * Analytics Privacy Not Balanced Diagnostic
 *
 * Checks analytics balance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
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
 * @since 0.6093.1200
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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! get_option( 'analytics_privacy_mode' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Analytics privacy settings are not balanced yet. A privacy-first analytics setup can help measure performance while respecting visitor data preferences.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/analytics-privacy-not-balanced?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
