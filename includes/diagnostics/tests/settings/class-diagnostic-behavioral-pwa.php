<?php
/**
 * Behavioral PWA Diagnostic
 *
 * Checks if the site behaves like a Progressive Web App with proper user interactions,
 * installability prompts, and app-like behavior patterns.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Behavioral_PWA Class
 *
 * Verifies that the site implements Progressive Web App behavioral patterns
 * including installability, app-like interactions, and offline-first approach.
 *
 * @since 1.6035.1445
 */
class Diagnostic_Behavioral_PWA extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'behavioral-pwa';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Behavioral PWA Patterns';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the site implements Progressive Web App behavioral patterns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the behavioral PWA diagnostic check.
	 *
	 * @since  1.6035.1445
	 * @return array|null Finding array if PWA behavior issues detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		// Check for PWA manifest.
		$manifest_exists = self::check_manifest();
		$stats['has_manifest'] = $manifest_exists;

		if ( ! $manifest_exists ) {
			$issues[] = __( 'No web app manifest detected for installability', 'wpshadow' );
		}

		// Check for service worker.
		$sw_exists = self::check_service_worker();
		$stats['has_service_worker'] = $sw_exists;

		if ( ! $sw_exists ) {
			$issues[] = __( 'No service worker detected for offline functionality', 'wpshadow' );
		}

		// Check for app-like meta tags.
		$app_meta = self::check_app_meta_tags();
		$stats['app_meta_tags'] = count( $app_meta['found'] );

		if ( empty( $app_meta['found'] ) ) {
			$issues[] = __( 'Missing mobile app meta tags (apple-mobile-web-app-capable, etc.)', 'wpshadow' );
		}

		// Check for standalone display mode support.
		$standalone_support = self::check_standalone_mode();
		$stats['standalone_support'] = $standalone_support;

		// Check for installability criteria.
		$installable = self::check_installability_criteria();
		$stats['installable'] = $installable;

		if ( ! $installable ) {
			$issues[] = __( 'Site does not meet installability criteria for "Add to Home Screen"', 'wpshadow' );
		}

		// Check for app icons.
		$icons = self::check_app_icons();
		$stats['app_icons'] = count( $icons );

		if ( empty( $icons ) ) {
			$issues[] = __( 'No app icons configured for home screen installation', 'wpshadow' );
		}

		// If issues detected, return finding.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site lacks Progressive Web App behavioral patterns. Users cannot install it as an app or use it offline. This affects mobile engagement and user retention.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/pwa-behavioral-patterns',
				'details'      => array(
					'issues'         => $issues,
					'stats'          => $stats,
					'why_it_matters' => __( 'PWA features improve mobile engagement by 50-300% through installability, offline access, and app-like interactions.', 'wpshadow' ),
					'benefits'       => array(
						'installable'    => __( 'Users can install to home screen', 'wpshadow' ),
						'offline_access' => __( 'Content available offline', 'wpshadow' ),
						'engagement'     => __( 'Higher retention and return visits', 'wpshadow' ),
						'performance'    => __( 'Faster loads from caching', 'wpshadow' ),
					),
					'next_steps'     => array(
						__( 'Install a PWA plugin like Super Progressive Web Apps', 'wpshadow' ),
						__( 'Add web app manifest with app icons', 'wpshadow' ),
						__( 'Implement service worker for offline caching', 'wpshadow' ),
						__( 'Test installability on mobile devices', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}

	/**
	 * Check if web app manifest exists.
	 *
	 * @since  1.6035.1445
	 * @return bool True if manifest exists.
	 */
	private static function check_manifest(): bool {
		$manifest_paths = array(
			ABSPATH . 'manifest.json',
			ABSPATH . 'site.webmanifest',
			get_template_directory() . '/manifest.json',
			get_template_directory() . '/site.webmanifest',
		);

		foreach ( $manifest_paths as $path ) {
			if ( file_exists( $path ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if service worker exists.
	 *
	 * @since  1.6035.1445
	 * @return bool True if service worker exists.
	 */
	private static function check_service_worker(): bool {
		$sw_files = array(
			ABSPATH . 'sw.js',
			ABSPATH . 'service-worker.js',
		);

		foreach ( $sw_files as $file ) {
			if ( file_exists( $file ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for app-like meta tags.
	 *
	 * @since  1.6035.1445
	 * @return array Found and missing meta tags.
	 */
	private static function check_app_meta_tags(): array {
		ob_start();
		wp_head();
		$head_content = ob_get_clean();

		$required_tags = array(
			'apple-mobile-web-app-capable',
			'mobile-web-app-capable',
			'apple-mobile-web-app-status-bar-style',
			'theme-color',
		);

		$found = array();
		$missing = array();

		foreach ( $required_tags as $tag ) {
			if ( strpos( $head_content, $tag ) !== false ) {
				$found[] = $tag;
			} else {
				$missing[] = $tag;
			}
		}

		return array(
			'found'   => $found,
			'missing' => $missing,
		);
	}

	/**
	 * Check for standalone display mode support.
	 *
	 * @since  1.6035.1445
	 * @return bool True if standalone mode supported.
	 */
	private static function check_standalone_mode(): bool {
		$manifest_path = ABSPATH . 'manifest.json';
		if ( ! file_exists( $manifest_path ) ) {
			return false;
		}

		$manifest = json_decode( file_get_contents( $manifest_path ), true );
		if ( ! $manifest ) {
			return false;
		}

		return isset( $manifest['display'] ) && 'standalone' === $manifest['display'];
	}

	/**
	 * Check installability criteria.
	 *
	 * @since  1.6035.1445
	 * @return bool True if meets criteria.
	 */
	private static function check_installability_criteria(): bool {
		// Criteria: HTTPS, manifest, service worker, icons.
		return is_ssl() && self::check_manifest() && self::check_service_worker();
	}

	/**
	 * Check for app icons.
	 *
	 * @since  1.6035.1445
	 * @return array Array of detected icon sizes.
	 */
	private static function check_app_icons(): array {
		$manifest_path = ABSPATH . 'manifest.json';
		if ( ! file_exists( $manifest_path ) ) {
			return array();
		}

		$manifest = json_decode( file_get_contents( $manifest_path ), true );
		if ( ! $manifest || ! isset( $manifest['icons'] ) ) {
			return array();
		}

		$sizes = array();
		foreach ( $manifest['icons'] as $icon ) {
			if ( isset( $icon['sizes'] ) ) {
				$sizes[] = $icon['sizes'];
			}
		}

		return $sizes;
	}
}
