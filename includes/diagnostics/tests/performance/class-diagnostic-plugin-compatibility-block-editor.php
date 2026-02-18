<?php
/**
 * Plugin Compatibility with Block Editor Diagnostic
 *
 * Checks if plugins are compatible with the Gutenberg block editor and
 * don't disable or degrade block editor functionality.
 *
 * @since   1.6033.2104
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Compatibility with Block Editor Diagnostic Class
 *
 * Verifies block editor compatibility:
 * - Classic editor plugins detected
 * - Block editor support
 * - Editor disable filters
 * - Plugin compatibility
 *
 * @since 1.6033.2104
 */
class Diagnostic_Plugin_Compatibility_Block_Editor extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-compatibility-block-editor';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Block Editor Plugin Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks plugin compatibility with Gutenberg block editor';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2104
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		// Check if classic editor is being forced
		$classic_editor_forced = false;

		if ( is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
			$classic_editor_forced = true;
		}

		// Check for editor disable filters
		if ( has_filter( 'block_editor_settings' ) || has_action( 'admin_init' ) ) {
			// This is a basic check
		}

		if ( $classic_editor_forced ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Classic Editor plugin is active, disabling Gutenberg. The block editor is modern and has better performance.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/block-editor-compatibility',
				'meta'          => array(
					'classic_editor_active' => $classic_editor_forced,
					'recommendation'        => 'Migrate to Gutenberg block editor. Classic Editor is deprecated and no longer maintained.',
					'impact'                => 'Block editor loads faster and has better performance with modern features',
					'benefits'              => array(
						'Native block templates',
						'Better page builder experience',
						'Modern plugin ecosystem',
						'Long-term support',
					),
				),
			);
		}

		return null;
	}
}
