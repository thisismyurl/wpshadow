<?php
/**
 * Comment Plugin Compatibility Treatment
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

class Treatment_Comment_Plugin_Compatibility extends Treatment_Base {
	protected static $slug = 'comment-plugin-compatibility';
	protected static $title = 'Comment Plugin Compatibility';
	protected static $description = 'Checks if comment plugins conflict with core';
	protected static $family = 'security';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Plugin_Compatibility' );
	}
}
