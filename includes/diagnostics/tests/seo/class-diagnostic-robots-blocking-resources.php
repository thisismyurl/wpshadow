<?php
/**
 * Robots.txt Blocking Important Resources Diagnostic
 *
 * Detects robots.txt rules that block CSS/JS files needed for proper
 * rendering, hurting Google's ability to index content accurately.
 *
 * @since   1.6028.1525
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Robots_Blocking_Resources Class
 *
 * Checks robots.txt for rules that block critical rendering resources
 * like CSS and JavaScript files.
 *
 * @since 1.6028.1525
 */
class Diagnostic_Robots_Blocking_Resources extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'robots-txt-blocking-resources';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Robots.txt Blocking Important Resources';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects robots.txt disallowing CSS/JS files needed for proper rendering';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * Parses robots.txt and checks for Disallow rules blocking
	 * critical resources like CSS, JS, fonts, or images.
	 *
	 * @since  1.6028.1525
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$robots_content = self::get_robots_txt();

		if ( ! $robots_content ) {
			return null; // No robots.txt or can't read it
		}

		$blocked_resources = self::analyze_robots_txt( $robots_content );

		if ( empty( $blocked_resources ) ) {
			return null; // No critical resources blocked
		}

		$severity = in_array( 'css', $blocked_resources, true ) || in_array( 'js', $blocked_resources, true ) ? 'high' : 'medium';
		$threat_level = $severity === 'high' ? 70 : 50;

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %s: comma-separated list of blocked resource types */
				__( 'Your robots.txt file is blocking %s resources from being crawled. This prevents Google from properly rendering and understanding your pages, hurting indexation quality and rankings.', 'wpshadow' ),
				implode( ', ', $blocked_resources )
			),
			'severity'      => $severity,
			'threat_level'  => $threat_level,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/robots-txt-resources',
			'family'        => self::$family,
			'meta'          => array(
				'blocked_resources' => $blocked_resources,
				'robots_url'        => home_url( 'robots.txt' ),
				'impact_level'      => $severity === 'high' ? __( 'High - Google cannot render pages properly', 'wpshadow' ) : __( 'Medium - Indexation quality reduced', 'wpshadow' ),
				'immediate_actions' => array(
					__( 'Edit robots.txt to allow CSS and JavaScript files', 'wpshadow' ),
					__( 'Test changes with Google Search Console URL Inspection', 'wpshadow' ),
					__( 'Request re-indexing after fixing', 'wpshadow' ),
				),
			),
			'details'       => array(
				'why_important'    => __( 'Google needs access to CSS and JavaScript to render your pages like a real browser. When these resources are blocked, Google sees an unstyled, broken page which can lead to incorrect indexation, lower rankings, or pages being dropped from the index entirely. Google explicitly recommends allowing access to all resources needed for rendering.', 'wpshadow' ),
				'user_impact'      => array(
					__( 'Rankings: Pages may rank lower due to poor rendering', 'wpshadow' ),
					__( 'Indexation: Pages may be excluded from index if content not visible', 'wpshadow' ),
					__( 'Mobile: Mobile-first indexing relies heavily on proper rendering', 'wpshadow' ),
					__( 'Rich Results: Structured data may not be detected without JS', 'wpshadow' ),
				),
				'google_guidance'  => array(
					__( 'Allow Googlebot to access CSS and JavaScript files', 'wpshadow' ),
					__( 'Allow access to fonts needed for proper text rendering', 'wpshadow' ),
					__( 'Allow access to images (at minimum, hero/critical images)', 'wpshadow' ),
					__( 'Test with Search Console Fetch & Render tool', 'wpshadow' ),
				),
				'solution_options' => array(
					'Edit robots.txt' => array(
						'description' => __( 'Remove or comment out blocking rules', 'wpshadow' ),
						'time'        => __( '10 minutes', 'wpshadow' ),
						'cost'        => __( 'Free', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
						'steps'       => array(
							__( 'Access robots.txt file (root directory or virtual)', 'wpshadow' ),
							__( 'Find lines like "Disallow: /wp-includes/"', 'wpshadow' ),
							__( 'Remove or comment out with # symbol', 'wpshadow' ),
							__( 'Save changes', 'wpshadow' ),
							__( 'Test: https://www.google.com/webmasters/tools/robots-testing-tool', 'wpshadow' ),
						),
					),
					'WordPress Plugin' => array(
						'description' => __( 'Use Yoast SEO or RankMath to manage robots.txt', 'wpshadow' ),
						'time'        => __( '5 minutes', 'wpshadow' ),
						'cost'        => __( 'Free', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
					),
				),
				'common_mistakes'  => array(
					__( 'Disallow: /wp-includes/ (blocks jQuery and core JS)', 'wpshadow' ),
					__( 'Disallow: /wp-content/ (blocks themes, plugins, media)', 'wpshadow' ),
					__( 'Disallow: /*.js (blocks all JavaScript)', 'wpshadow' ),
					__( 'Disallow: /*.css (blocks all stylesheets)', 'wpshadow' ),
					__( 'Old security advice to hide WordPress (outdated)', 'wpshadow' ),
				),
				'testing_steps'    => array(
					'Step 1' => __( 'Visit yoursite.com/robots.txt', 'wpshadow' ),
					'Step 2' => __( 'Look for Disallow rules mentioning CSS, JS, or directories', 'wpshadow' ),
					'Step 3' => __( 'Edit robots.txt to allow these resources', 'wpshadow' ),
					'Step 4' => __( 'Test: Google Search Console > URL Inspection > Test Live URL', 'wpshadow' ),
					'Step 5' => __( 'Verify "Page is indexable" and check rendered screenshot', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Get robots.txt content.
	 *
	 * @since  1.6028.1525
	 * @return string|false Robots.txt content or false if not found.
	 */
	private static function get_robots_txt() {
		// Try virtual robots.txt first
		$robots_url = home_url( 'robots.txt' );
		$response = wp_remote_get(
			$robots_url,
			array(
				'timeout' => 5,
				'sslverify' => false,
			)
		);

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			return wp_remote_retrieve_body( $response );
		}

		// Try physical file
		$robots_file = ABSPATH . 'robots.txt';
		if ( file_exists( $robots_file ) && is_readable( $robots_file ) ) {
			return file_get_contents( $robots_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		}

		return false;
	}

	/**
	 * Analyze robots.txt for blocked resources.
	 *
	 * @since  1.6028.1525
	 * @param  string $content Robots.txt content.
	 * @return array Array of blocked resource types.
	 */
	private static function analyze_robots_txt( $content ) {
		$blocked = array();

		// Check for common blocking patterns
		$patterns = array(
			'css'    => array( '/\.css', '/wp-content/themes/', '/wp-includes/css/' ),
			'js'     => array( '/\.js', '/wp-includes/js/' ),
			'fonts'  => array( '/\.woff', '/\.ttf', '/fonts/' ),
			'images' => array( '/\.jpg', '/\.png', '/\.gif', '/\.webp', '/wp-content/uploads/' ),
		);

		foreach ( $patterns as $type => $needles ) {
			foreach ( $needles as $needle ) {
				if ( preg_match( '/Disallow:\s*' . preg_quote( $needle, '/' ) . '/i', $content ) ) {
					$blocked[] = $type;
					break;
				}
			}
		}

		return array_unique( $blocked );
	}
}
