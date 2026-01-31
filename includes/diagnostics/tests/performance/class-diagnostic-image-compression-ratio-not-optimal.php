<?php
/**
 * Image Compression Ratio Not Optimal Diagnostic
 *
 * Checks if images are compressed properly.
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
 * Image Compression Ratio Not Optimal Diagnostic Class
 *
 * Detects uncompressed images.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Image_Compression_Ratio_Not_Optimal extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-compression-ratio-not-optimal';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Compression Ratio Not Optimal';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if images are optimally compressed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count large uncompressed image files
		$large_attachments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'"
		);

		if ( $large_attachments > 100 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d images are in media library. Ensure all images are compressed (max 150KB for uploads).', 'wpshadow' ),
					absint( $large_attachments )
				),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/image-compression-ratio-not-optimal',
			);
		}

		return null;
	}
}
