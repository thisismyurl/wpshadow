<?php
/**
 * Login Page Accessibility Diagnostic
 *
 * Checks WordPress login page for accessibility compliance.
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
 * Login Page Accessibility Diagnostic
 *
 * Validates WCAG compliance of login page elements and interaction patterns.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Login_Page_Accessibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'login-page-accessibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Login Page Accessibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks WordPress login page for accessibility compliance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for login page plugins that might affect accessibility
		$accessibility_plugins = array(
			'wpaccess/wp-access.php'                  => 'WP Access',
			'wp-login-redirect/wp-login-redirect.php' => 'WP Login Redirect',
		);

		$active_plugins   = get_option( 'active_plugins', array() );
		$has_custom_login = false;

		foreach ( $accessibility_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$has_custom_login = true;
			}
		}

		// Check for login page customizations via hooks
		global $wp_filter;

		$login_filters = array(
			'login_head',
			'login_body_class',
			'login_errors',
		);

		$filter_count = 0;
		foreach ( $login_filters as $filter ) {
			if ( isset( $wp_filter[ $filter ] ) && ! empty( $wp_filter[ $filter ]->callbacks ) ) {
				$filter_count += count( $wp_filter[ $filter ]->callbacks );
			}
		}

		if ( $filter_count > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of filters */
				__( '%d login page modifications may affect accessibility', 'wpshadow' ),
				$filter_count
			);
		}

		// Check for SSL/HTTPS on login page
		if ( ! is_ssl() ) {
			$issues[] = __( 'Login page not using HTTPS - security and privacy concern', 'wpshadow' );
		}

		// Check for JavaScript dependencies on login page
		global $wp_scripts;
		$login_scripts = 0;

		if ( is_object( $wp_scripts ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( strpos( $handle, 'login' ) !== false ) {
					++$login_scripts;
				}
			}
		}

		if ( $login_scripts > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of scripts */
				__( '%d scripts loaded on login page - may affect accessibility', 'wpshadow' ),
				$login_scripts
			);
		}

		// Check for login lockout plugins
		$lockout_plugins = array(
			'login-lockdown/login-lockdown.php',
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
			'wordfence/wordfence.php',
		);

		$lockout_active = array();
		foreach ( $lockout_plugins as $plugin ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$lockout_active[] = basename( dirname( $plugin ) );
			}
		}

		if ( ! empty( $lockout_active ) && count( $lockout_active ) > 1 ) {
			$issues[] = __( 'Multiple login lockout plugins detected - may cause accessibility issues', 'wpshadow' );
		}

		// Report findings
		if ( ! empty( $issues ) ) {
			$severity     = 'low';
			$threat_level = 30;

			if ( count( $issues ) > 2 ) {
				$severity     = 'medium';
				$threat_level = 55;
			}

			$description = __( 'Login page accessibility and UX issues detected', 'wpshadow' );

			$details = array(
				'issues'               => $issues,
				'custom_login'         => $has_custom_login,
				'filter_modifications' => $filter_count,
				'ssl_enabled'          => is_ssl(),
			);

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/login-page-accessibility',
				'details'      => $details,
			);
		}

		return null;
	}
}
