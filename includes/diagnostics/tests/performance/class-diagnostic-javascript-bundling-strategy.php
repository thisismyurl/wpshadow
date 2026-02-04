<?php
/**
 * JavaScript Bundling Strategy Diagnostic
 *
 * Analyzes JavaScript bundling approach and optimization opportunities.
 *
 * @since   1.6033.2115
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * JavaScript Bundling Strategy Diagnostic
 *
 * Evaluates JavaScript bundling patterns and identifies optimization opportunities.
 *
 * @since 1.6033.2115
 */
class Diagnostic_Javascript_Bundling_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'javascript-bundling-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'JavaScript Bundling Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes JavaScript bundling approach for optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2115
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts;

		if ( ! isset( $wp_scripts->registered ) ) {
			return null;
		}

		// Analyze enqueued scripts
		$total_scripts     = 0;
		$total_size        = 0;
		$external_scripts  = 0;
		$inline_scripts    = 0;
		$jquery_dependents = 0;

		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( ! $wp_scripts->is_enqueued( $handle ) ) {
				continue;
			}

			$total_scripts++;

			// Count external scripts
			if ( isset( $script->src ) && ! empty( $script->src ) ) {
				$external_scripts++;

				// Estimate size for local scripts
				if ( strpos( $script->src, home_url() ) !== false ) {
					$file_path = str_replace( home_url(), ABSPATH, $script->src );
					if ( file_exists( $file_path ) ) {
						$total_size += filesize( $file_path );
					}
				}
			}

			// Count inline scripts
			if ( isset( $script->extra['after'] ) || isset( $script->extra['before'] ) ) {
				$inline_scripts++;
			}

			// Count jQuery dependencies
			if ( isset( $script->deps ) && in_array( 'jquery', $script->deps, true ) ) {
				$jquery_dependents++;
			}
		}

		// Convert size to KB
		$total_size_kb = round( $total_size / 1024, 2 );

		// Check for bundling opportunities
		if ( $external_scripts > 8 && $total_size_kb < 200 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of scripts, 2: total size in KB */
					__( '%1$d separate JavaScript files detected (%2$s KB total). Consider bundling to reduce HTTP requests.', 'wpshadow' ),
					$external_scripts,
					$total_size_kb
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/javascript-bundling-strategy',
				'meta'         => array(
					'total_scripts'     => $total_scripts,
					'external_scripts'  => $external_scripts,
					'inline_scripts'    => $inline_scripts,
					'total_size_kb'     => $total_size_kb,
					'jquery_dependents' => $jquery_dependents,
					'recommendation'    => 'Use Autoptimize or WP Rocket to bundle scripts',
					'impact_estimate'   => sprintf( '%d--%d ms faster load time', $external_scripts * 20, $external_scripts * 50 ),
					'http_requests_saved' => $external_scripts - 2,
				),
			);
		}

		// Check for excessive jQuery dependencies
		if ( $jquery_dependents > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of jQuery-dependent scripts */
					__( '%d scripts depend on jQuery. Consider modern alternatives or vanilla JavaScript.', 'wpshadow' ),
					$jquery_dependents
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/javascript-bundling-strategy',
				'meta'         => array(
					'jquery_dependents' => $jquery_dependents,
					'recommendation'    => 'Migrate to vanilla JavaScript where possible',
					'impact_estimate'   => '30-50 KB jQuery removal potential',
					'jquery_size'       => '~32 KB (minified + gzipped)',
				),
			);
		}

		return null;
	}
}
