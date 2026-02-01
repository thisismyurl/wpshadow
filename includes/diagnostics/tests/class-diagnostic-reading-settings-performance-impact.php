<?php
/**
 * Reading Settings Performance Impact Diagnostic
 *
 * Analyzes reading settings (posts per page, feed items) for their impact
 * on site performance and SEO.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reading Settings Performance Impact Diagnostic Class
 *
 * Checks reading settings for performance optimization.
 *
 * @since 1.26032.1800
 */
class Diagnostic_Reading_Settings_Performance_Impact extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'reading-settings-performance-impact';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Reading Settings Performance Impact';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes reading settings for performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Posts per page is reasonable (not loading too many)
	 * - RSS feed posts count is appropriate
	 * - Feed excerpt settings are configured
	 *
	 * @since  1.26032.1800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get posts per page.
		$posts_per_page = (int) get_option( 'posts_per_page', 10 );

		if ( $posts_per_page > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: posts per page */
				__( 'Posts per page (%d) is very high; this may impact performance', 'wpshadow' ),
				$posts_per_page
			);
		} elseif ( $posts_per_page < 2 ) {
			$issues[] = __( 'Posts per page is very low; consider increasing for better UX', 'wpshadow' );
		}

		// Get RSS posts count.
		$posts_rss = (int) get_option( 'posts_per_rss', 10 );

		if ( $posts_rss > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: RSS feed posts */
				__( 'RSS feed item count (%d) is very high; this increases bandwidth usage', 'wpshadow' ),
				$posts_rss
			);
		}

		// Check if showing full content or excerpt in feeds.
		$rss_use_excerpt = (int) get_option( 'rss_use_excerpt', 0 );

		if ( 0 === $rss_use_excerpt ) {
			// Showing full content in feeds.
			if ( $posts_rss > 20 ) {
				$issues[] = __( 'Full post content is shown in RSS feeds with high item count; consider using excerpts instead', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/reading-settings-performance-impact',
			);
		}

		return null;
	}
}
