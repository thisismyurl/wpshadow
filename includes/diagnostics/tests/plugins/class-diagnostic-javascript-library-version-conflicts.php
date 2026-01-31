<?php
/**
 * JavaScript Library Version Conflicts Diagnostic
 *
 * Detects conflicts between different versions of JavaScript libraries loaded by plugins.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2205
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * JavaScript Library Version Conflicts Diagnostic Class
 *
 * Identifies duplicate jQuery, React, Vue, and other JavaScript library versions.
 *
 * @since 1.2601.2205
 */
class Diagnostic_JavaScript_Library_Version_Conflicts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'javascript-library-version-conflicts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'JavaScript Library Version Conflicts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects conflicts between different versions of JavaScript libraries';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2205
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts;

		if ( ! is_object( $wp_scripts ) || empty( $wp_scripts->registered ) ) {
			return null;
		}

		$conflicts   = array();
		$issues      = array();
		$libraries   = array();

		// Common JavaScript libraries to check.
		$library_patterns = array(
			'jquery'    => array( 'jquery', 'jquery-core', 'jquery-migrate' ),
			'react'     => array( 'react', 'react-dom' ),
			'vue'       => array( 'vue', 'vuejs' ),
			'angular'   => array( 'angular', 'angularjs' ),
			'lodash'    => array( 'lodash', 'underscore' ),
			'moment'    => array( 'moment', 'momentjs' ),
			'bootstrap' => array( 'bootstrap', 'bootstrap-js' ),
			'select2'   => array( 'select2' ),
			'datatables' => array( 'datatables', 'datatable' ),
		);

		// Check registered scripts for library versions.
		foreach ( $wp_scripts->registered as $handle => $script ) {
			$handle_lower = strtolower( $handle );

			foreach ( $library_patterns as $library => $patterns ) {
				foreach ( $patterns as $pattern ) {
					if ( strpos( $handle_lower, $pattern ) !== false ) {
						if ( ! isset( $libraries[ $library ] ) ) {
							$libraries[ $library ] = array();
						}
						$libraries[ $library ][] = array(
							'handle' => $handle,
							'src'    => $script->src,
							'ver'    => $script->ver,
						);
					}
				}
			}
		}

		// Check for duplicate libraries.
		foreach ( $libraries as $library => $instances ) {
			if ( count( $instances ) > 1 ) {
				$versions = array();
				foreach ( $instances as $instance ) {
					if ( ! empty( $instance['ver'] ) ) {
						$versions[] = $instance['ver'];
					}
				}
				$unique_versions = array_unique( $versions );

				if ( count( $unique_versions ) > 1 ) {
					$conflicts[] = sprintf(
						/* translators: 1: library name, 2: number of versions */
						__( '%1$s loaded %2$d times with different versions', 'wpshadow' ),
						ucfirst( $library ),
						count( $instances )
					);
				}
			}
		}

		// Check for multiple jQuery versions (critical issue).
		if ( isset( $libraries['jquery'] ) && count( $libraries['jquery'] ) > 1 ) {
			$jquery_versions = array_unique( array_filter( array_column( $libraries['jquery'], 'ver' ) ) );
			if ( count( $jquery_versions ) > 1 ) {
				$issues[] = sprintf(
					/* translators: %s: list of jQuery versions */
					__( 'Multiple jQuery versions detected: %s (causes major compatibility issues)', 'wpshadow' ),
					implode( ', ', $jquery_versions )
				);
			}
		}

		// Check for no-conflict mode issues.
		if ( isset( $libraries['jquery'] ) ) {
			foreach ( $libraries['jquery'] as $jquery_instance ) {
				if ( strpos( $jquery_instance['src'], 'jquery.min.js' ) !== false
					&& strpos( $jquery_instance['src'], 'wp-includes' ) === false ) {
					$issues[] = __( 'Plugin loading jQuery directly (bypassing WordPress jQuery no-conflict mode)', 'wpshadow' );
					break;
				}
			}
		}

		// Check for bundled versions vs CDN.
		$cdn_patterns = array( 'googleapis.com', 'cloudflare.com', 'jsdelivr.net', 'unpkg.com', 'cdnjs.cloudflare.com' );
		$cdn_scripts = 0;
		$local_scripts = 0;

		foreach ( $wp_scripts->registered as $script ) {
			if ( ! empty( $script->src ) ) {
				$is_cdn = false;
				foreach ( $cdn_patterns as $pattern ) {
					if ( strpos( $script->src, $pattern ) !== false ) {
						$is_cdn = true;
						++$cdn_scripts;
						break;
					}
				}
				if ( ! $is_cdn && ( strpos( $script->src, '/plugins/' ) !== false || strpos( $script->src, '/themes/' ) !== false ) ) {
					++$local_scripts;
				}
			}
		}

		if ( $cdn_scripts > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of CDN scripts */
				__( '%d scripts loaded from CDNs (may cause version conflicts with local copies)', 'wpshadow' ),
				$cdn_scripts
			);
		}

		// Report findings.
		if ( ! empty( $conflicts ) || ! empty( $issues ) ) {
			$severity     = 'medium';
			$threat_level = 60;

			if ( ! empty( $issues ) || count( $conflicts ) > 2 ) {
				$severity     = 'high';
				$threat_level = 80;
			}

			$description = __( 'JavaScript library version conflicts detected that may break functionality', 'wpshadow' );

			$details = array(
				'library_count' => count( $libraries ),
			);

			if ( ! empty( $conflicts ) ) {
				$details['conflicts'] = $conflicts;
			}
			if ( ! empty( $issues ) ) {
				$details['critical_issues'] = $issues;
			}
			$details['cdn_scripts']   = $cdn_scripts;
			$details['local_scripts'] = $local_scripts;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/javascript-library-version-conflicts',
				'details'      => $details,
			);
		}

		return null;
	}
}
