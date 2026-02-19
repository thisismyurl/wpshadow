<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Layout Thrashing Analyzer
 *
 * Analyzes JavaScript files for patterns that cause forced synchronous layouts (layout thrashing).
 * Detects common anti-patterns: reading layout properties immediately after writing to DOM.
 *
 * Philosophy: Educate (#5) - Help users understand and fix layout performance issues.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.6030.2200
 */
class Layout_Thrashing_Analyzer {

	/**
	 * Properties that trigger layout/reflow when read
	 *
	 * @var array
	 */
	private static $layout_properties = array(
		'offsetTop',
		'offsetLeft',
		'offsetWidth',
		'offsetHeight',
		'clientTop',
		'clientLeft',
		'clientWidth',
		'clientHeight',
		'scrollTop',
		'scrollLeft',
		'scrollWidth',
		'scrollHeight',
		'getBoundingClientRect',
		'getClientRects',
		'innerText',
		'outerText',
		'getComputedStyle',
	);

	/**
	 * Methods that modify DOM/styles
	 *
	 * @var array
	 */
	private static $dom_write_methods = array(
		'innerHTML',
		'outerHTML',
		'textContent',
		'appendChild',
		'removeChild',
		'insertBefore',
		'classList.add',
		'classList.remove',
		'classList.toggle',
		'setAttribute',
		'removeAttribute',
		'style\\.\\w+\\s*=', // Inline style writes
	);

	/**
	 * Analyze JavaScript files for layout thrashing patterns
	 *
	 * @return array Analysis results
	 */
	public static function analyze(): array {
		// Check cache first (24 hours)
		$cached = \WPShadow\Core\Cache_Manager::get( 'layout_thrash_analysis', 'wpshadow_monitoring' );
		if ( $cached ) {
			return $cached;
		}

		$results = array(
			'thrash_patterns'   => 0,
			'files_checked'     => 0,
			'files_with_issues' => 0,
			'patterns_found'    => array(),
		);

		// Get JS files from theme and plugins
		$js_files = self::get_js_files();

		foreach ( $js_files as $file ) {
			$file_patterns = self::analyze_file( $file );
			++$results['files_checked'];

			if ( ! empty( $file_patterns ) ) {
				$results['thrash_patterns'] += count( $file_patterns );
				++$results['files_with_issues'];
				$results['patterns_found'] = array_merge( $results['patterns_found'], $file_patterns );
			}
		}

		// Set cache
		\WPShadow\Core\Cache_Manager::set( 'layout_thrash_count', $results['thrash_patterns'], DAY_IN_SECONDS , 'wpshadow_monitoring');
		\WPShadow\Core\Cache_Manager::set( 'layout_thrash_analysis', $results, DAY_IN_SECONDS , 'wpshadow_monitoring');

		return $results;
	}

	/**
	 * Get JavaScript files from theme and active plugins
	 *
	 * @return array File paths
	 */
	private static function get_js_files(): array {
		$files = array();

		// Get theme JS
		$theme_dir = get_template_directory();
		$theme_js  = self::find_js_files( $theme_dir );
		$files     = array_merge( $files, $theme_js );

		// Get plugin JS (limit to 20 plugins for performance)
		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( array_slice( $active_plugins, 0, 20 ) as $plugin ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin );
			if ( is_dir( $plugin_dir ) ) {
				$plugin_js = self::find_js_files( $plugin_dir );
				$files     = array_merge( $files, $plugin_js );
			}
		}

		return $files;
	}

	/**
	 * Recursively find JavaScript files in directory
	 *
	 * @param string $dir Directory to scan
	 * @return array File paths
	 */
	private static function find_js_files( string $dir ): array {
		$js_files = array();

		if ( ! is_dir( $dir ) ) {
			return $js_files;
		}

		try {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
				\RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ( $iterator as $file ) {
				if ( ! $file->isFile() ) {
					continue;
				}

				// Skip vendor, node_modules, minified files
				$path = $file->getPathname();
				if ( strpos( $path, '/vendor/' ) !== false ||
					strpos( $path, '/node_modules/' ) !== false ||
					strpos( $path, '.min.js' ) !== false ) {
					continue;
				}

				// Skip large files (>500KB)
				if ( $file->getSize() > 512000 ) {
					continue;
				}

				// Only .js files
				if ( $file->getExtension() === 'js' ) {
					$js_files[] = $path;
				}

				// Limit to 50 files for performance
				if ( count( $js_files ) >= 50 ) {
					break;
				}
			}
		} catch ( \Exception $e ) {
			// Silent fail
		}

		return $js_files;
	}

	/**
	 * Analyze a single JS file for layout thrashing patterns
	 *
	 * Looks for: DOM write followed by layout property read in same scope
	 *
	 * @param string $file File path
	 * @return array Patterns found
	 */
	private static function analyze_file( string $file ): array {
		$patterns = array();

		if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
			return $patterns;
		}

		$mtime = (string) filemtime( $file );
		if ( '' !== $mtime ) {
			$file_cache_key = 'wpshadow_layout_thrash_file_' . md5( $file . '|' . $mtime );
			$cached_patterns = get_transient( $file_cache_key );
			if ( is_array( $cached_patterns ) ) {
				return $cached_patterns;
			}
		}

		$content = file_get_contents( $file );
		if ( ! $content ) {
			return $patterns;
		}

		// Remove comments to avoid false positives
		$content = preg_replace( '#/\*.*?\*/#s', '', $content );
		$content = preg_replace( '#//.*$#m', '', $content );

		// Split into lines for analysis
		$lines = explode( "\n", $content );

		// Look for write-then-read patterns within 10 lines
		$window_size = 10;
		for ( $i = 0; $i < count( $lines ); $i++ ) {
			$line = $lines[ $i ];

			// Check if this line has a DOM write
			$has_write = false;
			foreach ( self::$dom_write_methods as $method ) {
				if ( preg_match( '/' . $method . '/i', $line ) ) {
					$has_write = true;
					break;
				}
			}

			// If write found, check next few lines for layout read
			if ( $has_write ) {
				$end = min( $i + $window_size, count( $lines ) );
				for ( $j = $i + 1; $j < $end; $j++ ) {
					$next_line = $lines[ $j ];

					// Check for layout property read
					foreach ( self::$layout_properties as $prop ) {
						if ( preg_match( '/\.' . preg_quote( $prop, '/' ) . '\b/i', $next_line ) ) {
							$patterns[] = array(
								'file'     => basename( $file ),
								'line'     => $i + 1,
								'pattern'  => 'write-then-read',
								'distance' => $j - $i,
							);
							break 2; // Found one, move to next write
						}
					}
				}
			}
		}

		if ( isset( $file_cache_key ) ) {
			set_transient( $file_cache_key, $patterns, 12 * HOUR_IN_SECONDS );
		}

		return $patterns;
	}

	/**
	 * Clear cached analysis
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'layout_thrash_count', 'wpshadow_monitoring' );
		\WPShadow\Core\Cache_Manager::delete( 'layout_thrash_analysis', 'wpshadow_monitoring' );
	}
}
