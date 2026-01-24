<?php

/**
 * WPShadow Admin Diagnostic Test: Excessive Font Files
 *
 * Tests if wp-admin loads too many web font files, which causes:
 * - Slow initial page render (FOUT - Flash of Unstyled Text)
 * - Wasted bandwidth (fonts are large files)
 * - Multiple DNS lookups and HTTP requests
 * - Poor performance on slow connections
 *
 * Pattern: Counts font files in $wp_styles and checks for @font-face rules
 * Context: Requires admin context, uses $wp_styles
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin Performance
 * @philosophy  #7 Ridiculously Good - Fast font loading, minimal requests
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Admin Font File Count
 *
 * Detects excessive web font files (> 4)
 *
 * @verified Not yet tested
 */
class Test_Admin_Font_Files extends Diagnostic_Base
{

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		// Only run in admin context
		if (! is_admin()) {
			return null;
		}

		global $wp_styles;

		$font_files = array();
		$font_sources = array();

		// Check enqueued stylesheets for font files
		if (isset($wp_styles) && is_object($wp_styles)) {
			foreach ($wp_styles->queue ?? array() as $handle) {
				if (! isset($wp_styles->registered[$handle])) {
					continue;
				}

				$src = $wp_styles->registered[$handle]->src ?? '';

				// Check if this is a font file or contains fonts
				if ($this->is_font_file($src)) {
					$font_files[] = array(
						'handle' => $handle,
						'src'    => $src,
						'type'   => 'direct',
					);

					$source = $this->get_font_source($src);
					if ($source) {
						$font_sources[$source] = ($font_sources[$source] ?? 0) + 1;
					}
				}

				// Check for @font-face rules in inline styles
				$inline_css = '';
				if (! empty($wp_styles->registered[$handle]->extra['after'])) {
					$inline_css .= implode("\n", $wp_styles->registered[$handle]->extra['after']);
				}
				if (! empty($wp_styles->registered[$handle]->extra['before'])) {
					$inline_css .= implode("\n", $wp_styles->registered[$handle]->extra['before']);
				}

				if (! empty($inline_css)) {
					$font_face_count = $this->count_font_face_rules($inline_css);
					if ($font_face_count > 0) {
						$font_files[] = array(
							'handle' => $handle,
							'src'    => 'inline @font-face rules',
							'type'   => 'inline',
							'count'  => $font_face_count,
						);
					}
				}
			}
		}

		$font_count = count($font_files);

		// Threshold: More than 4 font files is excessive for admin
		// WordPress core typically uses 0-1 (Dashicons is an icon font)
		$threshold = 4;

		if ($font_count <= $threshold) {
			return null; // Pass
		}

		// Identify common font sources
		$google_fonts = $font_sources['Google Fonts'] ?? 0;
		$typekit = $font_sources['Adobe Typekit'] ?? 0;
		$local = $font_sources['Local'] ?? 0;

		return array(
			'id'           => 'admin-font-files',
			'title'        => 'Excessive Font Files in Admin',
			'description'  => sprintf(
				'WordPress admin is loading %d web font files. Each font file requires additional HTTP requests and bandwidth. Admin pages should use system fonts or minimal custom fonts. Sources: %s Google Fonts, %s Typekit, %s local fonts.',
				$font_count,
				$google_fonts,
				$typekit,
				$local
			)
			'kb_link'      => 'https://wpshadow.com/kb/reduce-font-files',
			'training_link' => 'https://wpshadow.com/training/optimize-web-fonts',
			'auto_fixable' => false,
			'threat_level' => 38,
			'module'       => 'admin-performance',
			'priority'     => 18,
			'meta'         => array(
				'font_count'     => $font_count,
				'threshold'      => $threshold,
				'google_fonts'   => $google_fonts,
				'typekit'        => $typekit,
				'local_fonts'    => $local,
				'sample_fonts'   => array_slice($font_files, 0, 5),
			),
		);
	}

	/**
	 * Check if URL is a font file
	 *
	 * @param string $url URL to check
	 * @return bool True if font file
	 */
	private function is_font_file(string $url): bool
	{
		if (empty($url)) {
			return false;
		}

		// Check for font file extensions
		if (preg_match('/\.(woff2?|ttf|otf|eot|svg)(\?|$)/i', $url)) {
			return true;
		}

		// Check for Google Fonts API
		if (strpos($url, 'fonts.googleapis.com') !== false || strpos($url, 'fonts.gstatic.com') !== false) {
			return true;
		}

		// Check for Adobe Typekit
		if (strpos($url, 'use.typekit.net') !== false || strpos($url, 'typekit.com') !== false) {
			return true;
		}

		// Check for other font services
		if (strpos($url, 'cloud.typography.com') !== false || strpos($url, 'fast.fonts.net') !== false) {
			return true;
		}

		return false;
	}

	/**
	 * Identify font source
	 *
	 * @param string $url Font URL
	 * @return string|null Source name
	 */
	private function get_font_source(string $url): ?string
	{
		if (strpos($url, 'fonts.googleapis.com') !== false || strpos($url, 'fonts.gstatic.com') !== false) {
			return 'Google Fonts';
		}
		if (strpos($url, 'typekit') !== false) {
			return 'Adobe Typekit';
		}
		if (strpos($url, 'wp-content') !== false) {
			return 'Local';
		}
		return 'External';
	}

	/**
	 * Count @font-face rules in CSS
	 *
	 * @param string $css CSS content
	 * @return int Number of @font-face rules
	 */
	private function count_font_face_rules(string $css): int
	{
		preg_match_all('/@font-face\s*\{/i', $css, $matches);
		return count($matches[0] ?? array());
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array Diagnostic information
	 */
	public static function get_info(): array
	{
		return array(
			'name'        => 'Admin Font Files',
			'category'    => 'admin-performance',
			'severity'    => 'medium',
			'description' => 'Detects excessive web font files slowing admin load',
		);
	}
}
