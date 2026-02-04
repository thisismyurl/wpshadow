<?php
/**
 * No Customer Education or Knowledge Base Diagnostic
 *
 * Checks if customer education resources or knowledge base exists.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Education/Knowledge Base Diagnostic
 *
 * Companies with strong knowledge bases reduce support costs by 40% and
 * increase customer satisfaction by 35%. Education drives retention.
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Customer_Education_Or_Knowledge_Base extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-customer-education-knowledge-base';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Customer Education or Knowledge Base';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customer education resources or knowledge base exists';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since  1.6035.0000
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$kb_articles = self::count_kb_content();

		if ( $kb_articles < 10 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of knowledge base articles */
					__( 'You have only %d knowledge base articles. Customers who can\'t find answers leave (85%%). Strong knowledge bases reduce support costs by 40%% and increase satisfaction by 35%%. Build: 1) Getting started guides, 2) Feature tutorials (one per feature), 3) Troubleshooting (common issues), 4) FAQs by topic, 5) Video walkthroughs, 6) Use case examples, 7) Advanced tips/best practices. Target 50+ articles covering all common questions. Self-serve education scales infinitely.', 'wpshadow' ),
					$kb_articles
				),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/customer-education-knowledge-base',
				'details'     => array(
					'issue'               => sprintf(
						/* translators: %d: number of knowledge base articles */
						__( 'Only %d knowledge base articles found (recommend 50+)', 'wpshadow' ),
						$kb_articles
					),
					'recommendation'      => __( 'Build comprehensive knowledge base with tutorials, guides, and troubleshooting', 'wpshadow' ),
					'business_impact'     => __( 'Support costs 40% higher and customer satisfaction 35% lower without proper education', 'wpshadow' ),
					'current_articles'    => $kb_articles,
					'content_types'       => self::get_content_types(),
					'article_structure'   => self::get_article_structure(),
				),
			);
		}

		return null;
	}

	/**
	 * Count knowledge base content.
	 *
	 * @since  1.6035.0000
	 * @return int Number of KB articles.
	 */
	private static function count_kb_content() {
		// Check for knowledge base post types
		$kb_post_types = array( 'post', 'page', 'docs', 'documentation', 'kb', 'knowledge-base', 'faq' );

		$kb_posts = get_posts(
			array(
				'post_type'      => $kb_post_types,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				's'              => 'how to OR tutorial OR guide OR troubleshoot OR getting started',
			)
		);

		return count( $kb_posts );
	}

	/**
	 * Get knowledge base content types.
	 *
	 * @since  1.6035.0000
	 * @return array Content types with descriptions.
	 */
	private static function get_content_types() {
		return array(
			'getting_started'  => __( 'Onboarding guides for new customers (first 7 days)', 'wpshadow' ),
			'how_to_guides'    => __( 'Step-by-step tutorials for each feature/capability', 'wpshadow' ),
			'troubleshooting'  => __( 'Solutions to common problems and error messages', 'wpshadow' ),
			'faqs'             => __( 'Frequently asked questions organized by topic', 'wpshadow' ),
			'video_tutorials'  => __( 'Screen recordings showing features in action', 'wpshadow' ),
			'use_cases'        => __( 'Real-world examples of how customers succeed', 'wpshadow' ),
			'best_practices'   => __( 'Advanced tips and optimization strategies', 'wpshadow' ),
			'integrations'     => __( 'How to connect with other tools/platforms', 'wpshadow' ),
			'glossary'         => __( 'Definitions of industry/product terminology', 'wpshadow' ),
		);
	}

	/**
	 * Get recommended article structure.
	 *
	 * @since  1.6035.0000
	 * @return array Article structure guidelines.
	 */
	private static function get_article_structure() {
		return array(
			'title'        => __( 'Clear, specific title starting with "How to..." or question', 'wpshadow' ),
			'summary'      => __( 'One-paragraph summary (what you\'ll learn, time required)', 'wpshadow' ),
			'toc'          => __( 'Table of contents for articles over 500 words', 'wpshadow' ),
			'steps'        => __( 'Numbered steps with screenshots/videos', 'wpshadow' ),
			'examples'     => __( 'Real examples showing the concept in action', 'wpshadow' ),
			'tips'         => __( 'Pro tips, shortcuts, or common mistakes to avoid', 'wpshadow' ),
			'related'      => __( 'Links to related articles for deeper learning', 'wpshadow' ),
			'feedback'     => __( 'Was this helpful? (thumbs up/down for improvement)', 'wpshadow' ),
			'last_updated' => __( 'Last updated date to show content freshness', 'wpshadow' ),
		);
	}
}
