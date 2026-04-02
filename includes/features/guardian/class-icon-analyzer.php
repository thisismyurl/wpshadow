<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Icon Analyzer
 *
 * Analyzes icon usage across theme and plugins to recommend optimal strategy.
 * Detects: icon fonts, SVG sprites, inline SVGs, bitmap icons.
 *
 * Philosophy: Educate (#5) - Show users their icon strategy and optimization opportunities.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.6093.1200
 */
class Icon_Analyzer {

	/**
	 * Analyze icon usage across the site
	 *
	 * Scans theme and plugins for:
	 * - Icon fonts (.woff, .woff2, .ttf with 'icon' in name)
	 * - SVG files (individual vs sprites)
	 * - Bitmap icons (.png, .jpg, .gif with 'icon' in name)
	 *
	 * @return array Analysis results with format counts
	 */
	public static function analyze(): array {
		// Check cache first (24 hours)
		$cached = \WPShadow\Core\Cache_Manager::get( 'icon_analysis_details', 'wpshadow_guardian' );
		if ( $cached ) {
			return $cached;
		}

		$results = array(
			'icon_fonts'           => 0,
			'svg_sprites'          => 0,
			'svg_inline'           => 0,
			'bitmap_icons'         => 0,
			'total_icon_files'     => 0,
			'recommended_strategy' => 'unknown',
			'files_scanned'        => 0,
		);

		// Get theme directory
		$theme_dir = get_template_directory();

		// Get active plugins (limit to 20 for performance)
		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_dirs    = array();
		foreach ( array_slice( $active_plugins, 0, 20 ) as $plugin ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin );
			if ( is_dir( $plugin_dir ) ) {
				$plugin_dirs[] = $plugin_dir;
			}
		}

		// Scan theme
		$theme_icons = self::scan_directory( $theme_dir );

		// Scan plugins
		$plugin_icons = array(
			'icon_fonts'   => 0,
			'svg_sprites'  => 0,
			'svg_inline'   => 0,
			'bitmap_icons' => 0,
		);

		foreach ( $plugin_dirs as $dir ) {
			$plugin_data                   = self::scan_directory( $dir );
			$plugin_icons['icon_fonts']   += $plugin_data['icon_fonts'];
			$plugin_icons['svg_sprites']  += $plugin_data['svg_sprites'];
			$plugin_icons['svg_inline']   += $plugin_data['svg_inline'];
			$plugin_icons['bitmap_icons'] += $plugin_data['bitmap_icons'];
		}

		// Aggregate results
		$results['icon_fonts']       = $theme_icons['icon_fonts'] + $plugin_icons['icon_fonts'];
		$results['svg_sprites']      = $theme_icons['svg_sprites'] + $plugin_icons['svg_sprites'];
		$results['svg_inline']       = $theme_icons['svg_inline'] + $plugin_icons['svg_inline'];
		$results['bitmap_icons']     = $theme_icons['bitmap_icons'] + $plugin_icons['bitmap_icons'];
		$results['total_icon_files'] = $results['icon_fonts'] + $results['svg_sprites'] + $results['svg_inline'] + $results['bitmap_icons'];
		$results['files_scanned']    = $theme_icons['files_scanned'] + count( $plugin_dirs );

		// Determine recommended strategy and format used
		$format_used                     = self::determine_format_used( $results );
		$results['recommended_strategy'] = self::recommend_strategy( $results );

		// Set cache for diagnostic consumption
		\WPShadow\Core\Cache_Manager::set( 'icon_format_used', $format_used, DAY_IN_SECONDS , 'wpshadow_guardian');
		\WPShadow\Core\Cache_Manager::set( 'icon_analysis_details', $results, DAY_IN_SECONDS , 'wpshadow_guardian');

		return $results;
	}

	/**
	 * Scan a directory for icon files
	 *
	 * @param string $dir Directory to scan
	 * @return array Icon file counts by type
	 */
	private static function scan_directory( string $dir ): array {
		$results = array(
			'icon_fonts'    => 0,
			'svg_sprites'   => 0,
			'svg_inline'    => 0,
			'bitmap_icons'  => 0,
			'files_scanned' => 0,
		);

		if ( ! is_dir( $dir ) ) {
			return $results;
		}

		try {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
				\RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ( $iterator as $file ) {
				// Skip directories and non-files
				if ( ! $file->isFile() ) {
					continue;
				}

				// Skip vendor and node_modules
				$path = $file->getPathname();
				if ( strpos( $path, '/vendor/' ) !== false || strpos( $path, '/node_modules/' ) !== false ) {
					continue;
				}

				// Skip large files (>2MB)
				if ( $file->getSize() > 2097152 ) {
					continue;
				}

				$filename  = $file->getFilename();
				$extension = strtolower( $file->getExtension() );

				// Check for icon fonts
				if ( in_array( $extension, array( 'woff', 'woff2', 'ttf', 'eot' ), true ) ) {
					if ( stripos( $filename, 'icon' ) !== false || stripos( $path, '/icons/' ) !== false ) {
						++$results['icon_fonts'];
					}
				}

				// Check for SVG files
				if ( $extension === 'svg' ) {
					$svg_mtime = (string) $file->getMTime();
					$cache_key = 'wpshadow_icon_svg_file_' . md5( $path . '|' . $svg_mtime );
					$cached_svg = get_transient( $cache_key );

					if ( is_array( $cached_svg ) && isset( $cached_svg['is_sprite'] ) ) {
						$is_sprite = (bool) $cached_svg['is_sprite'];
					} else {
						// Check if it's a sprite (contains multiple <symbol> tags)
						$content   = file_get_contents( $path );
						$is_sprite = (bool) ( $content && preg_match_all( '/<symbol\s+/i', $content ) > 3 );
						set_transient(
							$cache_key,
							array( 'is_sprite' => $is_sprite ),
							12 * HOUR_IN_SECONDS
						);
					}

					if ( $is_sprite ) {
						++$results['svg_sprites'];
					} else {
						++$results['svg_inline'];
					}
				}

				// Check for bitmap icons
				if ( in_array( $extension, array( 'png', 'jpg', 'jpeg', 'gif' ), true ) ) {
					if ( stripos( $filename, 'icon' ) !== false || stripos( $path, '/icons/' ) !== false ) {
						++$results['bitmap_icons'];
					}
				}

				++$results['files_scanned'];
			}
		} catch ( \Exception $e ) {
			// Silent fail, return partial results
		}

		return $results;
	}

	/**
	 * Determine which icon format is primarily used
	 *
	 * @param array $results Analysis results
	 * @return string Primary format: 'icon-font', 'svg-sprite', 'svg-inline', 'bitmap', or 'none'
	 */
	private static function determine_format_used( array $results ): string {
		$total = $results['total_icon_files'];

		if ( $total === 0 ) {
			return 'none';
		}

		// Find dominant format
		$formats = array(
			'icon-font'  => $results['icon_fonts'],
			'svg-sprite' => $results['svg_sprites'],
			'svg-inline' => $results['svg_inline'],
			'bitmap'     => $results['bitmap_icons'],
		);

		arsort( $formats );
		return key( $formats );
	}

	/**
	 * Recommend optimal icon strategy based on usage
	 *
	 * @param array $results Analysis results
	 * @return string Recommendation: 'svg-sprite', 'icon-font', or 'optimize'
	 */
	private static function recommend_strategy( array $results ): string {
		$total = $results['total_icon_files'];

		// If using many scattered SVGs, recommend sprite
		if ( $results['svg_inline'] > 20 ) {
			return 'svg-sprite';
		}

		// If using bitmaps, recommend SVG
		if ( $results['bitmap_icons'] > 5 ) {
			return 'use-svg';
		}

		// If using icon font efficiently, that's fine
		if ( $results['icon_fonts'] > 0 && $results['icon_fonts'] < 5 ) {
			return 'current-ok';
		}

		// If using SVG sprite, that's optimal
		if ( $results['svg_sprites'] > 0 ) {
			return 'current-optimal';
		}

		// Default: recommend sprite
		return 'svg-sprite';
	}

	/**
	 * Clear cached analysis
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'icon_format_used', 'wpshadow_guardian' );
		\WPShadow\Core\Cache_Manager::delete( 'icon_analysis_details', 'wpshadow_guardian' );
	}
}
