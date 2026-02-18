<?php
/**
 * Media Library Memory Usage Diagnostic
 *
 * Monitors memory usage when loading media library queries
 * and detects high memory consumption.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6033.1605
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Library_Memory_Usage Class
 *
 * Checks memory usage impact of media library operations.
 *
 * @since 1.6033.1605
 */
class Diagnostic_Media_Library_Memory_Usage extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-library-memory-usage';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Library Memory Usage';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors memory consumption for media library operations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1605
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
		$wp_memory_limit = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );

		if ( $memory_limit < 128 * MB_IN_BYTES && $wp_memory_limit < 128 * MB_IN_BYTES ) {
			$issues[] = __( 'PHP memory limit is low; media library may exceed available memory when browsing large libraries', 'wpshadow' );
		}

		$start_memory = memory_get_usage();
		$query = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 50,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		foreach ( $query->posts as $attachment_id ) {
			$metadata = wp_get_attachment_metadata( (int) $attachment_id );
			if ( empty( $metadata ) ) {
				continue;
			}
		}

		$end_memory = memory_get_usage();
		$memory_delta = $end_memory - $start_memory;

		if ( $memory_delta > 32 * MB_IN_BYTES ) {
			$issues[] = sprintf(
				/* translators: %s: memory usage */
				__( 'Media library operations used %s of memory; consider caching or reducing metadata loading', 'wpshadow' ),
				size_format( $memory_delta )
			);
		}

		if ( $memory_limit > 0 && $end_memory > ( 0.8 * $memory_limit ) ) {
			$issues[] = __( 'Media library memory usage is approaching the PHP memory limit; increase memory or optimize attachments', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-library-memory-usage',
			);
		}

		return null;
	}
}
