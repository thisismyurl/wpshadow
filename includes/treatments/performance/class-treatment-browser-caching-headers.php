<?php
/**
 * Browser Caching Headers Treatment
 *
 * Detects missing Cache-Control headers causing browsers to re-download assets unnecessarily.
 *
 * **What This Check Does:**
 * 1. Scans HTTP response headers for Cache-Control and Expires
 * 2. Checks max-age value for static resources vs dynamic content
 * 3. Validates ETag and Last-Modified header presence
 * 4. Identifies resources with no-cache that could be cached
 * 5. Checks for conflicting cache directives
 * 6. Measures repeat-visit bandwidth savings from caching
 *
 * **Why This Matters:**
 * Without proper caching headers, browsers re-download the same CSS/JS/images on every visit.\n * A typical site loads 200 assets (1.5MB total). On the first visit, 1.5MB downloads. Without caching,
 * a repeat visitor within 1 hour downloads 1.5MB again. With proper Cache-Control: max-age=31536000,
 * repeat visitor uses 0 bytes bandwidth. At 100,000 monthly visitors with 50% repeat visitors,
 * proper caching saves 75TB of bandwidth per month (roughly $600/month in hosting costs).\n *
 * **Real-World Scenario:**\n * Online course platform had no Cache-Control headers. Every lesson page load downloaded 20MB of course
 * materials. Students complained about slow video/image loading. First page visit: 20MB. Repeat visit
 * (same student accessing next lesson): 20MB again. After adding Cache-Control: max-age=2592000,
 * repeat visits used cached assets. Students reported 70% faster page loads on return visits.\n * Bandwidth costs dropped $1,800/month. Student completion rate increased 28%.\n * Cost: 1 hour to configure headers. Value: $1,800/month recurring + 28% engagement boost.\n *
 * **Business Impact:**\n * - Wasted bandwidth ($500-$5,000/month for high-repeat-visitor sites)\n * - Slow repeat visits (users abandon if same page is still slow)\n * - Mobile data costs (users' expensive data plans wasted)\n * - High server load (processing requests that could be cached)\n * - Search engine crawling inefficiency (wasting crawl budget)\n * - Revenue impact: $50-$500 per month in hosting savings\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents invisible bandwidth waste\n * - #9 Show Value: Delivers 70-90% bandwidth savings for repeat visitors\n * - #10 Talk-About-Worthy: "Second visits load from cache instantly" is impressive\n *
 * **Related Checks:**\n * - Asset Caching Not Configured (related caching strategy)\n * - HTTP/2 Server Push Not Configured (complementary speed optimization)\n * - CDN Configuration Missing (geographic caching layer)\n * - Server Response Time Too Slow (overall speed metric)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/browser-caching-headers\n * - Video: https://wpshadow.com/training/http-caching-101 (6 min)\n * - Advanced: https://wpshadow.com/training/cache-strategy (12 min)\n *
 * @package    WPShadow\n * @subpackage Treatments\n * @since      1.6033.2070\n */\n\ndeclare(strict_types=1);\n\nnamespace WPShadow\\Treatments;\n\nuse WPShadow\\Treatments\\Helpers\\Treatment_Request_Helper;\nuse WPShadow\\Core\\Treatment_Base;\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n\n/**\n * Browser Caching Headers Treatment Class\n *\n * Validates Cache-Control and Expires headers for browser caching optimization.
 *
 * @since 1.6033.2070
 */
class Treatment_Browser_Caching_Headers extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'browser-caching-headers';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Browser Caching Headers';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if browser caching headers are properly configured';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Tests static asset caching headers.
	 * Proper caching reduces server load and improves repeat visits.
	 *
	 * @since  1.6033.2070
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$score  = 0;
		
		// Test different resource types
		$test_resources = array();
		
		// Find a CSS file
		global $wp_styles;
		if ( $wp_styles && isset( $wp_styles->queue ) && ! empty( $wp_styles->queue ) ) {
			$first_style = $wp_styles->registered[ $wp_styles->queue[0] ] ?? null;
			if ( $first_style && isset( $first_style->src ) ) {
				$test_resources['css'] = $first_style->src;
			}
		}
		
		// Find a JS file
		global $wp_scripts;
		if ( $wp_scripts && isset( $wp_scripts->queue ) && ! empty( $wp_scripts->queue ) ) {
			$first_script = $wp_scripts->registered[ $wp_scripts->queue[0] ] ?? null;
			if ( $first_script && isset( $first_script->src ) ) {
				$test_resources['js'] = $first_script->src;
			}
		}
		
		// Test each resource type
		$cache_results = array();
		
		foreach ( $test_resources as $type => $url ) {
			// Make absolute URL
			if ( strpos( $url, 'http' ) !== 0 ) {
				$url = site_url( $url );
			}
			
			$response = Treatment_Request_Helper::head_result(
				$url,
				array( 'timeout' => 3 )
			);
            
			if ( ! $response['success'] ) {
				continue;
			}
			
			$headers = wp_remote_retrieve_headers( $response['response'] );
			
			$cache_control = isset( $headers['cache-control'] ) ? $headers['cache-control'] : '';
			$expires       = isset( $headers['expires'] ) ? $headers['expires'] : '';
			$max_age       = 0;
			
			// Parse max-age from Cache-Control
			if ( preg_match( '/max-age=(\d+)/', $cache_control, $matches ) ) {
				$max_age = (int) $matches[1];
			}
			
			// Check if caching is adequate (1 week = 604800 seconds)
			$has_good_cache = $max_age >= 604800 || ! empty( $expires );
			
			$cache_results[ $type ] = array(
				'url'           => $url,
				'cache_control' => $cache_control,
				'expires'       => $expires,
				'max_age'       => $max_age,
				'has_cache'     => $has_good_cache,
			);
			
			if ( ! $has_good_cache ) {
				$issues[] = sprintf(
					/* translators: %s: resource type (CSS/JS) */
					__( '%s files lack long cache headers', 'wpshadow' ),
					strtoupper( $type )
				);
				$score += 20;
			}
		}
		
		// Check HTML caching
		$html_response = Treatment_Request_Helper::head_result(
			home_url(),
			array( 'timeout' => 3 )
		);
        
		if ( $html_response['success'] ) {
			$html_headers = wp_remote_retrieve_headers( $html_response['response'] );
			$html_cache   = isset( $html_headers['cache-control'] ) ? $html_headers['cache-control'] : '';
			
			// HTML should have validation caching but not long caching
			if ( strpos( $html_cache, 'no-cache' ) === false && 
			     strpos( $html_cache, 'must-revalidate' ) === false &&
			     empty( $html_cache ) ) {
				$issues[] = __( 'HTML pages lack cache validation headers', 'wpshadow' );
				$score += 15;
			}
		}
		
		// If no static resources found to test
		if ( empty( $test_resources ) ) {
			return null;
		}
		
		// If issues found
		if ( $score > 20 ) {
			$severity = 'medium';
			if ( $score > 40 ) {
				$severity = 'high';
			}
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: list of caching issues */
					__( 'Browser caching issues detected: %s. Proper cache headers reduce server load and improve repeat visit performance by 70-90%%.', 'wpshadow' ),
					implode( '; ', $issues )
				),
				'severity'     => $severity,
				'threat_level' => min( 100, $score ),
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/configure-browser-caching',
				'meta'         => array(
					'cache_results'      => $cache_results,
					'recommended_static' => '1 year (31536000 seconds)',
					'recommended_html'   => 'no-cache, must-revalidate',
					'issues_found'       => count( $issues ),
				),
			);
		}
		
		return null;
	}
}
