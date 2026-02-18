<?php
/**
 * Mobile JavaScript Execution Time
 *
 * Detects long-running JavaScript tasks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.602.1600
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile JavaScript Execution Time
 *
 * Identifies long-running JavaScript tasks that block main thread
 * and delay interaction on mobile.
 *
 * @since 1.602.1600
 */
class Diagnostic_Mobile_JS_Execution_Time extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-js-execution-time';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile JavaScript Execution Time';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects long-running JavaScript tasks';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.602.1600
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::detect_long_tasks();

		if ( empty( $issues ) ) {
			return null; // No long tasks detected
		}

		$threat = 70;
		if ( count( $issues ) > 5 ) {
			$threat = 85; // Critical - many long tasks
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: number of long tasks */
				__( 'Found %d long-running JavaScript tasks', 'wpshadow' ),
				count( $issues )
			),
			'severity'        => 'high',
			'threat_level'    => $threat,
			'long_tasks'      => array_slice( $issues, 0, 5 ),
			'total_tasks'     => count( $issues ),
			'recommendations' => array(
				'Split long tasks into smaller chunks',
				'Use requestIdleCallback for non-critical work',
				'Defer heavy computations to Web Workers',
			),
			'user_impact'     => __( 'Long tasks cause INP delay (>200ms)', 'wpshadow' ),
			'auto_fixable'    => false,
			'kb_link'         => 'https://wpshadow.com/kb/js-execution-time',
		);
	}

	/**
	 * Detect long-running JavaScript tasks.
	 *
	 * @since  1.602.1600
	 * @return array Issues found.
	 */
	private static function detect_long_tasks(): array {
		$html = self::get_page_html();
		if ( ! $html ) {
			return array();
		}

		$issues = array();

		// Check for blocking loops
		preg_match_all( '/for\s*\([^)]+\s*\d+\d+[^)]*\)\s*{/', $html, $loops );
		if ( ! empty( $loops[0] ) ) {
			foreach ( array_slice( $loops[0], 0, 3 ) as $loop ) {
				$issues[] = array(
					'type'    => 'blocking-loop',
					'issue'   => 'Tight loop may block main thread',
					'pattern' => substr( $loop, 0, 50 ),
				);
			}
		}

		// Check for inefficient DOM queries
		preg_match_all( '/querySelector|getElementById|getElementsByClass/i', $html, $queries );
		if ( count( $queries[0] ) > 20 ) {
			$issues[] = array(
				'type'    => 'dom-queries',
				'count'   => count( $queries[0] ),
				'issue'   => 'Many DOM queries can block rendering',
				'fix'     => 'Cache selectors or batch DOM access',
			);
		}

		// Check for synchronous XHR
		if ( preg_match( '/XMLHttpRequest|\?async\s*=\s*false|open\(.*false\)/i', $html ) ) {
			$issues[] = array(
				'type'    => 'sync-xhr',
				'issue'   => 'Synchronous XHR blocks main thread',
				'fix'     => 'Use async: true or fetch API',
			);
		}

		return $issues;
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since  1.602.1600
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		// Load helper if available
		$helper_file = __DIR__ . '/helpers/class-diagnostic-html-helper.php';
		if ( file_exists( $helper_file ) && ! class_exists( 'WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper' ) ) {
			require_once $helper_file;
		}

		// Return null if helper not available
		if ( ! class_exists( 'WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper' ) ) {
			return null;
		}

		return Diagnostic_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
