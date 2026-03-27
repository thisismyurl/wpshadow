<?php
/**
 * Media Description Usage Treatment
 *
 * Checks whether media descriptions are populated and
 * usable for attachment pages and metadata.
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
 * Treatment_Media_Description_Usage Class
 *
 * Validates media description usage for attachments.
 *
 * @since 1.6093.1200
 */
class Treatment_Media_Description_Usage extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-description-usage';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Description Usage';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether media descriptions are used properly';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Description_Usage' );
	}
}
