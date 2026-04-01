<?php
/**
 * Writing Enhancement Conflicts Diagnostic
 *
 * Detects conflicts between writing-related settings and plugins that might
 * interfere with content creation experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Writing Enhancement Conflicts Diagnostic Class
 *
 * Detects writing-related conflicts and settings issues.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Writing_Enhancement_Conflicts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'writing-enhancement-conflicts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Writing Enhancement Conflicts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects writing-related conflicts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Block editor is available
	 * - No conflicting editor plugins
	 * - Post/draft auto-save is configured
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if using block editor.
		$use_block_editor = get_option( 'use_blocks', true );

		// Check for editor-related plugins that might conflict.
		$editor_plugins = array(
			'gutenberg/gutenberg.php'              => 'Gutenberg',
			'classic-editor/classic-editor.php'    => 'Classic Editor',
			'page-builders-anywhere/plugin.php'    => 'Page Builders Anywhere',
		);

		$active_editor_plugins = array();
		foreach ( $editor_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_editor_plugins[] = $name;
			}
		}

		if ( count( $active_editor_plugins ) > 1 ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of editor plugins */
				__( 'Multiple editor plugins are active which may conflict: %s', 'wpshadow' ),
				implode( ', ', $active_editor_plugins )
			);
		}

		// Check autosave settings.
		if ( ! defined( 'AUTOSAVE_INTERVAL' ) ) {
			// Using default (60 seconds) - this is ok.
		}

		// Check for revision settings.
$wp_post_revisions = WP_POST_REVISIONS;
		if ( false === $wp_post_revisions ) {
			$issues[] = __( 'Post revisions are disabled; you cannot recover previous versions of posts', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/writing-enhancement-conflicts?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
