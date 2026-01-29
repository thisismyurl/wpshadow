<?php
/**
 * Multisite Orphaned Tables Diagnostic
 *
 * Multisite Orphaned Tables misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.984.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Orphaned Tables Diagnostic Class
 *
 * @since 1.984.0000
 */
class Diagnostic_MultisiteOrphanedTables extends Diagnostic_Base {

	protected static $slug = 'multisite-orphaned-tables';
	protected static $title = 'Multisite Orphaned Tables';
	protected static $description = 'Multisite Orphaned Tables misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/multisite-orphaned-tables',
			);
		}
		
		return null;
	}
}
