<?php
/**
 * Robots.txt Configuration Diagnostic
 *
 * Analyzes robots.txt to verify it's properly configured to guide search
 * engine crawlers and prevent indexing of sensitive areas.
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
 * Diagnostic_Robots_Txt_Analysis Class
 *
 * Checks robots.txt configuration for SEO and security.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Robots_Txt_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'robots-txt-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Robots.txt Configuration Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes robots.txt for proper search engine crawler guidance';

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
	 * @return array|null Finding array if robots.txt issues detected, null otherwise.
	 */
	public static function check() {
		$robots_status = self::analyze_robots_txt();

		if ( $robots_status['is_optimal'] ) {
			return null;
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: issue count */
				__( 'Found %d robots.txt issues. Misconfiguration can prevent search engines from crawling your site.', 'wpshadow' ),
				count( $robots_status['issues'] )
			),
			'severity'      => 'medium',
			'threat_level'  => 50,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/robots-txt-optimization',
			'family'        => self::$family,
			'meta'          => array(
				'issues_found'      => $robots_status['issues'],
				'robots_txt_exists'  => $robots_status['exists'],
				'allows_crawling'    => $robots_status['allows_crawling'],
				'blocks_wp_admin'    => $robots_status['blocks_admin'],
			),
			'details'       => array(
				'current_issues'  => $robots_status['issues'],
				'best_practices'  => array(
					'✓ Allow major search engine bots' => __( 'Googlebot, Bingbot, etc.' ),
					'✓ Block spam bots' => __( 'AhrefsBot, SemrushBot (if unwanted)' ),
					'✓ Block admin areas' => __( 'Disallow: /wp-admin/, /wp-login.php' ),
					'✓ Block uploads meant for users only' => __( 'Disallow: /wp-includes/' ),
					'✓ Set crawl delay if needed' => __( 'Crawl-delay: 1 (for small servers)' ),
					'✓ Reference sitemap.xml' => __( 'Sitemap: https://example.com/sitemap.xml' ),
				),
				'example_robots_txt' => array(
					'User-agent: *' => 'Apply rules to all bots',
					'Allow: /' => 'Allow all public content',
					'Disallow: /wp-admin/' => 'Block WordPress admin',
					'Disallow: /wp-login.php' => 'Block login page',
					'Disallow: /wp-includes/' => 'Block includes directory',
					'Disallow: /?s=' => 'Block search results',
					'Sitemap: https://example.com/sitemap.xml' => 'Reference sitemap',
				),
				'blocking_rules'    => array(
					'Block Comment Forms' => 'Disallow: /*?s= (search) and /*?p= (may vary)',
					'Block Admin' => 'Disallow: /wp-admin/, /wp-login.php',
					'Block Private Areas' => 'Disallow: /wp-includes/, /wp-content/plugins/',
					'Block Duplicate Content' => 'Disallow: /?p=*, ?replytocom=* (trackback params)',
				),
				'tools'             => array(
					'Google Search Console' => 'Test robots.txt in "Robots.txt Tester" tool',
					'Seobility Robots.txt Checker' => 'Validates robots.txt syntax and rules',
					'Meta Robots Generator' => 'Creates optimized robots.txt for WordPress',
				),
				'common_mistakes'   => array(
					'❌ Blocking all bots (Disallow: /)' => 'Prevents search indexing completely',
					'❌ Blocking Googlebot' => 'Removes site from Google search results',
					'❌ No sitemap reference' => 'Search engines have harder time discovering pages',
					'❌ Overcomplicated rules' => 'Errors in syntax can cause unexpected behavior',
				),
			),
		);
	}

	/**
	 * Analyze robots.txt configuration.
	 *
	 * @since  1.2601.2148
	 * @return array Analysis results.
	 */
	private static function analyze_robots_txt() {
		$robots_url = home_url() . '/robots.txt';
		$response = wp_remote_get( $robots_url );

		$issues = array();
		$exists = false;
		$allows_crawling = true;
		$blocks_admin = false;

		if ( ! is_wp_error( $response ) ) {
			$code = wp_remote_retrieve_response_code( $response );

			if ( $code === 200 ) {
				$exists = true;
				$content = wp_remote_retrieve_body( $response );
				$content_lower = strtolower( $content );

				// Check if it allows crawling
				if ( strpos( $content_lower, 'disallow: /' ) !== false && 
					strpos( $content_lower, 'allow: /' ) === false ) {
					$allows_crawling = false;
					$issues[] = 'Robots.txt may be blocking all crawlers (Disallow: /)';
				}

				// Check if it blocks admin
				if ( strpos( $content_lower, 'disallow: /wp-admin' ) !== false ) {
					$blocks_admin = true;
				} else {
					$issues[] = 'WordPress admin directory not blocked in robots.txt';
				}

				// Check if sitemap is referenced
				if ( strpos( $content_lower, 'sitemap' ) === false ) {
					$issues[] = 'Sitemap not referenced in robots.txt';
				}
			} else {
				$exists = false;
				$issues[] = 'robots.txt not found (HTTP ' . $code . ')';
			}
		} else {
			$issues[] = 'Unable to fetch robots.txt';
		}

		
		// Check 4: Meta tags generated
		if ( ! (get_option( "seo_enabled" ) !== false) ) {
			$issues[] = __( 'Meta tags generated', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
			$issues[] = 'robots.txt is optimally configured';
		}

		return array(
			'is_optimal'      => count( $issues ) === 1 && strpos( $issues[0], 'optimally' ) !== false,
			'exists'          => $exists,
			'allows_crawling' => $allows_crawling,
			'blocks_admin'    => $blocks_admin,
			'issues'          => $issues,
		);
	}
}
