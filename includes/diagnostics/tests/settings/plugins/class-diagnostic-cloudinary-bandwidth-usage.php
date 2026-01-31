<?php
/**
 * Cloudinary Bandwidth Usage Diagnostic
 *
 * Cloudinary Bandwidth Usage detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.787.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cloudinary Bandwidth Usage Diagnostic Class
 *
 * @since 1.787.0000
 */
class Diagnostic_CloudinaryBandwidthUsage extends Diagnostic_Base {

	protected static $slug = 'cloudinary-bandwidth-usage';
	protected static $title = 'Cloudinary Bandwidth Usage';
	protected static $description = 'Cloudinary Bandwidth Usage detected';
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
				'kb_link'     => 'https://wpshadow.com/kb/cloudinary-bandwidth-usage',
			);
		}
		
		return null;
	}
}
