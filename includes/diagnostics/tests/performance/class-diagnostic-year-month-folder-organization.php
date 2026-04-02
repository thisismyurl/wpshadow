<?php
/**
 * Year/Month Folder Organization Diagnostic
 *
 * Verifies uploads are organized into year/month folders.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Year_Month_Folder_Organization Class
 *
 * Checks the uploads_use_yearmonth_folders setting and verifies recent
 * files follow the year/month folder structure.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Year_Month_Folder_Organization extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'year-month-folder-organization';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Year/Month Folder Organization';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies uploads are organized into year/month folders';

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
	 * - uploads_use_yearmonth_folders setting
	 * - File path structure for recent uploads
	 * - Mixed folder patterns
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$non_standard = 0;

		$use_yearmonth = get_option( 'uploads_use_yearmonth_folders', 1 );
		if ( '0' === (string) $use_yearmonth ) {
			$issues[] = __( 'Year/month folder organization is disabled - uploads may become cluttered', 'wpshadow' );
		}

		global $wpdb;
		$recent_files = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.meta_value as file_path
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file'
				WHERE p.post_type = %s
				ORDER BY p.post_date DESC
				LIMIT 30",
				'attachment'
			)
		);

		foreach ( $recent_files as $file ) {
			if ( empty( $file->file_path ) ) {
				continue;
			}

			if ( 1 !== preg_match( '#^\d{4}/\d{2}/#', $file->file_path ) ) {
				$non_standard++;
			}
		}

		if ( 0 < $non_standard ) {
			$issues[] = sprintf(
				/* translators: %d: number of files */
				_n(
					'%d recent upload does not follow year/month structure',
					'%d recent uploads do not follow year/month structure',
					$non_standard,
					'wpshadow'
				),
				$non_standard
			);
		}

		// Check for mixed folder usage.
		if ( 0 < $non_standard && '1' === (string) $use_yearmonth ) {
			$issues[] = __( 'Year/month folders are enabled but some files are stored elsewhere - possible migration issue', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d folder organization issue detected',
						'%d folder organization issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/year-month-folder-organization',
				'details'      => array(
					'issues'       => $issues,
					'non_standard' => $non_standard,
				),
			);
		}

		return null;
	}
}
