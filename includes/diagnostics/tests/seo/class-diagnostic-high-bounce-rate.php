<?php
/**
 * Diagnostic: High Bounce Rate Content
 *
 * Detects posts with bounce rates >70% which signals poor quality to Google.
 * High bounce rates indicate content doesn't match user expectations or fails
 * to engage readers.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7030.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * High Bounce Rate Diagnostic Class
 *
 * Checks for posts with bounce rates >70% via analytics plugins.
 *
 * Detection methods:
 * - Google Analytics integration (Site Kit, MonsterInsights)
 * - Matomo analytics data
 * - Average bounce rate calculation
 *
 * @since 1.7030.1445
 */
class Diagnostic_High_Bounce_Rate extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'high-bounce-rate';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'High Bounce Rate Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects posts with bounce rates >70% which signals poor quality to Google';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (3 points):
	 * - 2 points: Analytics plugin installed
	 * - 1 point: Bounce rate tracking meta exists
	 * - 0 points if high bounce rate detected
	 *
	 * @since  1.7030.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score         = 0;
		$max_score     = 3;
		$has_analytics = false;
		$bounce_data   = array();

		// Check for analytics plugins.
		$analytics_plugins = array(
			'google-site-kit/google-site-kit.php'           => 'Google Site Kit',
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'matomo/matomo.php'                             => 'Matomo',
			'ga-google-analytics/ga-google-analytics.php'   => 'GA Google Analytics',
		);

		foreach ( $analytics_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score        += 2;
				$has_analytics = true;
				break;
			}
		}

		// Check for bounce rate meta (some plugins store this).
		global $wpdb;
		$bounce_meta = $wpdb->get_results(
			"SELECT post_id, meta_value 
			FROM {$wpdb->postmeta} 
			WHERE meta_key LIKE '%bounce%' 
			OR meta_key LIKE '%exit%'
			LIMIT 10"
		);

		if ( ! empty( $bounce_meta ) ) {
			$score++;
			foreach ( $bounce_meta as $meta ) {
				$value = maybe_unserialize( $meta->meta_value );
				if ( is_numeric( $value ) && $value > 70 ) {
					$post = get_post( $meta->post_id );
					if ( $post && 'post' === $post->post_type ) {
						$bounce_data[] = array(
							'post_id'     => $post->ID,
							'title'       => $post->post_title,
							'bounce_rate' => floatval( $value ),
							'url'         => get_permalink( $post->ID ),
						);
					}
				}
			}
		}

		// If we found high bounce rate pages.
		if ( ! empty( $bounce_data ) ) {
			$score = 0; // Reset score to 0 if problem found.
		}

		// Pass if score is high (no issues detected).
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		// Build finding message.
		$message = sprintf(
			/* translators: %d: number of pages with high bounce rate */
			_n(
				'Found %d page with bounce rate >70%%',
				'Found %d pages with bounce rate >70%%',
				count( $bounce_data ),
				'wpshadow'
			),
			count( $bounce_data )
		);

		if ( ! $has_analytics ) {
			$message = __( 'No analytics plugin detected to measure bounce rate', 'wpshadow' );
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %s: detailed issue description */
				__( '%s. Bounce rate >70%% signals poor quality to Google. High bounce rates mean users don\'t find what they expected. Common causes: misleading titles, slow load times, poor mobile experience, intrusive ads, thin content. Industry average: 40-60%%. Fix: Match title to content, improve readability, add internal links, optimize page speed, enhance mobile UX.', 'wpshadow' ),
				$message
			),
			'severity'      => 'critical',
			'threat_level'  => 65,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/high-bounce-rate',
			'problem_pages' => $bounce_data,
			'recommendation' => __( 'Install Google Analytics or Site Kit to track bounce rates. Review high-bounce pages: verify title matches content, improve page speed, enhance readability, add engaging elements, reduce distractions.', 'wpshadow' ),
		);
	}
}
