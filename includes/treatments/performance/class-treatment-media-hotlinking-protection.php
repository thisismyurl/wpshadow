<?php
/**
 * Media Hotlinking Protection Treatment
 *
 * Checks for hotlinking protection rules in the uploads
 * directory and warns if missing.
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
 * Treatment_Media_Hotlinking_Protection Class
 *
 * Detects whether hotlinking protection is configured.
 *
 * @since 1.6093.1200
 */
class Treatment_Media_Hotlinking_Protection extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-hotlinking-protection';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Hotlinking Protection';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if hotlinking protection is configured';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Hotlinking_Protection' );
	}
}
