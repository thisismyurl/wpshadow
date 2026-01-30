<?php
/**
 * Untranslated Admin Strings Diagnostic
 *
 * Scans theme/plugin for hardcoded English strings not wrapped in
 * translation functions. Indicates incomplete i18n implementation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1825
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Untranslated_Strings Class
 *
 * Detects hardcoded text strings that should be translatable.
 *
 * @since 1.6028.1825
 */
class Diagnostic_Untranslated_Strings extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'untranslated-strings';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Untranslated Admin Strings Detected';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Scans for hardcoded English strings missing translation functions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1825
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$untranslated = self::scan_for_untranslated_strings();

		if ( empty( $untranslated ) ) {
			return null; // All strings are properly translated.
		}

		$string_count = count( $untranslated );

		// Calculate translation coverage.
		$total_strings = self::estimate_total_strings();
		$coverage = $total_strings > 0 ? ( ( $total_strings - $string_count ) / $total_strings ) * 100 : 100;

		// Determine severity.
		if ( $coverage < 80 ) {
			$severity     = 'medium';
			$threat_level = 50;
		} elseif ( $coverage < 95 ) {
			$severity     = 'low';
			$threat_level = 35;
		} else {
			$severity     = 'low';
			$threat_level = 25;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: untranslated count, 2: coverage percentage */
				__( 'Found %1$d untranslated strings (%.1f%% coverage)', 'wpshadow' ),
				$string_count,
				$coverage
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/untranslated-strings',
			'family'      => self::$family,
			'meta'        => array(
				'untranslated_count' => $string_count,
				'coverage'           => round( $coverage, 1 ),
				'recommended'        => __( 'Wrap all user-facing strings in __() or _e() functions', 'wpshadow' ),
				'impact_level'       => 'medium',
				'immediate_actions'  => array(
					__( 'Identify hardcoded English strings', 'wpshadow' ),
					__( 'Wrap in translation functions', 'wpshadow' ),
					__( 'Add text domain parameter', 'wpshadow' ),
					__( 'Generate new .pot file', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Hardcoded English strings make themes/plugins unusable in other languages. Every user-facing string must be wrapped in translation functions (__(), _e(), esc_html__(), etc.) for proper internationalization. Without this, translators can\'t provide localized versions. This blocks international adoption and WordPress.org plugin directory approval. Professional code always uses translation functions.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Language Mixing: English appears in translated interface', 'wpshadow' ),
					__( 'Market Exclusion: Non-English users can\'t fully use product', 'wpshadow' ),
					__( 'Unprofessional: Shows lack of quality standards', 'wpshadow' ),
					__( 'WP.org Rejection: Required for plugin directory', 'wpshadow' ),
				),
				'untranslated_examples' => array_slice( $untranslated, 0, 10 ), // Limit display.
				'common_patterns' => array(
					'echo_text'       => __( 'echo "Click here"; → echo __("Click here", "textdomain");', 'wpshadow' ),
					'html_attribute'  => __( 'placeholder="Name" → placeholder="<?php esc_attr_e(\'Name\', \'textdomain\'); ?>"', 'wpshadow' ),
					'button_label'    => __( '"Submit" → __("Submit", "textdomain")', 'wpshadow' ),
					'error_message'   => __( '"Error occurred" → __("Error occurred", "textdomain")', 'wpshadow' ),
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Manual String Wrapping', 'wpshadow' ),
						'description' => __( 'Add translation functions to all strings', 'wpshadow' ),
						'steps'       => array(
							__( 'Search codebase for echo/print statements', 'wpshadow' ),
							__( 'Replace: echo "Text" → echo __("Text", "your-textdomain")', 'wpshadow' ),
							__( 'For direct echo: _e("Text", "textdomain")', 'wpshadow' ),
							__( 'For attributes: esc_attr__("Text", "textdomain")', 'wpshadow' ),
							__( 'Generate .pot file: wp i18n make-pot', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Say What Plugin', 'wpshadow' ),
						'description' => __( 'Override hardcoded strings without editing code', 'wpshadow' ),
						'steps'       => array(
							__( 'Install "Say What?" plugin (temporary workaround)', 'wpshadow' ),
							__( 'Add text replacement rules for hardcoded strings', 'wpshadow' ),
							__( 'This allows translation while fixing code', 'wpshadow' ),
							__( 'Still need to fix code properly', 'wpshadow' ),
							__( 'Update theme/plugin with proper functions', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Automated Detection', 'wpshadow' ),
						'description' => __( 'Use tools to find all hardcoded strings', 'wpshadow' ),
						'steps'       => array(
							__( 'Install i18n-check tool: npm install -g i18n-check', 'wpshadow' ),
							__( 'Or use WordPress Coding Standards PHPCS sniffs', 'wpshadow' ),
							__( 'Run: phpcs --standard=WordPress-Core --sniffs=WordPress.WP.I18n', 'wpshadow' ),
							__( 'Review report of all hardcoded strings', 'wpshadow' ),
							__( 'Fix each occurrence systematically', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Always use translation functions for user-facing text', 'wpshadow' ),
					__( 'Add text domain as second parameter', 'wpshadow' ),
					__( 'Use esc_html__() for HTML output security', 'wpshadow' ),
					__( 'Add translator comments with sprintf: /* translators: %s: name */', 'wpshadow' ),
					__( 'Never translate debug messages or code identifiers', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Run: phpcs --standard=WordPress.WP.I18n path/', 'wpshadow' ),
						__( 'Or search for: grep -r "echo [\'\\"]" --include="*.php"', 'wpshadow' ),
						__( 'Switch site to different language', 'wpshadow' ),
						__( 'Look for English strings in interface', 'wpshadow' ),
					),
					'expected_result' => __( '>95% translation coverage, all user-facing strings wrapped', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Scan for untranslated strings in theme/plugin.
	 *
	 * @since  1.6028.1825
	 * @return array Untranslated string details.
	 */
	private static function scan_for_untranslated_strings() {
		$found = array();

		// Scan active theme.
		$theme_dir = get_template_directory();
		$theme_strings = self::scan_directory_for_strings( $theme_dir );
		$found = array_merge( $found, $theme_strings );

		// Scan top 3 active plugins.
		$active_plugins = array_slice( get_option( 'active_plugins', array() ), 0, 3 );
		foreach ( $active_plugins as $plugin ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin );
			if ( is_dir( $plugin_dir ) ) {
				$plugin_strings = self::scan_directory_for_strings( $plugin_dir );
				$found = array_merge( $found, $plugin_strings );
			}
		}

		return array_slice( $found, 0, 50 ); // Limit results.
	}

	/**
	 * Scan directory for untranslated strings.
	 *
	 * @since  1.6028.1825
	 * @param  string $directory Directory path.
	 * @return array Found untranslated strings.
	 */
	private static function scan_directory_for_strings( $directory ) {
		$found = array();

		if ( ! is_dir( $directory ) ) {
			return $found;
		}

		$php_files = self::get_php_files( $directory );

		foreach ( array_slice( $php_files, 0, 30 ) as $file ) {
			$strings = self::scan_file_for_strings( $file );
			$found = array_merge( $found, $strings );

			// Limit total findings.
			if ( count( $found ) >= 50 ) {
				break;
			}
		}

		return $found;
	}

	/**
	 * Scan single file for untranslated strings.
	 *
	 * @since  1.6028.1825
	 * @param  string $file File path.
	 * @return array Found strings.
	 */
	private static function scan_file_for_strings( $file ) {
		$found = array();
		$content = @file_get_contents( $file );

		if ( $content === false ) {
			return $found;
		}

		// Pattern for echo/print with hardcoded string (not already translated).
		$patterns = array(
			// echo "text" or echo 'text' (not followed by translation function).
			'/\b(?:echo|print)\s+["\']([A-Z][^"\']{5,50})["\'];/i',
			// Simple string assignment: $var = "Text";
			'/\$\w+\s*=\s*["\']([A-Z][^"\']{5,50})["\'];/i',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match_all( $pattern, $content, $matches, PREG_OFFSET_CAPTURE ) ) {
				foreach ( $matches[1] as $match ) {
					$string = $match[0];
					$position = $match[1];

					// Skip if looks like code/variable.
					if ( preg_match( '/[{}\[\]$><]/', $string ) ) {
						continue;
					}

					// Skip if very short or all caps (likely constant).
					if ( strlen( $string ) < 6 || strtoupper( $string ) === $string ) {
						continue;
					}

					// Get line number.
					$line_number = substr_count( substr( $content, 0, $position ), "\n" ) + 1;

					$found[] = array(
						'string' => $string,
						'file'   => str_replace( ABSPATH, '', $file ),
						'line'   => $line_number,
					);

					// Limit per file.
					if ( count( $found ) >= 10 ) {
						break 2;
					}
				}
			}
		}

		return $found;
	}

	/**
	 * Estimate total translatable strings.
	 *
	 * @since  1.6028.1825
	 * @return int Estimated total.
	 */
	private static function estimate_total_strings() {
		// Quick estimate based on codebase size.
		$theme_dir = get_template_directory();
		$php_files = self::get_php_files( $theme_dir );

		// Rough estimate: 10 translatable strings per file.
		return count( $php_files ) * 10;
	}

	/**
	 * Get PHP files in directory.
	 *
	 * @since  1.6028.1825
	 * @param  string $directory Directory path.
	 * @return array File paths.
	 */
	private static function get_php_files( $directory ) {
		$files = array();

		if ( ! is_dir( $directory ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $directory, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && $file->getExtension() === 'php' ) {
				// Skip vendor.
				if ( strpos( $file->getPathname(), '/vendor/' ) !== false ) {
					continue;
				}
				$files[] = $file->getPathname();
			}

			// Limit scan.
			if ( count( $files ) >= 50 ) {
				break;
			}
		}

		return $files;
	}
}
