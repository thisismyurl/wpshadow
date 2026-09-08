<?php
/**
 * Treatment: Set RSS Feed to Summary (Excerpt)
 *
 * Updates the rss_use_excerpt option to 1 so RSS feeds publish a summary
 * excerpt instead of full post content. This protects against content
 * scraping, reduces feed payload size, and drives traffic back to the site.
 *
 * Risk level: safe — single option update, fully reversible.
 *
 * @package WPShadow
 * @since   0.6095
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Switches RSS feeds from full content to excerpt/summary mode.
 */
class Treatment_Rss_Feed_Summary extends Treatment_Base {

	/**
	 * @var string
	 */
	protected static $slug = 'rss-feed-summary';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Set rss_use_excerpt to 1 (summary mode).
	 *
	 * @return array
	 */
	public static function apply() {
		$previous = (int) get_option( 'rss_use_excerpt', 0 );
		update_option( 'wpshadow_prev_rss_use_excerpt', $previous, false );
		update_option( 'rss_use_excerpt', 1 );

		return array(
			'success' => true,
			'message' => __( 'RSS feeds switched to summary (excerpt) mode. Subscribers see a short preview; full content requires visiting the site.', 'wpshadow' ),
			'details' => array( 'previous_value' => $previous, 'new_value' => 1 ),
		);
	}

	/**
	 * Restore the previous rss_use_excerpt value.
	 *
	 * @return array
	 */
	public static function undo() {
		$previous = get_option( 'wpshadow_prev_rss_use_excerpt' );

		if ( false === $previous ) {
			return array(
				'success' => false,
				'message' => __( 'No previous value stored — nothing to restore.', 'wpshadow' ),
			);
		}

		update_option( 'rss_use_excerpt', (int) $previous );
		delete_option( 'wpshadow_prev_rss_use_excerpt' );

		return array(
			'success' => true,
			'message' => __( 'RSS feed mode restored to full content.', 'wpshadow' ),
		);
	}
}
