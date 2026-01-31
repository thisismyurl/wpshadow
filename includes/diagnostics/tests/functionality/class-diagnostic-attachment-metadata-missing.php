<?php
/**
 * Attachment Metadata Missing Diagnostic
 *
 * Checks if attachment metadata is being generated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2345
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attachment Metadata Missing Diagnostic Class
 *
 * Detects missing attachment metadata.
 *
 * @since 1.2601.2345
 */
class Diagnostic_Attachment_Metadata_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'attachment-metadata-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Attachment Metadata Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if attachment metadata is generated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2345
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check if image attachments have metadata
		$images_without_metadata = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} 
			 WHERE meta_key = '_wp_attachment_metadata' AND meta_value = ''"
		);

		if ( $images_without_metadata > 0 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d image attachments are missing metadata. Regenerate thumbnails to fix this issue.', 'wpshadow' ),
					absint( $images_without_metadata )
				),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/attachment-metadata-missing',
			);
		}

		return null;
	}
}
