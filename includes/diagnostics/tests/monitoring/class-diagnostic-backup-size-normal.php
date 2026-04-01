<?php
/**
 * Backup Size Normal Diagnostic
 *
 * Verifies that the latest backup archive size appears reasonable and is not
 * suspiciously small, which can indicate truncation or failed backups.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Backup_Size_Normal Class
 *
 * Checks the most recent backup archive size against lightweight heuristics
 * to detect potentially incomplete backups.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Backup_Size_Normal extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-size-normal';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Size Normal';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the latest backup file size looks normal';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$latest_backup = self::get_latest_backup_file();

		if ( empty( $latest_backup ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No backup archives found. Confirm backups are being created and stored.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-size-normal?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		$size_bytes = (int) $latest_backup['size'];
		$file_name  = basename( $latest_backup['path'] );

		$attachments = wp_count_attachments();
		$attachment_count = 0;
		if ( is_object( $attachments ) && isset( $attachments->inherit ) ) {
			$attachment_count = (int) $attachments->inherit;
		}

		if ( $size_bytes < 1024 * 1024 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: backup file name */
					__( 'Latest backup file (%s) is under 1MB. This is unusually small and may be incomplete.', 'wpshadow' ),
					esc_html( $file_name )
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-size-normal?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'file_name'         => $file_name,
					'file_size_bytes'   => $size_bytes,
					'attachment_count'  => $attachment_count,
				),
			);
		}

		if ( $size_bytes < 5 * 1024 * 1024 && $attachment_count > 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: backup file name */
					__( 'Latest backup file (%s) appears small for the number of media files on the site.', 'wpshadow' ),
					esc_html( $file_name )
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-size-normal?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'file_name'         => $file_name,
					'file_size_bytes'   => $size_bytes,
					'attachment_count'  => $attachment_count,
				),
			);
		}

		return null;
	}

	/**
	 * Get the most recent backup file across common backup locations.
	 *
	 * @since 0.6093.1200
	 * @return array|null Array with file path, size, and modified time.
	 */
	private static function get_latest_backup_file() {
		$uploads = wp_get_upload_dir();
		$base_dirs = array(
			WP_CONTENT_DIR . '/updraft',
			WP_CONTENT_DIR . '/backups',
			$uploads['basedir'] . '/updraft',
			$uploads['basedir'] . '/backwpup',
			$uploads['basedir'] . '/ai1wm-backups',
			$uploads['basedir'] . '/duplicator',
			$uploads['basedir'] . '/backups',
		);

		$extensions = array( 'zip', 'gz', 'tar', 'tgz', 'wpress', 'daf' );
		$latest     = null;

		foreach ( $base_dirs as $dir ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}

			$pattern = $dir . '/*.{'. implode( ',', $extensions ) . '}';
			$files   = glob( $pattern, GLOB_BRACE );

			if ( empty( $files ) || ! is_array( $files ) ) {
				continue;
			}

			foreach ( $files as $file ) {
				if ( ! is_file( $file ) ) {
					continue;
				}

				$mtime = filemtime( $file );
				if ( false === $mtime ) {
					continue;
				}

				if ( null === $latest || $mtime > $latest['mtime'] ) {
					$latest = array(
						'path'  => $file,
						'size'  => filesize( $file ),
						'mtime' => $mtime,
					);
				}
			}
		}

		return $latest;
	}
}
