<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Paste_Cleanup extends Diagnostic_Base {

	protected static $slug        = 'paste-cleanup';
	protected static $title       = 'Pasted Content Cleanup';
	protected static $description = 'Detects inline styles and formatting issues from copied content (Word, Google Docs).';

	public static function check(): ?array {
		if ( get_option( 'wpshadow_paste_cleanup_enabled', false ) ) {
			return null;
		}

		global $wpdb;
		$posts_with_inline_styles = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND (post_content LIKE '%style=%' OR post_content LIKE '%font-family%')
			AND post_type IN ('post', 'page')"
		);

		if ( ! $posts_with_inline_styles ) {
			return null;
		}

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'Found %d posts with inline styles from pasted content. Enable paste cleanup to automatically remove formatting from Word and Google Docs.', 'wpshadow' ),
				$posts_with_inline_styles
			),
			'category'     => 'content',
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

}