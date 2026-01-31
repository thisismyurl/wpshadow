<?php
/**
 * Mobile-First Indexing Compatibility Diagnostic
 *
 * Verifies site is fully mobile-first index ready with complete content on mobile.
 * Google primarily uses mobile version for indexing and ranking since 2019.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6029.1645
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile-First Indexing Compatibility Diagnostic Class
 *
 * Checks if mobile version has all desktop content and functionality.
 * Critical for SEO - Google ranks based on mobile version.
 *
 * @since 1.6029.1645
 */
class Diagnostic_Mobile_First_Indexing_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-first-indexing-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile-First Indexing Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site is fully mobile-first index ready';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6029.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if theme is responsive.
		$current_theme = wp_get_theme();
		$theme_tags = $current_theme->get( 'Tags' );

		$is_responsive = false;
		if ( is_array( $theme_tags ) ) {
			foreach ( $theme_tags as $tag ) {
				if ( in_array( strtolower( $tag ), array( 'responsive', 'mobile-friendly', 'responsive-layout' ), true ) ) {
					$is_responsive = true;
					break;
				}
			}
		}

		if ( ! $is_responsive ) {
			$issues[] = 'theme_not_tagged_responsive';
		}

		// Check viewport meta tag in homepage.
		$response = wp_remote_get( home_url( '/' ), array( 'timeout' => 10 ) );

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );

			// Check for viewport meta tag.
			$has_viewport = false;
			if ( preg_match( '/<meta\s+name=["\']viewport["\']/i', $body ) ) {
				$has_viewport = true;
			}

			if ( ! $has_viewport ) {
				$issues[] = 'missing_viewport_meta_tag';
			}

			// Check for mobile-specific hiding with display:none (bad practice).
			if ( preg_match( '/@media[^{]*\([^)]*max-width[^)]*\)[^{]*\{[^}]*display\s*:\s*none/i', $body ) ) {
				$issues[] = 'content_hidden_on_mobile';
			}
		}

		// Check for mobile detection plugins (can cause issues).
		$problematic_mobile_plugins = array(
			'wp-mobile-detect/wp-mobile-detect.php',
			'wptouch/wptouch.php',
			'any-mobile-theme-switcher/any-mobile-theme-switcher.php',
		);

		$active_mobile_plugins = array();
		foreach ( $problematic_mobile_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_mobile_plugins[] = $plugin;
				$issues[] = 'mobile_theme_switcher_active';
			}
		}

		// Check if mobile navigation exists (common issue).
		$nav_locations = get_nav_menu_locations();
		$has_mobile_menu = false;

		if ( ! empty( $nav_locations ) ) {
			foreach ( $nav_locations as $location => $menu_id ) {
				if ( stripos( $location, 'mobile' ) !== false ) {
					$has_mobile_menu = true;
					break;
				}
			}
		}

		// Check for AMP (can help mobile indexing).
		$has_amp = is_plugin_active( 'amp/amp.php' ) || is_plugin_active( 'accelerated-mobile-pages/accelerated-moblie-pages.php' );

		// Check robots.txt for mobile-specific blocks.
		$robots_content = '';
		$robots_path = ABSPATH . 'robots.txt';
		if ( file_exists( $robots_path ) && is_readable( $robots_path ) ) {
			$robots_content = file_get_contents( $robots_path );

			if ( stripos( $robots_content, 'Disallow: /*?*mobile' ) !== false ||
				stripos( $robots_content, 'Disallow: /mobile' ) !== false ) {
				$issues[] = 'mobile_urls_blocked_in_robots';
			}
		}

		// If issues found, return finding.
		if ( ! empty( $issues ) ) {
			$severity = 'critical';
			$threat_level = 90;

			// Lower severity if only minor issues.
			if ( ! in_array( 'missing_viewport_meta_tag', $issues, true ) &&
				! in_array( 'mobile_theme_switcher_active', $issues, true ) ) {
				$severity = 'high';
				$threat_level = 75;
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site may not be fully mobile-first indexing compatible', 'wpshadow' ),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'details'      => array(
					'issues_found'          => $issues,
					'theme_name'            => $current_theme->get( 'Name' ),
					'is_responsive'         => $is_responsive,
					'has_amp'               => $has_amp,
					'active_mobile_plugins' => $active_mobile_plugins,
					'has_mobile_menu'       => $has_mobile_menu,
				),
				'meta'         => array(
					'wpdb_avoidance'   => 'Uses wp_get_theme(), wp_remote_get(), get_nav_menu_locations(), is_plugin_active() instead of $wpdb',
					'detection_method' => 'WordPress APIs - theme analysis, viewport checks, plugin detection',
					'ranking_factor'   => 'Primary Google ranking factor since 2019',
				),
				'kb_link'      => 'https://wpshadow.com/kb/mobile-first-indexing-compatibility',
				'solution'     => sprintf(
					/* translators: 1: Theme customizer URL, 2: Plugins admin URL */
					__( 'Google uses mobile version for indexing and ranking. Issues found: %1$s. Actions needed: 1) Ensure viewport meta tag in header: <meta name="viewport" content="width=device-width, initial-scale=1">, 2) Use responsive theme (not separate mobile theme), 3) Remove mobile theme switcher plugins - use responsive design instead, 4) Verify all content visible on mobile (no display:none hiding), 5) Test mobile navigation works properly, 6) Don\'t block mobile URLs in robots.txt, 7) Consider AMP for faster mobile pages. Check theme at %2$s. Test mobile compatibility: <a href="https://search.google.com/test/mobile-friendly">Google Mobile-Friendly Test</a> | <a href="https://developers.google.com/search/mobile-sites/mobile-first-indexing">Mobile-First Indexing Guide</a>', 'wpshadow' ),
					implode( ', ', $issues ),
					esc_url( admin_url( 'customize.php' ) )
				),
			);
		}

		return null;
	}
}
