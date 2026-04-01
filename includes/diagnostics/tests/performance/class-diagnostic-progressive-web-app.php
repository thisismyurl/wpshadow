<?php
/**
 * Progressive Web App Diagnostic
 *
 * Tests whether the site implements progressive web app features for app-like experience.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Progressive Web App Diagnostic Class
 *
 * PWAs provide app-like experiences including offline functionality, push notifications,
 * and installation to home screen, improving engagement and retention.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Progressive_Web_App extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'progressive-web-app';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Progressive Web App Features';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site implements progressive web app features for app-like experience';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'emerging-technology';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$pwa_score = 0;
		$max_score = 6;

		// Check for manifest.json file.
		$manifest_exists = self::check_manifest_file();
		if ( ! $manifest_exists ) {
			$issues[] = __( 'No web app manifest (manifest.json) detected', 'wpshadow' );
		} else {
			$pwa_score++;
		}

		// Check for service worker registration.
		$service_worker_exists = self::check_service_worker();
		if ( ! $service_worker_exists ) {
			$issues[] = __( 'No service worker registered for offline functionality', 'wpshadow' );
		} else {
			$pwa_score++;
		}

		// Check for HTTPS (required for PWA).
		if ( ! is_ssl() ) {
			$issues[] = __( 'HTTPS required for PWA features; site is not secure', 'wpshadow' );
		} else {
			$pwa_score++;
		}

		// Check for PWA plugins.
		$pwa_plugins = array(
			'super-progressive-web-apps/superpwa.php' => 'SuperPWA',
			'pwa/pwa.php' => 'PWA',
			'progressive-wp/progressive-wp.php' => 'Progressive WP',
		);

		$has_pwa_plugin = false;
		$active_plugin = '';
		foreach ( $pwa_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_pwa_plugin = true;
				$active_plugin = $plugin_name;
				$pwa_score++;
				break;
			}
		}

		// Check for theme PWA support.
		$theme_supports_pwa = current_theme_supports( 'pwa' ) || apply_filters( 'wpshadow_theme_supports_pwa', false );
		if ( $theme_supports_pwa ) {
			$pwa_score++;
		} elseif ( ! $has_pwa_plugin ) {
			$issues[] = __( 'No PWA plugin or theme support detected', 'wpshadow' );
		}

		// Check for offline page.
		$offline_page_exists = self::check_offline_page();
		if ( ! $offline_page_exists && ! $has_pwa_plugin ) {
			$issues[] = __( 'No offline fallback page configured', 'wpshadow' );
		} elseif ( $offline_page_exists ) {
			$pwa_score++;
		}

		// Check for app icons.
		$has_app_icons = self::check_app_icons();
		if ( ! $has_app_icons ) {
			$issues[] = __( 'App icons not configured for home screen installation', 'wpshadow' );
		} else {
			$pwa_score++;
		}

		// Determine severity based on PWA implementation completeness.
		$pwa_percentage = ( $pwa_score / $max_score ) * 100;

		if ( $pwa_percentage < 30 ) {
			// Minimal or no PWA implementation.
			$severity = 'medium';
			$threat_level = 50;
		} elseif ( $pwa_percentage < 70 ) {
			// Partial PWA implementation.
			$severity = 'low';
			$threat_level = 30;
		} else {
			// Good PWA implementation - no issue.
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: PWA implementation percentage */
				__( 'PWA implementation at %d%%. ', 'wpshadow' ),
				(int) $pwa_percentage
			) . implode( '. ', $issues );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/progressive-web-app?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}

	/**
	 * Check if manifest.json file exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if manifest exists, false otherwise.
	 */
	private static function check_manifest_file() {
		// Check common manifest locations.
		$manifest_paths = array(
			ABSPATH . 'manifest.json',
			get_template_directory() . '/manifest.json',
			get_stylesheet_directory() . '/manifest.json',
		);

		foreach ( $manifest_paths as $path ) {
			if ( file_exists( $path ) ) {
				return true;
			}
		}

		// Check if manifest link is in head.
		$manifest_link = has_action( 'wp_head', function() {
			return false !== strpos( ob_get_contents(), 'manifest.json' );
		} );

		return apply_filters( 'wpshadow_has_manifest_file', $manifest_link );
	}

	/**
	 * Check if service worker is registered.
	 *
	 * @since 0.6093.1200
	 * @return bool True if service worker exists, false otherwise.
	 */
	private static function check_service_worker() {
		// Check common service worker locations.
		$sw_paths = array(
			ABSPATH . 'sw.js',
			ABSPATH . 'service-worker.js',
			get_template_directory() . '/sw.js',
			get_stylesheet_directory() . '/sw.js',
		);

		foreach ( $sw_paths as $path ) {
			if ( file_exists( $path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_service_worker', false );
	}

	/**
	 * Check if offline page exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if offline page exists, false otherwise.
	 */
	private static function check_offline_page() {
		// Check for page with 'offline' slug.
		$offline_page = get_page_by_path( 'offline' );
		if ( $offline_page ) {
			return true;
		}

		// Check for offline.html file.
		$offline_html = ABSPATH . 'offline.html';
		if ( file_exists( $offline_html ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_offline_page', false );
	}

	/**
	 * Check if app icons are configured.
	 *
	 * @since 0.6093.1200
	 * @return bool True if app icons exist, false otherwise.
	 */
	private static function check_app_icons() {
		// Check for site icon (WordPress built-in).
		$site_icon = get_site_icon_url();
		if ( $site_icon ) {
			return true;
		}

		// Check for apple-touch-icon.
		$icon_paths = array(
			ABSPATH . 'apple-touch-icon.png',
			get_template_directory() . '/apple-touch-icon.png',
			get_stylesheet_directory() . '/apple-touch-icon.png',
		);

		foreach ( $icon_paths as $path ) {
			if ( file_exists( $path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_app_icons', false );
	}
}
