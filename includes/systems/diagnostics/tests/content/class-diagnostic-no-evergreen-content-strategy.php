<?php
/**
 * No Evergreen Content Strategy Diagnostic
 *
 * Detects when all content is time-sensitive,
 * missing long-term SEO value opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Evergreen Content Strategy
 *
 * Checks whether evergreen (timeless) content
 * is being created for long-term value.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Evergreen_Content_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-evergreen-content-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Evergreen Content Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether evergreen content is being created';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check recent posts for time-sensitive language
		$posts = get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => 20,
			'post_status'    => 'publish',
		) );

		$time_sensitive_count = 0;

		foreach ( $posts as $post ) {
			$title_and_content = $post->post_title . ' ' . $post->post_content;
			// Look for time-sensitive keywords
			if ( preg_match( '/\b(?:2024|2025|2026|this year|this month|this week|today|recently|latest|new|trending)\b/i', $title_and_content ) ) {
				$time_sensitive_count++;
			}
		}

		$percentage_time_sensitive = count( $posts ) > 0 ? ( $time_sensitive_count / count( $posts ) ) * 100 : 0;

		if ( $percentage_time_sensitive > 70 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__(
						'About %d%% of your content is time-sensitive, which means it loses value quickly. Evergreen content stays relevant for years: how-to guides, tutorials, best practices, case studies. Evergreen content: generates traffic for years (not just days), builds SEO authority over time, requires less frequent updates. Balance is key: mix trending news (quick traffic) with evergreen guides (long-term value). Top sites get 60-70%% of traffic from evergreen content.',
						'wpshadow'
					),
					round( $percentage_time_sensitive )
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'time_sensitive_percentage' => $percentage_time_sensitive,
				'business_impact' => array(
					'metric'         => 'Long-Term Traffic Value',
					'potential_gain' => 'Content that generates traffic for years',
					'roi_explanation' => 'Evergreen content provides long-term SEO value, generating traffic for years instead of days.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/evergreen-content-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
