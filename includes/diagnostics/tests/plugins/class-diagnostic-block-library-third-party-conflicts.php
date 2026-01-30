<?php
/**
 * Block Library Third Party Conflicts Diagnostic
 *
 * Block Library Third Party Conflicts issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1245.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Library Third Party Conflicts Diagnostic Class
 *
 * @since 1.1245.0000
 */
class Diagnostic_BlockLibraryThirdPartyConflicts extends Diagnostic_Base {

	protected static $slug = 'block-library-third-party-conflicts';
	protected static $title = 'Block Library Third Party Conflicts';
	protected static $description = 'Block Library Third Party Conflicts issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check if Gutenberg is active
		$has_gutenberg = function_exists( 'register_block_type' );

		if ( ! $has_gutenberg ) {
			return null;
		}

		$issues = array();

		// Check 1: Registered block count
		$registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();
		if ( count( $registered_blocks ) > 200 ) {
			$issues[] = sprintf( __( '%d registered blocks (slow editor)', 'wpshadow' ), count( $registered_blocks ) );
		}

		// Check 2: Duplicate block names
		$block_names = array();
		foreach ( $registered_blocks as $name => $block ) {
			if ( in_array( $name, $block_names, true ) ) {
				$issues[] = sprintf( __( 'Duplicate block: %s (conflicts)', 'wpshadow' ), $name );
			}
			$block_names[] = $name;
		}

		// Check 3: Block editor assets
		global $wp_scripts;
		$editor_scripts = 0;
		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( strpos( $handle, 'block-editor' ) !== false || strpos( $handle, 'wp-block' ) !== false ) {
				$editor_scripts++;
			}
		}
		if ( $editor_scripts > 50 ) {
			$issues[] = sprintf( __( '%d editor scripts (performance)', 'wpshadow' ), $editor_scripts );
		}

		// Check 4: Block categories
		$categories = get_block_categories( get_post() );
		if ( count( $categories ) > 20 ) {
			$issues[] = sprintf( __( '%d block categories (cluttered UI)', 'wpshadow' ), count( $categories ) );
		}

		// Check 5: Block styles
		global $wp_styles;
		$block_styles = 0;
		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( strpos( $handle, 'wp-block' ) !== false ) {
				$block_styles++;
			}
		}
		if ( $block_styles > 30 ) {
			$issues[] = sprintf( __( '%d block stylesheets (CSS bloat)', 'wpshadow' ), $block_styles );
		}

		// Check 6: Block patterns
		if ( function_exists( 'WP_Block_Patterns_Registry' ) ) {
			$patterns = \WP_Block_Patterns_Registry::get_instance()->get_all_registered();
			if ( count( $patterns ) > 100 ) {
				$issues[] = sprintf( __( '%d block patterns (slow loading)', 'wpshadow' ), count( $patterns ) );
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Block library has %d third-party issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/block-library-third-party-conflicts',
		);
	}
}
