<?php
/**
 * Multisite Content Duplication Diagnostic
 *
 * Multisite Content Duplication misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.968.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Content Duplication Diagnostic Class
 *
 * @since 1.968.0000
 */
class Diagnostic_MultisiteContentDuplication extends Diagnostic_Base {

	protected static $slug = 'multisite-content-duplication';
	protected static $title = 'Multisite Content Duplication';
	protected static $description = 'Multisite Content Duplication misconfigured';
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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/multisite-content-duplication',
			);
		}
		
		return null;
	}
}
