<?php
/**
 * OAuth2/SSO Integration Diagnostic
 *
 * Checks if enterprise single sign-on is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OAuth2/SSO Integration Diagnostic Class
 *
 * Verifies that enterprise single sign-on (SSO) is configured for centralized
 * authentication, better security, and improved user experience.
 *
 * @since 1.6035.1200
 */
class Diagnostic_Oauth2_Sso_Integration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'oauth2-sso-integration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'OAuth2/SSO Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if enterprise single sign-on is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'enterprise';

	/**
	 * Run the OAuth2/SSO diagnostic check.
	 *
	 * @since  1.6035.1200
	 * @return array|null Finding array if SSO gaps detected, null otherwise.
	 */
	public static function check() {
		$sso_methods = array();
		$warnings    = array();

		// Check for OAuth2/SSO plugins.
		$sso_plugins = array(
			'miniorange-login-openid/miniorange_openid_sso_login.php' => 'miniOrange OAuth',
			'wp-oauth-server/wp-oauth-server.php'                     => 'WP OAuth Server',
			'oauth2-provider/oauth2-provider.php'                     => 'OAuth2 Provider',
			'simple-saml/simple-saml.php'                             => 'SimpleSAMLphp',
			'onelogin-saml-sso/onelogin_saml.php'                     => 'OneLogin SAML SSO',
			'saml-20-single-sign-on/saml-20-single-sign-on.php'       => 'SAML 2.0 SSO',
		);

		foreach ( $sso_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$sso_methods[ sanitize_key( $name ) ] = $name;
			}
		}

		// Check for enterprise SSO services.
		// Check for Okta.
		if ( defined( 'OKTA_CLIENT_ID' ) || defined( 'OKTA_DOMAIN' ) ) {
			$sso_methods['okta'] = 'Okta SSO';
		}

		// Check for Auth0.
		if ( is_plugin_active( 'auth0/WP_Auth0.php' ) || defined( 'AUTH0_CLIENT_ID' ) ) {
			$sso_methods['auth0'] = 'Auth0';
		}

		// Check for Azure AD.
		if ( is_plugin_active( 'aad-sso-wordpress/aad-sso-wordpress.php' ) || 
			 defined( 'AZURE_AD_TENANT_ID' ) ) {
			$sso_methods['azure_ad'] = 'Azure Active Directory';
		}

		// Check for Google Workspace.
		if ( is_plugin_active( 'google-apps-login/google_apps_login.php' ) ) {
			$sso_methods['google_workspace'] = 'Google Workspace SSO';
		}

		// Check for custom OAuth2 implementation.
		if ( has_filter( 'authenticate' ) ) {
			$authenticate_filters = $GLOBALS['wp_filter']['authenticate'] ?? null;
			if ( $authenticate_filters ) {
				foreach ( $authenticate_filters as $priority => $callbacks ) {
					foreach ( $callbacks as $callback ) {
						if ( is_array( $callback['function'] ) ) {
							$class_name = is_object( $callback['function'][0] ) ? 
										  get_class( $callback['function'][0] ) : 
										  $callback['function'][0];
							if ( strpos( $class_name, 'OAuth' ) !== false || 
								 strpos( $class_name, 'SSO' ) !== false ) {
								$sso_methods['custom'] = __( 'Custom OAuth/SSO implementation', 'wpshadow' );
								break 2;
							}
						}
					}
				}
			}
		}

		// Determine if this is an enterprise environment.
		$is_enterprise = self::is_enterprise_environment();

		// If enterprise and no SSO configured.
		if ( $is_enterprise && empty( $sso_methods ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Adding single sign-on (SSO) lets your team use one login for everything (like using the same key for your house, car, and office). Instead of remembering different passwords for WordPress, people can use their work account login.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/oauth2-sso-integration',
				'context'      => array(
					'is_enterprise' => $is_enterprise,
				),
			);
		}

		// If SSO is configured, check for best practices.
		if ( ! empty( $sso_methods ) ) {
			// Check if 2FA is also enabled.
			$two_factor_plugins = array(
				'two-factor/two-factor.php',
				'two-factor-authentication/two-factor-authentication.php',
				'wordfence/wordfence.php',
			);

			$has_2fa = false;
			foreach ( $two_factor_plugins as $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					$has_2fa = true;
					break;
				}
			}

			if ( ! $has_2fa ) {
				$warnings[] = __( 'Two-factor authentication recommended alongside SSO', 'wpshadow' );
			}

			// Check if local admin accounts still exist.
			$admin_users = get_users( array( 'role' => 'administrator' ) );
			if ( count( $admin_users ) > 5 ) {
				$warnings[] = sprintf(
					/* translators: %d: number of admin users */
					__( '%d local admin accounts - consider consolidating through SSO', 'wpshadow' ),
					count( $admin_users )
				);
			}

			if ( ! empty( $warnings ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %s: SSO method name */
						__( 'SSO is configured (%s) but has recommendations: ', 'wpshadow' ),
						implode( ', ', $sso_methods )
					) . implode( ', ', $warnings ),
					'severity'     => 'low',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/oauth2-sso-integration',
					'context'      => array(
						'sso_methods' => $sso_methods,
						'warnings'    => $warnings,
					),
				);
			}
		}

		return null; // SSO is properly configured or not needed.
	}

	/**
	 * Determine if this is an enterprise environment.
	 *
	 * @since  1.6035.1200
	 * @return bool True if enterprise indicators detected, false otherwise.
	 */
	private static function is_enterprise_environment() {
		$enterprise_indicators = array(
			defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV,
			defined( 'WPE_CLUSTER_ID' ),
			defined( 'PANTHEON_ENVIRONMENT' ),
			is_multisite() && get_blog_count() > 50,
			get_user_count() > 100, // Large user base.
		);

		return in_array( true, $enterprise_indicators, true );
	}
}
