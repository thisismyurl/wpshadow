<?php
/**
 * Diagnostic: Publishing Frequency Too High
 *
 * Detects excessive publishing frequency (3+ posts/day). High frequency risks
 * content burnout, quality decline, and audience fatigue.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4380
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
 * High Publishing Frequency Diagnostic
 *
 * Checks if site publishes excessively (3+ posts/day). While content volume
 * is good, excessive frequency often indicates quality issues.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_High_Publishing_Frequency extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'high-publishing-frequency';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Publishing Frequency Too High';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects excessive publishing that may indicate quality issues or burnout risk';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Check for excessive publishing frequency.
	 *
	 * Analyzes posting frequency over last 30 days. Publishing 3+ posts per day
	 * often indicates rushed content or unsustainable pace.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check last 30 days.
		$thirty_days_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) );

		$post_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_type = 'post'
				AND post_status = 'publish'
				AND post_date > %s",
				$thirty_days_ago
			)
		);

		// Calculate average posts per day.
		$avg_per_day = $post_count / 30;

		// Threshold: 3+ posts per day.
		if ( $avg_per_day < 3 ) {
			return null;
		}

		// Low severity - this is more of a caution.
		$threat_level = 40; // Low.

		if ( $avg_per_day >= 5 ) {
			$threat_level = 50; // Very high frequency.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: average posts per day, 2: total posts in period */
				__(
					'Publishing frequency is very high (%.1f posts/day). You published %2$d posts in the last 30 days. While content volume is important, publishing 3+ posts per day risks quality decline and author burnout. Consider focusing on fewer, higher-quality pieces.',
					'wpshadow'
				),
				$avg_per_day,
				$post_count
			),
			'severity'     => 'low',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/high-publishing-frequency?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}
}
