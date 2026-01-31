<?php
/**
 * Diagnostic: Filesystem Disk Space
 *
 * Checks available disk space on the server filesystem.
 * Low disk space can cause site failures, data loss, and update issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Infrastructure
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Filesystem_Disk_Space
 *
 * Monitors server disk space availability.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Filesystem_Disk_Space extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'filesystem-disk-space';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Filesystem Disk Space';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks available disk space on server filesystem';

	/**
	 * Check filesystem disk space.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$path = ABSPATH;

		// Get disk space info.
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$total_space = @disk_total_space( $path );
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$free_space  = @disk_free_space( $path );

		if ( false === $total_space || false === $free_space ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Unable to determine disk space. This may be restricted by server configuration.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/filesystem_disk_space',
				'meta'        => array(
					'path'        => $path,
					'accessible'  => false,
				),
			);
		}

		// Calculate percentage of free space.
		$free_percentage = ( $free_space / $total_space ) * 100;

		// Critical: Less than 5% free space.
		if ( $free_percentage < 5 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: Free space percentage, 2: Free space in human-readable format, 3: Total space */
					__( 'CRITICAL: Only %.2f%% (%s) of disk space remains out of %s total. This can cause site failures and data loss.', 'wpshadow' ),
					$free_percentage,
					size_format( $free_space ),
					size_format( $total_space )
				),
				'severity'    => 'critical',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/filesystem_disk_space',
				'meta'        => array(
					'total_space'      => $total_space,
					'free_space'       => $free_space,
					'free_percentage'  => round( $free_percentage, 2 ),
				),
			);
		}

		// High: Less than 10% free space.
		if ( $free_percentage < 10 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: Free space percentage, 2: Free space in human-readable format, 3: Total space */
					__( 'Only %.2f%% (%s) of disk space remains out of %s total. Consider freeing up space soon.', 'wpshadow' ),
					$free_percentage,
					size_format( $free_space ),
					size_format( $total_space )
				),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/filesystem_disk_space',
				'meta'        => array(
					'total_space'      => $total_space,
					'free_space'       => $free_space,
					'free_percentage'  => round( $free_percentage, 2 ),
				),
			);
		}

		// Medium: Less than 20% free space.
		if ( $free_percentage < 20 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: Free space percentage, 2: Free space in human-readable format, 3: Total space */
					__( 'Disk space is at %.2f%% (%s remaining out of %s). Consider monitoring disk usage.', 'wpshadow' ),
					$free_percentage,
					size_format( $free_space ),
					size_format( $total_space )
				),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/filesystem_disk_space',
				'meta'        => array(
					'total_space'      => $total_space,
					'free_space'       => $free_space,
					'free_percentage'  => round( $free_percentage, 2 ),
				),
			);
		}

		// Disk space is healthy.
		return null;
	}
}
