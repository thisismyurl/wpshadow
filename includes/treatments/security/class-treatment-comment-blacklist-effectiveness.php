<?php
/**
 * Comment Blacklist Effectiveness Treatment
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6031.1400
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Comment_Blacklist_Effectiveness extends Treatment_Base {
	protected static $slug = 'comment-blacklist-effectiveness';
	protected static $title = 'Comment Blacklist Effectiveness';
	protected static $description = 'Measures effectiveness of comment blacklist rules';
	protected static $family = 'security';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Blacklist_Effectiveness' );
	}
}
