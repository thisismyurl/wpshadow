<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_HTML_Cleanup extends Diagnostic_Base {

	protected static $slug = 'html-cleanup';
	protected static $title = 'HTML Minification';
	protected static $description = 'Checks for opportunities to minify HTML by removing whitespace and comments.';

	public static function check(): ?array {
		if ( get_option( 'wpshadow_html_cleanup_enabled', false ) ) {
			return null;
		}

		ob_start();
		do_action( 'wp_head' );
		$head_content = ob_get_clean();

		$comment_count = substr_count( $head_content, '<!--' );
		$estimated_savings = strlen( $head_content ) * 0.15;

		if ( $comment_count < 5 && $estimated_savings < 1000 ) {
			return null;
		}

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'HTML minification could reduce page size by approximately %s. Found %d HTML comments and excess whitespace.', 'wpshadow' ),
				size_format( $estimated_savings ),
				$comment_count
			),
			'category'     => 'performance',
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
		);
	}
}
