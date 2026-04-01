<?php
/**
 * Homepage Title Tag Optimization Diagnostic
 *
 * Checks if homepage title tag is optimized for SEO with business name, service, location.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Homepage Title Tag Optimization Diagnostic
 *
 * Verifies the homepage title tag includes business name, services, and location for
 * SEO visibility. A well-optimized title tag is critical for search rankings—it's one
 * of Google's top ranking signals. Poor titles result in lost organic traffic.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Homepage_Title_Tag_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'homepage-title-tag-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Homepage Title Tag Is Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if homepage title includes business name, service, and location for SEO impact';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$title = self::get_homepage_title();

		// Analyze title quality
		$score = self::score_title( $title );

		if ( $score < 70 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: current title, %d: quality score */
					__( 'Your homepage title is "%s" (Score: %d/100). Optimize it to include: 1) Business name, 2) Main service/offering, 3) Location (if local business). This is your #1 SEO opportunity. Improving it can increase organic traffic by 30%%.', 'wpshadow' ),
					esc_html( $title ),
					$score
				),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/homepage-seo-optimization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'current_title' => $title,
					'title_length'  => strlen( $title ),
					'score'         => $score,
					'recommendations' => self::get_recommendations( $title ),
				),
			);
		}

		return null; // No issue found
	}

	/**
	 * Get the homepage title tag
	 *
	 * @since 0.6093.1200
	 * @return string Homepage title
	 */
	private static function get_homepage_title(): string {
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url );

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$body = wp_remote_retrieve_body( $response );

		if ( preg_match( '/<title>([^<]+)<\/title>/i', $body, $matches ) ) {
			return trim( $matches[1] );
		}

		return get_bloginfo( 'name' );
	}

	/**
	 * Score a title based on optimization factors
	 *
	 * @since 0.6093.1200
	 * @param  string $title Title to score.
	 * @return int Score from 0-100
	 */
	private static function score_title( string $title ): int {
		$score = 50; // Base score

		// Length check (ideal: 50-60 characters)
		$length = strlen( $title );
		if ( $length >= 50 && $length <= 60 ) {
			$score += 20;
		} elseif ( $length > 30 && $length < 70 ) {
			$score += 10;
		}

		// Check for separator (like dash or pipe)
		if ( preg_match( '/\s[\-\|]\s/', $title ) ) {
			$score += 15;
		}

		// Check for business keywords (basic heuristic)
		$keywords = array( 'service', 'solutions', 'expert', 'professional', 'business', 'company' );
		foreach ( $keywords as $keyword ) {
			if ( stripos( $title, $keyword ) !== false ) {
				$score += 5;
				break;
			}
		}

		// Check for location indicators
		if ( preg_match( '/^[a-z]{2}|[a-z]{2}$/i', $title ) || preg_match( '/city|town|region|local/i', $title ) ) {
			$score += 10;
		}

		return min( 100, max( 0, $score ) );
	}

	/**
	 * Get recommendations for title improvement
	 *
	 * @since 0.6093.1200
	 * @param  string $title Current title.
	 * @return array Array of recommendations
	 */
	private static function get_recommendations( string $title ): array {
		$recommendations = array();

		$length = strlen( $title );
		if ( $length < 30 ) {
			$recommendations[] = __( 'Title is too short. Aim for 50-60 characters to avoid truncation in search results.', 'wpshadow' );
		} elseif ( $length > 70 ) {
			$recommendations[] = __( 'Title is too long. Keep it under 60 characters so it displays fully in search results.', 'wpshadow' );
		}

		if ( ! preg_match( '/[\-\|\|–—]/', $title ) ) {
			$recommendations[] = __( 'Add a separator (dash or pipe) to separate business name from description.', 'wpshadow' );
		}

		if ( ! preg_match( '/[0-9]|service|solution|expert|professional/i', $title ) ) {
			$recommendations[] = __( 'Include what you do (service type) or your expertise level in the title.', 'wpshadow' );
		}

		if ( ! preg_match( '/[A-Z]{2}|city|region|local|near/i', $title ) ) {
			$recommendations[] = __( 'For local businesses, add your city or region to attract local searches.', 'wpshadow' );
		}

		return ! empty( $recommendations ) ? $recommendations : array( __( 'Title looks good!', 'wpshadow' ) );
	}
}
