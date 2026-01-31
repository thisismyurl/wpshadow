<?php
/**
 * Elementor Custom Code Security and Updates Diagnostic
 *
 * Detect security issues in Elementor custom CSS/JS and custom widgets.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6030.1230
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Custom Code Security Diagnostic Class
 *
 * @since 1.6030.1230
 */
class Diagnostic_ElementorCustomCodeSecurity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'elementor-custom-code-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Elementor Custom Code Security and Updates';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detect security issues in Elementor custom CSS/JS and custom widgets';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.1230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Elementor is active
		if ( ! defined( 'ELEMENTOR_VERSION' ) && ! class_exists( '\Elementor\Plugin' ) ) {
			return null;
		}

		$issues = array();
		global $wpdb;

		// Check 1: Check for inline JavaScript (XSS risk)
		$inline_js_count = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_elementor_data'
			AND (meta_value LIKE '%<script%' OR meta_value LIKE '%javascript:%' OR meta_value LIKE '%onclick=%')"
		);

		if ( $inline_js_count > 0 ) {
			$issues[] = sprintf( '%d elements with inline JavaScript (XSS risk)', $inline_js_count );
		}

		// Check 2: Verify custom CSS sanitized
		$custom_css_count = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_elementor_page_settings'
			AND meta_value LIKE '%custom_css%'"
		);

		if ( $custom_css_count > 20 ) {
			$issues[] = sprintf( '%d pages with custom CSS (review for malicious code)', $custom_css_count );
		}

		// Check 3: Test for custom widget security
		$custom_widgets = get_option( 'elementor_custom_widgets', array() );
		if ( ! empty( $custom_widgets ) && is_array( $custom_widgets ) ) {
			$issues[] = sprintf( '%d custom widgets registered (verify code security)', count( $custom_widgets ) );
		}

		// Check 4: Check for third-party Elementor addons security
		$active_plugins = get_option( 'active_plugins', array() );
		$elementor_addons = array_filter( $active_plugins, function( $plugin ) {
			return strpos( $plugin, 'elementor' ) !== false ||
				   strpos( $plugin, 'essential-addons' ) !== false ||
				   strpos( $plugin, 'premium-addons' ) !== false ||
				   strpos( $plugin, 'powerpack' ) !== false;
		});

		if ( count( $elementor_addons ) > 5 ) {
			$issues[] = sprintf( '%d Elementor addons active (verify all are trusted)', count( $elementor_addons ) );
		}

		// Check 5: Verify custom code doesn't expose sensitive data
		$sensitive_patterns = array( 'api_key', 'password', 'secret', 'token', 'private_key' );
		$sensitive_data_count = 0;

		foreach ( $sensitive_patterns as $pattern ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->postmeta}
					WHERE meta_key = '_elementor_data'
					AND meta_value LIKE %s",
					'%' . $wpdb->esc_like( $pattern ) . '%'
				)
			);
			$sensitive_data_count += intval( $count );
		}

		if ( $sensitive_data_count > 0 ) {
			$issues[] = sprintf( '%d elements may expose sensitive data', $sensitive_data_count );
		}

		// Check 6: Check for unsafe external script loading
		$external_scripts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_elementor_data'
			AND (meta_value LIKE '%src=\"http%' OR meta_value LIKE '%src=\\'http%')"
		);

		if ( $external_scripts > 10 ) {
			$issues[] = sprintf( '%d external scripts loaded (verify all sources)', $external_scripts );
		}

		// Return finding if issues exist
		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 65 + ( count( $issues ) * 5 ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Elementor custom code security issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/elementor-custom-code-security',
			);
		}

		return null;
	}
}
