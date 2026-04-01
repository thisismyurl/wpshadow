<?php
/**
 * Diagnostic: Publishing Frequency Too Low
 *
 * Detects insufficient publishing frequency (<1 post/month). Low frequency
 * causes loss of momentum and audience disengagement.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4379
 *
 * @package    WPShadow
 * @subpackage Diagnostics\ContentStrategy
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Low Publishing Frequency Diagnostic
 *
 * Checks if site publishes at minimum recommended frequency (4-8 posts/month).
 * Low frequency negatively impacts growth and SEO rankings.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_Low_Publishing_Frequency extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'low-publishing-frequency';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Publishing Frequency Too Low';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects insufficient publishing frequency that limits growth potential';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Check for low publishing frequency.
	 *
	 * Analyzes posting frequency over last 3 months. Minimum recommended
	 * frequency is 4-8 posts per month for sustained growth.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check last 3 months.
		$three_months_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-90 days' ) );

		$post_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_type = 'post'
				AND post_status = 'publish'
				AND post_date > %s",
				$three_months_ago
			)
		);

		// Calculate average posts per month.
		$avg_per_month = $post_count / 3;

		// Minimum threshold: 4 posts/month.
		if ( $avg_per_month >= 4 ) {
			return null;
		}

		// Determine threat level based on severity.
		$threat_level = 60; // Default: medium-high.

		if ( $avg_per_month < 1 ) {
			$threat_level = 70; // Less than 1/month is critical.
		} elseif ( $avg_per_month < 2 ) {
			$threat_level = 65; // Less than 2/month is high.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: average posts per month, 2: total posts in period */
				__(
					'Publishing frequency is too low (%.1f posts/month). You published %2$d posts in the last 90 days. Minimum recommended frequency is 4-8 posts per month for sustained growth and improved SEO rankings.',
					'wpshadow'
				),
				$avg_per_month,
				$post_count
			),
			'severity'     => 'medium',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/low-publishing-frequency?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}
}
