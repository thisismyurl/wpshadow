<?php
/**
 * CPT Block Patterns
 *
 * Provides pre-built block patterns for all custom post types,
 * allowing users to insert professional layouts with one click.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since      1.6181.2359
 */

declare(strict_types=1);

namespace WPShadow\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT_Block_Patterns Class
 *
 * Registers block patterns for testimonials, team members, portfolio items,
 * events, resources, case studies, services, locations, and documentation.
 *
 * @since 1.6181.2359
 */
class CPT_Block_Patterns {

	/**
	 * Initialize the block patterns.
	 *
	 * @since 1.6034.1200
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_pattern_category' ) );
		add_action( 'init', array( __CLASS__, 'register_patterns' ) );
	}

	/**
	 * Register the WPShadow pattern category.
	 *
	 * @since 1.6034.1200
	 * @return void
	 */
	public static function register_pattern_category() {
		register_block_pattern_category(
			'wpshadow-content',
			array(
				'label'       => __( 'WPShadow Content', 'wpshadow' ),
				'description' => __( 'Pre-built layouts for custom post types', 'wpshadow' ),
			)
		);
	}

	/**
	 * Register all block patterns.
	 *
	 * Only registers patterns for CPTs that are currently active.
	 *
	 * @since 1.6034.1200
	 * @return void
	 */
	public static function register_patterns() {
		// Testimonials patterns.
		if ( post_type_exists( 'testimonial' ) ) {
			self::register_testimonial_patterns();
		}

		// Team member patterns.
		if ( post_type_exists( 'team_member' ) ) {
			self::register_team_member_patterns();
		}

		// Portfolio patterns.
		if ( post_type_exists( 'portfolio_item' ) ) {
			self::register_portfolio_patterns();
		}

		// Event patterns.
		if ( post_type_exists( 'wps_event' ) ) {
			self::register_event_patterns();
		}

		// Resource patterns.
		if ( post_type_exists( 'resource' ) ) {
			self::register_resource_patterns();
		}

		// Case study patterns.
		if ( post_type_exists( 'case_study' ) ) {
			self::register_case_study_patterns();
		}

		// Service patterns.
		if ( post_type_exists( 'service' ) ) {
			self::register_service_patterns();
		}

		// Location patterns.
		if ( post_type_exists( 'location' ) ) {
			self::register_location_patterns();
		}

		// Documentation patterns.
		if ( post_type_exists( 'documentation' ) ) {
			self::register_documentation_patterns();
		}
	}

	/**
	 * Register testimonial block patterns.
	 *
	 * @since 1.6034.1200
	 * @return void
	 */
	private static function register_testimonial_patterns() {
		// Pattern: Testimonial Grid - 3 Column.
		register_block_pattern(
			'wpshadow/testimonials-grid-3-col',
			array(
				'title'       => __( 'Testimonials Grid - 3 Column', 'wpshadow' ),
				'description' => __( 'Display testimonials in a 3-column grid layout', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'What Our Clients Say', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Hear from businesses that trust us', 'wpshadow' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:wpshadow/testimonials {"columns":3,"showExcerpt":true,"showRating":true} /-->',
			)
		);

		// Pattern: Testimonials with Stats.
		register_block_pattern(
			'wpshadow/testimonials-with-stats',
			array(
				'title'       => __( 'Testimonials with Success Stats', 'wpshadow' ),
				'description' => __( 'Testimonials section with impressive statistics', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"4rem","bottom":"4rem"}}},"backgroundColor":"light-gray"} -->
<div class="wp-block-group has-light-gray-background-color has-background" style="padding-top:4rem;padding-bottom:4rem">

<!-- wp:wpshadow/stats-counter /-->

<!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="has-text-align-center">' . esc_html__( 'Trusted by Industry Leaders', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/testimonials {"columns":3,"showExcerpt":true,"showRating":true} /-->

<!-- wp:wpshadow/logo-grid /-->

</div>
<!-- /wp:group -->',
			)
		);

		// Pattern: Testimonials with FAQ.
		register_block_pattern(
			'wpshadow/testimonials-with-faq',
			array(
				'title'       => __( 'Testimonials + FAQ Section', 'wpshadow' ),
				'description' => __( 'Build trust with testimonials and answer common questions', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":2} -->
<h2>' . esc_html__( 'What Clients Say', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->
<!-- wp:wpshadow/testimonials {"columns":1,"showExcerpt":true,"showRating":true} /-->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":2} -->
<h2>' . esc_html__( 'Common Questions', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->
<!-- wp:wpshadow/faq-accordion /-->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->',
			)
		);

		// Pattern: Testimonial Slider.
		register_block_pattern(
			'wpshadow/testimonials-slider',
			array(
				'title'       => __( 'Testimonials Slider', 'wpshadow' ),
				'description' => __( 'Single testimonial slider with navigation', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"4rem","bottom":"4rem"}}},"backgroundColor":"light-gray"} -->
<div class="wp-block-group has-light-gray-background-color has-background" style="padding-top:4rem;padding-bottom:4rem">
<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Customer Success Stories', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/testimonials {"postsPerPage":1,"showRating":true,"className":"is-style-quote-bubble"} /-->
</div>
<!-- /wp:group -->',
			)
		);

		// Pattern: Featured Testimonial.
		register_block_pattern(
			'wpshadow/testimonial-featured',
			array(
				'title'       => __( 'Featured Testimonial', 'wpshadow' ),
				'description' => __( 'Large featured testimonial with company logo', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column {"width":"40%"} -->
<div class="wp-block-column" style="flex-basis:40%">
<!-- wp:heading -->
<h2>' . esc_html__( 'Trusted by Industry Leaders', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Join thousands of satisfied customers who have transformed their business with our solutions.', 'wpshadow' ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"60%"} -->
<div class="wp-block-column" style="flex-basis:60%">
<!-- wp:wpshadow/testimonials {"postsPerPage":1,"showRating":true,"showExcerpt":true,"className":"is-style-minimal"} /-->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->',
			)
		);
	}

	/**
	 * Register team member block patterns.
	 *
	 * @since 1.6034.1200
	 * @return void
	 */
	private static function register_team_member_patterns() {
		// Pattern: Team Grid - 4 Column.
		register_block_pattern(
			'wpshadow/team-grid-4-col',
			array(
				'title'       => __( 'Team Grid - 4 Column', 'wpshadow' ),
				'description' => __( 'Display team members in a 4-column grid', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Meet Our Team', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'The talented people behind our success', 'wpshadow' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:wpshadow/team-members {"columns":4,"showExcerpt":false} /-->',
			)
		);

		// Pattern: Leadership Team.
		register_block_pattern(
			'wpshadow/team-leadership',
			array(
				'title'       => __( 'Leadership Team', 'wpshadow' ),
				'description' => __( 'Showcase leadership team with large profiles', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Leadership Team', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/team-members {"columns":3,"showExcerpt":true,"className":"is-style-overlay"} /-->',
			)
		);

		// Pattern: Team with CTA.
		register_block_pattern(
			'wpshadow/team-with-cta',
			array(
				'title'       => __( 'Team Section with CTA', 'wpshadow' ),
				'description' => __( 'Team members followed by join our team call-to-action', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"5rem","bottom":"5rem"}}},"backgroundColor":"light-gray"} -->
<div class="wp-block-group has-light-gray-background-color has-background" style="padding-top:5rem;padding-bottom:5rem">
<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Our Amazing Team', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/team-members {"columns":4,"postsPerPage":4} /-->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"3rem"}}}} -->
<div class="wp-block-buttons" style="margin-top:3rem">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link">' . esc_html__( 'Join Our Team', 'wpshadow' ) . '</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->',
			)
		);
	}

	/**
	 * Register portfolio block patterns.
	 *
	 * @since 1.6034.1200
	 * @return void
	 */
	private static function register_portfolio_patterns() {
		// Pattern: Portfolio Masonry.
		register_block_pattern(
			'wpshadow/portfolio-masonry',
			array(
				'title'       => __( 'Portfolio Masonry Grid', 'wpshadow' ),
				'description' => __( 'Display portfolio items in a masonry layout', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Our Work', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Explore our latest projects and success stories', 'wpshadow' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:wpshadow/portfolio {"columns":3,"className":"is-style-masonry"} /-->',
			)
		);

		// Pattern: Portfolio with Skills.
		register_block_pattern(
			'wpshadow/portfolio-with-skills',
			array(
				'title'       => __( 'Portfolio with Team Skills', 'wpshadow' ),
				'description' => __( 'Showcase work alongside team expertise', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"5rem","bottom":"5rem"}}}} -->
<div class="wp-block-group" style="padding-top:5rem;padding-bottom:5rem">

<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column {"width":"60%"} -->
<div class="wp-block-column" style="flex-basis:60%">
<!-- wp:heading -->
<h2>' . esc_html__( 'Recent Projects', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->
<!-- wp:wpshadow/portfolio {"columns":2,"postsPerPage":4} /-->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"40%"} -->
<div class="wp-block-column" style="flex-basis:40%">
<!-- wp:heading -->
<h2>' . esc_html__( 'Our Expertise', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->
<!-- wp:wpshadow/progress-bar {"style":"animated"} /-->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->',
			)
		);

		// Pattern: Portfolio with Before/After.
		register_block_pattern(
			'wpshadow/portfolio-transformation',
			array(
				'title'       => __( 'Portfolio Transformation Showcase', 'wpshadow' ),
				'description' => __( 'Before/after comparison with portfolio items', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"5rem","bottom":"5rem"}}},"backgroundColor":"light-gray"} -->
<div class="wp-block-group has-light-gray-background-color has-background" style="padding-top:5rem;padding-bottom:5rem">

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Project Transformations', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/before-after /-->

<!-- wp:spacer {"height":"3rem"} -->
<div style="height:3rem" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"level":3,"textAlign":"center"} -->
<h3 class="has-text-align-center">' . esc_html__( 'More Portfolio Items', 'wpshadow' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:wpshadow/portfolio {"columns":3,"postsPerPage":6} /-->

<!-- wp:wpshadow/cta {"layout":"centered"} /-->

</div>
<!-- /wp:group -->',
			)
		);

		// Pattern: Featured Project.
		register_block_pattern(
			'wpshadow/portfolio-featured',
			array(
				'title'       => __( 'Featured Project', 'wpshadow' ),
				'description' => __( 'Large featured portfolio item with details', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center">
<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%">
<!-- wp:wpshadow/portfolio {"postsPerPage":1,"showExcerpt":true,"className":"is-style-hover-zoom"} /-->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%">
<!-- wp:heading -->
<h2>' . esc_html__( 'Featured Project', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'See how we helped our client achieve remarkable results through innovative solutions and strategic implementation.', 'wpshadow' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link">' . esc_html__( 'View All Projects', 'wpshadow' ) . '</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->',
			)
		);
	}

	/**
	 * Register event block patterns.
	 *
	 * @since 1.6034.1200
	 * @return void
	 */
	private static function register_event_patterns() {
		// Pattern: Event Timeline.
		register_block_pattern(
			'wpshadow/events-timeline',
			array(
				'title'       => __( 'Events Timeline', 'wpshadow' ),
				'description' => __( 'Display upcoming events in timeline format', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Upcoming Events', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/events {"postsPerPage":5,"className":"is-style-timeline"} /-->',
			)
		);

		// Pattern: Event with Countdown.
		register_block_pattern(
			'wpshadow/event-with-countdown',
			array(
				'title'       => __( 'Featured Event with Countdown', 'wpshadow' ),
				'description' => __( 'Promote upcoming event with live countdown timer', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"5rem","bottom":"5rem"}}},"backgroundColor":"light-gray"} -->
<div class="wp-block-group has-light-gray-background-color has-background" style="padding-top:5rem;padding-bottom:5rem">

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Don\'t Miss Our Next Event', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/countdown-timer {"style":"boxes"} /-->

<!-- wp:spacer {"height":"3rem"} -->
<div style="height:3rem" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wpshadow/events {"postsPerPage":1,"showExcerpt":true} /-->

<!-- wp:wpshadow/cta {"layout":"centered"} /-->

</div>
<!-- /wp:group -->',
			)
		);

		// Pattern: Events with Tabs.
		register_block_pattern(
			'wpshadow/events-tabbed',
			array(
				'title'       => __( 'Events Organized by Tabs', 'wpshadow' ),
				'description' => __( 'Display events in tabbed sections (upcoming, featured, past)', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Our Events', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/content-tabs {"orientation":"horizontal"} /-->',
			)
		);
	}

		// Pattern: Event Calendar View.
		register_block_pattern(
			'wpshadow/events-calendar',
			array(
				'title'       => __( 'Event Calendar', 'wpshadow' ),
				'description' => __( 'Display events in calendar-style cards', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"3rem","bottom":"3rem"}}},"backgroundColor":"light-gray"} -->
<div class="wp-block-group has-light-gray-background-color has-background" style="padding-top:3rem;padding-bottom:3rem">
<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Event Calendar', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/events {"columns":3,"showExcerpt":true,"className":"is-style-calendar"} /-->
</div>
<!-- /wp:group -->',
			)
		);
	}

	/**
	 * Register resource block patterns.
	 *
	 * @since 1.6034.1200
	 * @return void
	 */
	private static function register_resource_patterns() {
		// Pattern: Resource Library.
		register_block_pattern(
			'wpshadow/resources-library',
			array(
				'title'       => __( 'Resource Library', 'wpshadow' ),
				'description' => __( 'Complete resource library with categories', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Resource Library', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Download free resources to help your business grow', 'wpshadow' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:wpshadow/resources {"columns":3,"showExcerpt":true} /-->',
			)
		);

		// Pattern: Featured Resources.
		register_block_pattern(
			'wpshadow/resources-featured',
			array(
				'title'       => __( 'Featured Resources', 'wpshadow' ),
				'description' => __( 'Highlight top resources with large cards', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:wpshadow/resources {"postsPerPage":3,"showExcerpt":true,"className":"is-style-featured"} /-->',
			)
		);
	}

	/**
	 * Register case study block patterns.
	 *
	 * @since 1.6034.1200
	 * @return void
	 */
	private static function register_case_study_patterns() {
		// Pattern: Case Studies Grid.
		register_block_pattern(
			'wpshadow/case-studies-grid',
			array(
				'title'       => __( 'Case Studies Grid', 'wpshadow' ),
				'description' => __( 'Display case studies in a grid layout', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Success Stories', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/case-studies {"columns":2,"showExcerpt":true,"className":"is-style-metrics-focused"} /-->',
			)
		);

		// Pattern: Case Study with Timeline.
		register_block_pattern(
			'wpshadow/case-study-with-timeline',
			array(
				'title'       => __( 'Case Study with Project Timeline', 'wpshadow' ),
				'description' => __( 'Showcase project phases and results with visual timeline', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"5rem","bottom":"5rem"}}}} -->
<div class="wp-block-group" style="padding-top:5rem;padding-bottom:5rem">

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Client Success Story', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/case-studies {"postsPerPage":1,"showExcerpt":true} /-->

<!-- wp:heading {"level":3,"textAlign":"center"} -->
<h3 class="has-text-align-center">' . esc_html__( 'Project Journey', 'wpshadow' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:wpshadow/timeline {"layout":"vertical"} /-->

<!-- wp:wpshadow/stats-counter /-->

</div>
<!-- /wp:group -->',
			)
		);

		// Pattern: Case Study with Before/After.
		register_block_pattern(
			'wpshadow/case-study-with-comparison',
			array(
				'title'       => __( 'Case Study with Before/After Comparison', 'wpshadow' ),
				'description' => __( 'Visual comparison showing transformation', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"5rem","bottom":"5rem"}}},"backgroundColor":"light-gray"} -->
<div class="wp-block-group has-light-gray-background-color has-background" style="padding-top:5rem;padding-bottom:5rem">

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'See the Transformation', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/before-after /-->

<!-- wp:spacer {"height":"3rem"} -->
<div style="height:3rem" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wpshadow/case-studies {"postsPerPage":1,"showExcerpt":true} /-->

<!-- wp:wpshadow/cta {"layout":"centered"} /-->

</div>
<!-- /wp:group -->',
			)
		);

		// Pattern: Complete Case Study Landing.
		register_block_pattern(
			'wpshadow/case-study-complete',
			array(
				'title'       => __( 'Complete Case Study Landing Page', 'wpshadow' ),
				'description' => __( 'Full case study with timeline, stats, testimonials, and CTA', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"5rem","bottom":"5rem"}}}} -->
<div class="wp-block-group" style="padding-top:5rem;padding-bottom:5rem">

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Featured Case Study', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/case-studies {"postsPerPage":1,"showExcerpt":true} /-->

<!-- wp:spacer {"height":"3rem"} -->
<div style="height:3rem" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"level":3,"textAlign":"center"} -->
<h3 class="has-text-align-center">' . esc_html__( 'The Results', 'wpshadow' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:wpshadow/stats-counter /-->

<!-- wp:spacer {"height":"3rem"} -->
<div style="height:3rem" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"level":3,"textAlign":"center"} -->
<h3 class="has-text-align-center">' . esc_html__( 'Project Timeline', 'wpshadow' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:wpshadow/timeline {"layout":"horizontal"} /-->

<!-- wp:spacer {"height":"3rem"} -->
<div style="height:3rem" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"level":3,"textAlign":"center"} -->
<h3 class="has-text-align-center">' . esc_html__( 'Visual Transformation', 'wpshadow' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:wpshadow/before-after /-->

<!-- wp:spacer {"height":"3rem"} -->
<div style="height:3rem" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"level":3,"textAlign":"center"} -->
<h3 class="has-text-align-center">' . esc_html__( 'What Our Client Says', 'wpshadow' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:wpshadow/testimonials {"postsPerPage":1,"showRating":true} /-->

<!-- wp:spacer {"height":"3rem"} -->
<div style="height:3rem" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wpshadow/logo-grid /-->

<!-- wp:wpshadow/cta {"layout":"banner"} /-->

</div>
<!-- /wp:group -->',
			)
		);
	}

	/**
	 * Register service block patterns.
	 *
	 * @since 1.6034.1200
	 * @return void
	 */
	private static function register_service_patterns() {
		// Pattern: Services Grid.
		register_block_pattern(
			'wpshadow/services-grid',
			array(
				'title'       => __( 'Services Grid', 'wpshadow' ),
				'description' => __( 'Display services in a 3-column grid', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Our Services', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/services {"columns":3,"showExcerpt":true} /-->',
			)
		);

		// Pattern: Services with Pricing.
		register_block_pattern(
			'wpshadow/services-with-pricing',
			array(
				'title'       => __( 'Services with Pricing Table', 'wpshadow' ),
				'description' => __( 'Showcase services with professional pricing presentation', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"5rem","bottom":"5rem"}}},"backgroundColor":"light-gray"} -->
<div class="wp-block-group has-light-gray-background-color has-background" style="padding-top:5rem;padding-bottom:5rem">

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Our Service Packages', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Choose the perfect plan for your business needs', 'wpshadow' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:wpshadow/pricing-table /-->

<!-- wp:wpshadow/cta {"layout":"centered"} /-->

</div>
<!-- /wp:group -->',
			)
		);

		// Pattern: Service Features with Icon Boxes.
		register_block_pattern(
			'wpshadow/services-with-icons',
			array(
				'title'       => __( 'Services with Icon Highlights', 'wpshadow' ),
				'description' => __( 'Service overview with visual icon boxes', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'What We Offer', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:wpshadow/icon-box /-->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:wpshadow/icon-box /-->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:wpshadow/icon-box /-->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:heading {"textAlign":"center","level":3} -->
<h3 class="has-text-align-center">' . esc_html__( 'Our Services in Detail', 'wpshadow' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:wpshadow/services {"columns":2,"showExcerpt":true} /-->',
			)
		);

		// Pattern: Services with FAQ.
		register_block_pattern(
			'wpshadow/services-with-faq',
			array(
				'title'       => __( 'Services Landing Page', 'wpshadow' ),
				'description' => __( 'Complete services section with pricing, FAQ, and CTA', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"5rem","bottom":"5rem"}}}} -->
<div class="wp-block-group" style="padding-top:5rem;padding-bottom:5rem">

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Professional Services, Transparent Pricing', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/pricing-table /-->

<!-- wp:spacer {"height":"4rem"} -->
<div style="height:4rem" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%">
<!-- wp:heading {"level":3} -->
<h3>' . esc_html__( 'Frequently Asked Questions', 'wpshadow' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:wpshadow/faq-accordion /-->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%">
<!-- wp:heading {"level":3} -->
<h3>' . esc_html__( 'Featured Services', 'wpshadow' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:wpshadow/services {"columns":1,"postsPerPage":3} /-->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:wpshadow/cta {"layout":"banner"} /-->

</div>
<!-- /wp:group -->',
			)
		);
	}

	/**
	 * Register location block patterns.
	 *
	 * @since 1.6034.1200
	 * @return void
	 */
	private static function register_location_patterns() {
		// Pattern: Locations List.
		register_block_pattern(
			'wpshadow/locations-list',
			array(
				'title'       => __( 'Locations List', 'wpshadow' ),
				'description' => __( 'Display locations in a list format', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Our Locations', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/locations {"showExcerpt":true} /-->',
			)
		);

		// Pattern: Location Cards.
		register_block_pattern(
			'wpshadow/locations-cards',
			array(
				'title'       => __( 'Location Cards', 'wpshadow' ),
				'description' => __( 'Display locations as cards with contact info', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Visit Us', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/locations {"columns":2,"showExcerpt":true,"className":"is-style-card"} /-->',
			)
		);
	}

	/**
	 * Register documentation block patterns.
	 *
	 * @since 1.6034.1200
	 * @return void
	 */
	private static function register_documentation_patterns() {
		// Pattern: Documentation Grid.
		register_block_pattern(
			'wpshadow/docs-grid',
			array(
				'title'       => __( 'Documentation Grid', 'wpshadow' ),
				'description' => __( 'Display documentation in a grid layout', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Documentation', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:wpshadow/documentation {"columns":3,"showExcerpt":true} /-->',
			)
		);

		// Pattern: Getting Started Guide.
		register_block_pattern(
			'wpshadow/docs-getting-started',
			array(
				'title'       => __( 'Getting Started Guide', 'wpshadow' ),
				'description' => __( 'Welcome section with documentation links', 'wpshadow' ),
				'categories'  => array( 'wpshadow-content' ),
				'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"5rem","bottom":"5rem"}}},"backgroundColor":"light-gray"} -->
<div class="wp-block-group has-light-gray-background-color has-background" style="padding-top:5rem;padding-bottom:5rem">
<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__( 'Getting Started', 'wpshadow' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Everything you need to get up and running', 'wpshadow' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:wpshadow/documentation {"postsPerPage":6,"columns":3,"className":"is-style-accordion"} /-->
</div>
<!-- /wp:group -->',
			)
		);
	}
}
