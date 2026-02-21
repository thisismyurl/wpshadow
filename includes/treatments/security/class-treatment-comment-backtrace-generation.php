<?php
/**
 * Comment Backtrace Generation Treatment
 *
 * Checks if comment backtrace data is stored safely without exposing system paths.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6031.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Backtrace Treatment Class
 *
 * @since 1.6031.1300
 */
class Treatment_Comment_Backtrace_Generation extends Treatment_Base {

	protected static $slug = 'comment-backtrace-generation';
	protected static $title = 'Comment Backtrace Generation';
	protected static $description = 'Checks if comment backtrace data is stored safely';
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6031.1300
	 * @return array|null
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Backtrace_Generation' );
	}
}
