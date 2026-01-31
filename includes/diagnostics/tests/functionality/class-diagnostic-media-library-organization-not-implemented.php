<?php
/**
 * Media Library Organization Not Implemented Diagnostic
 *
 * Checks if media library is organized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2347
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Library Organization Not Implemented Diagnostic Class
 *
 * Detects unorganized media library.
 *
 * @since 1.2601.2347
 */
class Diagnostic_Media_Library_Organization_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-library-organization-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Library Organization Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if media library is organized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2347
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get total media count
		$total_attachments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
		);

		// Get media without descriptions
		$no_description = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			 WHERE post_type = 'attachment' 
			 AND post_excerpt = '' 
			 AND post_content = ''"
		);

		if ( absint( $no_description ) > ( absint( $total_attachments ) / 2 ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Media library is poorly organized. Add alt text and descriptions to images for better SEO and accessibility.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/media-library-organization-not-implemented',
			);
		}

		return null;
	}
}
