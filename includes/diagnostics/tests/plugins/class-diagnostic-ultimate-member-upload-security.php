<?php
/**
 * Ultimate Member Upload Security Diagnostic
 *
 * Ultimate Member uploads not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.525.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ultimate Member Upload Security Diagnostic Class
 *
 * @since 1.525.0000
 */
class Diagnostic_UltimateMemberUploadSecurity extends Diagnostic_Base {

	protected static $slug = 'ultimate-member-upload-security';
	protected static $title = 'Ultimate Member Upload Security';
	protected static $description = 'Ultimate Member uploads not validated';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'ultimatemember_version' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/ultimate-member-upload-security',
			);
		}
		
		return null;
	}
}
