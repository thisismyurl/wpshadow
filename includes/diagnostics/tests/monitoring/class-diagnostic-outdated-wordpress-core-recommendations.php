<?php
/**
 * Outdated WordPress Core Recommendations
 *
 * Detects when Site Health provides incorrect or outdated advice about WordPress updates.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SiteHealth
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Outdated_WordPress_Core_Recommendations Class
 *
 * Validates WordPress core update recommendations for accuracy.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Outdated_WordPress_Core_Recommendations extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'outdated-wordpress-core-recommendations';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Core Recommendations Accuracy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that Site Health provides current WordPress recommendations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'site_health';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests for outdated core recommendations.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_version;

		$issues = array();

		// 1. Check WordPress version current status
		$version_issue = self::check_version_current_status();
		if ( $version_issue ) {
			$issues[] = $version_issue;
		}

		// 2. Check for deprecated recommendations
		$deprecated_issue = self::check_deprecated_recommendations();
		if ( $deprecated_issue ) {
			$issues[] = $deprecated_issue;
		}

		// 3. Check for incompatible advice
		$incompatible_issue = self::check_incompatible_advice();
		if ( $incompatible_issue ) {
			$issues[] = $incompatible_issue;
		}

		// 4. Check hosting-specific outdated advice
		$hosting_issue = self::check_hosting_recommendations();
		if ( $hosting_issue ) {
			$issues[] = $hosting_issue;
		}

		// 5. Check security recommendations accuracy
		$security_issue = self::check_security_recommendations();
		if ( $security_issue ) {
			$issues[] = $security_issue;
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues, %s: WordPress version */
					__( '%d outdated recommendations found (WordPress %s)', 'wpshadow' ),
					count( $issues ),
					$wp_version
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-recommendations-accuracy',
				'recommendations' => array(
					__( 'Cross-reference recommendations with official WordPress docs', 'wpshadow' ),
					__( 'Check WordPress release notes for current guidance', 'wpshadow' ),
					__( 'Verify recommendations match your hosting environment', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check version current status.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_version_current_status() {
		global $wp_version;

		// Check if running supported WordPress version
		// Current major version and one previous are supported
		$major_version = (int) explode( '.', $wp_version )[0];

		if ( $major_version < 5 ) {
			return sprintf(
				/* translators: %s: WordPress version */
				__( 'Running WordPress %s (recommend updating to 6.0+)', 'wpshadow' ),
				$wp_version
			);
		}

		// Check for minor version recommendations
		if ( ! function_exists( 'get_core_updates' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		if ( ! function_exists( 'get_core_updates' ) ) {
			return null;
		}

		$core_updates = get_core_updates();
		if ( empty( $core_updates ) ) {
			return null; // Up to date
		}

		// Check if security update is available
		foreach ( $core_updates as $update ) {
			if ( 'security' === $update->response ) {
				return sprintf(
					/* translators: %s: recommended version */
					__( 'Security update available to %s', 'wpshadow' ),
					$update->version
				);
			}
		}

		return null;
	}

	/**
	 * Check for deprecated recommendations.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_deprecated_recommendations() {
		// Check for outdated REST API warning
		if ( has_filter( 'rest_api_init' ) ) {
			// Old versions warned about REST API security, but modern WordPress handles this
			global $wp_version;
			if ( version_compare( $wp_version, '6.0', '>=' ) ) {
				return __( 'Site Health may warn about REST API (deprecated warning in 6.0+)', 'wpshadow' );
			}
		}

		// Check for obsolete MySQL recommendation
		global $wpdb;
		$mysql_version = $wpdb->db_version();

		if ( version_compare( $mysql_version, '5.6', '<' ) ) {
			return sprintf(
				/* translators: %s: MySQL version */
				__( 'MySQL %s is recommended for upgrade', 'wpshadow' ),
				$mysql_version
			);
		}

		return null;
	}

	/**
	 * Check for incompatible advice.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_incompatible_advice() {
		global $wp_version;

		// Check for theme/plugin compatibility issues
		$plugins = get_plugins();
		$theme   = wp_get_theme();

		// Check if theme/plugins are marked as incompatible
		$incompatible_count = 0;

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$requires_wp = $plugin_data['RequiresWP'] ?? '';

			if ( $requires_wp && version_compare( $wp_version, $requires_wp, '<' ) ) {
				$incompatible_count++;
			}
		}

		// Check theme compatibility
		if ( $theme && method_exists( $theme, 'requires' ) ) {
			$requires = $theme->requires();
			if ( $requires && version_compare( $wp_version, $requires, '<' ) ) {
				$incompatible_count++;
			}
		}

		if ( $incompatible_count > 0 ) {
			return sprintf(
				/* translators: %d: number of incompatible items */
				__( '%d plugins/theme marked as incompatible (verify compatibility)', 'wpshadow' ),
				$incompatible_count
			);
		}

		return null;
	}

	/**
	 * Check hosting recommendations.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_hosting_recommendations() {
		// Check for hosting-specific outdated advice
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

		// LiteSpeed specific
		if ( strpos( $server_software, 'LiteSpeed' ) !== false ) {
			// LiteSpeed has modern caching, old advice may not apply
			return __( 'Site Health may suggest outdated caching for LiteSpeed (modern LSAPI has built-in caching)', 'wpshadow' );
		}

		// Nginx specific
		if ( strpos( $server_software, 'nginx' ) !== false ) {
			// Nginx doesn't support .htaccess, recommendations might be wrong
			return __( 'Site Health may suggest .htaccess rules but server uses nginx (use nginx config instead)', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check security recommendations accuracy.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_security_recommendations() {
		// Check for outdated security practices
		if ( is_ssl() ) {
			// If using SSL, certain old recommendations shouldn't appear
			return null;
		}

		// Check .htaccess recommendations
		if ( function_exists( 'got_rewrite_rules' ) ) {
			// Modern WordPress has better security practices
			global $wp_version;
			if ( version_compare( $wp_version, '6.2', '>=' ) ) {
				return __( 'Site Health may suggest outdated .htaccess patterns (use core security headers instead)', 'wpshadow' );
			}
		}

		return null;
	}
}
