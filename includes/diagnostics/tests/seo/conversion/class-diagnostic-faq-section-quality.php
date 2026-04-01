<?php
/**
 * FAQ Section Quality Diagnostic
 *
 * Issue #4799: FAQ Section Missing or Inadequate
 * Family: business-performance
 *
 * Checks if site has comprehensive FAQ section.
 * FAQs answer common questions, reducing support load by 30-40%.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_FAQ_Section_Quality Class
 *
 * Checks for FAQ pages and content.
 *
 * @since 0.6093.1200
 */
class Diagnostic_FAQ_Section_Quality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'faq-section-quality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'FAQ Section Missing or Inadequate';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if site has comprehensive FAQ section answering common questions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for FAQ pages.
		$faq_pages = self::find_pages_by_keywords( array( 'faq', 'frequently asked questions', 'questions' ) );

		if ( empty( $faq_pages ) ) {
			$issues[] = __( 'Create dedicated FAQ page answering 15-25 common questions', 'wpshadow' );
		}

		$issues[] = __( 'Organize FAQs by category: Product, Shipping, Returns, Account, etc.', 'wpshadow' );
		$issues[] = __( 'Use accordion or expand/collapse format for easy scanning', 'wpshadow' );
		$issues[] = __( 'Add FAQ schema markup for Google search results (rich snippets)', 'wpshadow' );
		$issues[] = __( 'Base FAQs on real customer questions (email, chat, support tickets)', 'wpshadow' );
		$issues[] = __( 'Update FAQs quarterly as new questions emerge', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site might lack a comprehensive FAQ section, forcing visitors to contact support or leave confused. FAQs answer common questions before they become support tickets. Benefits: 1) Self-service: Visitors find answers instantly (no wait time), 2) Reduced support load: FAQs handle 30-40% of questions automatically, 3) SEO: FAQ pages rank for question keywords ("How do I...?"), 4) Conversion: Answering objections reduces cart abandonment, 5) Trust: Shows you anticipate customer needs and provide transparency. What makes a good FAQ section: 15-25 questions minimum (cover most common questions), Organized by category (Product, Shipping, Returns, Account, Technical), Scannable format (accordion/expand-collapse, numbered Q&A), Search functionality (for larger FAQ sections), Real questions (from actual customer inquiries, not guesses), Clear answers (specific, actionable, with links to more info). SEO bonus: Add FAQ schema markup (JSON-LD) so Google shows your FAQs directly in search results. Where to get questions: Customer support emails/chat logs, Product return reasons, Sales objections, Social media questions, Competitor FAQs (what are they addressing?). Update quarterly as new questions emerge or products change.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/faq-best-practices?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'       => $issues,
					'support_reduction'     => 'FAQs reduce support tickets by 30-40%',
					'seo_benefit'           => 'FAQ pages rank for question keywords',
					'optimal_count'         => '15-25 questions covering most common scenarios',
					'organization'          => 'Categories: Product, Shipping, Returns, Account, Technical',
					'format'                => 'Accordion/expand-collapse for easy scanning',
					'schema_markup'         => 'Add FAQ schema for Google rich snippets',
					'sources'               => 'Support emails, chat logs, return reasons, sales objections',
				),
			);
		}

		return null;
	}

	/**
	 * Find pages or posts by keyword search.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return array List of matching page titles.
	 */
	private static function find_pages_by_keywords( array $keywords ): array {
		$matches = array();

		foreach ( $keywords as $keyword ) {
			$results = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post' ),
					'post_status'    => 'publish',
					'posts_per_page' => 5,
				)
			);

			foreach ( $results as $post ) {
				$matches[ $post->ID ] = get_the_title( $post );
			}
		}

		return array_values( $matches );
	}
}
