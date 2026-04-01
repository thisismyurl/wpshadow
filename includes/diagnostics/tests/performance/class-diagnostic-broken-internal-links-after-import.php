<?php
/**
 * Broken Internal Links After Import Diagnostic
 *
 * Tests whether internal links remain functional after migration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_URL_And_Pattern_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ensure helper class is loaded
if ( ! class_exists( '\WPShadow\Diagnostics\Helpers\Diagnostic_URL_And_Pattern_Helper' ) ) {
	$helper_file = WPSHADOW_PATH . 'includes/diagnostics/helpers/class-diagnostic-url-and-pattern-helper.php';
	if ( file_exists( $helper_file ) ) {
		require_once $helper_file;
	}
}

/**
 * Broken Internal Links After Import Diagnostic Class
 *
 * Tests whether internal links point to the correct domain after import/migration.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Broken_Internal_Links_After_Import extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'broken-internal-links-after-import';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Broken Internal Links After Import';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether internal links remain functional after migration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$home_url = home_url();
		$home_domain = Diagnostic_URL_And_Pattern_Helper::get_domain( $home_url );

		// Sample recent posts for broken links.
		$recent_posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		) );

		if ( empty( $recent_posts ) ) {
			return null;
		}

		$broken_links = 0;
		$posts_checked = 0;

		foreach ( $recent_posts as $post ) {
			$posts_checked++;

			// Extract all links from post content.
			if ( preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches ) ) {
				foreach ( $matches[1] as $link_url ) {
					// Check if it's an internal link to different domain.
					$link_domain = Diagnostic_URL_And_Pattern_Helper::get_domain( $link_url );

					if ( ! empty( $link_domain ) && $link_domain !== $home_domain ) {
						// Link points to different domain - might be old site.
						$broken_links++;
					}
				}
			}
		}

		if ( $broken_links > 0 ) {
			$percentage = ( $broken_links / $posts_checked ) * 100;
			$issues[] = sprintf(
				/* translators: %d: percentage of links to external/old domains */
				__( '%d%% of sampled posts contain links to external or old domains', 'wpshadow' ),
				round( $percentage )
			);
		}

		// Check for protocol mismatches (http vs https).
		$site_scheme = Diagnostic_URL_And_Pattern_Helper::get_scheme( $home_url );
		$protocol_mismatches = 0;

		foreach ( $recent_posts as $post ) {
			if ( preg_match_all( '/href=["\']([^"\']+)["\']/', $post->post_content, $matches ) ) {
				foreach ( $matches[1] as $link_url ) {
					$link_scheme = Diagnostic_URL_And_Pattern_Helper::get_scheme( $link_url );
					if ( ! empty( $link_scheme ) && $link_scheme !== $site_scheme ) {
						$protocol_mismatches++;
					}
				}
			}
		}

		if ( $protocol_mismatches > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of protocol mismatches */
				__( '%d links have protocol mismatches (http vs https)', 'wpshadow' ),
				$protocol_mismatches
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/broken-internal-links-after-import?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
