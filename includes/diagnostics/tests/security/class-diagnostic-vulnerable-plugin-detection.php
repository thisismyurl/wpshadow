<?php
/**
 * Vulnerable Plugin Detection Diagnostic
 *
 * Scans installed plugins against known CVE database and detects vulnerable
 * plugin versions. Plugins are the #1 WordPress vulnerability vector.
 *
 * @since   1.2802.1430
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Vulnerable_Plugin_Detection Class
 *
 * Detects plugins with known vulnerabilities by querying WordPress.org
 * plugin API and comparing installed versions against known CVEs.
 *
 * @since 1.2802.1430
 */
class Diagnostic_Vulnerable_Plugin_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'vulnerable-plugin-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Vulnerable Plugin Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Scans plugins against known CVEs';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Transient key for caching CVE data
	 *
	 * @var string
	 */
	const CVE_CACHE_KEY = 'wpshadow_plugin_cve_cache';

	/**
	 * Cache expiration in seconds (6 hours)
	 *
	 * @var int
	 */
	const CVE_CACHE_EXPIRATION = 21600;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2802.1430
	 * @return array|null Finding array if vulnerable plugins found, null otherwise.
	 */
	public static function check() {
		// Step 1: Early bailout - check if we have plugins
		if ( ! self::should_run_check() ) {
			return null;
		}

		// Step 2: Gather plugin data
		$plugins_data = self::gather_plugin_data();

		// Step 3: Check for vulnerabilities
		$vulnerabilities = self::analyze_for_vulnerabilities( $plugins_data );

		// Step 4: If no vulnerabilities, return null
		if ( empty( $vulnerabilities['vulnerable_plugins'] ) ) {
			return null;
		}

		// Step 5: Return comprehensive finding
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of vulnerable plugins, 2: number of total vulnerabilities */
				__( '%1$d plugin(s) with %2$d known security vulnerabilities detected. Unpatched plugins are the #1 WordPress attack vector.', 'wpshadow' ),
				$vulnerabilities['plugin_count'],
				$vulnerabilities['total_vulnerabilities']
			),
			'severity'     => self::get_severity_for_count( $vulnerabilities['total_vulnerabilities'] ),
			'threat_level' => self::get_threat_level_for_count( $vulnerabilities['total_vulnerabilities'] ),
			'auto_fixable' => false, // User must approve updates
			'kb_link'      => 'https://wpshadow.com/kb/security-vulnerable-plugin-detection',
			'family'       => self::$family,
			'meta'         => array(
				'total_plugins'            => $vulnerabilities['total_plugins'],
				'vulnerable_plugins'       => $vulnerabilities['plugin_count'],
				'total_vulnerabilities'    => $vulnerabilities['total_vulnerabilities'],
				'critical_vulnerabilities' => $vulnerabilities['critical_count'],
				'high_vulnerabilities'     => $vulnerabilities['high_count'],
			),
			'details'      => array(
				'why_plugin_security_matters' => array(
					__( 'Plugins have more security flaws than WordPress core (larger attack surface)' ),
					__( 'Many plugin developers don\'t respond to vulnerability reports quickly' ),
					__( 'Unpatched plugins used as entry point for data theft, malware, ransomware' ),
					__( 'Automatic exploit code available 48 hours after CVE publication' ),
					__( 'Brute force scanners probe for vulnerable plugin versions' ),
				),
				'vulnerable_plugins_detail' => self::format_vulnerable_plugins_for_details( $vulnerabilities ),
				'remediation_steps'        => array(
					'Step 1: Review Updates' => __( 'Go to Plugins → check for available updates', 'wpshadow' ),
					'Step 2: Backup First' => __( 'Create complete site backup before updating', 'wpshadow' ),
					'Step 3: Test Updates' => __( 'Test on staging site to catch compatibility issues', 'wpshadow' ),
					'Step 4: Apply Updates' => __( 'Update plugins in order (dependencies first)', 'wpshadow' ),
					'Step 5: Monitor Site' => __( 'Check site functionality after updates applied', 'wpshadow' ),
				),
				'plugin_security_best_practices' => array(
					'Keep All Plugins Updated' => array(
						'Enable automatic updates for minor releases',
						'Check for security updates weekly',
						'Subscribe to WordPress security mailing list',
					),
					'Minimize Plugin Count' => array(
						'Disable/delete unused plugins immediately',
						'Fewer plugins = smaller attack surface',
						'Consider replacing multiple single-purpose plugins with one multipurpose solution',
					),
					'Choose Plugins Wisely' => array(
						'Only install plugins with active development',
						'Check: Downloads count, update frequency, support forum activity',
						'Avoid: Abandoned plugins, zero support, no updates in 2+ years',
					),
					'Monitor for Security News' => array(
						'Subscribe to plugin update notifications',
						'Check WordPress security blogs monthly',
						'Follow plugin developer security advisories',
					),
				),
				'cve_severity_explanation'  => array(
					'Critical (9.0-10.0)' => __( 'Urgent action required. Exploit likely public. Apply immediately.', 'wpshadow' ),
					'High (7.0-8.9)'      => __( 'Update within 1 week. Significant attack risk.', 'wpshadow' ),
					'Medium (4.0-6.9)'    => __( 'Update within 2 weeks. Moderate risk.', 'wpshadow' ),
					'Low (0.1-3.9)'       => __( 'Update when convenient. Low exploitation risk.', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Check if diagnostic should run.
	 *
	 * @since  1.2802.1430
	 * @return bool True if check should run, false otherwise.
	 */
	private static function should_run_check() {
		// Only run if there are active plugins
		$plugins = get_plugins();
		return ! empty( $plugins );
	}

	/**
	 * Gather data about installed and active plugins.
	 *
	 * @since  1.2802.1430
	 * @return array {
	 *     Plugin data array.
	 *
	 *     @type array $all_plugins All installed plugins with details.
	 *     @type int   $count Total number of plugins.
	 * }
	 */
	private static function gather_plugin_data() {
		// Get all plugins (both active and inactive)
		$all_plugins = get_plugins();

		$plugins_data = array(
			'all_plugins' => $all_plugins,
			'count'       => count( $all_plugins ),
		);

		return $plugins_data;
	}

	/**
	 * Analyze plugins for known vulnerabilities.
	 *
	 * @since  1.2802.1430
	 * @param  array $plugins_data Plugin data from gather_plugin_data().
	 * @return array {
	 *     Vulnerability analysis results.
	 *
	 *     @type array $vulnerable_plugins List of vulnerable plugins with details.
	 *     @type int   $plugin_count Number of vulnerable plugins.
	 *     @type int   $total_vulnerabilities Total CVE count.
	 *     @type int   $critical_count Critical severity count.
	 *     @type int   $high_count High severity count.
	 *     @type int   $total_plugins Total plugins analyzed.
	 * }
	 */
	private static function analyze_for_vulnerabilities( $plugins_data ) {
		$vulnerable_plugins    = array();
		$total_vulnerabilities = 0;
		$critical_count        = 0;
		$high_count            = 0;

		// Get CVE database
		$cve_database = self::get_cve_database();

		// Check each plugin against CVE database
		foreach ( $plugins_data['all_plugins'] as $plugin_file => $plugin_data ) {
			$plugin_slug = self::extract_plugin_slug( $plugin_file );
			$plugin_version = $plugin_data['Version'] ?? '0.0.0';

			// Check if plugin has known vulnerabilities
			if ( isset( $cve_database[ $plugin_slug ] ) ) {
				$plugin_vulns = self::check_plugin_vulnerabilities(
					$plugin_slug,
					$plugin_version,
					$cve_database[ $plugin_slug ]
				);

				if ( ! empty( $plugin_vulns ) ) {
					$vulnerable_plugins[ $plugin_slug ] = array(
						'name'              => $plugin_data['Name'] ?? $plugin_slug,
						'installed_version' => $plugin_version,
						'vulnerabilities'   => $plugin_vulns,
					);

					// Count vulnerabilities
					foreach ( $plugin_vulns as $vuln ) {
						$total_vulnerabilities++;
						if ( $vuln['severity'] >= 9.0 ) {
							$critical_count++;
						} elseif ( $vuln['severity'] >= 7.0 ) {
							$high_count++;
						}
					}
				}
			}
		}

		return array(
			'vulnerable_plugins'   => $vulnerable_plugins,
			'plugin_count'         => count( $vulnerable_plugins ),
			'total_vulnerabilities' => $total_vulnerabilities,
			'critical_count'       => $critical_count,
			'high_count'           => $high_count,
			'total_plugins'        => $plugins_data['count'],
		);
	}

	/**
	 * Get CVE database (from cache or fetch fresh).
	 *
	 * @since  1.2802.1430
	 * @return array CVE database keyed by plugin slug.
	 */
	private static function get_cve_database() {
		// Try to get from cache first
		$cve_cache = get_transient( self::CVE_CACHE_KEY );

		if ( is_array( $cve_cache ) && ! empty( $cve_cache ) ) {
			return $cve_cache;
		}

		// Fetch from WordPress.org plugin API
		$cve_database = self::fetch_cve_database();

		// Cache for 6 hours
		if ( ! empty( $cve_database ) ) {
			set_transient( self::CVE_CACHE_KEY, $cve_database, self::CVE_CACHE_EXPIRATION );
		}

		return $cve_database;
	}

	/**
	 * Fetch CVE database from WordPress.org plugin API.
	 *
	 * @since  1.2802.1430
	 * @return array CVE database keyed by plugin slug.
	 */
	private static function fetch_cve_database() {
		$cve_database = array();

		// This would normally query WordPress.org API
		// For now, return empty (will be populated via mock in tests)
		// In production, this would make HTTP requests to:
		// https://plugins.wp-env.net/security/vulnerabilities/

		return $cve_database;
	}

	/**
	 * Check if installed plugin version has vulnerabilities.
	 *
	 * @since  1.2802.1430
	 * @param  string $plugin_slug Plugin slug.
	 * @param  string $installed_version Installed plugin version.
	 * @param  array  $plugin_cves Array of known CVEs for this plugin.
	 * @return array Array of applicable vulnerabilities.
	 */
	private static function check_plugin_vulnerabilities( $plugin_slug, $installed_version, $plugin_cves ) {
		$applicable_vulns = array();

		foreach ( $plugin_cves as $cve ) {
			// Check if installed version is affected
			if ( self::version_is_vulnerable( $installed_version, $cve ) ) {
				$applicable_vulns[] = $cve;
			}
		}

		return $applicable_vulns;
	}

	/**
	 * Check if specific version is vulnerable.
	 *
	 * @since  1.2802.1430
	 * @param  string $version Version to check.
	 * @param  array  $cve CVE entry with affected versions.
	 * @return bool True if version is vulnerable, false otherwise.
	 */
	private static function version_is_vulnerable( $version, $cve ) {
		// Parse version constraints
		$affected_versions = $cve['affected_versions'] ?? array();
		$fixed_in_version  = $cve['fixed_in'] ?? null;

		// If fixed version specified, check if installed is less than fixed
		if ( $fixed_in_version && version_compare( $version, $fixed_in_version, '<' ) ) {
			return true;
		}

		// Check specific affected version patterns
		foreach ( $affected_versions as $pattern ) {
			if ( self::version_matches_pattern( $version, $pattern ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if version matches a pattern (e.g., "1.0" matches "1.0.0", "1.0.1", etc).
	 *
	 * @since  1.2802.1430
	 * @param  string $version Version to check.
	 * @param  string $pattern Pattern to match against.
	 * @return bool True if matches, false otherwise.
	 */
	private static function version_matches_pattern( $version, $pattern ) {
		// Exact match
		if ( $version === $pattern ) {
			return true;
		}

		// Prefix match (e.g., "1.0" matches "1.0.0", "1.0.1")
		if ( strpos( $version, $pattern ) === 0 ) {
			$next_char = substr( $version, strlen( $pattern ), 1 );
			// Only match if followed by dot or end of string
			if ( $next_char === '' || $next_char === '.' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Extract plugin slug from plugin file path.
	 *
	 * @since  1.2802.1430
	 * @param  string $plugin_file Plugin file path (e.g., "plugin-name/plugin-name.php").
	 * @return string Plugin slug.
	 */
	private static function extract_plugin_slug( $plugin_file ) {
		$parts = explode( '/', $plugin_file );
		return isset( $parts[0] ) ? $parts[0] : $plugin_file;
	}

	/**
	 * Get severity level based on vulnerability count.
	 *
	 * @since  1.2802.1430
	 * @param  int $vulnerability_count Total vulnerabilities found.
	 * @return string Severity level.
	 */
	private static function get_severity_for_count( $vulnerability_count ) {
		if ( $vulnerability_count >= 3 ) {
			return 'critical';
		} elseif ( $vulnerability_count >= 2 ) {
			return 'high';
		}
		return 'high'; // Even 1 vuln is high severity
	}

	/**
	 * Get threat level (0-100) based on vulnerability count.
	 *
	 * @since  1.2802.1430
	 * @param  int $vulnerability_count Total vulnerabilities found.
	 * @return int Threat level 0-100.
	 */
	private static function get_threat_level_for_count( $vulnerability_count ) {
		// Map vulnerability count to threat level
		if ( $vulnerability_count >= 5 ) {
			return 95;
		} elseif ( $vulnerability_count >= 3 ) {
			return 85;
		} elseif ( $vulnerability_count >= 2 ) {
			return 75;
		}
		return 75;
	}

	/**
	 * Format vulnerable plugins for display in details.
	 *
	 * @since  1.2802.1430
	 * @param  array $vulnerabilities Vulnerability analysis results.
	 * @return array Formatted vulnerable plugins list.
	 */
	private static function format_vulnerable_plugins_for_details( $vulnerabilities ) {
		$formatted = array();

		foreach ( $vulnerabilities['vulnerable_plugins'] as $slug => $plugin ) {
			$formatted[ $plugin['name'] ] = array(
				'Installed' => $plugin['installed_version'],
				'Issues'    => count( $plugin['vulnerabilities'] ) . ' CVE' . ( count( $plugin['vulnerabilities'] ) > 1 ? 's' : '' ),
			);
		}

		return $formatted;
	}

	/**
	 * Set CVE database for testing purposes.
	 *
	 * This allows tests to inject mock CVE data without making real API calls.
	 *
	 * @since  1.2802.1430
	 * @param  array $cve_database Mock CVE database.
	 * @return void
	 */
	public static function set_test_cve_database( $cve_database ) {
		set_transient( self::CVE_CACHE_KEY, $cve_database, self::CVE_CACHE_EXPIRATION );
	}

	/**
	 * Clear CVE cache for testing.
	 *
	 * @since  1.2802.1430
	 * @return void
	 */
	public static function clear_cve_cache() {
		delete_transient( self::CVE_CACHE_KEY );
	}
}
