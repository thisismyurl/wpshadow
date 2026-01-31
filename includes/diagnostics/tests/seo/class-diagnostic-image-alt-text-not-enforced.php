<?php
/**
 * Image Alt Text Not Enforced Diagnostic
 *
 * Checks if images have alt text configured.
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
 * Image Alt Text Not Enforced Diagnostic Class
 *
 * Detects images lacking alt text.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Image_Alt_Text_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-alt-text-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Alt Text Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if images have alt text';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count images without alt text
		$images_without_alt = $wpdb->get_var(
			"SELECT COUNT(p.ID) FROM {$wpdb->posts} p 
			WHERE p.post_type = 'attachment' AND p.post_mime_type LIKE 'image/%' 
			AND (p.post_excerpt = '' OR p.post_excerpt IS NULL) 
			AND post_status = 'inherit'"
		);

		if ( $images_without_alt > 20 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d images lack alt text. This hurts SEO and accessibility. Alt text helps search engines understand image content.', 'wpshadow' ),
					absint( $images_without_alt )
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/image-alt-text-not-enforced',
			);
		}

		return null;
	}
}
