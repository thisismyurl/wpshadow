<?php
/**
 * Storage Space Availability Diagnostic
 *
 * Checks available disk space in uploads directory.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Storage_Space_Availability Class
 *
 * Validates disk space for uploads. Low disk space can break uploads,
 * thumbnail generation, and backups.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Storage_Space_Availability extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'storage-space-availability';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Storage Space Availability';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks available disk space in uploads directory';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - Free disk space
	 * - Disk usage percentage
	 * - Multisite quota limits
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$upload_dir = wp_upload_dir();
		$base_dir   = $upload_dir['basedir'];

		if ( empty( $base_dir ) || ! is_dir( $base_dir ) ) {
			$issues[] = __( 'Uploads directory is missing - cannot check disk space', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Uploads directory missing - storage check failed', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/storage-space-availability',
				'details'      => array( 'issues' => $issues ),
			);
		}

		$free_space  = @disk_free_space( $base_dir );
		$total_space = @disk_total_space( $base_dir );

		if ( false === $free_space || false === $total_space ) {
			$issues[] = __( 'Unable to determine disk space (disk_free_space failed)', 'wpshadow' );
		}

		if ( false !== $free_space && false !== $total_space && 0 < $total_space ) {
			$percent_free = ( $free_space / $total_space ) * 100;

			if ( 10 > $percent_free ) {
				$issues[] = sprintf(
					/* translators: %s: percent free */
					__( 'Only %s%% disk space free - uploads may fail soon', 'wpshadow' ),
					number_format_i18n( $percent_free, 1 )
				);
			}

			if ( ( 1024 * 1024 * 1024 ) > $free_space ) {
				$issues[] = sprintf(
					/* translators: %s: free space */
					__( 'Free space is %s - recommend at least 1GB for media operations', 'wpshadow' ),
					size_format( $free_space )
				);
			}
		}

		// Multisite quota checks.
		if ( is_multisite() ) {
			$allowed = get_space_allowed();
			$used    = get_space_used();

			if ( 0 < $allowed && 0 < $used ) {
				$remaining = $allowed - $used;
				if ( ( $allowed * 0.1 ) > $remaining ) {
					$issues[] = sprintf(
						/* translators: %s: remaining space */
						__( 'Multisite quota nearly full - only %sMB remaining', 'wpshadow' ),
						number_format_i18n( $remaining )
					);
				}
			}
		}

		// Check for uploads outside of standard path.
		$upload_path = get_option( 'upload_path' );
		if ( ! empty( $upload_path ) ) {
			$issues[] = __( 'Custom upload_path is set - ensure storage space is monitored for that path', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'Your server is running low on storage space. When storage runs out completely, your site will stop working and you may lose data. This affects: media uploads, thumbnail generation, plugin updates, and database operations.',
						'Your server has %d storage space issues that could cause site failures. When storage runs out, uploads fail, backups break, and your site may become inaccessible.',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/storage-space-management',
				'details'      => array(
					'issues'       => $issues,
					'free_space'   => is_numeric( $free_space ) ? size_format( $free_space ) : 'N/A',
					'total_space'  => is_numeric( $total_space ) ? size_format( $total_space ) : 'N/A',
				),
			);

			// Add upgrade path if Vault not active
			if ( ! Upgrade_Path_Helper::has_pro_product( 'vault' ) ) {
				$finding = Upgrade_Path_Helper::add_upgrade_path(
					$finding,
					'vault',
					'cloud-offload',
					'https://wpshadow.com/kb/manual-storage-cleanup'
				);
			}

			return $finding;
		}

		return null;
	}
}
