<?php
/**
 * Vulnerable Plugins Detection Diagnostic
 *
 * Checks all installed WordPress plugins against WPScan's database of 30,000+
 * known vulnerabilities to detect security risks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Security;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Security\Security_API_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vulnerable Plugins Detection Diagnostic Class
 *
 * Scans all installed plugins against WPScan vulnerability database.
 * Helps users understand if their plugins have known security problems
 * that could be exploited by hackers.
 *
 * **Real-World Analogy:**
 * "Like checking if your smoke detectors have been recalled—we'll tell you if
 * any of your plugins have known security problems that hackers could exploit."
 *
 * @since 1.6093.1200
 */
class Diagnostic_Vulnerable_Plugins_WPScan extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'vulnerable-plugins-wpscan';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Security Vulnerabilities';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks plugins for known security issues';

	/**
	 * Diagnostic family (groups related diagnostics)
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check
	 *
	 * Scans all installed plugins against WPScan's vulnerability database.
	 *
	 * **Return Structure (if vulnerabilities found):**
	 * ```
	 * array(
	 *     'id'           => 'vulnerable-plugins-wpscan',
	 *     'title'        => 'Plugin Security Vulnerabilities',
	 *     'description'  => 'Found vulnerable plugins...',
	 *     'severity'     => 'critical' | 'high' | 'medium' | 'low',
	 *     'threat_level' => 75,
	 *     'auto_fixable' => false,
	 *     'affected_items' => array(
	 *         array(
	 *             'plugin'      => 'plugin-name',
	 *             'version'     => '1.0.0',
	 *             'vulnerabilities' => array(
	 *                 array(
	 *                     'title'       => 'SQL Injection',
	 *                     'description' => 'Allows attackers to...',
	 *                     'cvssv3'      => '7.5',
	 *                     'fixed_in'    => '1.2.0',
	 *                     'link'        => 'https://wpscan.com/...',
	 *                 ),
	 *                 // ... more vulnerabilities
	 *             ),
	 *         ),
	 *         // ... more plugins
	 *     ),
	 *     'kb_link' => 'https://wpshadow.com/kb/vulnerable-plugins-fix',
	 * )
	 * ```
	 *
	 * **Return Structure (if no vulnerabilities):**
	 * Returns null (no issue found)
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if vulnerabilities detected, null otherwise.
	 */
	public static function check() {
		// Check if WPScan API is enabled
		if ( ! Security_API_Manager::is_enabled( 'wpscan' ) ) {
			return array(
				'id'           => 'wpscan-api-not-configured',
				'title'        => __( 'Plugin Security Scanner Not Set Up Yet', 'wpshadow' ),
				'description'  => __( 'Get a free WPScan API key to automatically check if your plugins have known security issues. Think of it like having a security expert review your plugins daily. Takes 2 minutes to set up.', 'wpshadow' ),
				'severity'     => 'info',
				'threat_level' => 0,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wpscan-setup',
				'action_url'   => admin_url( 'admin.php?page=wpshadow-security-api' ),
				'action_text'  => __( 'Set Up Free WPScan API', 'wpshadow' ),
			);
		}

		// Get API key
		$api_key = Security_API_Manager::get_api_key( 'wpscan' );

		if ( empty( $api_key ) ) {
			return array(
				'id'           => 'wpscan-api-missing',
				'title'        => __( 'WPScan API Key Not Configured', 'wpshadow' ),
				'description'  => __( 'The WPScan service is enabled, but the API key is missing. Please update it in the settings.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 0,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wpscan-setup',
				'action_url'   => admin_url( 'admin.php?page=wpshadow-security-api' ),
				'action_text'  => __( 'Add API Key', 'wpshadow' ),
			);
		}

		// Get all plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();

		if ( empty( $all_plugins ) ) {
			// No plugins installed, no vulnerabilities possible
			return null;
		}

		// Check each plugin for vulnerabilities
		$vulnerable_plugins = array();
		$total_vulnerabilities = 0;

		foreach ( $all_plugins as $plugin_path => $plugin_data ) {
			// Extract plugin slug from path (e.g., "plugin-name/plugin-name.php" → "plugin-name")
			$plugin_slug = explode( '/', $plugin_path );
			$plugin_slug = $plugin_slug[0];
			$plugin_version = isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : 'unknown';

			// Check cache first
			$cached = Security_API_Manager::get_cache( 'wpscan', $plugin_slug );
			if ( false !== $cached ) {
				if ( ! empty( $cached['vulnerabilities'] ) ) {
					$vulnerable_plugins[] = array(
						'plugin'           => $plugin_slug,
						'name'             => $plugin_data['Name'] ?? $plugin_slug,
						'version'          => $plugin_version,
						'vulnerabilities'  => $cached['vulnerabilities'],
					);
					$total_vulnerabilities += count( $cached['vulnerabilities'] );
				}
				continue;
			}

			// Query WPScan API
			$response = wp_remote_get(
				'https://wpscan.com/api/v3/plugins/' . urlencode( $plugin_slug ),
				array(
					'timeout' => 5,
					'headers' => array(
						'Authorization' => 'Token token=' . $api_key,
					),
				)
			);

			// Handle network errors gracefully (Murphy's Law)
			if ( is_wp_error( $response ) ) {
				Security_API_Manager::log_call( 'wpscan', 'error', array(
					'plugin' => $plugin_slug,
					'error'  => $response->get_error_message(),
				) );
				continue;
			}

			$status_code = wp_remote_retrieve_response_code( $response );
			$body = wp_remote_retrieve_body( $response );

			// Handle rate limiting gracefully
			if ( 429 === $status_code ) {
				Security_API_Manager::log_call( 'wpscan', 'rate_limited', array(
					'plugin' => $plugin_slug,
				) );
				// Continue with next plugin, don't fail
				continue;
			}

			// Handle invalid API key
			if ( 403 === $status_code ) {
				return array(
					'id'           => 'wpscan-api-invalid',
					'title'        => __( 'WPScan API Key Invalid', 'wpshadow' ),
					'description'  => __( 'The WPScan API key is invalid or has been revoked. Please update it with a valid key.', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/wpscan-setup',
					'action_url'   => admin_url( 'admin.php?page=wpshadow-security-api' ),
					'action_text'  => __( 'Update API Key', 'wpshadow' ),
				);
			}

			// Plugin not found in WPScan (404 is normal)
			if ( 404 === $status_code ) {
				// Cache empty result
				Security_API_Manager::set_cache( 'wpscan', $plugin_slug, array(
					'vulnerabilities' => array(),
				), DAY_IN_SECONDS );
				continue;
			}

			// Other error codes
			if ( 200 !== $status_code ) {
				Security_API_Manager::log_call( 'wpscan', 'error', array(
					'plugin'      => $plugin_slug,
					'status_code' => $status_code,
				) );
				continue;
			}

			// Parse response
			$data = json_decode( $body, true );

			if ( ! is_array( $data ) || ! isset( $data['vulnerabilities'] ) ) {
				// Invalid response structure, cache empty
				Security_API_Manager::set_cache( 'wpscan', $plugin_slug, array(
					'vulnerabilities' => array(),
				), DAY_IN_SECONDS );
				continue;
			}

			$vulnerabilities = $data['vulnerabilities'];

			// Cache result
			Security_API_Manager::set_cache( 'wpscan', $plugin_slug, array(
				'vulnerabilities' => $vulnerabilities,
			), DAY_IN_SECONDS );

			// If vulnerabilities found, add to list
			if ( ! empty( $vulnerabilities ) ) {
				$vulnerable_plugins[] = array(
					'plugin'          => $plugin_slug,
					'name'            => $plugin_data['Name'] ?? $plugin_slug,
					'version'         => $plugin_version,
					'vulnerabilities' => $vulnerabilities,
				);
				$total_vulnerabilities += count( $vulnerabilities );
			}

			// Be nice to the API (small delay between requests)
			usleep( 100000 ); // 100ms
		}

		// No vulnerabilities found
		if ( empty( $vulnerable_plugins ) ) {
			return null;
		}

		// Determine severity based on number and severity of vulnerabilities
		$severity = self::determine_severity( $vulnerable_plugins );

		// Build detailed description
		$description = self::build_description( $vulnerable_plugins, $total_vulnerabilities );

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => $description,
			'severity'         => $severity,
			'threat_level'     => self::calculate_threat_level( $vulnerable_plugins ),
			'auto_fixable'     => false,
			'affected_items'   => $vulnerable_plugins,
			'item_count'       => count( $vulnerable_plugins ),
			'total_vuln_count' => $total_vulnerabilities,
			'kb_link'          => 'https://wpshadow.com/kb/vulnerable-plugins-fix',
		);
	}

	/**
	 * Determine severity based on vulnerabilities
	 *
	 * @since 1.6093.1200
	 * @param  array $vulnerable_plugins Vulnerable plugins array.
	 * @return string Severity level.
	 */
	private static function determine_severity( $vulnerable_plugins ) {
		// Check for high-severity vulnerabilities
		foreach ( $vulnerable_plugins as $plugin ) {
			foreach ( $plugin['vulnerabilities'] as $vuln ) {
				// Check CVSS score (if available)
				if ( isset( $vuln['cvssv3'] ) && (float) $vuln['cvssv3']['score'] >= 7.0 ) {
					return 'critical';
				}
				// Check for RCE or auth bypass
				if ( isset( $vuln['title'] ) ) {
					$title = strtolower( $vuln['title'] );
					if ( strpos( $title, 'remote code execution' ) !== false ||
						strpos( $title, 'rce' ) !== false ||
						strpos( $title, 'authentication bypass' ) !== false ||
						strpos( $title, 'sql injection' ) !== false ) {
						return 'critical';
					}
				}
			}
		}

		// Default to high for any vulnerabilities
		return 'high';
	}

	/**
	 * Calculate threat level (0-100)
	 *
	 * @since 1.6093.1200
	 * @param  array $vulnerable_plugins Vulnerable plugins array.
	 * @return int Threat level.
	 */
	private static function calculate_threat_level( $vulnerable_plugins ) {
		// 1-2 plugins with 1-2 vulns each = 40
		// 3+ plugins or 5+ vulns = 70
		// Critical vulns = 90+

		$total_vulns = 0;
		$has_critical = false;

		foreach ( $vulnerable_plugins as $plugin ) {
			$total_vulns += count( $plugin['vulnerabilities'] );

			foreach ( $plugin['vulnerabilities'] as $vuln ) {
				if ( isset( $vuln['cvssv3'] ) && (float) $vuln['cvssv3']['score'] >= 7.0 ) {
					$has_critical = true;
				}
			}
		}

		if ( $has_critical ) {
			return 90;
		} elseif ( count( $vulnerable_plugins ) >= 3 || $total_vulns >= 5 ) {
			return 70;
		} else {
			return 40;
		}
	}

	/**
	 * Build human-readable description
	 *
	 * @since 1.6093.1200
	 * @param  array $vulnerable_plugins Vulnerable plugins array.
	 * @param  int   $total_vulnerabilities Total vulnerability count.
	 * @return string Formatted description.
	 */
	private static function build_description( $vulnerable_plugins, $total_vulnerabilities ) {
		$plugin_count = count( $vulnerable_plugins );
		$severity_critical = 0;
		$severity_high = 0;

		// Count severity levels
		foreach ( $vulnerable_plugins as $plugin ) {
			foreach ( $plugin['vulnerabilities'] as $vuln ) {
				if ( isset( $vuln['cvssv3'] ) && (float) $vuln['cvssv3']['score'] >= 7.0 ) {
					$severity_critical++;
				} else {
					$severity_high++;
				}
			}
		}

		// Build message
		$message = sprintf(
			/* translators: %d: number of plugins with vulnerabilities */
			esc_html__( 'Found %d plugin(s) with %d known security vulnerabilities.', 'wpshadow' ),
			$plugin_count,
			$total_vulnerabilities
		);

		if ( $severity_critical > 0 ) {
			$message .= ' ' . sprintf(
				/* translators: %d: number of critical vulnerabilities */
				esc_html__( '%d are critical or high-severity (like open doors to your site).', 'wpshadow' ),
				$severity_critical
			);
		}

		$message .= ' ' . esc_html__( 'Hackers often target plugins with known vulnerabilities.', 'wpshadow' );
		$message .= ' ' . esc_html__( 'Here\'s what to do:', 'wpshadow' );
		$message .= "\n\n";

		// Action steps
		$message .= "1. " . esc_html__( 'Update all plugins to their latest versions', 'wpshadow' );
		$message .= "\n";
		$message .= "2. " . esc_html__( 'If no update is available, consider replacing or disabling the plugin', 'wpshadow' );
		$message .= "\n";
		$message .= "3. " . esc_html__( 'Run this check again after updating', 'wpshadow' );

		return $message;
	}
}
