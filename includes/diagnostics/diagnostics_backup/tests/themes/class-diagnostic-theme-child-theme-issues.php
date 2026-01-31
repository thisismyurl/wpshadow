<?php
/**
 * Theme Child Theme Issues Diagnostic
 *
 * Detects problems with child theme implementation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1700
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Child Theme Issues Class
 *
 * Validates child theme setup and parent theme availability.
 * Improper child themes break on parent updates.
 *
 * @since 1.5029.1700
 */
class Diagnostic_Theme_Child_Theme_Issues extends Diagnostic_Base {

	protected static $slug        = 'theme-child-theme-issues';
	protected static $title       = 'Theme Child Theme Issues';
	protected static $description = 'Detects child theme implementation problems';
	protected static $family      = 'themes';

	public static function check() {
		$cache_key = 'wpshadow_child_theme_issues';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$current_theme = wp_get_theme();
		
		// Only check if it's a child theme.
		if ( ! is_child_theme() ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$issues = array();
		$parent = $current_theme->parent();

		// Check if parent theme exists.
		if ( ! $parent || ! $parent->exists() ) {
			$issues[] = 'Parent theme is missing or not found';
		}

		// Check for proper style.css header.
		$stylesheet_path = get_stylesheet_directory() . '/style.css';
		if ( file_exists( $stylesheet_path ) ) {
			$stylesheet_content = file_get_contents( $stylesheet_path );
			
			if ( strpos( $stylesheet_content, 'Template:' ) === false ) {
				$issues[] = 'Missing "Template:" header in style.css';
			}
		}

		// Check if functions.php properly enqueues parent styles.
		$functions_path = get_stylesheet_directory() . '/functions.php';
		if ( file_exists( $functions_path ) ) {
			$functions_content = file_get_contents( $functions_path );
			
			if ( strpos( $functions_content, 'wp_enqueue_style' ) === false ) {
				$issues[] = 'May not be properly enqueuing parent theme styles';
			}
		}

		// Check parent theme version compatibility.
		if ( $parent && $parent->exists() ) {
			$parent_version = $parent->get( 'Version' );
			$last_updated = $parent->get( 'LastUpdated' );
			
			// If parent was updated recently, child might have compatibility issues.
			if ( $last_updated && ( time() - strtotime( $last_updated ) ) < ( 30 * DAY_IN_SECONDS ) ) {
				$issues[] = 'Parent theme updated recently - test child theme compatibility';
			}
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: theme name */
					__( 'Child theme "%s" has implementation issues. Review configuration.', 'wpshadow' ),
					$current_theme->get( 'Name' )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/themes-child-theme-setup',
				'data'         => array(
					'theme_name' => $current_theme->get( 'Name' ),
					'parent_theme' => $parent ? $parent->get( 'Name' ) : 'Unknown',
					'issues' => $issues,
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
