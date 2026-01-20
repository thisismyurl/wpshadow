<?php
/**
 * Treatment Registry
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

/**
 * Registry for managing treatments/fixes
 */
class Treatment_Registry {
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
		'Treatment_Hotlink_Protection',
		'Treatment_Head_Cleanup',
		'Treatment_Iframe_Busting',
		'Treatment_Image_Lazy_Load',
		'Treatment_External_Fonts',
		'Treatment_Plugin_Auto_Updates',
		'Treatment_Error_Log',
		'Treatment_Skiplinks',
		'Treatment_Asset_Versions',
		'Treatment_CSS_Classes',
		'Treatment_Maintenance',
		'Treatment_Nav_ARIA',
		'Treatment_Search_Indexing',
	);
	
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
	 * @return array Result array.
	 */
	public static function apply_treatment( $finding_id ) {
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
		
		return call_user_func( array( $treatment, 'apply' ) );
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
