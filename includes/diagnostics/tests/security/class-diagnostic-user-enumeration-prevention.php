<?php
/**
 * User Enumeration Prevention Diagnostic
 *
 * Issue #4949: User Enumeration Enabled (Security Risk)
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if user enumeration is prevented.
 * Attackers discover usernames via /?author=1 queries.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_User_Enumeration_Prevention Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_User_Enumeration_Prevention extends Diagnostic_Base {

	protected static $slug = 'user-enumeration-prevention';
	protected static $title = 'User Enumeration Enabled (Security Risk)';
	protected static $description = 'Checks if user enumeration via author archives is blocked';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Block /?author=N queries from non-logged-in users', 'wpshadow' );
		$issues[] = __( 'Disable REST API user endpoint /wp-json/wp/v2/users', 'wpshadow' );
		$issues[] = __( 'Use author slugs instead of IDs in URLs', 'wpshadow' );
		$issues[] = __( 'Return 404 for enumeration attempts', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WordPress allows discovering usernames via author archives. Attackers use this for brute force attacks and targeted phishing.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/user-enumeration',
				'details'      => array(
					'recommendations'         => $issues,
					'attack_method'           => 'Visit yoursite.com/?author=1, /?author=2, etc',
					'rest_api_leak'           => '/wp-json/wp/v2/users lists all usernames',
					'block_method'            => 'Redirect to 404 or homepage if not logged in',
				),
			);
		}

		return null;
	}
}
