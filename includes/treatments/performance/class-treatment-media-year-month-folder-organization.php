<?php
/**
 * Media Year/Month Folder Organization Treatment
 *
 * Verifies uploads are organized into year/month folders
 * and checks folder structure integrity.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1615
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Year_Month_Folder_Organization Class
 *
 * Validates year/month folder organization for uploads.
 *
 * @since 1.6033.1615
 */
class Treatment_Media_Year_Month_Folder_Organization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-year-month-folder-organization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Year/Month Folder Organization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies uploads use year/month folder structure';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1615
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
				'kb_link'      => 'https://wpshadow.com/kb/media-year-month-folder-organization',
			);
		}

		return null;
	}
}
