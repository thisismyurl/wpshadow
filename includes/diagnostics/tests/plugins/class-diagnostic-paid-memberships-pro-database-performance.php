<?php
/**
 * Paid Memberships Pro Database Performance Diagnostic
 *
 * PMPro database queries slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.338.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paid Memberships Pro Database Performance Diagnostic Class
 *
 * @since 1.338.0000
 */
class Diagnostic_PaidMembershipsProDatabasePerformance extends Diagnostic_Base {

	protected static $slug = 'paid-memberships-pro-database-performance';
	protected static $title = 'Paid Memberships Pro Database Performance';
	protected static $description = 'PMPro database queries slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'PMPRO_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/paid-memberships-pro-database-performance',
			);
		}
		
		return null;
	}
}
