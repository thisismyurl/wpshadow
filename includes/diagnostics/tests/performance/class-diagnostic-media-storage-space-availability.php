<?php
/**
 * Media Storage Space Availability Diagnostic
 *
 * Checks available disk space in the uploads directory
 * and warns before reaching critical limits.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Storage_Space_Availability Class
 *
 * Monitors available disk space for media uploads.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Media_Storage_Space_Availability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-storage-space-availability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Storage Space Availability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks available disk space for uploads directory';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$upload_dir = wp_upload_dir();
		$base_dir = $upload_dir['basedir'];

		if ( empty( $base_dir ) || ! is_dir( $base_dir ) ) {
			$issues[] = __( 'Uploads directory is missing or inaccessible; cannot check storage space', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-storage-space-availability',
			);
		}

		$free_space = @disk_free_space( $base_dir );
		$total_space = @disk_total_space( $base_dir );

		if ( false === $free_space || $free_space < 0 ) {
			$issues[] = __( 'Unable to determine free disk space for uploads directory', 'wpshadow' );
		}

		if ( is_numeric( $free_space ) && $free_space < 500 * MB_IN_BYTES ) {
			$issues[] = sprintf(
				/* translators: %s: free space */
				__( 'Low disk space: only %s free in uploads directory', 'wpshadow' ),
				size_format( $free_space )
			);
		}

		if ( is_numeric( $free_space ) && is_numeric( $total_space ) && $total_space > 0 ) {
			$percent_free = ( $free_space / $total_space ) * 100;
			if ( $percent_free < 10 ) {
				$issues[] = sprintf(
					/* translators: %s: percentage */
					__( 'Uploads storage is below %s%% free; consider cleaning up or expanding storage', 'wpshadow' ),
					number_format( $percent_free, 2 )
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-storage-space-availability',
			);
		}

		return null;
	}
}
