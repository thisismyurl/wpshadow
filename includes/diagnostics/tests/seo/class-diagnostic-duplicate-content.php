<?php
/**
 * Duplicate Content Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1730
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Duplicate_Content extends Diagnostic_Base {

	protected static $slug        = 'duplicate-content';
	protected static $title       = 'Duplicate Content Detection';
	protected static $description = 'Finds duplicate/near-duplicate pages';
	protected static $family      = 'seo';

	public static function check() {
		$cache_key = 'wpshadow_duplicate_content';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		// Check for exact title duplicates.
		$duplicate_titles = $wpdb->get_results(
			"SELECT post_title, COUNT(*) as count
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type IN ('post', 'page')
			AND post_title != ''
			GROUP BY post_title
			HAVING count > 1
			LIMIT 20"
		);

		if ( ! empty( $duplicate_titles ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d pages have duplicate titles. Consolidate for better SEO.', 'wpshadow' ),
					count( $duplicate_titles )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/seo-duplicate-content',
				'data'         => array(
					'duplicate_titles' => $duplicate_titles,
					'total_duplicates' => count( $duplicate_titles ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
