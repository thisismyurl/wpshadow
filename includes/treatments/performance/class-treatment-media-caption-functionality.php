<?php
/**
 * Media Caption Functionality Treatment
 *
 * Validates caption display support on images by
 * checking caption shortcode and attachment captions.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Caption_Functionality Class
 *
 * Checks caption shortcode support and attachment captions.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Caption_Functionality extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-caption-functionality';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Caption Functionality';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates caption display and output functionality';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Caption_Functionality' );
	}
}
