<?php
/**
 * Thumbnail Loading Speed Treatment
 *
 * Measures thumbnail load performance and detects missing or slow thumbnails.
 *
 * @package    WPShadow
 * @subpackage Treatments\Media
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Thumbnail_Loading_Speed Class
 *
 * Validates thumbnail availability and performance. Missing thumbnails cause
 * on-demand generation, which slows down the media library and front-end.
 *
 * @since 1.6030.2148
 */
class Treatment_Thumbnail_Loading_Speed extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'thumbnail-loading-speed';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Thumbnail Loading Speed';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Measures thumbnail load performance and detects missing thumbnails';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Validates:
	 * - Missing thumbnail metadata
	 * - Missing files on disk
	 * - Time to resolve thumbnails
	 * - Thumbnail regeneration needs
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Thumbnail_Loading_Speed' );
	}
}
