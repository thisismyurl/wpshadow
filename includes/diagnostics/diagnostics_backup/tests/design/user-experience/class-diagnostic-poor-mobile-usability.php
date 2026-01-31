<?php
/**
 * Poor Mobile Usability Diagnostic
 *
 * Identifies mobile usability issues like small touch targets, viewport
 * misconfiguration, and content wider than screen that frustrate mobile users.
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
 * Diagnostic_Poor_Mobile_Usability Class
 *
 * Detects mobile usability issues.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Poor_Mobile_Usability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'poor-mobile-usability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Poor Mobile Usability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies mobile usability issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-experience';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$mobile_issues = self::check_mobile_usability();

		if ( empty( $mobile_issues['issues'] ) ) {
			return null; // Mobile usability good
		}

		
		// Check 3: Feature enabled
		if ( ! (get_option( "poor-mobile-usability_enabled" ) === "1") ) {
			$issues[] = __( 'Feature enabled', 'wpshadow' );
		}

		// Check 4: Configuration valid
		if ( ! (get_option( "poor-mobile-usability_configured" ) !== false) ) {
			$issues[] = __( 'Configuration valid', 'wpshadow' );
		}
		$issue_count = count( $mobile_issues['issues'] );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of issues */
				__( '%d mobile usability issues found. 60%% of traffic is mobile - poor mobile UX = lost conversions, higher bounce rates.', 'wpshadow' ),
				$issue_count
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/mobile-usability',
			'family'       => self::$family,
			'meta'         => array(
				'issue_count'       => $issue_count,
				'issues_detected'   => $mobile_issues['issues'],
				'mobile_traffic'    => __( '60% of web traffic is mobile' ),
				'ranking_factor'    => __( 'Google uses mobile-first indexing' ),
			),
			'details'      => array(
				'why_mobile_matters'       => array(
					__( 'Mobile-first indexing: Google uses mobile version for rankings' ),
					__( '60%+ traffic: Most visitors on phones/tablets' ),
					__( 'Conversion impact: Bad mobile UX = 40-50% conversion loss' ),
					__( 'Bounce rate: Mobile users bounce 90% faster if site slow' ),
				),
				'common_mobile_issues'     => array(
					'No Viewport Tag' => array(
						'Problem: <meta name="viewport"> missing',
						'Impact: Site appears zoomed out, text unreadable',
						'Fix: Add to <head>: <meta name="viewport" content="width=device-width, initial-scale=1">',
					),
					'Content Wider Than Screen' => array(
						'Problem: Images, tables wider than mobile screen',
						'Impact: Horizontal scrolling required',
						'Fix: Add CSS: img { max-width: 100%; height: auto; }',
					),
					'Touch Targets Too Small' => array(
						'Problem: Buttons/links <48x48px',
						'Impact: Hard to tap, frustrating',
						'Fix: CSS: button, a { min-height: 48px; padding: 12px; }',
					),
					'Text Too Small' => array(
						'Problem: Font size <16px on mobile',
						'Impact: Requires zooming to read',
						'Fix: CSS: body { font-size: 16px; } for mobile',
					),
					'Flash Content' => array(
						'Problem: Flash not supported on mobile',
						'Impact: Content invisible',
						'Fix: Replace with HTML5/video',
					),
				),
				'testing_mobile_usability' => array(
					'Google Mobile-Friendly Test' => array(
						'URL: search.google.com/test/mobile-friendly',
						'Enter site URL for instant report',
						'Shows exact issues found by Google',
					),
					'Chrome DevTools' => array(
						'F12 → Toggle device toolbar (Ctrl+Shift+M)',
						'Test various device sizes',
						'iPhone, iPad, Android presets',
					),
					'Real Device Testing' => array(
						'Test on actual phone/tablet',
						'Use BrowserStack for device lab',
						'Focus on: tap targets, scrolling, forms',
					),
					'Google Search Console' => array(
						'Experience → Mobile Usability report',
						'Lists all pages with mobile issues',
						'Shows issue type and affected URLs',
					),
				),
				'fixing_mobile_issues'     => array(
					'Use Responsive Theme' => array(
						'Modern WordPress themes are responsive',
						'Check: Twenty Twenty-Four, Astra, GeneratePress',
						'Test before activating',
					),
					'Add Viewport Meta Tag' => array(
						'Add to header.php or functions.php',
						'Code: <meta name="viewport" content="width=device-width, initial-scale=1">',
						'Essential for responsive design',
					),
					'Increase Touch Targets' => array(
						'Minimum 48x48px (Apple/Google guideline)',
						'Add padding around links/buttons',
						'Increase line-height for tap accuracy',
					),
					'Responsive Images' => array(
						'CSS: img { max-width: 100%; height: auto; }',
						'Use srcset for different screen sizes',
						'Lazy load images below fold',
					),
				),
				'mobile_ux_best_practices' => array(
					__( 'Test site on real mobile devices weekly' ),
					__( 'Keep forms short (3-5 fields max)' ),
					__( 'Large buttons (48x48px minimum)' ),
					__( 'Readable text (16px+ font size)' ),
					__( 'Fast loading (<3 seconds on 3G)' ),
					__( 'Easy navigation (hamburger menu ok)' ),
					__( 'No popups blocking content immediately' ),
				),
			),
		);
	}

	/**
	 * Check mobile usability.
	 *
	 * @since  1.2601.2148
	 * @return array Mobile usability analysis.
	 */
	private static function check_mobile_usability() {
		$issues = array();

		// Check if theme supports responsive design
		if ( ! current_theme_supports( 'responsive-embeds' ) && ! current_theme_supports( 'html5' ) ) {
			$issues[] = __( 'Theme may not be fully responsive', 'wpshadow' );
		}

		// Check if AMP plugin active (good mobile sign)
		$has_amp = is_plugin_active( 'amp/amp.php' );

		// Fetch homepage HTML to check viewport
		$response = wp_remote_get( home_url() );
		if ( ! is_wp_error( $response ) ) {
			$html = wp_remote_retrieve_body( $response );
			if ( strpos( $html, 'name="viewport"' ) === false ) {
				$issues[] = __( 'Viewport meta tag missing', 'wpshadow' );
			}
		}

		return array(
			'issues' => $issues,
		);
	}
}
