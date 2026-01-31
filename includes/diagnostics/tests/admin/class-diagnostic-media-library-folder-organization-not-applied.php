<?php
/**
 * Media Library Folder Organization Not Applied Diagnostic
 *
 * Checks if media library is organized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Library Folder Organization Not Applied Diagnostic Class
 *
 * Detects disorganized media library.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Media_Library_Folder_Organization_Not_Applied extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-library-folder-organization-not-applied';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Library Folder Organization Not Applied';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if media library is organized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for media organization plugin
		if ( ! is_plugin_active( 'filetype-conditionals-for-media-library/filetype-conditionals-for-media-library.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Media library folder organization is not applied. Use media folder organization plugins or rename files systematically for better asset management.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/media-library-folder-organization-not-applied',
			);
		}

		return null;
	}
}
