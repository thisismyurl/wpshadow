<?php
/**
 * Comment Author IP Tracking Treatment
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Comment_Author_IP_Tracking extends Treatment_Base {
	protected static $slug = 'comment-author-ip-tracking';
	protected static $title = 'Comment Author IP Tracking';
	protected static $description = 'Checks if comment author IPs being stored securely';
	protected static $family = 'privacy';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Author_IP_Tracking' );
	}
}
