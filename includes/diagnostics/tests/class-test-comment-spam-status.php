<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Comment Spam Status
 *
 * Monitors spam comments and identifies moderation queue buildup.
 * Excessive unmoderated comments can indicate bot attacks or poor filtering.
 *
 * @since 1.2.0
 */
class Test_Comment_Spam_Status extends Diagnostic_Base {


	/**
	 * Check comment spam status
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$spam_status = self::analyze_comment_spam();

		if ( $spam_status['threat_level'] === 0 ) {
			return null;
		}

		return array(
			'threat_level'  => $spam_status['threat_level'],
			'threat_color'  => 'yellow',
			'passed'        => false,
			'issue'         => $spam_status['issue'],
			'metadata'      => $spam_status,
			'kb_link'       => 'https://wpshadow.com/kb/wordpress-comment-moderation/',
			'training_link' => 'https://wpshadow.com/training/wordpress-spam-management/',
		);
	}

	/**
	 * Guardian Sub-Test: Pending comments
	 *
	 * @return array Test result
	 */
	public static function test_pending_comments(): array {
		$pending_count = wp_count_comments()->moderated ?? 0;

		$status = 'normal';
		if ( $pending_count > 100 ) {
			$status = 'high';
		} elseif ( $pending_count > 20 ) {
			$status = 'moderate';
		}

		return array(
			'test_name'     => 'Pending Comments',
			'pending_count' => $pending_count,
			'status'        => $status,
			'passed'        => $status === 'normal',
			'description'   => sprintf( '%d comments awaiting moderation', $pending_count ),
		);
	}

	/**
	 * Guardian Sub-Test: Spam comments
	 *
	 * @return array Test result
	 */
	public static function test_spam_comments(): array {
		$spam_count = wp_count_comments()->spam ?? 0;

		$status = 'normal';
		if ( $spam_count > 1000 ) {
			$status = 'critical';
		} elseif ( $spam_count > 500 ) {
			$status = 'high';
		} elseif ( $spam_count > 100 ) {
			$status = 'moderate';
		}

		return array(
			'test_name'   => 'Spam Comments',
			'spam_count'  => $spam_count,
			'status'      => $status,
			'passed'      => $status === 'normal',
			'description' => sprintf( '%d spam comments in trash', $spam_count ),
		);
	}

	/**
	 * Guardian Sub-Test: Spam filtering plugin
	 *
	 * @return array Test result
	 */
	public static function test_spam_filtering_plugin(): array {
		$active_plugins = get_plugins();

		$spam_plugins = array(
			'akismet/akismet.php'             => 'Akismet',
			'antispam-bee/antispam-bee.php'   => 'Antispam Bee',
			'wp-spamshield/wp-spamshield.php' => 'WP Spamshield',
		);

		$active_filter = null;
		foreach ( $spam_plugins as $plugin_file => $plugin_name ) {
			if ( isset( $active_plugins[ $plugin_file ] ) ) {
				$active_filter = $plugin_name;
				break;
			}
		}

		return array(
			'test_name'     => 'Spam Filtering Plugin',
			'active_filter' => $active_filter,
			'passed'        => $active_filter !== null,
			'description'   => $active_filter ?? 'No spam filtering plugin active',
		);
	}

	/**
	 * Guardian Sub-Test: Comment settings
	 *
	 * @return array Test result
	 */
	public static function test_comment_settings(): array {
		$comments_enabled   = get_option( 'default_comment_status' );
		$comment_moderation = get_option( 'comment_moderation' );
		$require_name_email = get_option( 'require_name_email' );

		$issues = array();

		if ( $comments_enabled !== 'open' ) {
			$issues[] = 'Comments not enabled by default';
		}

		if ( ! $comment_moderation ) {
			$issues[] = 'Comment moderation not required';
		}

		if ( ! $require_name_email ) {
			$issues[] = 'Name and email not required';
		}

		return array(
			'test_name'        => 'Comment Settings',
			'comments_enabled' => $comments_enabled,
			'moderation_on'    => $comment_moderation,
			'require_fields'   => $require_name_email,
			'issues'           => $issues,
			'passed'           => empty( $issues ),
			'description'      => empty( $issues ) ? 'Comment settings are secure' : sprintf( '%d settings concerns', count( $issues ) ),
		);
	}

	/**
	 * Analyze comment spam
	 *
	 * @return array Spam analysis
	 */
	private static function analyze_comment_spam(): array {
		$comment_counts = wp_count_comments();

		$threat_level = 0;
		$issues       = array();

		// Check pending comments
		$pending_count = $comment_counts->moderated ?? 0;
		if ( $pending_count > 100 ) {
			$issues[]     = sprintf( '%d pending comments', $pending_count );
			$threat_level = max( $threat_level, 20 );
		}

		// Check spam count
		$spam_count = $comment_counts->spam ?? 0;
		if ( $spam_count > 1000 ) {
			$issues[]     = sprintf( '%d spam comments', $spam_count );
			$threat_level = max( $threat_level, 30 );
		}

		// Check for spam filter
		$active_plugins = get_plugins();
		$spam_plugins   = array(
			'akismet/akismet.php',
			'antispam-bee/antispam-bee.php',
			'wp-spamshield/wp-spamshield.php',
		);

		$has_filter = false;
		foreach ( $spam_plugins as $plugin_file ) {
			if ( isset( $active_plugins[ $plugin_file ] ) ) {
				$has_filter = true;
				break;
			}
		}

		if ( ! $has_filter && $spam_count > 50 ) {
			$issues[]     = 'No spam filtering plugin active';
			$threat_level = max( $threat_level, 25 );
		}

		$issue = ! empty( $issues ) ? implode( '; ', $issues ) : 'Comment spam is under control';

		return array(
			'threat_level'  => $threat_level,
			'issue'         => $issue,
			'pending_count' => $pending_count,
			'spam_count'    => $spam_count,
			'has_filter'    => $has_filter,
		);
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Comment Spam Status';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Monitors spam comments and identifies moderation queue buildup';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Maintenance';
	}
}
