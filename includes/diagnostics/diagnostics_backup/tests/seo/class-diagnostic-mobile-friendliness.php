<?php
/**
 * Mobile Friendliness Diagnostic
 *
 * Checks if the site is properly configured for mobile devices,
 * including viewport meta tag and responsive design.
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
 * Diagnostic_Mobile_Friendliness Class
 *
 * Verifies mobile-friendly configuration and design.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Mobile_Friendliness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-friendliness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Friendliness Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site is properly optimized for mobile devices';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if mobile issues detected, null otherwise.
	 */
	public static function check() {
		$mobile_checks = self::check_mobile_configuration();

		if ( $mobile_checks['all_good'] ) {
			return null;
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: issue count */
				__( 'Found %d mobile-friendliness issues. Mobile traffic is 60%% of web traffic - optimization is critical.', 'wpshadow' ),
				count( $mobile_checks['issues'] )
			),
			'severity'      => 'high',
			'threat_level'  => 70,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/mobile-friendliness',
			'family'        => self::$family,
			'meta'          => array(
				'mobile_issues'        => $mobile_checks['issues'],
				'issue_count'          => count( $mobile_checks['issues'] ),
				'impact'               => __( 'Mobile users experience poor navigation, slow load times, layout issues' ),
				'seo_impact'           => __( 'Google prioritizes mobile-friendly sites in mobile search results' ),
				'conversion_impact'    => __( '80% of users abandon mobile-unfriendly sites immediately' ),
			),
			'details'       => array(
				'issues'              => $mobile_checks['issues'],
				'quick_fixes'         => array(
					'Fix 1: Add Viewport Meta Tag' => array(
						'Add to <head>: <meta name="viewport" content="width=device-width, initial-scale=1">',
						'This tells browsers how to scale page on mobile',
					),
					'Fix 2: Use Responsive Theme' => array(
						'Switch to mobile-friendly theme (most modern themes are)',
						'Recommended: GeneratePress, Astra, OceanWP',
					),
					'Fix 3: Test with Google Mobile-Friendly Test' => array(
						'Visit: https://search.google.com/test/mobile-friendly',
						'Paste your URL to see specific issues',
						'Fix issues and re-test',
					),
				),
				'mobile_optimization_checklist' => array(
					__( '✓ Viewport meta tag configured' ),
					__( '✓ Font sizes are readable (min 16px)' ),
					__( '✓ Touch targets are large enough (min 48x48px)' ),
					__( '✓ No horizontal scrolling required' ),
					__( '✓ Images are responsive (max-width: 100%)' ),
					__( '✓ Forms are easy to use on mobile' ),
					__( '✓ Page load time < 3 seconds on 3G' ),
					__( '✓ CSS and JavaScript optimized' ),
				),
				'mobile_seo_importance' => array(
					__( 'Google now uses Mobile-First Indexing' ),
					__( 'Mobile version is crawled first, used for ranking' ),
					__( '60%+ of search traffic is from mobile devices' ),
					__( 'Mobile-friendly sites rank higher in mobile search' ),
					__( 'Page experience signal includes mobile usability' ),
				),
			),
		);
	}

	/**
	 * Check mobile configuration.
	 *
	 * @since  1.2601.2148
	 * @return array Mobile check results.
	 */
	private static function check_mobile_configuration() {
		$issues = array();

		// Check 1: Viewport meta tag
		$response = wp_remote_get( home_url() );
		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			if ( strpos( $body, 'viewport' ) === false ) {
				$issues[] = 'Missing viewport meta tag - browsers won\'t scale correctly';
			}
		}

		// Check 2: Theme support
		if ( ! current_theme_supports( 'responsive-embeds' ) ) {
			$issues[] = 'Theme doesn\'t support responsive embeds';
		}

		// Check 3: Check if theme is mobile-friendly
		$theme = wp_get_theme();
		$mobile_friendly_themes = array(
			'GeneratePress',
			'Astra',
			'OceanWP',
			'Neve',
			'Divi',
			'Elementor',
		);

		$theme_name = $theme->get( 'Name' );
		$is_modern = false;
		foreach ( $mobile_friendly_themes as $mobile_theme ) {
			if ( stripos( $theme_name, $mobile_theme ) !== false ) {
				$is_modern = true;
				break;
			}
		}

		if ( ! $is_modern ) {
			$issues[] = 'Theme may not be fully responsive (consider upgrading)';
		}

		return array(
			'all_good' => empty( $issues ),
			'issues'   => $issues,
		);
	}
}
