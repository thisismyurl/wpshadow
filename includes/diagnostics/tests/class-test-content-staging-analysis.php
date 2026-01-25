<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Content Staging Analysis
 *
 * Analyzes draft, scheduled, and trash content for optimization opportunities.
 * Excessive post revisions and trash slow down database performance.
 *
 * @since 1.2.0
 */
class Test_Content_Staging_Analysis extends Diagnostic_Base {


	/**
	 * Check content staging status
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$staging_data = self::analyze_content_staging();

		if ( $staging_data['threat_level'] === 0 ) {
			return null;
		}

		return array(
			'threat_level'  => $staging_data['threat_level'],
			'threat_color'  => 'yellow',
			'passed'        => false,
			'issue'         => $staging_data['issue'],
			'metadata'      => $staging_data,
			'kb_link'       => 'https://wpshadow.com/kb/wordpress-content-optimization/',
			'training_link' => 'https://wpshadow.com/training/wordpress-content-management/',
		);
	}

	/**
	 * Guardian Sub-Test: Draft posts analysis
	 *
	 * @return array Test result
	 */
	public static function test_draft_posts(): array {
		$draft_count      = wp_count_posts( 'post', 'readable' )->draft ?? 0;
		$page_draft_count = wp_count_posts( 'page', 'readable' )->draft ?? 0;

		$total_drafts = $draft_count + $page_draft_count;

		$status = 'normal';
		if ( $total_drafts > 50 ) {
			$status = 'high';
		} elseif ( $total_drafts > 20 ) {
			$status = 'moderate';
		}

		return array(
			'test_name'    => 'Draft Content',
			'draft_posts'  => $draft_count,
			'draft_pages'  => $page_draft_count,
			'total_drafts' => $total_drafts,
			'status'       => $status,
			'passed'       => $status === 'normal',
			'description'  => sprintf( '%d draft posts and pages', $total_drafts ),
		);
	}

	/**
	 * Guardian Sub-Test: Scheduled content
	 *
	 * @return array Test result
	 */
	public static function test_scheduled_content(): array {
		$scheduled_count = wp_count_posts( 'post', 'readable' )->future ?? 0;
		$scheduled_pages = wp_count_posts( 'page', 'readable' )->future ?? 0;

		$total_scheduled = $scheduled_count + $scheduled_pages;

		// Get oldest scheduled post
		$oldest = get_posts(
			array(
				'post_status' => 'future',
				'numberposts' => 1,
				'orderby'     => 'post_date',
				'order'       => 'ASC',
			)
		);

		$oldest_date = $oldest ? $oldest[0]->post_date : null;

		return array(
			'test_name'        => 'Scheduled Content',
			'scheduled_posts'  => $scheduled_count,
			'scheduled_pages'  => $scheduled_pages,
			'total_scheduled'  => $total_scheduled,
			'oldest_scheduled' => $oldest_date,
			'passed'           => $total_scheduled < 100,
			'description'      => sprintf( '%d scheduled items', $total_scheduled ),
		);
	}

	/**
	 * Guardian Sub-Test: Trash content
	 *
	 * @return array Test result
	 */
	public static function test_trash_content(): array {
		$trash_posts       = wp_count_posts( 'post', 'readable' )->trash ?? 0;
		$trash_pages       = wp_count_posts( 'page', 'readable' )->trash ?? 0;
		$trash_attachments = wp_count_posts( 'attachment', 'readable' )->trash ?? 0;

		$total_trash = $trash_posts + $trash_pages + $trash_attachments;

		$status = 'normal';
		if ( $total_trash > 100 ) {
			$status = 'high';
		} elseif ( $total_trash > 50 ) {
			$status = 'moderate';
		}

		return array(
			'test_name'         => 'Trash Content',
			'trash_posts'       => $trash_posts,
			'trash_pages'       => $trash_pages,
			'trash_attachments' => $trash_attachments,
			'total_trash'       => $total_trash,
			'status'            => $status,
			'passed'            => $status === 'normal',
			'description'       => sprintf( '%d items in trash', $total_trash ),
		);
	}

	/**
	 * Guardian Sub-Test: Post revisions
	 *
	 * @return array Test result
	 */
	public static function test_post_revisions(): array {
		global $wpdb;

		$revision_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'"
		);

		$db_size          = 0;
		$revision_results = $wpdb->get_results(
			"SELECT post_author, COUNT(*) as count FROM {$wpdb->posts} WHERE post_type = 'revision' GROUP BY post_author"
		);

		$status = 'normal';
		if ( $revision_count > 5000 ) {
			$status = 'critical';
		} elseif ( $revision_count > 2000 ) {
			$status = 'high';
		} elseif ( $revision_count > 500 ) {
			$status = 'moderate';
		}

		return array(
			'test_name'       => 'Post Revisions',
			'total_revisions' => $revision_count,
			'status'          => $status,
			'passed'          => $status === 'normal',
			'description'     => sprintf( '%d post revisions stored', $revision_count ),
		);
	}

	/**
	 * Analyze content staging
	 *
	 * @return array Staging analysis
	 */
	private static function analyze_content_staging(): array {
		$draft_count     = wp_count_posts( 'post', 'readable' )->draft ?? 0;
		$scheduled_count = wp_count_posts( 'post', 'readable' )->future ?? 0;
		$trash_count     = wp_count_posts( 'post', 'readable' )->trash ?? 0;

		global $wpdb;
		$revision_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'"
		);

		$threat_level = 0;
		$issue        = 'Content staging is optimized';

		$issues = array();

		if ( $draft_count > 50 ) {
			$issues[] = sprintf( '%d draft posts', $draft_count );
		}

		if ( $scheduled_count > 50 ) {
			$issues[] = sprintf( '%d scheduled posts', $scheduled_count );
		}

		if ( $trash_count > 100 ) {
			$issues[] = sprintf( '%d items in trash', $trash_count );
		}

		if ( $revision_count > 5000 ) {
			$issues[] = sprintf( '%d post revisions', $revision_count );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 50, count( $issues ) * 15 );
			$issue        = 'Content staging could be optimized: ' . implode( ', ', $issues );
		}

		return array(
			'threat_level' => $threat_level,
			'issue'        => $issue,
			'drafts'       => $draft_count,
			'scheduled'    => $scheduled_count,
			'trash'        => $trash_count,
			'revisions'    => $revision_count,
		);
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Content Staging Analysis';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Analyzes draft, scheduled, and trash content for optimization opportunities';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Performance';
	}
}
