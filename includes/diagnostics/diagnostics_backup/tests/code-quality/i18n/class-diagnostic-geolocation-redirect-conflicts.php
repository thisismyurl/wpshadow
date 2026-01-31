<?php
/**
 * Geolocation Redirect Conflicts Diagnostic
 *
 * Detects if auto-language redirect conflicts with user choice, trapping users
 * in the wrong language without an override option.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Geolocation Redirect Conflicts Diagnostic Class
 *
 * Tests auto-language redirect behavior to ensure user choice is respected
 * and users aren't trapped in redirect loops.
 *
 * @since 1.6028.1445
 */
class Diagnostic_Geolocation_Redirect_Conflicts extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'geolocation-redirect-conflicts';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Geolocation Redirect Conflicts';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects if auto-language redirect conflicts with user choice, trapping users in wrong language';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'i18n';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check cache first.
		$cache_key = 'wpshadow_geolocation_redirect_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$result = self::check_geolocation_redirects();

		// Cache for 12 hours.
		set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Check for geolocation redirect conflicts.
	 *
	 * @since  1.6028.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	private static function check_geolocation_redirects() {
		// Check if multilingual plugins are active.
		$i18n_data = self::get_i18n_plugin_data();

		if ( empty( $i18n_data['plugin'] ) ) {
			return null; // No multilingual plugin, no redirect conflicts.
		}

		$issues = array();

		// Check for auto-redirect configuration.
		if ( $i18n_data['has_auto_redirect'] ) {
			// Check if user choice is respected.
			if ( ! $i18n_data['respects_user_choice'] ) {
				$issues[] = __( 'Auto-redirect does not respect user language choice', 'wpshadow' );
			}

			// Check for language switcher.
			if ( ! $i18n_data['has_language_switcher'] ) {
				$issues[] = __( 'No language switcher available to override auto-redirect', 'wpshadow' );
			}

			// Check for cookie/session storage.
			if ( ! $i18n_data['stores_preference'] ) {
				$issues[] = __( 'Language preference not stored (causes repeated redirects)', 'wpshadow' );
			}

			// Check for redirect loops.
			if ( self::has_redirect_loops() ) {
				$issues[] = __( 'Redirect loop detected when switching languages', 'wpshadow' );
			}
		}

		// If issues found, create finding.
		if ( ! empty( $issues ) ) {
			$severity     = count( $issues ) >= 3 ? 'high' : 'medium';
			$threat_level = count( $issues ) >= 3 ? 65 : 50;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( '%d geolocation redirect issues detected that may trap users', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/geolocation-redirect-conflicts',
				'meta'         => array(
					'i18n_plugin'           => $i18n_data['plugin'],
					'has_auto_redirect'     => $i18n_data['has_auto_redirect'],
					'respects_user_choice'  => $i18n_data['respects_user_choice'],
					'has_language_switcher' => $i18n_data['has_language_switcher'],
					'stores_preference'     => $i18n_data['stores_preference'],
					'issues_count'          => count( $issues ),
				),
				'details'      => $issues,
				'recommendations' => self::get_recommendations( $i18n_data, $issues ),
			);
		}

		return null;
	}

	/**
	 * Get i18n plugin data.
	 *
	 * @since  1.6028.1445
	 * @return array Plugin data.
	 */
	private static function get_i18n_plugin_data() {
		// Check WPML.
		if ( class_exists( 'SitePress' ) ) {
			return self::get_wpml_data();
		}

		// Check Polylang.
		if ( function_exists( 'pll_current_language' ) ) {
			return self::get_polylang_data();
		}

		// Check TranslatePress.
		if ( class_exists( 'TRP_Translate_Press' ) ) {
			return self::get_translatepress_data();
		}

		// Check Weglot.
		if ( class_exists( 'Weglot\Client\Api\LanguageEntry' ) ) {
			return self::get_weglot_data();
		}

		return array( 'plugin' => null );
	}

	/**
	 * Get WPML configuration data.
	 *
	 * @since  1.6028.1445
	 * @return array WPML data.
	 */
	private static function get_wpml_data() {
		$settings = get_option( 'icl_sitepress_settings', array() );

		return array(
			'plugin'                => 'wpml',
			'has_auto_redirect'     => isset( $settings['automatic_redirect'] ) && 1 === (int) $settings['automatic_redirect'],
			'respects_user_choice'  => isset( $settings['remember_language'] ) && 1 === (int) $settings['remember_language'],
			'has_language_switcher' => self::has_wpml_language_switcher(),
			'stores_preference'     => isset( $_COOKIE['wp-wpml_current_language'] ),
		);
	}

	/**
	 * Get Polylang configuration data.
	 *
	 * @since  1.6028.1445
	 * @return array Polylang data.
	 */
	private static function get_polylang_data() {
		$options = get_option( 'polylang', array() );

		return array(
			'plugin'                => 'polylang',
			'has_auto_redirect'     => isset( $options['browser'] ) && 1 === (int) $options['browser'],
			'respects_user_choice'  => true, // Polylang respects choice by default.
			'has_language_switcher' => is_active_widget( false, false, 'polylang' ),
			'stores_preference'     => isset( $_COOKIE['pll_language'] ),
		);
	}

	/**
	 * Get TranslatePress configuration data.
	 *
	 * @since  1.6028.1445
	 * @return array TranslatePress data.
	 */
	private static function get_translatepress_data() {
		$settings = get_option( 'trp_settings', array() );

		return array(
			'plugin'                => 'translatepress',
			'has_auto_redirect'     => isset( $settings['force-language-to-browser'] ) && 'yes' === $settings['force-language-to-browser'],
			'respects_user_choice'  => true, // TranslatePress respects choice.
			'has_language_switcher' => true, // Always has floater/shortcode.
			'stores_preference'     => isset( $_COOKIE['trp_language'] ),
		);
	}

	/**
	 * Get Weglot configuration data.
	 *
	 * @since  1.6028.1445
	 * @return array Weglot data.
	 */
	private static function get_weglot_data() {
		$options = get_option( 'weglot-options', array() );

		return array(
			'plugin'                => 'weglot',
			'has_auto_redirect'     => isset( $options['auto_redirect'] ) && 1 === (int) $options['auto_redirect'],
			'respects_user_choice'  => true, // Weglot respects choice.
			'has_language_switcher' => true, // Always has switcher.
			'stores_preference'     => isset( $_COOKIE['weglot_language'] ),
		);
	}

	/**
	 * Check if WPML language switcher is active.
	 *
	 * @since  1.6028.1445
	 * @return bool True if switcher detected.
	 */
	private static function has_wpml_language_switcher() {
		// Check for WPML language switcher widget.
		if ( is_active_widget( false, false, 'icl_lang_sel_widget' ) ) {
			return true;
		}

		// Check homepage for language switcher.
		$response = wp_remote_get( home_url( '/' ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$html = wp_remote_retrieve_body( $response );

		return false !== strpos( $html, 'wpml-ls' ) ||
		       false !== strpos( $html, 'language-switcher' );
	}

	/**
	 * Check for redirect loops.
	 *
	 * @since  1.6028.1445
	 * @return bool True if redirect loop detected.
	 */
	private static function has_redirect_loops() {
		// Test homepage for redirect behavior.
		$args = array(
			'timeout'     => 5,
			'redirection' => 2, // Limit redirects.
			'headers'     => array(
				'Accept-Language' => 'fr-FR,fr;q=0.9', // Test with French.
			),
		);

		$response = wp_remote_get( home_url( '/' ), $args );

		if ( is_wp_error( $response ) ) {
			// Check if error is due to too many redirects.
			return false !== strpos( $response->get_error_message(), 'redirect' );
		}

		return false;
	}

	/**
	 * Get recommendations based on issues.
	 *
	 * @since  1.6028.1445
	 * @param  array $i18n_data Plugin data.
	 * @param  array $issues Issues found.
	 * @return array Recommendations.
	 */
	private static function get_recommendations( $i18n_data, $issues ) {
		$recommendations = array();

		$plugin = $i18n_data['plugin'];

		if ( ! $i18n_data['respects_user_choice'] ) {
			switch ( $plugin ) {
				case 'wpml':
					$recommendations[] = __( 'Enable "Remember visitors language" in WPML settings', 'wpshadow' );
					break;
				case 'polylang':
					$recommendations[] = __( 'Polylang should respect user choice by default', 'wpshadow' );
					break;
				default:
					$recommendations[] = __( 'Configure plugin to remember user language preference', 'wpshadow' );
			}
		}

		if ( ! $i18n_data['has_language_switcher'] ) {
			$recommendations[] = __( 'Add language switcher widget or menu item', 'wpshadow' );
			$recommendations[] = __( 'Place language switcher in header or footer for visibility', 'wpshadow' );
		}

		if ( ! $i18n_data['stores_preference'] ) {
			$recommendations[] = __( 'Enable cookie storage for language preference', 'wpshadow' );
		}

		if ( in_array( __( 'Redirect loop detected when switching languages', 'wpshadow' ), $issues, true ) ) {
			$recommendations[] = __( 'URGENT: Fix redirect loops immediately', 'wpshadow' );
			$recommendations[] = __( 'Check for conflicting redirect rules in .htaccess', 'wpshadow' );
		}

		// General recommendations.
		$recommendations[] = __( 'Test language switching from different geolocations', 'wpshadow' );
		$recommendations[] = __( 'Provide clear visual feedback when language changes', 'wpshadow' );
		$recommendations[] = __( 'Consider disabling auto-redirect for first-time visitors', 'wpshadow' );

		return $recommendations;
	}
}
