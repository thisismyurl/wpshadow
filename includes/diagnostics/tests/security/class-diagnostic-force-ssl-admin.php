<?php
/**
 * Force SSL Admin Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 03.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Server_Environment_Helper as Server_Env;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Force SSL Admin Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Force_Ssl_Admin extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'force-ssl-admin';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Force SSL Admin';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for Force SSL Admin. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Use force_ssl_admin() and is_ssl checks in admin context.
	 *
	 * TODO Fix Plan:
	 * Fix by setting FORCE_SSL_ADMIN and redirect rules.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		// Only meaningful when the site is served over HTTPS.
		if ( ! WP_Settings::is_site_url_https() ) {
			return null;
		}

		if ( Server_Env::is_force_ssl_admin() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'FORCE_SSL_ADMIN is not set in wp-config.php. Even though your site URL uses HTTPS, WordPress may still allow admin-area logins over an unencrypted HTTP connection on some server configurations. Setting FORCE_SSL_ADMIN ensures credentials are always encrypted in transit.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/force-ssl-admin',
			'details'      => array(
				'force_ssl_admin' => false,
				'site_url_https'  => true,
				'fix'             => __( 'Add define( \'FORCE_SSL_ADMIN\', true ); to wp-config.php.', 'wpshadow' ),
			),
		);
	}
}
