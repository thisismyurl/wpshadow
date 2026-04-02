<?php
/**
 * About Page Personal Connection Diagnostic
 *
 * Issue #4812: About Page Lacks Personal Connection
 *
 * Detects when About pages lack personal narrative and human connection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Conversion
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Conversion;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_About_Page_Personal_Connection Class
 *
 * Checks if the About page tells a personal story and builds human connection.
 *
 * @since 1.6093.1200
 */
class Diagnostic_About_Page_Personal_Connection extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'about-page-personal-connection';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'About Page Lacks Personal Connection';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if About page tells a personal story and builds human connection';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Find About page.
		$about_pages = self::find_pages_by_keywords( array( 'about', 'about us', 'our story', 'team' ) );

		if ( empty( $about_pages ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Your site has no About page. This is like introducing yourself without telling your story. An About page is the #1 most-visited page on websites (after the homepage). Visitors want to know: Who are you? Why should they trust you? What\'s your story? Without an About page, you miss the opportunity to build human connection and convert visitors into customers.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/about-page-personal-connection',
				'details'       => array(
					'impact'             => __( 'Missing About page reduces trust and conversion rates. Studies show visitors spend 30+ seconds on About pages before deciding to buy. The About page is your #1 trust-building tool.', 'wpshadow' ),
					'recommendations'    => array(
						__( '1. Create an About page that tells your personal story (not just company facts)', 'wpshadow' ),
						__( '2. Start with "Why" - explain why you started this business (emotional connection)', 'wpshadow' ),
						__( '3. Include a professional photo of you/team members (humanizes your business)', 'wpshadow' ),
						__( '4. Mention credentials and experience that build credibility (awards, certifications, years in business)', 'wpshadow' ),
						__( '5. Tell the origin story (how did you start? What problem were you solving for yourself?)', 'wpshadow' ),
						__( '6. Mention challenges you\'ve overcome (builds relatability)', 'wpshadow' ),
						__( '7. Explain what makes you different from competitors (unique value proposition)', 'wpshadow' ),
						__( '8. Include customer success stories/testimonials (proof of impact)', 'wpshadow' ),
						__( '9. End with a clear call-to-action (Buy now, Schedule consultation, Learn more)', 'wpshadow' ),
					),
					'commandments' => array(
						__( '✓ WPSHADOW-1: Every page is designed for human connection, not just information', 'wpshadow' ),
						__( '✓ WPSHADOW-5: Built on trust through transparency and authentic storytelling', 'wpshadow' ),
						__( '✓ WPSHADOW-7: Ridiculously good experience that makes humans feel understood', 'wpshadow' ),
					),
					'examples'           => array(
						'bad_example'  => __( 'About page: "ABC Company was founded in 2015. We provide digital marketing services."', 'wpshadow' ),
						'good_example' => __( 'About page: "I started XYZ after spending 10 years frustrated with overpriced marketing agencies. I wanted to give small businesses like the coffee shop down the street access to the same strategies big corporations use. Today, I\'ve helped 200+ local businesses reach more customers. Here\'s my story..."', 'wpshadow' ),
					),
					'why_it_matters'     => __( 'People buy from people, not companies. Studies show personal About pages increase trust by 43% and conversion rates by 30%. The About page is where you prove you\'re not just another faceless company—you\'re a real person solving real problems.', 'wpshadow' ),
				),
			);
		}

		// Check About page content for personal connection elements.
		$about_post = reset( $about_pages );
		$content    = wp_strip_all_tags( $about_post->post_content );

		$personal_elements = array(
			'has_personal_pronouns' => (bool) preg_match( '/\b(I|we|my|our|me|us)\b/i', $content ),
			'has_story'             => (bool) preg_match( '/(started|founded|began|created|built|launched|journey|story|began with|started because|why I|why we)/i', $content ),
			'has_challenges'        => (bool) preg_match( '/(struggled|faced|overcome|challenge|difficult|overcame|problem|issue)/i', $content ),
			'has_emotion'           => (bool) preg_match( '/(passionate|love|believe|proud|grateful|mission|purpose|purpose-driven)/i', $content ),
			'has_experience'        => (bool) preg_match( '/(year|experience|certified|award|expertise|expert|specialist|degree|training)/i', $content ),
		);

		$connection_score = array_sum( $personal_elements ) / count( $personal_elements ) * 100;

		if ( $connection_score < 60 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Your About page reads like a corporate brochure instead of telling your personal story. Score: ' . round( $connection_score ) . '%%. Without personal narrative, visitors can\'t connect with you as a human and you miss the trust-building opportunity. Add your "Why" story, credentials, challenges you\'ve overcome, and what makes you different.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/about-page-personal-connection',
				'details'       => array(
					'connection_score'   => round( $connection_score ) . '%',
					'missing_elements'   => array_keys( array_filter( $personal_elements, fn( $v ) => ! $v ) ),
					'impact'             => __( 'About pages scoring below 60% for personal connection have 40% lower conversion rates. Visitors leave because they don\'t feel they know or trust you.', 'wpshadow' ),
					'recommendations'    => array(
						__( 'Rewrite About page to tell your personal story (use "I" and "we" pronouns)', 'wpshadow' ),
						__( 'Add your "Why" story - why did you start this? What problem were you solving?', 'wpshadow' ),
						__( 'Include professional photos of you and team members', 'wpshadow' ),
						__( 'Mention specific credentials, awards, experience (80-100 words)', 'wpshadow' ),
						__( 'Share challenges you\'ve overcome (makes you relatable)', 'wpshadow' ),
						__( 'Include customer success stories showing your impact', 'wpshadow' ),
						__( 'Avoid corporate speak - write like you\'re talking to a friend', 'wpshadow' ),
						__( 'Use emotional language (passionate, believe, love, grateful)', 'wpshadow' ),
					),
					'commandments' => array(
						__( '✓ WPSHADOW-1: Human connection over corporate polish', 'wpshadow' ),
						__( '✓ WPSHADOW-5: Transparency builds trust', 'wpshadow' ),
					),
					'examples'    => array(
						'weak'  => __( 'John Smith is CEO of XYZ Corp. He has 20 years experience.', 'wpshadow' ),
						'strong' => __( 'I\'m John, and honestly, I started XYZ Corp out of frustration. For years, I watched small business owners get ripped off by overpriced consultants. So I created XYZ to give them access to enterprise-level strategy at a fair price. It\'s been rewarding to help 2,000+ businesses grow.', 'wpshadow' ),
					),
				),
			);
		}

		return null; // About page has good personal connection.
	}
}
