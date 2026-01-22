<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * CSS Analyzer for Guardian
 *
 * Analyzes CSS selector complexity across active theme and plugins.
 * Sets transient data for diagnostic consumption.
 *
 * Philosophy: Educate (#5) - Help developers write performant CSS
 * by identifying overly complex selectors that slow rendering.
 *
 * Performance: Only scans CSS files once per day, results cached in transient.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.2601.2200
 */
class CSS_Analyzer {

	/**
	 * Analyze CSS selector complexity
	 *
	 * Scans CSS files from active theme, parent theme, and active plugins.
	 * Counts selectors with >4 descendant levels (e.g., .a .b .c .d .e).
	 *
	 * Results cached in transient for 24 hours:
	 * - wpshadow_complex_selector_count: Total count
	 * - wpshadow_css_analysis_details: Full analysis data
	 *
	 * Called by Guardian_Manager::run_health_check()
	 *
	 * @return array Analysis results with counts and details
	 */
	public static function analyze(): array {
		// Check if analysis ran recently (cache for 24 hours)
		$cached = get_transient( 'wpshadow_css_analysis_details' );
		if ( $cached && is_array( $cached ) ) {
			return $cached;
		}

		$results = array(
			'complex_count'       => 0,
			'total_selectors'     => 0,
			'files_scanned'       => 0,
			'complex_examples'    => array(),
			'css_variables_count' => 0,
			'import_count'        => 0,
			'animation_count'     => 0,
			'timestamp'           => time(),
		);

		// Get CSS files to scan
		$css_files = self::get_css_files();

		foreach ( $css_files as $file_path => $file_info ) {
			if ( ! is_readable( $file_path ) ) {
				continue;
			}

			$file_results = self::analyze_file( $file_path );

			$results['complex_count']       += $file_results['complex_count'];
			$results['total_selectors']     += $file_results['total_selectors'];
			$results['css_variables_count'] += $file_results['css_variables_count'];
			$results['import_count']        += $file_results['import_count'];
			$results['animation_count']     += $file_results['animation_count'];
			$results['files_scanned']++;

			// Keep top 10 most complex examples
			if ( ! empty( $file_results['complex_selectors'] ) ) {
				foreach ( $file_results['complex_selectors'] as $selector_data ) {
					$results['complex_examples'][] = array(
						'selector'   => $selector_data['selector'],
						'levels'     => $selector_data['levels'],
						'file'       => $file_info['relative_path'],
						'line'       => $selector_data['line'] ?? 0,
						'source'     => $file_info['source'], // 'theme' or 'plugin'
						'source_name'=> $file_info['name'],
					);
				}
			}
		}

		// Sort examples by complexity, keep top 10
		usort( $results['complex_examples'], function( $a, $b ) {
			return $b['levels'] - $a['levels'];
		} );
		$results['complex_examples'] = array_slice( $results['complex_examples'], 0, 10 );

		// Store results in transients for diagnostics to consume
		set_transient( 'wpshadow_complex_selector_count', $results['complex_count'], DAY_IN_SECONDS );
		set_transient( 'wpshadow_css_custom_properties_count', $results['css_variables_count'], DAY_IN_SECONDS );
		set_transient( 'wpshadow_css_import_count', $results['import_count'], DAY_IN_SECONDS );
		set_transient( 'wpshadow_css_animation_count', $results['animation_count'], DAY_IN_SECONDS );
		set_transient( 'wpshadow_css_analysis_details', $results, DAY_IN_SECONDS );

		return $results;
	}

	/**
	 * Get list of CSS files to analyze
	 *
	 * Includes:
	 * - Active theme CSS files
	 * - Parent theme CSS files (if child theme active)
	 * - Active plugin CSS files (limited to avoid timeout)
	 *
	 * @return array Associative array: file_path => file_info
	 */
	private static function get_css_files(): array {
		$files = array();

		// Active theme
		$theme      = wp_get_theme();
		$theme_dir  = get_stylesheet_directory();
		$theme_files = self::find_css_in_directory( $theme_dir, 'theme', $theme->get( 'Name' ) );
		$files       = array_merge( $files, $theme_files );

		// Parent theme (if child theme)
		if ( is_child_theme() ) {
			$parent_theme = wp_get_theme( $theme->get( 'Template' ) );
			$parent_dir   = get_template_directory();
			$parent_files = self::find_css_in_directory( $parent_dir, 'theme', $parent_theme->get( 'Name' ) );
			$files        = array_merge( $files, $parent_files );
		}

		// Active plugins (limit to 20 plugins to avoid timeout)
		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_count   = 0;
		foreach ( $active_plugins as $plugin_file ) {
			if ( $plugin_count >= 20 ) {
				break;
			}

			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin_file );
			if ( ! is_dir( $plugin_dir ) ) {
				continue;
			}

			// Get plugin name
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file, false, false );
			$plugin_name = $plugin_data['Name'] ?? dirname( $plugin_file );

			$plugin_files = self::find_css_in_directory( $plugin_dir, 'plugin', $plugin_name );
			$files        = array_merge( $files, $plugin_files );
			$plugin_count++;
		}

		return $files;
	}

	/**
	 * Find CSS files in directory
	 *
	 * Recursively scans directory for .css files.
	 * Excludes minified files (.min.css) and common vendor paths.
	 *
	 * @param string $dir         Directory path
	 * @param string $source_type 'theme' or 'plugin'
	 * @param string $source_name Theme or plugin name
	 *
	 * @return array Associative array: file_path => file_info
	 */
	private static function find_css_in_directory( string $dir, string $source_type, string $source_name ): array {
		$files = array();

		if ( ! is_dir( $dir ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( ! $file->isFile() || $file->getExtension() !== 'css' ) {
				continue;
			}

			$file_path = $file->getPathname();

			// Skip minified files
			if ( strpos( $file_path, '.min.css' ) !== false ) {
				continue;
			}

			// Skip common vendor/library paths
			$relative_path = str_replace( $dir, '', $file_path );
			if ( preg_match( '#/(node_modules|vendor|bower_components|libraries)/#', $relative_path ) ) {
				continue;
			}

			// Skip very large files (>500KB) to avoid memory issues
			if ( $file->getSize() > 500000 ) {
				continue;
			}

			$files[ $file_path ] = array(
				'relative_path' => $relative_path,
				'source'        => $source_type,
				'name'          => $source_name,
				'size'          => $file->getSize(),
			);
		}

		return $files;
	}

	/**
	 * Analyze single CSS file
	 *
	 * Parses CSS and collects metrics:
	 * - Selector complexity (descendant combinators)
	 * - CSS custom properties (--var-name)
	 * - @import statements
	 * - @keyframes animations
	 *
	 * @param string $file_path Full path to CSS file
	 *
	 * @return array File analysis results
	 */
	private static function analyze_file( string $file_path ): array {
		$results = array(
			'complex_count'        => 0,
			'total_selectors'      => 0,
			'complex_selectors'    => array(),
			'css_variables_count'  => 0,
			'import_count'         => 0,
			'animation_count'      => 0,
		);

		$content = file_get_contents( $file_path );
		if ( empty( $content ) ) {
			return $results;
		}

		$original_content = $content;

		// Count @import statements (before removing comments)
		preg_match_all( '#@import\s+#i', $original_content, $import_matches );
		$results['import_count'] = count( $import_matches[0] );

		// Count @keyframes animations
		preg_match_all( '#@keyframes\s+[\w-]+#i', $original_content, $animation_matches );
		$results['animation_count'] = count( $animation_matches[0] );

		// Count CSS custom properties (--variable-name)
		preg_match_all( '#--[\w-]+\s*:#', $original_content, $css_var_matches );
		$results['css_variables_count'] = count( array_unique( $css_var_matches[0] ) );

		// Remove comments
		$content = preg_replace( '#/\*.*?\*/#s', '', $content );

		// Remove @media, @keyframes, @font-face blocks (analyze only their contents)
		$content = preg_replace( '#@(media|keyframes|font-face|supports)[^{]*\{#', '{', $content );

		// Extract all selectors (everything before opening brace)
		preg_match_all( '#([^{}]+)\{#', $content, $matches, PREG_OFFSET_CAPTURE );

		if ( empty( $matches[1] ) ) {
			return $results;
		}

		$line_number = 1;
		foreach ( $matches[1] as $selector_data ) {
			$selector_text = trim( $selector_data[0] );
			$offset        = $selector_data[1];

			// Calculate line number
			$line_number = substr_count( $content, "\n", 0, $offset ) + 1;

			// Split by comma (multiple selectors)
			$individual_selectors = array_map( 'trim', explode( ',', $selector_text ) );

			foreach ( $individual_selectors as $selector ) {
				if ( empty( $selector ) ) {
					continue;
				}

				$results['total_selectors']++;

				$complexity = self::calculate_selector_complexity( $selector );

				// Flag selectors with >4 levels
				if ( $complexity > 4 ) {
					$results['complex_count']++;
					$results['complex_selectors'][] = array(
						'selector' => $selector,
						'levels'   => $complexity,
						'line'     => $line_number,
					);
				}
			}
		}

		return $results;
	}

	/**
	 * Calculate selector complexity
	 *
	 * Counts descendant combinators (spaces) and other combinators.
	 * Ignores spaces within pseudo-selectors, attributes, and functions.
	 *
	 * @param string $selector CSS selector
	 *
	 * @return int Complexity level (number of descendant combinators + 1)
	 */
	private static function calculate_selector_complexity( string $selector ): int {
		// Remove pseudo-selectors and their contents
		$selector = preg_replace( '#:(hover|active|focus|nth-child\([^)]+\)|not\([^)]+\))#', '', $selector );

		// Remove attribute selectors [attr="value"]
		$selector = preg_replace( '#\[[^\]]+\]#', '', $selector );

		// Remove ::before, ::after
		$selector = preg_replace( '#::[a-z-]+#', '', $selector );

		// Normalize whitespace
		$selector = preg_replace( '#\s+#', ' ', trim( $selector ) );

		// Count spaces (descendant combinators)
		$space_count = substr_count( $selector, ' ' );

		// Count other combinators: >, +, ~
		$combinator_count = substr_count( $selector, '>' ) + substr_count( $selector, '+' ) + substr_count( $selector, '~' );

		// Total levels = space_count + combinator_count + 1 (the base selector)
		return $space_count + $combinator_count + 1;
	}

	/**
	 * Get latest analysis results
	 *
	 * Returns cached results without re-scanning.
	 *
	 * @return array|null Analysis results or null if no cache
	 */
	public static function get_results(): ?array {
		return get_transient( 'wpshadow_css_analysis_details' ) ?: null;
	}

	/**
	 * Clear analysis cache
	 *
	 * Forces next analyze() call to re-scan files.
	 * Useful when theme/plugins are updated.
	 */
	public static function clear_cache(): void {
		delete_transient( 'wpshadow_complex_selector_count' );
		delete_transient( 'wpshadow_css_custom_properties_count' );
		delete_transient( 'wpshadow_css_import_count' );
		delete_transient( 'wpshadow_css_animation_count' );
		delete_transient( 'wpshadow_css_analysis_details' );
	}
}
