<?php
/**
 * Media Alt Text Coverage Treatment
 *
 * Measures percentage of images with alt text to
 * detect accessibility issues.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1615
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Alt_Text_Coverage Class
 *
 * Checks image attachments for missing alt text.
 *
 * @since 1.6033.1615
 */
class Treatment_Media_Alt_Text_Coverage extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-alt-text-coverage';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Alt Text Coverage';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measures percentage of images with alt text';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1615
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Alt_Text_Coverage' );
	}
}
