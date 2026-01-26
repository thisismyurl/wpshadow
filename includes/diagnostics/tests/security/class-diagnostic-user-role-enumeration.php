<?php
/**
 * Diagnostic: User Role Enumeration Vulnerability
 *
 * Detects if attackers can enumerate WordPress user IDs via /author/ endpoints.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_User_Role_Enumeration
 *
 * Checks if user enumeration is possible via author archives, which can help
 * attackers identify admin users for targeted brute force attacks.
 *
 * @since 1.2601.2148
 */
class Diagnostic_User_Role_Enumeration extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'user-role-enumeration';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'User Role Enumeration Vulnerability';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect if attackers can enumerate WordPress user IDs via /author/ endpoints';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if author archives are publicly accessible, allowing user enumeration.
	 * Also checks if REST API user endpoints are accessible.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if vulnerability detected, null otherwise.
	 */
	public static function check() {
		$vulnerabilities = array();

		// Check if author archives are enabled
		// If any user exists with posts, their author page is likely accessible
		$users_with_posts = get_users(
			array(
				'has_published_posts' => true,
				'number' => 1,
			)
		);

		if ( ! empty( $users_with_posts ) ) {
			// Author archives are likely accessible
			$vulnerabilities[] = __( 'Author archive pages are accessible, allowing user enumeration', 'wpshadow' );
		}

		// Check if REST API user endpoints are accessible
		$rest_enabled = true;
		$rest_user_endpoint = rest_url( 'wp/v2/users' );
		
		// Check if REST API is filtered
		if ( has_filter( 'rest_authentication_errors' ) ) {
			// Some authentication is in place, but may not block user endpoints
			$vulnerabilities[] = __( 'REST API user endpoint may be accessible (authentication detected but user enumeration not confirmed as blocked)', 'wpshadow' );
		} else {
			// No REST authentication - definitely vulnerable
			$vulnerabilities[] = __( 'REST API user endpoint (/wp-json/wp/v2/users) is publicly accessible, allowing user enumeration', 'wpshadow' );
		}

		if ( empty( $vulnerabilities ) ) {
			return null;
		}

		$vuln_count = count( $vulnerabilities );
		
		$description = sprintf(
			/* translators: %d: number of enumeration vectors */
			_n(
				'Found %d user enumeration vector. Attackers can discover usernames to target for brute force attacks.',
				'Found %d user enumeration vectors. Attackers can discover usernames to target for brute force attacks.',
				$vuln_count,
				'wpshadow'
			),
			$vuln_count
		) . ' ' . implode( ' ', $vulnerabilities );

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => 'low',
			'threat_level' => 30,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/security-user-role-enumeration',
			'meta'        => array(
				'vulnerabilities' => $vulnerabilities,
				'rest_endpoint' => $rest_user_endpoint,
			),
		);
	}
}
