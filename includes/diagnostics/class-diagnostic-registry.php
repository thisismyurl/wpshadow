<?php
/**
 * Diagnostic Registry
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

/**
 * Registry for managing diagnostic checks.
 */
class Diagnostic_Registry {
	/**
	 * List of diagnostic classes.
	 *
	 * @var array
	 */
	private static $diagnostics = array(
		'Diagnostic_Memory_Limit',
		'Diagnostic_Backup',
		'Diagnostic_Permalinks',
		'Diagnostic_Tagline',
		'Diagnostic_SSL',
		'Diagnostic_Outdated_Plugins',
		'Diagnostic_Debug_Mode',
		'Diagnostic_WordPress_Version',
		'Diagnostic_Plugin_Count',
		'Diagnostic_Inactive_Plugins',
		'Diagnostic_Hotlink_Protection',
		'Diagnostic_Head_Cleanup',
		'Diagnostic_Iframe_Busting',
		'Diagnostic_Image_Lazy_Load',
		'Diagnostic_External_Fonts',
		'Diagnostic_Plugin_Auto_Updates',
		'Diagnostic_Error_Log',
		'Diagnostic_Core_Integrity',
		'Diagnostic_Skiplinks',
		'Diagnostic_Asset_Versions',
		'Diagnostic_CSS_Classes',
		'Diagnostic_Maintenance',
		'Diagnostic_Nav_ARIA',
		'Diagnostic_Admin_Username',
		'Diagnostic_Search_Indexing',
		'Diagnostic_Admin_Email',
		'Diagnostic_Timezone',
	);
	
	/**
	 * Initialize and load all diagnostic classes.
	 */
	public static function init() {
		self::load_diagnostics();
	}
	
	/**
	 * Load all diagnostic class files.
	 */
	private static function load_diagnostics() {
		$diagnostics_dir = plugin_dir_path( __FILE__ );
		$core_dir = dirname( $diagnostics_dir ) . '/core/';
		
		// Load base class first (from core directory)
		$base_file = $core_dir . 'class-diagnostic-base.php';
		if ( file_exists( $base_file ) ) {
			require_once $base_file;
		}
		
		foreach ( self::$diagnostics as $diagnostic ) {
			$file = $diagnostics_dir . 'class-' . str_replace( '_', '-', strtolower( $diagnostic ) ) . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	}
	
	/**
	 * Run all diagnostic checks.
	 *
	 * @return array Array of findings.
	 */
	public static function run_all_checks() {
		$findings = array();
		
		foreach ( self::$diagnostics as $diagnostic ) {
			$class_name = __NAMESPACE__ . '\\' . $diagnostic;
			if ( class_exists( $class_name ) && method_exists( $class_name, 'check' ) ) {
				$result = call_user_func( array( $class_name, 'check' ) );
				if ( null !== $result ) {
					$findings[] = $result;
				}
			}
		}
		
		return $findings;
	}
	
	/**
	 * Get list of registered diagnostic classes.
	 *
	 * @return array List of diagnostic class names.
	 */
	public static function get_diagnostics() {
		return self::$diagnostics;
	}
	
	/**
	 * Register a new diagnostic class.
	 *
	 * @param string $class_name Diagnostic class name.
	 */
	public static function register( $class_name ) {
		if ( ! in_array( $class_name, self::$diagnostics, true ) ) {
			self::$diagnostics[] = $class_name;
		}
	}
	
	/**
	 * Unregister a diagnostic class.
	 *
	 * @param string $class_name Diagnostic class name.
	 */
	public static function unregister( $class_name ) {
		$key = array_search( $class_name, self::$diagnostics, true );
		if ( false !== $key ) {
			unset( self::$diagnostics[ $key ] );
			self::$diagnostics = array_values( self::$diagnostics );
		}
	}
}
