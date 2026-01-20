<?php
/**
 * Broken Links Diagnostic
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

/**
 * Check for broken links site-wide (deep scan only).
 */
class Diagnostic_Broken_Links {
	/**
	 * Run the diagnostic check (deep scan).
	 *
	 * @return array|null Finding data or null if no issues.
	 */
	public static function check() {
		if ( ! function_exists( 'wpshadow_run_broken_links_scan' ) ) {
			return null;
		}

		$result = wpshadow_run_broken_links_scan( array(
			'check_internal' => true,
			'check_external' => true,
			'check_images'   => true,
			'limit'          => 100,
		) );

		if ( empty( $result['broken_links'] ) ) {
			return null;
		}

		$broken = $result['broken_links'];
		$count  = count( $broken );
		$first  = $broken[0];

		$title = sprintf( 'Broken links found (%d)', (int) $count );
		$description = sprintf(
			/* translators: 1: URL, 2: post title, 3: status code */
			__( 'Example: %1$s in "%2$s" returned %3$s.', 'wpshadow' ),
			$first['url'],
			$first['post_title'],
			$first['status_code']
		);

		return array(
			'id'           => 'broken-links',
			'title'        => $title,
			'description'  => $description,
			'color'        => '#f44336',
			'bg_color'     => '#ffebee',
			'kb_link'      => 'https://wpshadow.com/kb/fix-broken-links/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=broken-links',
			'auto_fixable' => false,
			'threat_level' => 60,
			'category'     => 'seo',
			'extra'        => array(
				'broken_links'  => $broken,
				'posts_checked' => $result['posts_checked'] ?? 0,
				'links_checked' => $result['links_checked'] ?? 0,
			),
		);
	}
}