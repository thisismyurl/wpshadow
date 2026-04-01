<?php
/**
 * Media Library Grid vs List Diagnostic
 *
 * Tests performance differences between grid and list views
 * in the media library.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Library_Grid_Vs_List Class
 *
 * Compares query performance for grid and list modes to
 * detect slow media library view rendering.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Media_Library_Grid_Vs_List extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-library-grid-vs-list';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Library Grid vs List';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Compares performance of grid and list views in the media library';

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

		$grid_start = microtime( true );
		$grid_query = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 40,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			)
		);
		$grid_time = microtime( true ) - $grid_start;

		$list_start = microtime( true );
		$list_query = new \WP_Query(
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
		$list_time = microtime( true ) - $list_start;

		if ( $grid_time > 2.0 ) {
			$issues[] = sprintf(
				/* translators: %s: seconds */
				__( 'Grid view query took %s seconds; consider optimizing image metadata or database indexes', 'wpshadow' ),
				number_format( $grid_time, 2 )
			);
		}

		if ( $list_time > 2.0 ) {
			$issues[] = sprintf(
				/* translators: %s: seconds */
				__( 'List view query took %s seconds; consider optimizing attachment queries', 'wpshadow' ),
				number_format( $list_time, 2 )
			);
		}

		if ( $grid_time > 0 && $list_time > 0 && $grid_time > ( $list_time *1.0 ) ) {
			$issues[] = __( 'Grid view is significantly slower than list view; large thumbnails may be slowing the media library', 'wpshadow' );
		}

		if ( $grid_query->found_posts > 10000 ) {
			$issues[] = __( 'Large media libraries can make grid view sluggish; consider cleaning up unused media', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-library-grid-vs-list?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
