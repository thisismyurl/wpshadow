<?php
/**
 * Multisite Asset Sharing Diagnostic
 *
 * Multisite Asset Sharing misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.965.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Asset Sharing Diagnostic Class
 *
 * @since 1.965.0000
 */
class Diagnostic_MultisiteAssetSharing extends Diagnostic_Base {

	protected static $slug = 'multisite-asset-sharing';
	protected static $title = 'Multisite Asset Sharing';
	protected static $description = 'Multisite Asset Sharing misconfigured';
	protected static $family = 'functionality';

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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/multisite-asset-sharing',
			);
		}
		
		return null;
	}
}
