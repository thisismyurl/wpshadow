<?php
/**
 * Meta Box Plugin Conflicts Diagnostic
 *
 * Checks for conflicts between Meta Box and other meta field plugins.
 *
 * @since   1.26033.0800
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Meta_Box_Plugin_Conflicts Class
 *
 * Detects conflicts between Meta Box and other plugins.
 *
 * @since 1.26033.0800
 */
class Diagnostic_Meta_Box_Plugin_Conflicts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'meta-box-plugin-conflicts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Meta Box Plugin Conflicts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for conflicts between Meta Box and other plugins';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'meta';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Meta Box is active
		$metabox_active = function_exists( 'rwmb_register_field_group' );

		if ( ! $metabox_active ) {
			return null; // Meta Box not active
		}

		// Check for conflicting plugins
		$conflicting_plugins = array(
			'acf/acf.php'                    => 'Advanced Custom Fields',
			'toolset-types/types.php'        => 'Toolset Types',
			'cpt-ui/custom-post-type-ui.php' => 'Custom Post Type UI with conflicting meta handling',
		);

		$active_conflicts = array();
		foreach ( $conflicting_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$active_conflicts[] = $plugin_name;
			}
		}

		if ( ! empty( $active_conflicts ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: list of conflicting plugins */
					__( 'Detected %d potentially conflicting meta field plugins active: %s. This may cause data conflicts or display issues.', 'wpshadow' ),
					count( $active_conflicts ),
					implode( ', ', $active_conflicts )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/meta-box-plugin-conflicts',
			);
		}

		return null; // No Meta Box conflicts detected
	}
}
