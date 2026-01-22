<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Is schema markup for AI present?
 *
 * Category: AI & ML Readiness
 * Priority: 3
 * Philosophy: 7
 *
 * Test Description:
 * Is schema markup for AI present?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 */
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
		// Check for schema.org structured data plugins/functionality
		$has_schema = false;
		
		// Check for common schema markup plugins
		$schema_plugins = array(
			'schema/schema.php',                    // Schema plugin
			'wp-seopress/seopress.php',            // SEOPress (has schema)
			'wordpress-seo/wp-seo.php',            // Yoast SEO (has schema)
			'all-in-one-seo-pack/all_in_one_seo_pack.php', // AIOSEO (has schema)
			'schema-app-structured-data-for-schemaorg/schema-app.php',
		);
		
		$active_plugins = get_option( 'active_plugins', array() );
		
		foreach ( $schema_plugins as $plugin ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$has_schema = true;
				break;
			}
		}
		
		// Check if theme supports schema via JSON-LD (look for common filters/actions)
		if ( ! $has_schema && has_action( 'wp_head', 'wp_print_schema_org' ) ) {
			$has_schema = true;
		}
		
		// If no schema detected, return a finding
		if ( ! $has_schema ) {
			return array(
				'id'            => 'ai-structured-data',
				'title'         => 'Missing AI-Ready Structured Data',
				'description'   => 'No schema.org structured data markup detected. Adding structured data helps AI systems and search engines better understand your content.',
				'category'      => 'ai_readiness',
				'severity'      => 'medium',
				'threat_level'  => 59,
				'kb_link'       => 'https://wpshadow.com/kb/ai-structured-data/',
				'training_link' => 'https://wpshadow.com/training/ai-structured-data/',
				'auto_fixable'  => false,
			);
		}
		
		return array();
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
		// Reuse the run() method logic for check()
		$result = self::run();
		
		// If no issues found (empty array), return null
		if ( empty( $result ) ) {
			return null;
		}
		
		// Otherwise, return the finding
		return $result;
	}
}
