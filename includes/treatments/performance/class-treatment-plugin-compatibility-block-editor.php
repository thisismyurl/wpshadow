<?php
/**
 * Plugin Compatibility with Block Editor Treatment
 *
 * Checks if plugins are compatible with the Gutenberg block editor and
 * don't disable or degrade block editor functionality.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Compatibility with Block Editor Treatment Class
 *
 * Verifies block editor compatibility:
 * - Classic editor plugins detected
 * - Block editor support
 * - Editor disable filters
 * - Plugin compatibility
 *
 * @since 1.6093.1200
 */
class Treatment_Plugin_Compatibility_Block_Editor extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-compatibility-block-editor';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Block Editor Plugin Compatibility';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks plugin compatibility with Gutenberg block editor';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Plugin_Compatibility_Block_Editor' );
	}
}
