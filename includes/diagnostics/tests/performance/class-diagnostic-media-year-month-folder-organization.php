<?php
/**
 * Media Year/Month Folder Organization Diagnostic
 *
 * Verifies uploads are organized into year/month folders
 * and checks folder structure integrity.
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
 * Diagnostic_Media_Year_Month_Folder_Organization Class
 *
 * Validates year/month folder organization for uploads.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Media_Year_Month_Folder_Organization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-year-month-folder-organization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Year/Month Folder Organization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies uploads use year/month folder structure';

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

		$use_yearmonth = (bool) get_option( 'uploads_use_yearmonth_folders', true );
		if ( ! $use_yearmonth ) {
			$issues[] = __( 'Year/month folder organization is disabled; this can lead to large, slow upload directories', 'wpshadow' );
		}

		$recent_attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 5,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$misplaced = 0;
		foreach ( $recent_attachments as $attachment ) {
			$file = get_attached_file( $attachment->ID );
			if ( empty( $file ) ) {
				continue;
			}

			if ( ! preg_match( '/\/[0-9]{4}\/[0-9]{2}\//', $file ) ) {
				$misplaced++;
			}
		}

		if ( $use_yearmonth && $misplaced > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of files */
				_n(
					'%d recent upload is not stored in a year/month folder; check upload path settings',
					'%d recent uploads are not stored in year/month folders; check upload path settings',
					$misplaced,
					'wpshadow'
				),
				$misplaced
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-year-month-folder-organization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
