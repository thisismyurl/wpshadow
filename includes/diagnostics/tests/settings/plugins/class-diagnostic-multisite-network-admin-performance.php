<?php
/**
 * Multisite Network Admin Performance Diagnostic
 *
 * Multisite Network Admin Performance misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.938.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Network Admin Performance Diagnostic Class
 *
 * @since 1.938.0000
 */
class Diagnostic_MultisiteNetworkAdminPerformance extends Diagnostic_Base {

	protected static $slug = 'multisite-network-admin-performance';
	protected static $title = 'Multisite Network Admin Performance';
	protected static $description = 'Multisite Network Admin Performance misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! is_multisite() ) {
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/multisite-network-admin-performance',
			);
		}
		
		return null;
	}
}
