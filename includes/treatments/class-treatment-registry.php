<?php
/**
 * Treatment Registry
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Abstract_Registry;

/**
 * Registry for managing treatments/fixes
 */
class Treatment_Registry extends Abstract_Registry {
	/**
	 * List of treatment classes
	 *
	 * @var array
	 */
	private static $treatments = array(
		'Treatment_Permalinks',
		'Treatment_Memory_Limit',
		'Treatment_Debug_Mode',
		'Treatment_SSL',
		'Treatment_Inactive_Plugins',
		'Treatment_Outdated_Plugins',
		'Treatment_Theme_Update_Noise',
		'Treatment_Plugin_Update_Noise',
		'Treatment_Hotlink_Protection',
		'Treatment_Head_Cleanup',
		'Treatment_Iframe_Busting',
		'Treatment_Image_Lazy_Load',
		'Treatment_External_Fonts',
		'Treatment_JQuery_Migrate',
		'Treatment_Block_Analytics_Hosts',
		'Treatment_Plugin_Auto_Updates',
		'Treatment_Error_Log',
		'Treatment_Skiplinks',
		'Treatment_Asset_Versions',
		'Treatment_CSS_Classes',
		'Treatment_Maintenance',
		'Treatment_Nav_ARIA',
		'Treatment_Search_Indexing',
		'Treatment_Content_Optimizer',
		'Treatment_Paste_Cleanup',
		'Treatment_HTML_Cleanup',
		'Treatment_Pre_Publish_Review',
		'Treatment_Embed_Disable',
		'Treatment_Interactivity_Cleanup',
		'Treatment_Strip_Resource_Hints',
		'Treatment_Strip_SpeculationRules',
		'Treatment_Strip_JSON_LD',
		'Treatment_Strip_Social_Meta',
		'Treatment_Remove_Comments_Menu',
		'Treatment_Remove_Howdy',
		'Treatment_File_Editors',
	);

	/**
	 * Get the list of registered items.
	 *
	 * @return array Array of class names.
	 */
	protected static function get_registered_items() {
		return self::$treatments;
	}

	/**
	 * Get the namespace for registered items.
	 *
	 * @return string Namespace prefix.
	 */
	protected static function get_namespace() {
		return __NAMESPACE__ . '\\';
	}

	/**
	 * Initialize and load all treatment classes
	 */
	public static function init() {
		self::load_treatments();
	}

	/**
	 * Load all treatment class files
	 */
	private static function load_treatments() {
		$treatments_dir = plugin_dir_path( __FILE__ );

		// Load interface definition before concrete treatments.
		$interface_file = $treatments_dir . 'interface-treatment.php';
		if ( file_exists( $interface_file ) ) {
			require_once $interface_file;
		}

		foreach ( self::$treatments as $treatment ) {
			$file = $treatments_dir . 'class-' . str_replace( '_', '-', strtolower( $treatment ) ) . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	}

	/**
	 * Get treatment for a specific finding
	 *
	 * @param string $finding_id Finding identifier.
	 * @return string|null Treatment class name or null if not found.
	 */
	public static function get_treatment( $finding_id ) {
		foreach ( self::$treatments as $treatment ) {
			$class_name = __NAMESPACE__ . '\\' . $treatment;
			if ( class_exists( $class_name ) && method_exists( $class_name, 'get_finding_id' ) ) {
				if ( call_user_func( array( $class_name, 'get_finding_id' ) ) === $finding_id ) {
					return $class_name;
				}
			}
		}
		return null;
	}

	/**
	 * Apply a treatment
	 *
	 * @param string $finding_id Finding identifier.
	 * @param bool   $dry_run    Whether to run in dry-run mode (default: false).
	 * @return array Result array.
	 */
	public static function apply_treatment( $finding_id, $dry_run = false ) {
		$treatment = self::get_treatment( $finding_id );

		if ( ! $treatment ) {
			return array(
				'success' => false,
				'message' => 'No treatment available for this finding.',
			);
		}

		if ( ! call_user_func( array( $treatment, 'can_apply' ) ) ) {
			return array(
				'success' => false,
				'message' => 'Treatment cannot be applied at this time.',
			);
		}

		// Use execute method if available (supports dry run)
		if ( method_exists( $treatment, 'execute' ) ) {
			return call_user_func( array( $treatment, 'execute' ), $dry_run );
		}

		// Fallback to direct apply (no dry run support)
		if ( $dry_run ) {
			return array(
				'success'     => true,
				'message'     => 'Treatment can be applied (dry run - no changes made)',
				'dry_run'     => true,
				'would_apply' => true,
			);
		}

		return call_user_func( array( $treatment, 'apply' ) );
	}

	/**
	 * Undo a treatment
	 *
	 * @param string $finding_id Finding identifier.
	 * @return array Result array.
	 */
	public static function undo_treatment( $finding_id ) {
		$treatment = self::get_treatment( $finding_id );

		if ( ! $treatment ) {
			return array(
				'success' => false,
				'message' => 'No treatment available for this finding.',
			);
		}

		if ( ! method_exists( $treatment, 'undo' ) ) {
			return array(
				'success' => false,
				'message' => 'This treatment does not support undo.',
			);
		}

		return call_user_func( array( $treatment, 'undo' ) );
	}

	/**
	 * Get list of registered treatments
	 *
	 * @return array List of treatment class names.
	 */
	public static function get_treatments() {
		return self::$treatments;
	}

	/**
	 * Register a new treatment
	 *
	 * @param string $class_name Treatment class name.
	 */
	public static function register( $class_name ) {
		if ( ! in_array( $class_name, self::$treatments, true ) ) {
			self::$treatments[] = $class_name;
		}
	}

	/**
	 * Unregister a treatment
	 *
	 * @param string $class_name Treatment class name.
	 */
	public static function unregister( $class_name ) {
		$key = array_search( $class_name, self::$treatments, true );
		if ( false !== $key ) {
			unset( self::$treatments[ $key ] );
			self::$treatments = array_values( self::$treatments );
		}
	}
}
