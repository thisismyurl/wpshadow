<?php
/**
 * Media Attachment Metadata Not Generating Diagnostic
 *
 * Checks if media attachment metadata is being generated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Attachment Metadata Not Generating Diagnostic Class
 *
 * Detects missing media metadata generation.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Media_Attachment_Metadata_Not_Generating extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-attachment-metadata-not-generating';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Attachment Metadata Not Generating';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if media attachment metadata is generated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count posts without metadata
		$posts_without_metadata = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND ID NOT IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s)",
				'attachment',
				'_wp_attachment_metadata'
			)
		);

		if ( $posts_without_metadata > 5 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d attachments are missing metadata. Regenerate thumbnails to fix this issue.', 'wpshadow' ),
					absint( $posts_without_metadata )
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/media-attachment-metadata-not-generating',
			);
		}

		return null;
	}
}
