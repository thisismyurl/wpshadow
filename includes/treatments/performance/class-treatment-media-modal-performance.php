<?php
/**
 * Media Modal Performance Treatment
 *
 * Tests performance of the media picker modal in the editor
 * and identifies slow attachment queries or missing scripts.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Modal_Performance Class
 *
 * Measures media modal readiness by checking required scripts
 * and attachment query performance.
 *
 * @since 1.6093.1200
 */
class Treatment_Media_Modal_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-modal-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Modal Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media picker modal performance in the editor';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Modal_Performance' );
	}
}
