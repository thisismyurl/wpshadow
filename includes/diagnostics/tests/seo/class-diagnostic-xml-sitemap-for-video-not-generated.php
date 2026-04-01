<?php
/**
 * XML Sitemap For Video Not Generated Diagnostic
 *
 * Checks if video sitemap is generated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML Sitemap For Video Not Generated Diagnostic Class
 *
 * Detects missing video sitemap.
 *
 * @since 0.6093.1200
 */
class Diagnostic_XML_Sitemap_For_Video_Not_Generated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'xml-sitemap-for-video-not-generated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XML Sitemap For Video Not Generated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if video sitemap is generated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if video sitemap exists
		if ( ! file_exists( WP_CONTENT_DIR . '/uploads/video-sitemap.xml' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Video XML sitemap is not generated. Create a video sitemap and submit it to Google Search Console for better video indexing.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/xml-sitemap-for-video-not-generated?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
