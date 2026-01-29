<?php
/**
 * Multisite Audit Logging Diagnostic
 *
 * Multisite Audit Logging misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.978.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Audit Logging Diagnostic Class
 *
 * @since 1.978.0000
 */
class Diagnostic_MultisiteAuditLogging extends Diagnostic_Base {

	protected static $slug = 'multisite-audit-logging';
	protected static $title = 'Multisite Audit Logging';
	protected static $description = 'Multisite Audit Logging misconfigured';
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
				'kb_link'     => 'https://wpshadow.com/kb/multisite-audit-logging',
			);
		}
		
		return null;
	}
}
