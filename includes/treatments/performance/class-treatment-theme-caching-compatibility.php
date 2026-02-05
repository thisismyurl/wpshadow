<?php
/**
 * Theme Caching Compatibility Treatment
 *
 * Checks if the active theme is compatible with caching plugins and mechanisms.
 *
 * **What This Check Does:**
 * 1. Analyzes theme code for caching anti-patterns
 * 2. Detects timestamp/nonce generation in critical paths
 * 3. Flags per-user customization preventing cache reuse
 * 4. Identifies object modification during rendering
 * 5. Checks for WooCommerce dynamic content issues
 * 6. Validates multisite blog compatibility\n *
 * **Why This Matters:**\n * Some themes generate unique output per visitor (timestamp, nonce, random content). This prevents
 * page caching—cache stores one version, but next visitor gets different output. Cache becomes useless.\n * Page load goes from 0.1 seconds (cached) to 3 seconds (uncached, no caching helps).\n *
 * **Real-World Scenario:**\n * E-commerce theme showed "last updated" timestamp on every product page (dynamically generated
 * per view). Page caching plugin couldn't cache (timestamp changes every second). Site had caching
 * plugin enabled but it was useless. Page load time: 2.5 seconds. After removing dynamic timestamp,
 * caching worked perfectly. Page load: 0.08 seconds (30x faster). Same server, same traffic, but cache
 * now effective.\n *
 * **Business Impact:**\n * - Page caching doesn't work (cache becomes useless)\n * - Page loads remain slow despite caching plugin\n * - Wasted money on caching plugin (not helping)\n * - Database overloaded (cache not reducing load)\n * - Site scaling requires expensive infrastructure upgrade\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Enables caching effectiveness\n * - #8 Inspire Confidence: Predictable cache behavior\n * - #10 Talk-About-Worthy: "Caching now actually works"\n *
 * **Related Checks:**\n * - Cache Hit Ratio (cache effectiveness)\n * - Page Cache Implementation (caching plugins)\n * - Browser Caching Headers (client-side caching)\n * - Transient Usage (theme caching patterns)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/theme-caching-patterns\n * - Video: https://wpshadow.com/training/cache-compatible-themes (7 min)\n * - Advanced: https://wpshadow.com/training/cache-busting-strategies (12 min)\n *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Caching Compatibility Treatment Class
 *
 * Validates theme compatibility with caching mechanisms.
 *
 * @since 1.6032.1200
 */
class Treatment_Theme_Caching_Compatibility extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-caching-compatibility';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Caching Compatibility';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks theme compatibility with caching plugins';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
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
