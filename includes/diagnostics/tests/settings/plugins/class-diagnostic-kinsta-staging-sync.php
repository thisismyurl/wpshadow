<?php
/**
 * Kinsta Staging Sync Diagnostic
 *
 * Kinsta Staging Sync needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.996.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kinsta Staging Sync Diagnostic Class
 *
 * @since 1.996.0000
 */
class Diagnostic_KinstaStagingSync extends Diagnostic_Base {

	protected static $slug = 'kinsta-staging-sync';
	protected static $title = 'Kinsta Staging Sync';
	protected static $description = 'Kinsta Staging Sync needs attention';
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
				'kb_link'     => 'https://wpshadow.com/kb/kinsta-staging-sync',
			);
		}
		
		return null;
	}
}
