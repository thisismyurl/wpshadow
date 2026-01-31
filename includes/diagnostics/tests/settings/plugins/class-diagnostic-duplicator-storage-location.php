<?php
/**
 * Duplicator Storage Location Diagnostic
 *
 * Duplicator storage location insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.395.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicator Storage Location Diagnostic Class
 *
 * @since 1.395.0000
 */
class Diagnostic_DuplicatorStorageLocation extends Diagnostic_Base {

	protected static $slug = 'duplicator-storage-location';
	protected static $title = 'Duplicator Storage Location';
	protected static $description = 'Duplicator storage location insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'DUP_PRO_Package' ) || class_exists( 'DUP_Package' ) ) {
			return null;
		}
		
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
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/duplicator-storage-location',
			);
		}
		
		return null;
	}
}
