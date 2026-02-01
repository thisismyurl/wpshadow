<?php
/**
 * Theme Caching Compatibility Diagnostic
 *
 * Checks if the active theme is compatible with caching plugins and
 * page caching mechanisms. Detects problematic patterns that prevent
 * effective caching.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Caching Compatibility Diagnostic Class
 *
 * Validates theme compatibility with caching mechanisms.
 *
 * @since 1.6032.1200
 */
class Diagnostic_Theme_Caching_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-caching-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Caching Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks theme compatibility with caching plugins';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues       = array();
		$template_dir = get_template_directory();

		// Patterns that can break caching.
		$problematic_patterns = array(
			'time()'           => __( 'Direct time() calls prevent page caching', 'wpshadow' ),
			'date('            => __( 'Direct date() calls prevent page caching', 'wpshadow' ),
			'current_time('    => __( 'current_time() without caching strategy', 'wpshadow' ),
			'session_start()'  => __( 'PHP sessions prevent effective caching', 'wpshadow' ),
			'$_COOKIE'         => __( 'Direct cookie access can break caching', 'wpshadow' ),
			'nocache'          => __( 'Explicit cache-busting detected', 'wpshadow' ),
		);

		// Scan key theme files.
		$key_files = array(
			'header.php',
			'footer.php',
			'functions.php',
			'index.php',
			'single.php',
			'page.php',
		);

		foreach ( $key_files as $file ) {
			$file_path = $template_dir . '/' . $file;
			if ( file_exists( $file_path ) ) {
				$content = file_get_contents( $file_path );

				foreach ( $problematic_patterns as $pattern => $description ) {
					if ( false !== stripos( $content, $pattern ) ) {
						$issues[] = array(
							'file'        => $file,
							'pattern'     => $pattern,
							'description' => $description,
						);
					}
				}
			}
		}

		// Check if DONOTCACHEPAGE constant is set in theme.
		$functions_file = $template_dir . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			$content = file_get_contents( $functions_file );
			if ( false !== stripos( $content, 'DONOTCACHEPAGE' ) ) {
				$issues[] = array(
					'file'        => 'functions.php',
					'pattern'     => 'DONOTCACHEPAGE',
					'description' => __( 'Theme globally disables page caching', 'wpshadow' ),
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of caching compatibility issues */
					__( 'Found %d caching compatibility issues in your theme.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'recommendation' => __( 'Consider refactoring dynamic content to use AJAX or fragment caching techniques.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
