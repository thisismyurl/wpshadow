<?php
/**
 * Video Thumbnails Not Generated Treatment
 *
 * Detects when uploaded videos lack auto-generated thumbnails,
 * resulting in broken video displays and poor user experience.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Thumbnails Not Generated Treatment Class
 *
 * Checks if videos have thumbnails. WordPress doesn't generate
 * thumbnails automatically, requiring manual work.
 *
 * @since 0.6093.1200
 */
class Treatment_Video_Thumbnails_Not_Generated extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-thumbnails-not-generated';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Video Thumbnails Not Auto-Generated';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects videos without auto-generated thumbnails';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-optimization';

	/**
	 * Run the treatment check.
	 *
	 * Checks if videos have thumbnails. Auto-generated thumbnails
	 * improve UX and eliminate manual work.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Video_Thumbnails_Not_Generated' );
	}
}
