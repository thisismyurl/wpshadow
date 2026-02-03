<?php
/**
 * Sensitive Data Exposure In Logs Diagnostic
 *
 * Checks log security.
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
 * Diagnostic_Sensitive_Data_Exposure_In_Logs Class
 *
 * Performs diagnostic check for Sensitive Data Exposure In Logs.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Sensitive_Data_Exposure_In_Logs extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'sensitive-data-exposure-in-logs';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Sensitive Data Exposure In Logs';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks log security';

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
		if (   !get_option('log_sanitization_enabled' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Sensitive data exposed in logs. Mask passwords,
						'severity'   =>   'high',
						'threat_level'   =>   80,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/sensitive-data-exposure-in-logs'
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
