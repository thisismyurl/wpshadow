<?php
/**
 * Broken Internal Links Diagnostic
 *
 * Tests for broken internal links (404 errors) which hurt user experience
 * and SEO. Internal 404s waste crawl budget and damage user trust.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5003.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Broken_Internal_Links Class
 *
 * Detects broken internal links that lead to 404 pages. These hurt user
 * experience, waste crawl budget, and signal poor site maintenance.
 *
 * @since 1.5003.1200
 */
class Diagnostic_Broken_Internal_Links extends Diagnostic_Base {

	protected static $slug = 'broken-internal-links';
	protected static $title = 'Broken Internal Links';
	protected static $description = 'Tests for broken internal links (404 errors)';
	protected static $family = 'internal-linking';

	public static function check() {
		$score          = 0;
		$max_score      = 3;
		$score_details  = array();
		$recommendations = array();

		// Check for link checker plugins.
		$link_checker_plugins = array(
			'broken-link-checker/broken-link-checker.php',
			'wp-link-status/wp-link-status.php',
		);

		$has_link_checker = false;
		foreach ( $link_checker_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_link_checker = true;
				++$score;
				$score_details[] = __( '✓ Link checker plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_link_checker ) {
			$score_details[]   = __( '✗ No link checker plugin installed', 'wpshadow' );
			$recommendations[] = __( 'Install Broken Link Checker plugin to automatically detect 404s', 'wpshadow' );
		}

		// Sample check - look for links in recent content.
		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 20,
				'post_status'    => 'publish',
			)
		);

		$total_internal_links = 0;
		$home_url = home_url();

		foreach ( $posts as $post ) {
			preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches );
			
			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $url ) {
					// Check if internal link.
					if ( strpos( $url, $home_url ) === 0 || strpos( $url, '/' ) === 0 ) {
						++$total_internal_links;
					}
				}
			}
		}

		if ( $total_internal_links > 30 ) {
			++$score;
			$score_details[] = sprintf( __( '✓ Active internal linking (found %d+ links)', 'wpshadow' ), $total_internal_links );
		} elseif ( $total_internal_links > 10 ) {
			$score_details[]   = sprintf( __( '◐ Limited internal linking (%d links found)', 'wpshadow' ), $total_internal_links );
			$recommendations[] = __( 'Increase internal linking between related content', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Very few internal links detected', 'wpshadow' );
			$recommendations[] = __( 'Add internal links to connect related content', 'wpshadow' );
		}

		// Check for redirect plugins (to handle moved content).
		if ( is_plugin_active( 'redirection/redirection.php' ) ) {
			++$score;
			$score_details[] = __( '✓ Redirect plugin active (handles moved content)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No redirect plugin installed', 'wpshadow' );
			$recommendations[] = __( 'Install Redirection plugin to handle moved/deleted content', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'critical';
		$threat_level = 50;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Broken link score: %d%%. Internal 404s frustrate users (90%% bounce immediately), waste crawl budget, and signal poor maintenance. Sites with <1%% broken links rank 30%% better. Install link checker to automate detection and fix broken links immediately.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/broken-internal-links',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Broken internal links damage user experience, waste search engine crawl budget, and indicate poor site quality.', 'wpshadow' ),
		);
	}
}
