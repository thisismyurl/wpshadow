<?php
/**
 * wpForo Member Roles Diagnostic
 *
 * wpForo member roles misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.532.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wpForo Member Roles Diagnostic Class
 *
 * @since 1.532.0000
 */
class Diagnostic_WpforoMemberRoles extends Diagnostic_Base {

	protected static $slug = 'wpforo-member-roles';
	protected static $title = 'wpForo Member Roles';
	protected static $description = 'wpForo member roles misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WPFORO_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wpforo-member-roles',
			);
		}
		
		return null;
	}
}
