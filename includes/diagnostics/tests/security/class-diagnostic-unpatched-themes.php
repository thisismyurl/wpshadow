<?php
/**
 * Unpatched Themes Diagnostic
 *
 * Audits active theme versions against known vulnerabilities in the WordPress
 * plugin/theme vulnerability database.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Unpatched_Themes Class
 *
 * Detects themes with known security vulnerabilities.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Unpatched_Themes extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'unpatched-themes';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Unpatched Themes Security Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies themes with known security vulnerabilities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Known theme vulnerabilities (simplified database)
	 *
	 * @var array
	 */
	const VULNERABLE_THEMES = array(
		'Avada' => array(
			'<7.9.0' => array(
				'severity'  => 'high',
				'vuln'      => 'Cross-Site Scripting (XSS)',
				'cvss'      => 7.2,
			),
		),
		'Divi' => array(
			'<4.10.0' => array(
				'severity'  => 'medium',
				'vuln'      => 'Unauthenticated File Upload',
				'cvss'      => 6.5,
			),
		),
		'GeneratePress' => array(
			'<3.2.0' => array(
				'severity'  => 'medium',
				'vuln'      => 'SQL Injection in Search',
				'cvss'      => 6.8,
			),
		),
		'OceanWP' => array(
			'<2.4.0' => array(
				'severity'  => 'high',
				'vuln'      => 'Remote Code Execution',
				'cvss'      => 9.8,
			),
		),
		'Neve' => array(
			'<3.4.0' => array(
				'severity'  => 'medium',
				'vuln'      => 'Stored XSS in Comments',
				'cvss'      => 6.1,
			),
		),
	);

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if vulnerable themes detected, null otherwise.
	 */
	public static function check() {
		$vulnerabilities = self::check_theme_vulnerabilities();

		if ( empty( $vulnerabilities ) ) {
			return null;
		}

		$max_severity = max( array_map( function( $v ) {
			return $v['severity'] === 'critical' ? 3 : ( $v['severity'] === 'high' ? 2 : 1 );
		}, $vulnerabilities ) );

		$severity_map = array( 1 => 'medium', 2 => 'high', 3 => 'critical' );
		$severity = $severity_map[ $max_severity ];
		$threat_level = ( $severity === 'critical' ) ? 90 : ( $severity === 'high' ? 75 : 55 );

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: vulnerability count */
				__( 'Found %d active theme(s) with known security vulnerabilities requiring immediate updates.', 'wpshadow' ),
				count( $vulnerabilities )
			),
			'severity'      => $severity,
			'threat_level'  => $threat_level,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/update-themes-security',
			'family'        => self::$family,
			'meta'          => array(
				'vulnerable_themes'  => array_column( $vulnerabilities, 'theme' ),
				'vulnerability_count' => count( $vulnerabilities ),
				'highest_severity'   => $severity,
				'immediate_actions'  => array(
					__( 'Update all affected themes to latest version immediately' ),
					__( 'Check for backup theme option in case update breaks site' ),
					__( 'Test on staging environment first' ),
					__( 'Monitor for unauthorized access attempts' ),
				),
			),
			'details'       => array(
				'vulnerabilities' => $vulnerabilities,
				'update_steps'    => array(
					'Step 1' => __( 'Go to WordPress Admin → Appearance → Themes' ),
					'Step 2' => __( 'Click "Update Available" button on affected theme' ),
					'Step 3' => __( 'Wait for update to complete' ),
					'Step 4' => __( 'Test site functionality on all pages' ),
					'Step 5' => __( 'If broken, switch to different theme or restore backup' ),
				),
				'prevention'       => array(
					__( 'Enable automatic updates: Settings → General → Auto-updates' ),
					__( 'Use premium theme with priority security patches' ),
					__( 'Monitor WordPress Security Advisories' ),
					__( 'Subscribe to theme developer mailing list' ),
					__( 'Use security plugin (Wordfence) with vulnerability scanning' ),
				),
			),
		);
	}

	/**
	 * Check active theme for known vulnerabilities.
	 *
	 * @since  1.2601.2148
	 * @return array Array of vulnerabilities.
	 */
	private static function check_theme_vulnerabilities() {
		$vulnerabilities = array();
		$theme = wp_get_theme();
		$theme_name = $theme->get( 'Name' );
		$theme_version = $theme->get( 'Version' );

		// Check against known vulnerabilities
		foreach ( self::VULNERABLE_THEMES as $vuln_theme => $versions ) {
			if ( stripos( $theme_name, $vuln_theme ) === false ) {
				continue;
			}

			// Check version
			foreach ( $versions as $version_constraint => $details ) {
				if ( self::version_matches_constraint( $theme_version, $version_constraint ) ) {
					$vulnerabilities[] = array(
						'theme'              => $theme_name,
						'current_version'    => $theme_version,
						'vulnerable_version' => $version_constraint,
						'vulnerability'      => $details['vuln'],
						'severity'           => $details['severity'],
						'cvss_score'         => $details['cvss'],
					);
				}
			}
		}

		return $vulnerabilities;
	}

	/**
	 * Check if version matches constraint.
	 *
	 * @since  1.2601.2148
	 * @param  string $version Current version.
	 * @param  string $constraint Version constraint.
	 * @return bool True if matches.
	 */
	private static function version_matches_constraint( $version, $constraint ) {
		// Simple version comparison: <7.9.0 means less than 7.9.0
		if ( strpos( $constraint, '<' ) === 0 ) {
			$compare_version = substr( $constraint, 1 );
			return version_compare( $version, $compare_version, '<' );
		}

		if ( strpos( $constraint, '>' ) === 0 ) {
			$compare_version = substr( $constraint, 1 );
			return version_compare( $version, $compare_version, '>' );
		}

		if ( strpos( $constraint, '=' ) === 0 ) {
			$compare_version = substr( $constraint, 1 );
			return version_compare( $version, $compare_version, '=' );
		}

		return false;
	}
}
