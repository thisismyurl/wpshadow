<?php
/**
 * CPT UI Archive Template Diagnostic
 *
 * CPT UI archive templates missing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.447.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT UI Archive Template Diagnostic Class
 *
 * @since 1.447.0000
 */
class Diagnostic_CptuiArchiveTemplate extends Diagnostic_Base {

	protected static $slug = 'cptui-archive-template';
	protected static $title = 'CPT UI Archive Template';
	protected static $description = 'CPT UI archive templates missing';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'CPT_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Archive support.
		$post_types = get_option( 'cptui_post_types', array() );
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $pt ) {
				if ( isset( $pt['has_archive'] ) && false === $pt['has_archive'] ) {
					$issues[] = "archive disabled for {$pt['name']}";
					break;
				}
			}
		}

		// Check 2: Template file existence.
		$theme_dir = get_template_directory();
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $pt ) {
				if ( ! file_exists( $theme_dir . '/archive-' . $pt['name'] . '.php' ) ) {
					$issues[] = "missing archive template for {$pt['name']}";
					break;
				}
			}
		}

		// Check 3: Rewrite rules.
		$rewrite = get_option( 'cptui_rewrite_rules_flushed', '0' );
		if ( '0' === $rewrite ) {
			$issues[] = 'rewrite rules not flushed';
		}

		// Check 4: Query vars.
		$query_vars = get_option( 'cptui_register_query_vars', '1' );
		if ( '0' === $query_vars ) {
			$issues[] = 'query vars not registered';
		}

		// Check 5: Archive label.
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $pt ) {
				if ( empty( $pt['label'] ) ) {
					$issues[] = 'missing archive label';
					break;
				}
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 55, 40 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'CPTUI archive issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cptui-archive-template',
			);
		}

		return null;
	}
}
