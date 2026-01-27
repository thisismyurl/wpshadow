<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Title_Tag_Exists_Test extends Diagnostic_Base {
	protected static $slug = 'html-title-tag-exists-test';
	protected static $title = 'Title Tag Presence Test';
	protected static $description = 'Tests title tag existence';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		if ( empty( $post->post_title ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Page has no title. Add a title to this page in the editor.', 'wpshadow' ),
				'severity' => 'critical',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/html-title-tag-exists-test',
				'meta' => array(),
			);
		}
		return null;
	}
}
