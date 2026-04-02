<?php
/**
 * LDAP/Active Directory Diagnostic
 *
 * Checks if directory integration (LDAP/AD) is active for enterprise environments.
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
 * LDAP/Active Directory Diagnostic Class
 *
 * Verifies that LDAP or Active Directory integration is configured for
 * centralized user management in enterprise environments.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Ldap_Active_Directory extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ldap-active-directory';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'LDAP/Active Directory Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if directory integration (LDAP/AD) is active for enterprise environments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'enterprise';

	/**
	 * Run the LDAP/AD diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if LDAP/AD gaps detected, null otherwise.
	 */
	public static function check() {
		$ldap_methods = array();
		$warnings     = array();

		// Check for LDAP PHP extension.
		$ldap_extension_loaded = extension_loaded( 'ldap' );

		// Check for LDAP/AD integration plugins.
		$ldap_plugins = array(
			'ldap-login-for-intranet-sites/ldap-login-for-intranet.php' => 'LDAP Login for Intranet',
			'active-directory-integration/ad-integration.php'             => 'Active Directory Integration',
			'wp-ldap-oauth2/wp-ldap-oauth2.php'                          => 'WP LDAP OAuth2',
			'simple-ldap-login/simple-ldap-login.php'                    => 'Simple LDAP Login',
			'mo-ldap-local-login/mo-ldap-local-login.php'                => 'LDAP/AD Integration',
		);

		foreach ( $ldap_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$ldap_methods[ sanitize_key( $name ) ] = $name;
			}
		}

		// Check for LDAP configuration constants.
		if ( defined( 'LDAP_SERVER' ) || defined( 'AD_SERVER' ) || defined( 'LDAP_HOST' ) ) {
			$ldap_methods['ldap_constants'] = __( 'LDAP configuration constants defined', 'wpshadow' );
		}

		// Check for Active Directory specific constants.
		if ( defined( 'AD_DOMAIN' ) || defined( 'AD_BASE_DN' ) ) {
			$ldap_methods['ad_constants'] = __( 'Active Directory constants defined', 'wpshadow' );
		}

		// Check for environment variables.
		if ( getenv( 'LDAP_SERVER' ) || getenv( 'AD_SERVER' ) ) {
			$ldap_methods['ldap_env'] = __( 'LDAP environment variables configured', 'wpshadow' );
		}

		// Check for custom LDAP authentication filter.
		if ( has_filter( 'authenticate' ) ) {
			$authenticate_filters = $GLOBALS['wp_filter']['authenticate'] ?? null;
			if ( $authenticate_filters ) {
				foreach ( $authenticate_filters as $priority => $callbacks ) {
					foreach ( $callbacks as $callback ) {
						if ( is_array( $callback['function'] ) ) {
							$class_name = is_object( $callback['function'][0] ) ? 
										  get_class( $callback['function'][0] ) : 
										  $callback['function'][0];
							if ( strpos( strtolower( $class_name ), 'ldap' ) !== false || 
								 strpos( strtolower( $class_name ), 'activedirectory' ) !== false ) {
								$ldap_methods['custom'] = __( 'Custom LDAP/AD implementation', 'wpshadow' );
								break 2;
							}
						}
					}
				}
			}
		}

		// Determine if this is an enterprise environment.
		$is_enterprise = self::is_enterprise_environment();

		// If enterprise and no LDAP/AD integration.
		if ( $is_enterprise && empty( $ldap_methods ) ) {
			$description = __( 'No LDAP or Active Directory integration detected in enterprise environment. Directory integration simplifies user management and improves security.', 'wpshadow' );
			
			if ( ! $ldap_extension_loaded ) {
				$description .= ' ' . __( 'Note: PHP LDAP extension is not installed.', 'wpshadow' );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ldap-active-directory',
				'context'      => array(
					'ldap_extension_loaded' => $ldap_extension_loaded,
					'is_enterprise'         => $is_enterprise,
				),
			);
		}

		// If LDAP methods detected, check for best practices.
		if ( ! empty( $ldap_methods ) ) {
			// Check if LDAP extension is loaded.
			if ( ! $ldap_extension_loaded ) {
				$warnings[] = __( 'PHP LDAP extension not loaded - integration may not function', 'wpshadow' );
			}

			// Check if SSL/TLS is configured for LDAP.
			$ldap_uri = defined( 'LDAP_SERVER' ) ? LDAP_SERVER : getenv( 'LDAP_SERVER' );
			if ( $ldap_uri && strpos( $ldap_uri, 'ldaps://' ) === false && strpos( $ldap_uri, 'ldap://localhost' ) === false ) {
				$warnings[] = __( 'LDAP connection not using LDAPS (secure) protocol', 'wpshadow' );
			}

			// Check for fallback authentication.
			$local_admin_users = get_users( array( 
				'role'   => 'administrator',
				'number' => 1,
			) );

			if ( empty( $local_admin_users ) ) {
				$warnings[] = __( 'No local administrator accounts - risk of lockout if LDAP fails', 'wpshadow' );
			}

			if ( ! empty( $warnings ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'LDAP/AD integration is configured but has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
					'severity'     => 'medium',
					'threat_level' => 45,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/ldap-active-directory',
					'context'      => array(
						'ldap_methods'          => $ldap_methods,
						'warnings'              => $warnings,
						'ldap_extension_loaded' => $ldap_extension_loaded,
					),
				);
			}
		}

		return null; // LDAP/AD is properly configured or not needed.
	}

	/**
	 * Determine if this is an enterprise environment.
	 *
	 * @since 1.6093.1200
	 * @return bool True if enterprise indicators detected, false otherwise.
	 */
	private static function is_enterprise_environment() {
		$enterprise_indicators = array(
			defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV,
			defined( 'WPE_CLUSTER_ID' ),
			defined( 'PANTHEON_ENVIRONMENT' ),
			is_multisite() && get_blog_count() > 50,
			get_user_count() > 100,
		);

		return in_array( true, $enterprise_indicators, true );
	}
}
