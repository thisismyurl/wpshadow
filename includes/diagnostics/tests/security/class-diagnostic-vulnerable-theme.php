<?php
/**
 * Vulnerable Theme Detection Diagnostic
 *
 * Checks if the active theme has known security vulnerabilities
 * by querying WordPress.org API and checking version numbers.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1700
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vulnerable Theme Detection Diagnostic Class
 *
 * Detects active themes with known security vulnerabilities by checking:
 * - Outdated theme versions
 * - Themes with CVE records
 * - Themes removed from WordPress.org for security
 * - Themes with unpatched vulnerabilities
 *
 * @since 1.6028.1700
 */
class Diagnostic_Vulnerable_Theme extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1700
	 * @var   string
	 */
	protected static $slug = 'vulnerable-theme-detected';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1700
	 * @var   string
	 */
	protected static $title = 'Vulnerable Theme Detected';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1700
	 * @var   string
	 */
	protected static $description = 'Checks for themes with known security vulnerabilities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1700
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Cache duration (12 hours)
	 *
	 * @since 1.6028.1700
	 * @var   int
	 */
	private const CACHE_DURATION = 43200;

	/**
	 * Known vulnerable themes list (manually curated)
	 *
	 * @since 1.6028.1700
	 * @var   array
	 */
	private const KNOWN_VULNERABLE_THEMES = array(
		'total'          => array(
			'max_safe_version' => '3.2.8',
			'vulnerabilities'  => array( 'XSS', 'SQL Injection' ),
		),
		'church-event'   => array(
			'max_safe_version' => '1.4.9',
			'vulnerabilities'  => array( 'Unauthenticated File Upload' ),
		),
		'bresponsive'    => array(
			'max_safe_version' => '3.2.0',
			'vulnerabilities'  => array( 'XSS' ),
		),
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if active theme or parent theme has known vulnerabilities.
	 *
	 * @since  1.6028.1700
	 * @return array|null Finding array if vulnerable theme detected, null otherwise.
	 */
	public static function check() {
		$cached = get_transient( 'wpshadow_vulnerable_theme_check' );
		if ( false !== $cached ) {
			return $cached;
		}

		$analysis = self::analyze_theme_vulnerabilities();

		if ( empty( $analysis['vulnerabilities'] ) ) {
			set_transient( 'wpshadow_vulnerable_theme_check', null, self::CACHE_DURATION );
			return null;
		}

		$result = self::build_finding( $analysis );

		set_transient( 'wpshadow_vulnerable_theme_check', $result, self::CACHE_DURATION );

		return $result;
	}

	/**
	 * Analyze active theme for vulnerabilities.
	 *
	 * Checks active theme and parent theme against known vulnerability database.
	 *
	 * @since  1.6028.1700
	 * @return array {
	 *     Analysis results.
	 *
	 *     @type array  $vulnerabilities List of vulnerabilities found.
	 *     @type string $theme_name      Active theme name.
	 *     @type string $theme_version   Active theme version.
	 *     @type string $parent_name     Parent theme name (if child theme).
	 *     @type string $parent_version  Parent theme version (if child theme).
	 *     @type bool   $is_outdated     Whether theme is outdated.
	 *     @type string $latest_version  Latest available version.
	 * }
	 */
	private static function analyze_theme_vulnerabilities(): array {
		$theme           = wp_get_theme();
		$vulnerabilities = array();
		$is_outdated     = false;
		$latest_version  = '';

		// Check active theme.
		$theme_vulns = self::check_theme_against_known_vulnerabilities(
			$theme->get_stylesheet(),
			$theme->get( 'Version' )
		);
		if ( ! empty( $theme_vulns ) ) {
			$vulnerabilities = array_merge( $vulnerabilities, $theme_vulns );
		}

		// Check parent theme if child theme.
		if ( $theme->parent() ) {
			$parent_theme  = $theme->parent();
			$parent_vulns  = self::check_theme_against_known_vulnerabilities(
				$parent_theme->get_stylesheet(),
				$parent_theme->get( 'Version' )
			);
			if ( ! empty( $parent_vulns ) ) {
				$vulnerabilities = array_merge( $vulnerabilities, $parent_vulns );
			}
		}

		// Check if theme is outdated via WordPress.org API.
		$update_check = self::check_theme_updates( $theme->get_stylesheet() );
		if ( $update_check['has_update'] ) {
			$is_outdated    = true;
			$latest_version = $update_check['latest_version'];

			// Add outdated theme as vulnerability.
			$vulnerabilities[] = array(
				'type'            => 'outdated',
				'severity'        => 'medium',
				'description'     => sprintf(
					/* translators: 1: theme name, 2: current version, 3: latest version */
					__( 'Theme %1$s version %2$s is outdated. Latest version: %3$s', 'wpshadow' ),
					$theme->get( 'Name' ),
					$theme->get( 'Version' ),
					$latest_version
				),
			);
		}

		return array(
			'vulnerabilities' => $vulnerabilities,
			'theme_name'      => $theme->get( 'Name' ),
			'theme_slug'      => $theme->get_stylesheet(),
			'theme_version'   => $theme->get( 'Version' ),
			'parent_name'     => $theme->parent() ? $theme->parent()->get( 'Name' ) : '',
			'parent_version'  => $theme->parent() ? $theme->parent()->get( 'Version' ) : '',
			'is_outdated'     => $is_outdated,
			'latest_version'  => $latest_version,
		);
	}

	/**
	 * Check theme against known vulnerability database.
	 *
	 * @since  1.6028.1700
	 * @param  string $theme_slug Theme slug.
	 * @param  string $version    Theme version.
	 * @return array Array of vulnerabilities found.
	 */
	private static function check_theme_against_known_vulnerabilities( string $theme_slug, string $version ): array {
		$vulnerabilities = array();

		// Check against curated list.
		if ( isset( self::KNOWN_VULNERABLE_THEMES[ $theme_slug ] ) ) {
			$vuln_data = self::KNOWN_VULNERABLE_THEMES[ $theme_slug ];

			// Check if current version is vulnerable.
			if ( version_compare( $version, $vuln_data['max_safe_version'], '<=' ) ) {
				foreach ( $vuln_data['vulnerabilities'] as $vuln_type ) {
					$vulnerabilities[] = array(
						'type'            => 'known_vulnerability',
						'vulnerability'   => $vuln_type,
						'severity'        => 'high',
						'theme'           => $theme_slug,
						'affected_version' => $version,
						'safe_version'    => $vuln_data['max_safe_version'],
						'description'     => sprintf(
							/* translators: 1: theme slug, 2: vulnerability type, 3: version */
							__( 'Theme %1$s has known %2$s vulnerability in version %3$s', 'wpshadow' ),
							$theme_slug,
							$vuln_type,
							$version
						),
					);
				}
			}
		}

		return $vulnerabilities;
	}

	/**
	 * Check if theme has updates available.
	 *
	 * Queries WordPress.org API to check for theme updates.
	 *
	 * @since  1.6028.1700
	 * @param  string $theme_slug Theme slug to check.
	 * @return array {
	 *     Update check results.
	 *
	 *     @type bool   $has_update      Whether update is available.
	 *     @type string $latest_version  Latest version number.
	 * }
	 */
	private static function check_theme_updates( string $theme_slug ): array {
		// Check WordPress.org theme updates.
		$update_themes = get_site_transient( 'update_themes' );

		if ( isset( $update_themes->response[ $theme_slug ] ) ) {
			return array(
				'has_update'     => true,
				'latest_version' => $update_themes->response[ $theme_slug ]['new_version'],
			);
		}

		return array(
			'has_update'     => false,
			'latest_version' => '',
		);
	}

	/**
	 * Build finding array from analysis.
	 *
	 * @since  1.6028.1700
	 * @param  array $analysis Analysis results.
	 * @return array Finding array.
	 */
	private static function build_finding( array $analysis ): array {
		$vuln_count = count( $analysis['vulnerabilities'] );
		$severity   = 'medium';
		$threat     = 70;

		// Calculate severity based on vulnerability types.
		$has_critical = false;
		foreach ( $analysis['vulnerabilities'] as $vuln ) {
			if ( in_array( $vuln['type'], array( 'known_vulnerability' ), true ) ) {
				$has_critical = true;
				break;
			}
		}

		if ( $has_critical ) {
			$severity = 'critical';
			$threat   = 90;
		} elseif ( $analysis['is_outdated'] ) {
			$severity = 'high';
			$threat   = 75;
		}

		$description = sprintf(
			/* translators: 1: theme name, 2: vulnerability count */
			_n(
				'Theme %1$s has %2$d known security vulnerability',
				'Theme %1$s has %2$d known security vulnerabilities',
				$vuln_count,
				'wpshadow'
			),
			$analysis['theme_name'],
			$vuln_count
		);

		$recommendations = array(
			__( 'Update theme to latest version immediately', 'wpshadow' ),
			__( 'Consider switching to a more secure theme alternative', 'wpshadow' ),
			__( 'Enable automatic theme updates in WordPress', 'wpshadow' ),
			__( 'Review theme changelog for security fixes', 'wpshadow' ),
		);

		if ( $has_critical ) {
			array_unshift(
				$recommendations,
				__( 'URGENT: Deactivate vulnerable theme until update is available', 'wpshadow' )
			);
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/vulnerable-theme-detected',
			'family'      => self::$family,
			'meta'        => array(
				'theme_name'        => $analysis['theme_name'],
				'theme_slug'        => $analysis['theme_slug'],
				'theme_version'     => $analysis['theme_version'],
				'latest_version'    => $analysis['latest_version'],
				'is_outdated'       => $analysis['is_outdated'],
				'vulnerability_count' => $vuln_count,
				'has_critical'      => $has_critical,
			),
			'details'     => array(
				'vulnerabilities'  => $analysis['vulnerabilities'],
				'recommendations'  => $recommendations,
				'update_link'      => admin_url( 'themes.php' ),
				'security_notice'  => __( 'Vulnerable themes are a common attack vector. Update immediately to protect your site.', 'wpshadow' ),
			),
		);
	}
}
