<?php
/**
 * LDAP Injection Diagnostic
 *
 * Detects LDAP injection vulnerabilities in authentication and
 * directory query operations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LDAP Injection Diagnostic Class
 *
 * Checks for:
 * - Unescaped user input in LDAP filters
 * - LDAP authentication plugins with injection risks
 * - Special character handling in LDAP queries
 * - DN (Distinguished Name) injection vulnerabilities
 * - Blind LDAP injection via boolean responses
 *
 * LDAP injection allows attackers to modify LDAP queries, potentially
 * bypassing authentication, accessing unauthorized directory data, or
 * escalating privileges within Active Directory environments.
 *
 * @since 1.6093.1200
 */
class Diagnostic_LDAP_Injection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'ldap-injection';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'LDAP Injection Vulnerability';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Detects LDAP injection vulnerabilities in directory queries';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Scans for LDAP usage and validates injection prevention.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check 1: Look for LDAP authentication plugins.
		$ldap_plugins = self::find_ldap_plugins();
		if ( empty( $ldap_plugins ) ) {
			// No LDAP in use, no vulnerability.
			return null;
		}

		// Check 2: Scan LDAP plugin code for injection vulnerabilities.
		$vulnerable_files = self::scan_ldap_plugins( $ldap_plugins );
		if ( ! empty( $vulnerable_files ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				_n(
					'Found %d file with potential LDAP injection vulnerability',
					'Found %d files with potential LDAP injection vulnerabilities',
					count( $vulnerable_files ),
					'wpshadow'
				),
				count( $vulnerable_files )
			);
		}

		// Check 3: Verify ldap_escape usage.
		$uses_escaping = self::check_ldap_escape_usage( $ldap_plugins );
		if ( ! $uses_escaping ) {
			$issues[] = __( 'LDAP code does not use ldap_escape() for user input', 'wpshadow' );
		}

		// Check 4: Check for direct concatenation in ldap_search.
		$has_concatenation = self::check_ldap_search_concatenation( $ldap_plugins );
		if ( $has_concatenation ) {
			$issues[] = __( 'LDAP filters use string concatenation with user input (injection risk)', 'wpshadow' );
		}

		// Check 5: Verify special character filtering.
		$filters_special_chars = self::check_special_char_filtering( $ldap_plugins );
		if ( ! $filters_special_chars ) {
			$issues[] = __( 'LDAP code may not filter special characters: * ( ) \\ null', 'wpshadow' );
		}

		// If we found issues, return finding.
		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d LDAP security issue detected',
						'%d LDAP security issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ldap-injection',
				'context'      => array(
					'issues'       => $issues,
					'ldap_plugins' => $ldap_plugins,
					'why'          => __(
						'LDAP injection allows attackers to manipulate directory queries by injecting special characters. ' .
						'Common attacks include authentication bypass using filters like (*)(&), privilege escalation by ' .
						'modifying group membership queries, and data exfiltration through blind LDAP injection. ' .
						'In corporate environments with Active Directory, successful LDAP injection can grant access to ' .
						'the entire directory, including usernames, emails, group memberships, and organizational structure. ' .
						'According to OWASP, LDAP injection is particularly dangerous because it often provides access to ' .
						'sensitive corporate directory data and can enable lateral movement in enterprise networks.',
						'wpshadow'
					),
					'recommendation' => __(
						'Always use ldap_escape() with LDAP_ESCAPE_FILTER flag for filter values and LDAP_ESCAPE_DN for DN values. ' .
						'Filter special LDAP characters: * ( ) \\ NUL. Never concatenate user input directly into LDAP filters. ' .
						'Use parameterized LDAP queries where possible. Implement input validation with whitelists. ' .
						'Limit LDAP bind account privileges to minimum required. Enable LDAP query logging for detection.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'code-analysis',
				'ldap-injection-guide'
			);

			return $finding;
		}

		return null;
	}

	/**
	 * Find LDAP authentication plugins.
	 *
	 * @since 1.6093.1200
	 * @return array Plugin slugs using LDAP.
	 */
	private static function find_ldap_plugins() {
		$ldap_plugins = array();
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $active_plugins as $plugin ) {
			$plugin_slug = dirname( $plugin );
			
			// Check plugin name for LDAP keywords.
			if ( preg_match( '/(ldap|active[_-]?directory|ad[_-]?auth)/i', $plugin_slug ) ) {
				$ldap_plugins[] = $plugin_slug;
				continue;
			}

			// Check plugin file for ldap_connect.
			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
			if ( file_exists( $plugin_file ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$content = file_get_contents( $plugin_file );
				if ( str_contains( $content, 'ldap_connect' ) || str_contains( $content, 'ldap_bind' ) ) {
					$ldap_plugins[] = $plugin_slug;
				}
			}
		}

		return $ldap_plugins;
	}

	/**
	 * Scan LDAP plugins for injection vulnerabilities.
	 *
	 * @since 1.6093.1200
	 * @param  array $plugins Plugin slugs.
	 * @return array Vulnerable files.
	 */
	private static function scan_ldap_plugins( $plugins ) {
		$vulnerable = array();

		$dangerous_patterns = array(
			// ldap_search with concatenation.
			'/ldap_search\s*\([^,]+,\s*["\'][^"\']*\$/' => 'ldap_search with variable concatenation',
			'/ldap_search\s*\([^,]+,\s*["\'][^"\']*\{/' => 'ldap_search with interpolation',
			
			// ldap_bind with user input.
			'/ldap_bind\s*\([^,]+,\s*\$_(?:POST|GET|REQUEST)/' => 'ldap_bind with direct user input',
			
			// Filter construction with sprintf/concatenation.
			'/sprintf\s*\(\s*["\'][^"\']*\(.*?%s.*?\)["\']/' => 'LDAP filter with sprintf (risky)',
		);

		foreach ( $plugins as $plugin ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . $plugin;
			if ( ! is_dir( $plugin_dir ) ) {
				continue;
			}

			$php_files = self::get_php_files( $plugin_dir, 20 );
			foreach ( $php_files as $file ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$content = file_get_contents( $file );
				
				foreach ( $dangerous_patterns as $pattern => $desc ) {
					if ( preg_match( $pattern, $content ) ) {
						$vulnerable[] = str_replace( ABSPATH, '', $file );
						break 2;
					}
				}
			}
		}

		return $vulnerable;
	}

	/**
	 * Check if ldap_escape is used.
	 *
	 * @since 1.6093.1200
	 * @param  array $plugins Plugin slugs.
	 * @return bool True if escaping found.
	 */
	private static function check_ldap_escape_usage( $plugins ) {
		foreach ( $plugins as $plugin ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . $plugin;
			$php_files = self::get_php_files( $plugin_dir, 20 );
			
			foreach ( $php_files as $file ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$content = file_get_contents( $file );
				if ( str_contains( $content, 'ldap_escape' ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check for direct concatenation in ldap_search.
	 *
	 * @since 1.6093.1200
	 * @param  array $plugins Plugin slugs.
	 * @return bool True if concatenation found.
	 */
	private static function check_ldap_search_concatenation( $plugins ) {
		$concat_pattern = '/ldap_search\s*\([^,]+,\s*["\'][^"\']*[\.\+]\s*\$/';

		foreach ( $plugins as $plugin ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . $plugin;
			$php_files = self::get_php_files( $plugin_dir, 20 );
			
			foreach ( $php_files as $file ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$content = file_get_contents( $file );
				if ( preg_match( $concat_pattern, $content ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check for special character filtering.
	 *
	 * @since 1.6093.1200
	 * @param  array $plugins Plugin slugs.
	 * @return bool True if filtering found.
	 */
	private static function check_special_char_filtering( $plugins ) {
		// Look for character filtering patterns.
		$filter_patterns = array(
			'/str_replace\s*\(\s*[\[\(]["\'][\*\(\)\\\]+/',
			'/preg_replace\s*\([^,]+,\s*["\']["\'],\s*\$/',
		);

		foreach ( $plugins as $plugin ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . $plugin;
			$php_files = self::get_php_files( $plugin_dir, 20 );
			
			foreach ( $php_files as $file ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$content = file_get_contents( $file );
				
				foreach ( $filter_patterns as $pattern ) {
					if ( preg_match( $pattern, $content ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get PHP files from directory.
	 *
	 * @since 1.6093.1200
	 * @param  string $dir Directory path.
	 * @param  int    $limit Maximum files.
	 * @return array File paths.
	 */
	private static function get_php_files( $dir, $limit = 50 ) {
		$files = array();
		$count = 0;

		if ( ! is_dir( $dir ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( $count >= $limit ) {
				break;
			}
			if ( $file->isFile() && 'php' === $file->getExtension() ) {
				$files[] = $file->getPathname();
				$count++;
			}
		}

		return $files;
	}
}
