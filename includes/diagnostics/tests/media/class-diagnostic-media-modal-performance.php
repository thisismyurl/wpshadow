<?php
/**
 * Media Modal Performance Diagnostic
 *
 * Tests performance of the media picker modal in the editor
 * and identifies slow attachment queries or missing scripts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.26033.1605
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Modal_Performance Class
 *
 * Measures media modal readiness by checking required scripts
 * and attachment query performance.
 *
 * @since 1.26033.1605
 */
class Diagnostic_Media_Modal_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-modal-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Modal Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media picker modal performance in the editor';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.1605
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		if ( ! function_exists( 'wp_enqueue_media' ) ) {
			$issues[] = __( 'wp_enqueue_media is unavailable; media modal may not load correctly', 'wpshadow' );
		}

		if ( ! wp_script_is( 'media-views', 'registered' ) || ! wp_script_is( 'media-editor', 'registered' ) ) {
			$issues[] = __( 'Required media modal scripts are not registered; editor media picker may fail or be slow', 'wpshadow' );
		}

		$start = microtime( true );
		$query = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 40,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
				'fields'         => 'ids',
			)
		);
		$query_time = microtime( true ) - $start;

		if ( $query_time > 2.0 ) {
			$issues[] = sprintf(
				/* translators: %s: query time in seconds */
				__( 'Attachment query for media modal took %s seconds; consider database optimization or caching', 'wpshadow' ),
				number_format( $query_time, 2 )
			);
		}

		$attachment_count = (int) $query->found_posts;
		if ( $attachment_count > 10000 ) {
			$issues[] = sprintf(
				/* translators: %s: formatted count */
				__( 'Large media library detected (%s items); media modal may open slowly', 'wpshadow' ),
				number_format_i18n( $attachment_count )
			);
		}

		if ( ! wp_using_ext_object_cache() && $attachment_count > 5000 ) {
			$issues[] = __( 'Object caching is disabled; enabling it can improve media modal responsiveness', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-modal-performance',
			);
		}

		return null;
	}
}
