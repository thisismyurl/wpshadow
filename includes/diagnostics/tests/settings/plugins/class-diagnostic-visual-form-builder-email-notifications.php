<?php
/**
 * Visual Form Builder Email Notifications Diagnostic
 *
 * Visual Form Builder Email Notifications issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1216.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visual Form Builder Email Notifications Diagnostic Class
 *
 * @since 1.1216.0000
 */
class Diagnostic_VisualFormBuilderEmailNotifications extends Diagnostic_Base {

	protected static $slug = 'visual-form-builder-email-notifications';
	protected static $title = 'Visual Form Builder Email Notifications';
	protected static $description = 'Visual Form Builder Email Notifications issue found';
	protected static $family = 'functionality';

	public static function check() {
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/visual-form-builder-email-notifications',
			);
		}
		
		return null;
	}
}
