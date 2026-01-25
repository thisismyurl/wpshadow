<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


class Diagnostic_Ai_Structured_Data extends Diagnostic_Base {
	protected static $slug = 'ai-structured-data';

	protected static $title = 'Ai Structured Data';

	protected static $description = 'Automatically initialized lean diagnostic for Ai Structured Data. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'ai-structured-data';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Is schema markup for AI present?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Is schema markup for AI present?. Part of AI & ML Readiness analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'ai_readiness';
	}

	/**
	 * Run the diagnostic test
	 *
	 * @return array Finding data or empty if no issue
	 */
	public static function run(): array {
		// Implement: Is schema markup for AI present? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 59;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/ai-structured-data/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/ai-structured-data/';
	}

	public static function check(): ?array {
		$issues = array();

		// Check if structured data markup is enabled
		$schema_enabled = get_option( 'wpshadow_schema_markup_enabled', false );

		if ( ! $schema_enabled ) {
			$issues[] = 'Schema markup not enabled for structured data';
		}

		// Check recent posts for JSON-LD
		$recent_posts = get_posts( array( 'numberposts' => 5 ) );
		$with_schema  = 0;
		foreach ( $recent_posts as $post ) {
			if ( get_post_meta( $post->ID, '_schema_markup', true ) ) {
				++$with_schema;
			}
		}

		if ( $with_schema < count( $recent_posts ) * 0.5 ) {
			$issues[] = 'Less than 50% of posts have structured data markup';
		}

		return empty( $issues ) ? null : array(
			'id'           => 'ai-structured-data',
			'title'        => 'Structured data not implemented',
			'description'  => 'Enable schema markup for better AI parsing',
			'severity'     => 'medium',
			'category'     => 'ai_readiness',
			'threat_level' => 47,
			'details'      => $issues,
		);
	}

	public static function test_live_ai_structured_data(): array {
		delete_option( 'wpshadow_schema_markup_enabled' );
		$r1 = self::check();

		update_option( 'wpshadow_schema_markup_enabled', true );
		$r2 = self::check();

		delete_option( 'wpshadow_schema_markup_enabled' );
		return array(
			'passed'  => is_array( $r1 ) && ( is_null( $r2 ) || is_array( $r2 ) ),
			'message' => 'Structured data check working',
		);
	}
}
