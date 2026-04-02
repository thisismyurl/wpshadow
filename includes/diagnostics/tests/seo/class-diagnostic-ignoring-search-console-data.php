<?php
/**
 * Ignoring Search Console Data Diagnostic
 *
 * Detects lack of Google Search Console integration or monitoring,
 * missing critical SEO insights.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ignoring Search Console Data Diagnostic Class
 *
 * Checks if Google Search Console data is being monitored to optimize
 * SEO performance based on real search data.
 *
 * **Why This Matters:**
 * - Search Console shows how Google sees your site
 * - Reveals indexing issues immediately
 * - Shows actual search queries driving traffic
 * - Identifies content opportunities
 * - Critical for technical SEO monitoring
 *
 * **Search Console Provides:**
 * - Click-through rates
 * - Average position in SERPs
 * - Index coverage issues
 * - Mobile usability problems
 * - Core Web Vitals performance
 * - Security issues
 *
 * @since 1.6093.1200
 */
class Diagnostic_Ignoring_Search_Console_Data extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ignoring-search-console-data';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Ignoring Search Console Data';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Google Search Console isn\'t being monitored, missing critical SEO insights';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if Search Console not integrated, null otherwise.
	 */
	public static function check() {
		// Check if Site Kit is installed and connected
		if ( self::has_site_kit_integration() ) {
			return null;
		}

		// Check if other Search Console plugins are active
		if ( self::has_search_console_plugin() ) {
			return null;
		}

		// Check if site is verified in Search Console (via meta tag)
		if ( self::has_search_console_verification() ) {
			return null; // Verified, but recommend plugin for easier access
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your site isn\'t connected to Google Search Console. You\'re missing critical SEO data about how Google sees your site.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/seo-search-console',
			'details'      => array(
				'message'          => 'Install Site Kit by Google or verify site in Search Console',
				'recommended_tool' => 'Site Kit by Google (free plugin)',
				'benefits'         => array(
					'See what queries bring users to your site',
					'Monitor indexing and coverage issues',
					'Track Core Web Vitals performance',
					'Identify security and usability problems',
					'Optimize content based on search data',
				),
			),
		);
	}

	/**
	 * Check if Site Kit is installed and connected
	 *
	 * @since 1.6093.1200
	 * @return bool True if Site Kit connected, false otherwise.
	 */
	private static function has_site_kit_integration() {
		if ( ! is_plugin_active( 'google-site-kit/google-site-kit.php' ) ) {
			return false;
		}

		// Check if Search Console module is connected
		if ( function_exists( 'googlesitekit_is_module_connected' ) ) {
			return googlesitekit_is_module_connected( 'search-console' );
		}

		return false;
	}

	/**
	 * Check if other Search Console plugins are active
	 *
	 * @since 1.6093.1200
	 * @return bool True if Search Console plugin active, false otherwise.
	 */
	private static function has_search_console_plugin() {
		$plugins = array(
			'search-console/google-search-console.php',
			'wp-google-search-console-plugin/wp-google-search-console.php',
		);

		foreach ( $plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if site has Search Console verification meta tag
	 *
	 * @since 1.6093.1200
	 * @return bool True if verification tag found, false otherwise.
	 */
	private static function has_search_console_verification() {
		ob_start();
		wp_head();
		$head = ob_get_clean();

		return strpos( $head, 'google-site-verification' ) !== false;
	}
}
