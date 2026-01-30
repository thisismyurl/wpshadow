<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Verify_Logical_Heading_Hierarchy_H1_H2_H3 extends Diagnostic_Base {
	protected static $slug = 'html-verify-logical-heading-hierarchy-h1-h2-h3';
	protected static $title = 'Invalid Heading Hierarchy';
	protected static $description = 'Verifies logical H1>H2>H3 heading hierarchy';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$hierarchy_issue = false;
		if ( preg_match_all( '/<h([1-6])/', $post->post_content, $matches ) ) {
			$prev_level = 0;
			foreach ( $matches[1] as $level ) {
				$level = (int) $level;
				if ( $prev_level > 0 && $level > $prev_level + 1 ) {
					$hierarchy_issue = true;
					break;
				}
				$prev_level = $level;
			}
		}
		if ( $hierarchy_issue ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Heading hierarchy breaks logical sequence. Don\'t skip levels (e.g., h1 > h3). Use: H1 > H2 > H3 > H4 for proper structure.', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/html-verify-logical-heading-hierarchy-h1-h2-h3',
				'meta' => array(),
			);
		}
		return null;
	}
}
