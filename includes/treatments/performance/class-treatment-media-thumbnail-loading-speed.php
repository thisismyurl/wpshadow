<?php
/**
 * Media Thumbnail Loading Speed Treatment
 *
 * Measures thumbnail retrieval performance and detects
 * lazy loading configuration issues.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1605
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Thumbnail_Loading_Speed Class
 *
 * Checks thumbnail generation and loading performance for
 * recent image attachments and lazy loading configuration.
 *
 * @since 1.6033.1605
 */
class Treatment_Media_Thumbnail_Loading_Speed extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-thumbnail-loading-speed';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Thumbnail Loading Speed';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measures thumbnail load time and lazy loading configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1605
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Thumbnail_Loading_Speed' );
	}
}
