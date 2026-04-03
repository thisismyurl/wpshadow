<?php
/**
 * Force SSL Admin Diagnostic
 *
 * Checks whether the WordPress admin dashboard is forced to use HTTPS
 * via the FORCE_SSL_ADMIN constant or equivalent server configuration.
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
 * Force SSL Admin Diagnostic Class
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
	protected static $description = 'Checks whether FORCE_SSL_ADMIN is enabled to ensure all WordPress admin panel sessions are served exclusively over HTTPS.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Calls force_ssl_admin() in an HTTPS context to confirm the constant or
	 * filter is active, flagging admin sessions that are not SSL-enforced.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when admin SSL is not enforced, null when healthy.
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
			'kb_link'      => 'https://wpshadow.com/kb/force-ssl-admin?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'force_ssl_admin' => false,
				'site_url_https'  => true,
				'fix'             => __( 'Add define( \'FORCE_SSL_ADMIN\', true ); to wp-config.php.', 'wpshadow' ),
			),
		);
	}
}
