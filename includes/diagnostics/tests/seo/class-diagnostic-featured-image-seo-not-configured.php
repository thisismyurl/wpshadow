<?php
/**
 * Featured Image SEO Not Configured Diagnostic
 *
 * Checks if featured image SEO is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Featured Image SEO Not Configured Diagnostic Class
 *
 * Detects missing featured image SEO configuration.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Featured_Image_SEO_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'featured-image-seo-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Featured Image SEO Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if featured image SEO is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if featured image SEO filter is active
		if ( ! has_filter( 'wp_get_attachment_image', 'add_featured_image_seo' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Featured image SEO is not configured. Add alt text and descriptive titles to featured images for better accessibility and search rankings.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/featured-image-seo-not-configured',
			);
		}

		return null;
	}
}
