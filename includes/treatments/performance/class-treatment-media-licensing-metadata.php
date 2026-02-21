<?php
/**
 * Media Licensing Metadata Treatment
 *
 * Tests copyright and licensing metadata storage.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Licensing Metadata Treatment Class
 *
 * Verifies storage and retrieval of copyright and licensing metadata for media.
 *
 * @since 1.6033.0000
 */
class Treatment_Media_Licensing_Metadata extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-licensing-metadata';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Licensing Metadata';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests copyright and licensing metadata storage';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Licensing_Metadata' );
	}
}
