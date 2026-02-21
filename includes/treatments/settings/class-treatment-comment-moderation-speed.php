<?php
/**
 * Comment Moderation Speed Treatment
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6031.1500
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Comment_Moderation_Speed extends Treatment_Base {
	protected static $slug = 'comment-moderation-speed';
	protected static $title = 'Comment Moderation Speed';
	protected static $description = 'Detects slow moderation workflow';
	protected static $family = 'functionality';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Moderation_Speed' );
	}
}
