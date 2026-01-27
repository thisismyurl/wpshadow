<?php
/**
 * Diagnostic: Mobile Responsiveness Check
 *
 * Verifies site layout works on mobile devices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Mobile_Responsiveness
 *
 * Checks for mobile-friendly design indicators including viewport meta tag,
 * responsive theme support, and common mobile usability issues.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Mobile_Responsiveness extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-responsiveness';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Responsiveness Check';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verify site layout works on mobile devices';

	/**
	 * Run the diagnostic check.
	 *
	 * Performs basic mobile-friendliness checks.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if not mobile-friendly, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for viewport meta tag
		ob_start();
		wp_head();
		$head_content = ob_get_clean();

		$has_viewport_meta = false !== strpos( $head_content, 'name="viewport"' );

		if ( ! $has_viewport_meta ) {
			$issues[] = __( 'Missing viewport meta tag - essential for mobile responsiveness.', 'wpshadow' );
		}

		// Check if theme supports responsive design
		$theme = wp_get_theme();
		$theme_tags = $theme->get( 'Tags' );
		$is_responsive = false;

		if ( is_array( $theme_tags ) ) {
			$responsive_keywords = array( 'responsive', 'mobile', 'flexible-layout', 'fluid-layout' );
			foreach ( $responsive_keywords as $keyword ) {
				if ( in_array( $keyword, $theme_tags, true ) ) {
					$is_responsive = true;
					break;
				}
			}
		}

		// Check for mobile theme switcher plugins (outdated approach)
		$mobile_theme_plugins = array(
			'wptouch/wptouch.php' => 'WPtouch',
			'jetpack/jetpack.php' => 'Jetpack Mobile Theme',
		);

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$has_mobile_switcher = false;
		foreach ( $mobile_theme_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_mobile_switcher = true;
				break;
			}
		}

		if ( $has_mobile_switcher ) {
			$issues[] = __( 'Using mobile theme switcher plugin - modern responsive design is preferred over separate mobile themes.', 'wpshadow' );
		}

		// Check for fixed-width theme (simple heuristic)
		$stylesheet_uri = get_stylesheet_uri();
		$stylesheet_content = '';

		$response = wp_remote_get( $stylesheet_uri );
		if ( ! is_wp_error( $response ) ) {
			$stylesheet_content = wp_remote_retrieve_body( $response );
		}

		// Look for media queries in stylesheet
		$has_media_queries = false !== strpos( $stylesheet_content, '@media' );

		if ( ! $is_responsive && ! $has_media_queries && ! $has_viewport_meta ) {
			$issues[] = __( 'Theme does not appear to use responsive design (no media queries detected).', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			// Site appears mobile-friendly
			return null;
		}

		$description = sprintf(
			/* translators: %d: number of mobile responsiveness issues */
			_n(
				'Found %d mobile responsiveness issue. Over 60%% of web traffic now comes from mobile devices. Google uses mobile-first indexing, meaning mobile experience directly impacts SEO.',
				'Found %d mobile responsiveness issues. Over 60%% of web traffic now comes from mobile devices. Google uses mobile-first indexing, meaning mobile experience directly impacts SEO.',
				count( $issues ),
				'wpshadow'
			),
			count( $issues )
		) . ' ' . implode( ' ', $issues );

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => 'low',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/performance-mobile-responsiveness',
			'meta'        => array(
				'issues' => $issues,
				'has_viewport_meta' => $has_viewport_meta,
				'theme_responsive' => $is_responsive,
				'has_media_queries' => $has_media_queries,
			),
		);
	}
}
