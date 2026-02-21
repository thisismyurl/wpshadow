<?php
/**
 * Media Filename Sanitization Treatment
 *
 * Validates filename sanitization and detects special
 * characters that could cause URL issues.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1625
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Filename_Sanitization Class
 *
 * Checks whether uploaded filenames are sanitized.
 *
 * @since 1.6033.1625
 */
class Treatment_Media_Filename_Sanitization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-filename-sanitization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Filename Sanitization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates filename sanitization for media uploads';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1625
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Filename_Sanitization' );
	}
}
