<?php
/**
 * Unpatched Plugins Diagnostic
 *
 * Audits active plugin versions against known security vulnerabilities
 * to ensure all plugins are current.
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
 * Diagnostic_Unpatched_Plugins Class
 *
 * Detects plugins with known security vulnerabilities.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Unpatched_Plugins extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'unpatched-plugins';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Unpatched Plugins Security Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies plugins with known security vulnerabilities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Known plugin vulnerabilities database
	 *
	 * @var array
	 */
	const VULNERABLE_PLUGINS = array(
		'elementor' => array(
			'<3.5.0' => array( 'vuln' => 'Arbitrary File Upload', 'severity' => 'critical' ),
		),
		'woocommerce' => array(
			'<6.0.0' => array( 'vuln' => 'SQL Injection', 'severity' => 'high' ),
		),
		'jetpack' => array(
			'<10.0.0' => array( 'vuln' => 'Unauthenticated Options Update', 'severity' => 'critical' ),
		),
		'contact-form-7' => array(
			'<5.5.0' => array( 'vuln' => 'Stored XSS', 'severity' => 'high' ),
		),
		'yoast-seo' => array(
			'<18.0.0' => array( 'vuln' => 'Blind SQL Injection', 'severity' => 'high' ),
		),
		'wordfence' => array(
			'<7.5.0' => array( 'vuln' => 'Privilege Escalation', 'severity' => 'critical' ),
		),
	);

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if vulnerable plugins detected, null otherwise.
	 */
	public static function check() {
		$vulnerabilities = self::check_plugin_vulnerabilities();

		if ( empty( $vulnerabilities ) ) {
			return null;
		}

		// Determine severity based on worst vulnerability
		$max_severity = 'high';
		foreach ( $vulnerabilities as $vuln ) {
			if ( $vuln['severity'] === 'critical' ) {
				$max_severity = 'critical';
				break;
			}
		}

		$threat_level = ( $max_severity === 'critical' ) ? 92 : 70;

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: plugin count */
				__( 'Found %d plugin(s) with known security vulnerabilities. Updates are critical.', 'wpshadow' ),
				count( $vulnerabilities )
			),
			'severity'      => $max_severity,
			'threat_level'  => $threat_level,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/update-plugins-security',
			'family'        => self::$family,
			'meta'          => array(
				'vulnerable_plugins'  => array_column( $vulnerabilities, 'plugin_name' ),
				'critical_count'      => count( array_filter( $vulnerabilities, function( $v ) {
					return $v['severity'] === 'critical';
				} ) ),
				'high_count'          => count( array_filter( $vulnerabilities, function( $v ) {
					return $v['severity'] === 'high';
				} ) ),
				'immediate_actions'   => array(
					__( 'Update all plugins to latest version immediately' ),
					__( 'Backup database before updating' ),
					__( 'Test on staging first if possible' ),
					__( 'Check plugin compatibility with WordPress version' ),
				),
			),
			'details'       => array(
				'vulnerabilities' => $vulnerabilities,
				'update_steps'    => array(
					'Step 1' => __( 'Go to WordPress Admin → Plugins → Plugin Updates' ),
					'Step 2' => __( 'Click "Update" for each vulnerable plugin' ),
					'Step 3' => __( 'Or use "Select All" and "Update Plugins" button' ),
					'Step 4' => __( 'Monitor site for any issues post-update' ),
					'Step 5' => __( 'If issues occur, deactivate plugin and revert to previous version' ),
				),
				'impact'           => array(
					__( 'Critical vulnerabilities allow complete site takeover, data theft, malware injection' ),
					__( 'High vulnerabilities allow unauthorized access, data modification' ),
					__( 'Every day unpatched increases attack risk exponentially' ),
				),
				'best_practices'   => array(
					__( 'Enable automatic plugin updates: Settings → General' ),
					__( 'Review plugin updates weekly' ),
					__( 'Remove unused plugins to reduce attack surface' ),
					__( 'Choose plugins with good security track record' ),
					__( 'Monitor WordPress Security Advisories mailing list' ),
				),
			),
		);
	}

	/**
	 * Check active plugins for known vulnerabilities.
	 *
	 * @since  1.2601.2148
	 * @return array Array of vulnerabilities.
	 */
	private static function check_plugin_vulnerabilities() {
		$vulnerabilities = array();
		$active_plugins = get_plugins();

		foreach ( $active_plugins as $plugin_file => $plugin_data ) {
			$plugin_slug = dirname( $plugin_file );
			$plugin_version = $plugin_data['Version'];
			$plugin_name = $plugin_data['Name'];

			foreach ( self::VULNERABLE_PLUGINS as $vuln_slug => $versions ) {
				if ( strpos( $plugin_slug, $vuln_slug ) === false ) {
					continue;
				}

				foreach ( $versions as $version_constraint => $details ) {
					if ( self::version_matches_constraint( $plugin_version, $version_constraint ) ) {
						$vulnerabilities[] = array(
							'plugin_name'        => $plugin_name,
							'plugin_slug'        => $plugin_slug,
							'current_version'    => $plugin_version,
							'vulnerable_version' => $version_constraint,
							'vulnerability'      => $details['vuln'],
							'severity'           => $details['severity'],
						);
					}
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
	 * @return bool True if matches constraint.
	 */
	private static function version_matches_constraint( $version, $constraint ) {
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
