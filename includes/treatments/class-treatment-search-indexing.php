<?php
declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Interface;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Search_Indexing implements Treatment_Interface {

	public static function get_finding_id(): string {
		return 'search-indexing';
	}

	public static function can_apply(): bool {
		return current_user_can( 'manage_options' );
	}

	public static function apply(): array {
		$prev_value = get_option( 'blog_public' );
		update_option( 'wpshadow_prev_blog_public', $prev_value, false );

		update_option( 'blog_public', '1' );

		KPI_Tracker::log_fix_applied( 'search-indexing', 'seo' );

		return array(
			'success' => true,
			'message' => __( 'Search engine indexing enabled. Your site is now visible to search engines like Google.', 'wpshadow' ),
		);
	}

	public static function undo(): array {
		$prev_value = get_option( 'wpshadow_prev_blog_public', '0' );
		update_option( 'blog_public', $prev_value );
		delete_option( 'wpshadow_prev_blog_public' );

		return array(
			'success' => true,
			'message' => __( 'Reverted to previous search indexing setting.', 'wpshadow' ),
		);
	}
}
