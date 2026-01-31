<?php
/**
 * Theme Function Conflicts Diagnostic
 *
 * Detects function name conflicts or redeclaration errors in theme.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1245
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Function Conflicts Diagnostic Class
 *
 * Checks for function naming conflicts in theme code.
 *
 * @since 1.5049.1245
 */
class Diagnostic_Theme_Function_Conflicts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-function-conflicts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Function Conflicts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for function naming conflicts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1245
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();
		$theme_dir = get_stylesheet_directory();
		$functions_file = $theme_dir . '/functions.php';
		$issues = array();

		if ( ! file_exists( $functions_file ) ) {
			return null;
		}

		$functions_content = file_get_contents( $functions_file );

		// Check for functions without function_exists() wrapper.
		preg_match_all( '/function\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\(/i', $functions_content, $matches );
		
		if ( ! empty( $matches[1] ) ) {
			$unwrapped_functions = array();
			
			foreach ( $matches[1] as $function_name ) {
				// Skip anonymous functions and closures.
				if ( $function_name === 'function' ) {
					continue;
				}
				
				// Check if function has function_exists() check.
				$check_pattern = '/if\s*\(\s*!\s*function_exists\s*\(\s*[\'"]' . preg_quote( $function_name, '/' ) . '[\'"]\s*\)\s*\)/i';
				if ( ! preg_match( $check_pattern, $functions_content ) ) {
					$unwrapped_functions[] = $function_name;
				}
			}

			if ( count( $unwrapped_functions ) > 5 ) {
				$issues[] = sprintf(
					/* translators: %d: number of unwrapped functions */
					__( '%d functions lack function_exists() checks', 'wpshadow' ),
					count( $unwrapped_functions )
				);
			}
		}

		// Check for common WordPress function names that might conflict.
		$wp_functions = array( 'the_content', 'the_title', 'get_header', 'get_footer', 'get_sidebar' );
		$conflicting_functions = array();

		foreach ( $wp_functions as $wp_function ) {
			if ( preg_match( '/function\s+' . preg_quote( $wp_function, '/' ) . '\s*\(/i', $functions_content ) ) {
				$conflicting_functions[] = $wp_function;
			}
		}

		if ( ! empty( $conflicting_functions ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of function names */
				__( 'Theme redefines WordPress core functions: %s', 'wpshadow' ),
				implode( ', ', $conflicting_functions )
			);
		}

		// Check for generic function names (potential conflicts).
		$generic_names = array( 'setup', 'init', 'header', 'footer', 'sidebar', 'content' );
		$generic_functions = array();

		foreach ( $generic_names as $generic ) {
			if ( preg_match( '/function\s+' . preg_quote( $generic, '/' ) . '\s*\(/i', $functions_content ) ) {
				$generic_functions[] = $generic;
			}
		}

		if ( count( $generic_functions ) > 2 ) {
			$issues[] = sprintf(
				/* translators: %d: number of generic function names */
				__( '%d functions use generic names (potential conflicts)', 'wpshadow' ),
				count( $generic_functions )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Theme functions may conflict with other code', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'     => array(
					'theme'                  => $theme->get( 'Name' ),
					'unwrapped_count'        => isset( $unwrapped_functions ) ? count( $unwrapped_functions ) : 0,
					'conflicting_functions'  => $conflicting_functions ?? array(),
					'generic_functions'      => $generic_functions ?? array(),
					'issues'                 => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-function-conflicts',
			);
		}

		return null;
	}
}
