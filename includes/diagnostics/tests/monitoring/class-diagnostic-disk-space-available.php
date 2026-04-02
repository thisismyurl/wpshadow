<?php
/**
 * Disk Space Available Diagnostic
 *
 * Checks available disk space for uploads and backups.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Disk_Space_Available Class
 *
 * Evaluates free disk space in wp-content.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Disk_Space_Available extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'disk-space-available';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Disk Space Available';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks available disk space for uploads and backups';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$path = WP_CONTENT_DIR;
		if ( ! function_exists( 'disk_free_space' ) || ! function_exists( 'disk_total_space' ) ) {
			return null;
		}

		$free  = @disk_free_space( $path );
		$total = @disk_total_space( $path );

		if ( false === $free || false === $total || 0 === $total ) {
			return null;
		}

		$free_gb = round( $free / 1024 / 1024 / 1024, 2 );
		$free_pct = ( $free / $total ) * 100;

		if ( $free_gb < 1 || $free_pct < 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Low disk space available. Backups and uploads may fail.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/disk-space-available',
				'meta'         => array(
					'free_gb'  => $free_gb,
					'free_pct' => round( $free_pct, 2 ),
				),
			);
		}

		if ( $free_gb < 2 || $free_pct < 20 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Disk space is running low. Consider cleaning up old backups or unused media.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/disk-space-available',
				'meta'         => array(
					'free_gb'  => $free_gb,
					'free_pct' => round( $free_pct, 2 ),
				),
			);
		}

		return null;
	}
}