<?php
/**
 * Mobile Responsiveness Check Diagnostic
 *
 * Validates pages render correctly on mobile devices
 * and follow mobile-first best practices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1045
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Responsiveness Check Class
 *
 * Validates mobile viewport configuration and responsive design.
 * 60%+ of traffic is mobile; bad mobile UX increases bounce rate.
 *
 * @since 1.5029.1045
 */
class Diagnostic_Mobile_Responsiveness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-responsiveness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Responsiveness Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates mobile-first design and viewport configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'design';

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes homepage HTML for mobile responsiveness indicators.
	 * Checks viewport meta, responsive CSS, and mobile optimizations.
	 *
	 * @since  1.5029.1045
	 * @return array|null Finding array if mobile issues found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_mobile_responsiveness_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();

		// Fetch homepage HTML.
		$response = wp_remote_get( home_url(), array(
			'timeout'   => 15,
			'sslverify' => false,
		) );

		if ( is_wp_error( $response ) ) {
			set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
			return null;
		}

		$html = wp_remote_retrieve_body( $response );

		if ( empty( $html ) ) {
			set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
			return null;
		}

		// Check for viewport meta tag.
		$has_viewport = preg_match( '/<meta\s+name=["\']viewport["\']/i', $html );
		if ( ! $has_viewport ) {
			$issues[] = __( 'No viewport meta tag found (required for mobile)', 'wpshadow' );
		} else {
			// Check viewport configuration.
			if ( ! preg_match( '/content=["\'][^"\']*width=device-width[^"\']*["\']/i', $html ) ) {
				$issues[] = __( 'Viewport not set to device-width', 'wpshadow' );
			}
		}

		// Check for responsive CSS indicators.
		$has_media_queries = preg_match( '/@media\s*\([^)]*\)/i', $html );
		if ( ! $has_media_queries ) {
			$issues[] = __( 'No CSS media queries detected (not responsive)', 'wpshadow' );
		}

		// Check for mobile-unfriendly elements.
		if ( preg_match( '/<table\s+[^>]*width\s*=\s*["\']?\d+["\']?/i', $html ) ) {
			$issues[] = __( 'Fixed-width tables detected (may not be mobile-friendly)', 'wpshadow' );
		}

		// Check theme support.
		$theme_supports_responsive = current_theme_supports( 'responsive-embeds' );
		if ( ! $theme_supports_responsive ) {
			$issues[] = __( 'Theme does not declare responsive embed support', 'wpshadow' );
		}

		// Check for mobile-specific optimizations.
		$has_amp = is_plugin_active( 'amp/amp.php' );
		$has_mobile_plugin = is_plugin_active( 'jetpack/jetpack.php' ); // Jetpack has mobile theme.

		// Check font sizes (common mobile issue).
		if ( preg_match( '/font-size\s*:\s*[0-9]{1,2}px/i', $html, $matches ) ) {
			// Small fixed fonts are bad for mobile.
			$issues[] = __( 'Small fixed font sizes detected (readability issue on mobile)', 'wpshadow' );
		}

		// If issues found, flag it.
		if ( ! empty( $issues ) ) {
			$threat_level = 45;
			if ( count( $issues ) >= 2 ) {
				$threat_level = 55;
			}
			if ( ! $has_viewport ) {
				$threat_level = 65;
			}

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( 'Mobile responsiveness has %d issues. Site may not display correctly on mobile devices.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => $threat_level > 60 ? 'high' : 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/design-mobile-responsiveness',
				'data'         => array(
					'issues'                 => $issues,
					'has_viewport'           => $has_viewport,
					'has_media_queries'      => $has_media_queries,
					'theme_supports_responsive' => $theme_supports_responsive,
					'has_amp'                => $has_amp,
					'has_mobile_plugin'      => $has_mobile_plugin,
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
