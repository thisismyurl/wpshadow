<?php
/**
 * Media Orphaned Media Detection Diagnostic
 *
 * Identifies media files not attached to any posts and
 * detects unused media bloat.
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
 * Diagnostic_Media_Orphaned_Media_Detection Class
 *
 * Counts unattached media and warns if the volume is high.
 *
 * @since 1.6033.1605
 */
class Diagnostic_Media_Orphaned_Media_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-orphaned-media-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned Media Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies media not attached to any posts';

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
		global $wpdb;
		$issues = array();

		$total = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
		);
		$unattached = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_parent = 0"
		);

		if ( $total > 0 ) {
			$ratio = ( $unattached / $total ) * 100;
			if ( $unattached > 1000 || $ratio > 20 ) {
				$issues[] = sprintf(
					/* translators: 1: unattached count, 2: percentage */
					__( 'Found %1$s unattached media files (%2$s%% of library); consider cleaning up unused files', 'wpshadow' ),
					number_format_i18n( $unattached ),
					number_format( $ratio, 2 )
				);
			}
		}

		$missing_meta = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} m ON p.ID = m.post_id AND m.meta_key = '_wp_attached_file'
			WHERE p.post_type = 'attachment' AND m.meta_id IS NULL"
		);

		if ( $missing_meta > 0 ) {
			$issues[] = sprintf(
				/* translators: %s: count */
				__( '%s media items are missing attached file metadata; these may be orphaned records', 'wpshadow' ),
				number_format_i18n( $missing_meta )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-orphaned-media-detection',
			);
		}

		return null;
	}
}
