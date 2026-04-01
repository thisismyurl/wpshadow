<?php
/**
 * Local Backlinks Earned Diagnostic
 *
 * Tests whether the site earns quality backlinks from local organizations, news outlets,
 * and community sites. Local backlinks are crucial for local search authority.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Earns_Local_Backlinks Class
 *
 * Diagnostic #17: Local Backlinks Earned from Specialized & Emerging Success Habits.
 * Checks if the site has earned quality local backlinks.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Earns_Local_Backlinks extends Diagnostic_Base {

	protected static $slug = 'earns-local-backlinks';
	protected static $title = 'Local Backlinks Earned';
	protected static $description = 'Tests whether the site earns quality backlinks from local organizations and news outlets';
	protected static $family = 'local-seo';

	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check backlink plugin.
		$backlink_plugins = array(
			'broken-link-checker/broken-link-checker.php',
			'link-checker/link-checker.php',
			'wp-external-links/wp-external-links.php',
		);

		$has_backlink_plugin = false;
		foreach ( $backlink_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_backlink_plugin = true;
				break;
			}
		}

		// Check press/media page.
		$press_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'press media coverage news featured',
			)
		);

		if ( ! empty( $press_pages ) ) {
			++$score;
			$score_details[] = __( '✓ Press/media coverage page exists', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No press page found', 'wpshadow' );
			$recommendations[] = __( 'Create a press/media page to showcase local news coverage and mentions', 'wpshadow' );
		}

		// Check mentions of local media.
		$media_mentions = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				's'              => 'featured in published quoted interviewed',
			)
		);

		if ( count( $media_mentions ) >= 3 ) {
			$score += 2;
			$score_details[] = sprintf(
				/* translators: %d: number of media mentions */
				__( '✓ %d+ media mentions found', 'wpshadow' ),
				count( $media_mentions )
			);
		} elseif ( ! empty( $media_mentions ) ) {
			++$score;
			$score_details[]   = sprintf( __( '◐ %d media mention(s) found', 'wpshadow' ), count( $media_mentions ) );
			$recommendations[] = __( 'Earn at least 3 local media mentions per year through press releases and newsjacking', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No local media coverage documented', 'wpshadow' );
			$recommendations[] = __( 'Pitch stories to local news outlets, blogs, and community websites', 'wpshadow' );
		}

		// Check chamber/association links.
		$association_links = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'chamber association member organization',
			)
		);

		if ( ! empty( $association_links ) ) {
			++$score;
			$score_details[] = __( '✓ Chamber/association memberships mentioned', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No business association memberships found', 'wpshadow' );
			$recommendations[] = __( 'Join local Chamber of Commerce and industry associations for directory backlinks', 'wpshadow' );
		}

		// Check local partnerships generating backlinks.
		$partnership_links = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'partnership collaboration affiliate link',
			)
		);

		if ( ! empty( $partnership_links ) ) {
			++$score;
			$score_details[] = __( '✓ Partnership backlink opportunities present', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No partnership backlinks documented', 'wpshadow' );
			$recommendations[] = __( 'Build relationships with complementary local businesses for mutual backlinking', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 25;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Local backlinks score: %d%%. Local backlinks from .org, .edu, and news sites increase domain authority by 35%% and local search rankings by 48%%. Quality beats quantity - one local news link = 50 directory links.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/local-backlinks?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Local backlinks signal geographic relevance to search engines and drive qualified referral traffic from trusted community sources.', 'wpshadow' ),
		);
	}
}
