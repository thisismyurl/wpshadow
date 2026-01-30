<?php
/**
 * Multilingual Press Content Relations Diagnostic
 *
 * Multilingual Press Content Relations misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1175.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multilingual Press Content Relations Diagnostic Class
 *
 * @since 1.1175.0000
 */
class Diagnostic_MultilingualPressContentRelations extends Diagnostic_Base {

	protected static $slug = 'multilingual-press-content-relations';
	protected static $title = 'Multilingual Press Content Relations';
	protected static $description = 'Multilingual Press Content Relations misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'mlp_get_linked_elements' ) && ! class_exists( 'Multilingual_Press' ) ) {
			return null;
		}

		$issues = array();

		// Check if multisite is active
		if ( ! is_multisite() ) {
			$issues[] = 'Multilingual Press requires multisite installation';
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Multilingual Press content relations issue: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( 80 ),
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/multilingual-press-content-relations',
			);
		}

		// Check for content relationships
		global $wpdb;
		$relations_table = $wpdb->base_prefix . 'multilingual_linked';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$relations_table}'" ) === $relations_table ) {
			$relation_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$relations_table}" );

			if ( $relation_count < 1 ) {
				$issues[] = 'no content relationships configured';
			}
		}

		// Check for orphaned translations
		$orphaned = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$relations_table} r
			 LEFT JOIN {$wpdb->posts} p ON r.post_id = p.ID
			 WHERE p.ID IS NULL"
		);

		if ( $orphaned > 0 ) {
			$issues[] = "orphaned translation links ({$orphaned} broken relationships)";
		}

		// Check for automatic translation linking
		$auto_link = get_site_option( 'mlp_auto_link_translations', '0' );
		if ( '0' === $auto_link ) {
			$issues[] = 'automatic translation linking disabled (manual work required)';
		}

		// Check for redirect configuration
		$redirect_enabled = get_site_option( 'mlp_redirect_enabled', '1' );
		if ( '0' === $redirect_enabled ) {
			$issues[] = 'language redirects disabled (users may see wrong language)';
		}

		// Check for duplicate content issues
		$duplicate_content = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} p1
			 JOIN {$wpdb->posts} p2 ON p1.post_title = p2.post_title
			 AND p1.ID != p2.ID AND p1.post_type = p2.post_type
			 WHERE p1.post_status = 'publish' AND p2.post_status = 'publish'
			 LIMIT 10"
		);

		if ( $duplicate_content > 5 ) {
			$issues[] = 'duplicate content detected across language sites';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Multilingual Press content relation issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/multilingual-press-content-relations',
			);
		}

		return null;
	}
}
