<?php
/**
 * Duplicator Database Encryption Diagnostic
 *
 * Duplicator database dumps not encrypted.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.394.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicator Database Encryption Diagnostic Class
 *
 * @since 1.394.0000
 */
class Diagnostic_DuplicatorDatabaseEncryption extends Diagnostic_Base {

	protected static $slug = 'duplicator-database-encryption';
	protected static $title = 'Duplicator Database Encryption';
	protected static $description = 'Duplicator database dumps not encrypted';
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
				'severity'    => 75,
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/duplicator-database-encryption',
			);
		}
		
		return null;
	}
}
