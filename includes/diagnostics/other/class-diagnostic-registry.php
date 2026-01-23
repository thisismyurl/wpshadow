<?php
declare(strict_types=1);
/**
 * Diagnostic Registry
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Diagnostic_Result_Normalizer;

/**
 * Registry for managing diagnostic checks.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Registry {
	/**
	 * Quick scan diagnostic classes (previously all checks).
	 *
	 * @var array
	 */
	private static $quick_diagnostics = array(
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
		'Diagnostic_Theme_Update_Noise',
		'Diagnostic_Plugin_Update_Noise',
		'Diagnostic_Hotlink_Protection',
		// Head Cleanup family (split into 4 individual tests)
		'Diagnostic_Head_Cleanup_Emoji',
		'Diagnostic_Head_Cleanup_OEmbed',
		'Diagnostic_Head_Cleanup_RSD',
		'Diagnostic_Head_Cleanup_Shortlink',
		'Diagnostic_Iframe_Busting',
		'Diagnostic_Image_Lazy_Load',
		'Diagnostic_External_Fonts',
		'Diagnostic_Jquery_Migrate',
		'Diagnostic_Plugin_Auto_Updates',
		'Diagnostic_Error_Log',
		'Diagnostic_Core_Integrity',
		'Diagnostic_Skiplinks',
		// Asset Versions family (split into 2 individual tests)
		'Diagnostic_Asset_Versions_CSS',
		'Diagnostic_Asset_Versions_JS',
		'Diagnostic_CSS_Classes',
		'Diagnostic_Maintenance',
		'Diagnostic_Nav_ARIA',
		'Diagnostic_Admin_Username',
		'Diagnostic_Admin_Font_Bloat',
		'Diagnostic_Admin_Theme_Assets',
		'Diagnostic_Search_Indexing',
		'Diagnostic_Admin_Email',
		'Diagnostic_User_Notification_Email',
		'Diagnostic_Timezone',
		'Diagnostic_Content_Optimizer',
		'Diagnostic_Paste_Cleanup',
		'Diagnostic_HTML_Cleanup',
		'Diagnostic_Pre_Publish_Review',
		'Diagnostic_Embed_Disable',
		'Diagnostic_Interactivity_Cleanup',
		'Diagnostic_PHP_Version',
		'Diagnostic_File_Permissions',
		'Diagnostic_Security_Headers',
		'Diagnostic_Post_Via_Email',
		'Diagnostic_Post_Via_Email_Category',
		'Diagnostic_Initial_Setup',
		'Diagnostic_Comments_Disabled',
		'Diagnostic_Howdy_Greeting',
		'Diagnostic_Dark_Mode',
		'Diagnostic_Mobile_Friendliness',
		'Diagnostic_Database_Indexes',
		'Diagnostic_PHP_Compatibility',
		'Diagnostic_Theme_Performance',
		'Diagnostic_Font_Optimization',
		'Diagnostic_Monitoring_Status',
		'Diagnostic_Backup_Verification',
		'Diagnostic_Automation_Readiness',
		'Diagnostic_Object_Cache',
		'Diagnostic_Heartbeat_Throttling',
		'Diagnostic_XML_Sitemap',
		'Diagnostic_Robots_Txt',
		'Diagnostic_Favicon',
		'Diagnostic_Two_Factor',
		'Diagnostic_Disallow_File_Edit',
		'Diagnostic_Webhooks_Readiness',
		'Diagnostic_Resource_Hints',
		'Diagnostic_REST_API',
		'Diagnostic_RSS_Feeds',
		'Diagnostic_WP_Generator',
		'Diagnostic_Block_Cleanup',
		'Diagnostic_Consent_Checks',
		'Diagnostic_Emoji_Scripts',
		'Diagnostic_JQuery_Cleanup',
	);

	/**
	 * Deep scan only diagnostic classes (run in addition to quick set).
	 *
	 * @var array
	 */
	private static $deep_diagnostics = array(
		// Heavier or more intrusive checks (network/database intensive)
		'Diagnostic_Database_Health',
		'Diagnostic_Core_Integrity',
		'Diagnostic_Broken_Links',
		'Diagnostic_Database_Indexes',
		'Diagnostic_File_Permissions',
		// Extended coverage
		'Diagnostic_Security_Headers',
		'Diagnostic_Object_Cache',
		'Diagnostic_XML_Sitemap',
		'Diagnostic_Robots_Txt',
		'Diagnostic_Two_Factor',
		'Diagnostic_Disallow_File_Edit',
		'Diagnostic_Webhooks_Readiness',
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
		$base_dir        = dirname( $diagnostics_dir );
		$core_dir        = $base_dir . '/core/';

		// Load base class first (from core directory)
		$base_file = $core_dir . 'class-diagnostic-base.php';
		if ( file_exists( $base_file ) ) {
			require_once $base_file;
		}

		// Shared helpers used by lean diagnostics and normalization.
		$lean_file = $core_dir . 'class-diagnostic-lean-checks.php';
		if ( file_exists( $lean_file ) ) {
			require_once $lean_file;
		}

		$normalizer_file = $core_dir . 'class-diagnostic-result-normalizer.php';
		if ( file_exists( $normalizer_file ) ) {
			require_once $normalizer_file;
		}

		$all = array_unique( array_merge( self::$quick_diagnostics, self::$deep_diagnostics ) );

		foreach ( $all as $diagnostic ) {
			$slug    = 'class-' . str_replace( '_', '-', strtolower( $diagnostic ) ) . '.php';
			$primary = $base_dir . '/' . $slug;
			$legacy  = $diagnostics_dir . $slug;

			// Preferred location (includes/diagnostics/*)
			if ( file_exists( $primary ) ) {
				require_once $primary;
				continue;
			}

			// Legacy location (includes/diagnostics/other/*)
			if ( file_exists( $legacy ) ) {
				require_once $legacy;
				continue;
			}

			// Fallback: search one directory deep for reorganized diagnostics (e.g., seo/, performance/)
			$subdirs = glob( $base_dir . '/*/' . $slug );
			if ( is_array( $subdirs ) ) {
				foreach ( $subdirs as $subfile ) {
					if ( file_exists( $subfile ) ) {
						require_once $subfile;
						break;
					}
				}
			}
		}
	}

	/**
	 * Run quick scan checks (default set, matches Quick Scan button).
	 *
	 * @return array Array of findings.
	 */
	public static function run_quickscan_checks() {
		$findings = array();

		foreach ( self::$quick_diagnostics as $diagnostic ) {
			$class_name = __NAMESPACE__ . '\\' . $diagnostic;
			if ( class_exists( $class_name ) ) {
				$result = self::execute_diagnostic( $class_name );

				// Log that this diagnostic ran (even if it found nothing)
				if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
					$category   = is_array( $result ) && isset( $result['category'] ) ? (string) $result['category'] : '';
					$finding_id = is_array( $result ) && isset( $result['id'] ) ? (string) $result['id'] : ( method_exists( $class_name, 'get_slug' ) ? (string) call_user_func( array( $class_name, 'get_slug' ) ) : '' );

					Activity_Logger::log(
						'diagnostic_run',
						sprintf( 'Ran diagnostic: %s', $diagnostic ),
						$category,
						array(
							'diagnostic'  => $diagnostic,
							'trigger'     => 'quick_scan',
							'found_issue' => is_array( $result ) && ! empty( $result ),
							'finding_id'  => $finding_id,
						)
					);
				}

				if ( null !== $result ) {
					$findings[] = $result;
				}
			}
		}

		return $findings;
	}

	/**
	 * Run deep scan checks (quick set plus deep-only diagnostics).
	 *
	 * @return array Array of findings.
	 */
	public static function run_deepscan_checks() {
		$findings    = array();
		$deep_extras = apply_filters( 'wpshadow_deep_scan_diagnostics', self::$deep_diagnostics );
		$diagnostics = array_unique( array_merge( self::$quick_diagnostics, $deep_extras ) );

		foreach ( $diagnostics as $diagnostic ) {
			$class_name = __NAMESPACE__ . '\\' . $diagnostic;
			if ( class_exists( $class_name ) ) {
				$result = self::execute_diagnostic( $class_name );

				// Log that this diagnostic ran (even if it found nothing)
				if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
					$category   = is_array( $result ) && isset( $result['category'] ) ? (string) $result['category'] : '';
					$finding_id = is_array( $result ) && isset( $result['id'] ) ? (string) $result['id'] : ( method_exists( $class_name, 'get_slug' ) ? (string) call_user_func( array( $class_name, 'get_slug' ) ) : '' );

					Activity_Logger::log(
						'diagnostic_run',
						sprintf( 'Ran diagnostic: %s', $diagnostic ),
						$category,
						array(
							'diagnostic'  => $diagnostic,
							'trigger'     => 'deep_scan',
							'found_issue' => is_array( $result ) && ! empty( $result ),
							'finding_id'  => $finding_id,
						)
					);
				}

				if ( null !== $result ) {
					$findings[] = $result;
				}
			}
		}

		// Clear cache after deep scan completes so dashboard picks up new results
		if ( function_exists( 'wpshadow_clear_findings_cache' ) ) {
			wpshadow_clear_findings_cache();
		}

		return $findings;
	}

	/**
	 * Run all checks (alias for run_deepscan_checks).
	 *
	 * @return array Array of findings.
	 */
	public static function run_all_checks() {
		return self::run_deepscan_checks();
	}

	/**
	 * Get list of registered diagnostic classes.
	 *
	 * @return array List of diagnostic class names.
	 */
	public static function get_diagnostics() {
		return self::$quick_diagnostics;
	}

	/**
	 * Register a new diagnostic class.
	 *
	 * @param string $class_name Diagnostic class name.
	 */
	public static function register( $class_name ) {
		if ( ! in_array( $class_name, self::$quick_diagnostics, true ) ) {
			self::$quick_diagnostics[] = $class_name;
		}
	}

	/**
	 * Unregister a diagnostic class.
	 *
	 * @param string $class_name Diagnostic class name.
	 */
	public static function unregister( $class_name ) {
		$key = array_search( $class_name, self::$quick_diagnostics, true );
		if ( false !== $key ) {
			unset( self::$quick_diagnostics[ $key ] );
			self::$quick_diagnostics = array_values( self::$quick_diagnostics );
		}
	}

	/**
	 * Get all diagnostics grouped by family
	 *
	 * @return array Array keyed by family slug with diagnostic info
	 */
	public static function get_diagnostics_by_family() {
		$all      = array_unique( array_merge( self::$quick_diagnostics, self::$deep_diagnostics ) );
		$families = array();

		foreach ( $all as $diagnostic ) {
			$class_name = __NAMESPACE__ . '\\' . $diagnostic;
			if ( class_exists( $class_name ) && method_exists( $class_name, 'get_family' ) ) {
				$family = call_user_func( array( $class_name, 'get_family' ) );
				if ( ! empty( $family ) ) {
					if ( ! isset( $families[ $family ] ) ) {
						$families[ $family ] = array(
							'label'       => call_user_func( array( $class_name, 'get_family_label' ) ),
							'diagnostics' => array(),
							'count'       => 0,
						);
					}
					$families[ $family ]['diagnostics'][] = array(
						'class'       => $class_name,
						'slug'        => call_user_func( array( $class_name, 'get_slug' ) ),
						'title'       => call_user_func( array( $class_name, 'get_title' ) ),
						'description' => call_user_func( array( $class_name, 'get_description' ) ),
					);
					++$families[ $family ]['count'];
				}
			}
		}

		return $families;
	}

	/**
	 * Get all diagnostics in a specific family
	 *
	 * @param string $family Family slug.
	 * @return array Array of diagnostic class slugs
	 */
	public static function get_family_members( string $family ) {
		$families = self::get_diagnostics_by_family();
		if ( ! isset( $families[ $family ] ) ) {
			return array();
		}

		$members = array();
		foreach ( $families[ $family ]['diagnostics'] as $diagnostic ) {
			$members[] = $diagnostic['slug'];
		}
		return $members;
	}

	/**
	 * Get family information
	 *
	 * @param string $family Family slug.
	 * @return array|null Family info or null if not found
	 */
	public static function get_family_info( string $family ) {
		$families = self::get_diagnostics_by_family();
		return isset( $families[ $family ] ) ? $families[ $family ] : null;
	}

	/**
	 * Check if a diagnostic belongs to a family
	 *
	 * @param string $diagnostic_slug Diagnostic slug.
	 * @return string|null Family slug or null if not in family
	 */
	public static function get_diagnostic_family( string $diagnostic_slug ) {
		$all = array_unique( array_merge( self::$quick_diagnostics, self::$deep_diagnostics ) );

		foreach ( $all as $diagnostic ) {
			$class_name = __NAMESPACE__ . '\\' . $diagnostic;
			if ( class_exists( $class_name ) && method_exists( $class_name, 'check' ) ) {
				$slug = call_user_func( array( $class_name, 'get_slug' ) );
				if ( $slug === $diagnostic_slug ) {
					$family = call_user_func( array( $class_name, 'get_family' ) );
					return ! empty( $family ) ? $family : null;
				}
			}
		}

		return null;
	}

	/**
	 * Execute a diagnostic using its supported method and normalize the output.
	 *
	 * @param string $class_name Diagnostic class name (fully qualified).
	 * @return array|null Normalized finding or null when no issue or invalid structure.
	 */
	private static function execute_diagnostic( string $class_name ): ?array {
		$method = '';

		if ( method_exists( $class_name, 'check' ) ) {
			$method = 'check';
		} elseif ( method_exists( $class_name, 'run' ) ) {
			// Legacy lean diagnostics supported via run() implementations.
			$method = 'run';
		}

		if ( '' === $method ) {
			return null;
		}

		$result = call_user_func( array( $class_name, $method ) );

		if ( class_exists( '\\WPShadow\\Core\\Diagnostic_Result_Normalizer' ) ) {
			return Diagnostic_Result_Normalizer::normalize( $class_name, $result );
		}

		return is_array( $result ) ? $result : null;
	}
}
