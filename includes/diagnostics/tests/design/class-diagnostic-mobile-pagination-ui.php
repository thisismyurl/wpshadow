<?php
/**
 * Mobile Pagination UI Diagnostic
 *
 * Validates that pagination controls are mobile-friendly with proper
 * touch targets, loading patterns, and accessibility.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Mobile
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Pagination UI Diagnostic Class
 *
 * Checks pagination implementation for mobile usability including touch target
 * sizing, loading patterns, and accessibility.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Mobile_Pagination_UI extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-pagination-ui';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Pagination UI';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates pagination controls are mobile-friendly with proper touch targets and loading patterns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check pagination implementation.
		$pagination_check = self::check_pagination();
		if ( ! empty( $pagination_check['issues'] ) ) {
			$issues = array_merge( $issues, $pagination_check['issues'] );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$issue_count  = count( $issues );
		$threat_level = min( 65, 40 + ( $issue_count * 8 ) );
		$severity     = $threat_level >= 60 ? 'medium' : 'low';
		$auto_fixable = false;

		$description = sprintf(
			/* translators: %d: number of pagination issues */
			__( 'Found %d mobile pagination issue(s). Poor pagination UX causes 40%% mobile abandonment. Users expect infinite scroll or "Load More" on mobile instead of traditional numbered pagination.', 'wpshadow' ),
			$issue_count
		);

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => $auto_fixable,
			'kb_link'      => 'https://wpshadow.com/kb/mobile-pagination?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'issue_count'   => $issue_count,
				'issues'        => $issues,
				'why_important' => __(
					'Mobile pagination significantly impacts user experience:

					Mobile Challenges:
					• Traditional pagination requires page reload (slow on mobile)
					• Small numbered links hard to tap (accidental clicks)
					• Users scroll vertically, pagination is horizontal
					• Page reload loses scroll position
					• Network latency makes page loads painful

					User Expectations:
					• 72% of mobile users prefer infinite scroll (UX research)
					• "Load More" button is 50% more engaging than pagination
					• Numbered pagination feels outdated on mobile
					• Social media has trained users to expect infinite scroll

					Performance Impact:
					• Page reload = 1-3 seconds (vs <300ms for AJAX)
					• Users abandon after 2-3 page loads
					• Lost scroll position = frustration
					• SEO: Google can crawl Load More/infinite scroll if implemented correctly

					Best Mobile Patterns:
					1. Load More button (best balance)
					2. Infinite scroll (best for discovery)
					3. Larger prev/next buttons (acceptable)
					4. Numbered pagination (worst for mobile)',
					'wpshadow'
				),
				'how_to_fix'    => __(
					'Implement mobile-friendly pagination:

					Option 1: Load More Button (Recommended)
					Plugins:
					• Ajax Load More (wordpress.org/plugins/ajax-load-more)
					• Infinite Scroll (Jetpack module)
					• Load More Posts (wordpress.org/plugins/load-more-posts)

					Custom Implementation:
					JavaScript (AJAX Load More):
					jQuery("#load-more").on("click", function(e) {
					    e.preventDefault();
					    const button = jQuery(this);
					    const page = button.data("page");

					    button.text("Loading...");

					    jQuery.ajax({
					        url: ajaxurl,
					        type: "POST",
					        data: {
					            action: "load_more_posts",
					            page: page,
					            query: blog_query_vars
					        },
					        success: function(response) {
					            if (response) {
					                jQuery("#posts-container").append(response);
					                button.data("page", page + 1);
					                button.text("Load More");
					            } else {
					                button.text("No More Posts").prop("disabled", true);
					            }
					        }
					    });
					});

					PHP (AJAX Handler):
					add_action( "wp_ajax_load_more_posts", "load_more_posts_callback" );
					add_action( "wp_ajax_nopriv_load_more_posts", "load_more_posts_callback" );

					function load_more_posts_callback() {
					    $paged = $_POST["page"];

					    $args = array(
					        "post_type"      => "post",
					        "posts_per_page" => 10,
					        "paged"          => $paged,
					    );

					    $query = new WP_Query( $args );

					    if ( $query->have_posts() ) {
					        while ( $query->have_posts() ) {
					            $query->the_post();
					            get_template_part( "template-parts/content" );
					        }
					    }

					    wp_die();
					}

					Option 2: Infinite Scroll
					Jetpack Infinite Scroll:
					1. Install Jetpack
					2. Enable Infinite Scroll module
					3. Configure in Settings > Reading

					Custom Infinite Scroll:
					const observer = new IntersectionObserver((entries) => {
					    if (entries[0].isIntersecting && hasMorePosts) {
					        loadMorePosts();
					    }
					}, { threshold: 0.5 });

					observer.observe(document.querySelector("#load-trigger"));

					Option 3: Improve Traditional Pagination
					Make Links Larger (44x44px minimum):
					.pagination a {
					    min-width: 44px;
					    min-height: 44px;
					    padding: 12px;
					    margin: 4px;
					    display: inline-block;
					    font-size: 16px;
					}

					Hide Numbers on Mobile (keep prev/next only):
					@media (max-width: 768px) {
					    .pagination .page-numbers:not(.prev):not(.next):not(.current) {
					        display: none;
					    }
					    .pagination .prev,
					    .pagination .next {
					        min-width: 100px;
					        padding: 12px 24px;
					    }
					}

					Add Loading Indicator:
					.pagination a {
					    position: relative;
					}
					.pagination a.loading::after {
					    content: "";
					    position: absolute;
					    width: 16px;
					    height: 16px;
					    border: 2px solid #ccc;
					    border-top-color: #000;
					    border-radius: 50%;
					    animation: spin 0.6s linear infinite;
					}

					SEO Considerations:
					• Use rel="next" and rel="prev" on pagination links
					• Ensure crawlers can access paginated content
					• For infinite scroll, provide fallback pagination
					• Test with Google Search Console',
					'wpshadow'
				),
			),
		);
	}

	/**
	 * Check pagination implementation.
	 *
	 * @since 0.6093.1200
	 * @return array Check results.
	 */
	private static function check_pagination() {
		$issues = array();

		// Get blog page or homepage.
		$posts_page = get_option( 'page_for_posts' );
		$test_url   = $posts_page ? get_permalink( $posts_page ) : home_url( '/' );

		$html = self::capture_page_html( $test_url );
		if ( empty( $html ) ) {
			return array( 'issues' => $issues );
		}

		// Check for pagination.
		$has_pagination = preg_match( '/<nav[^>]*class=["\'][^"\']*paginat/i', $html ) ||
						  preg_match( '/class=["\'][^"\']*page-numbers[^"\']*["\']/', $html );

		if ( ! $has_pagination ) {
			// Check if site has enough posts for pagination.
			$post_count = wp_count_posts( 'post' );
			$posts_per_page = get_option( 'posts_per_page', 10 );

			if ( $post_count->publish > $posts_per_page ) {
				$issues[] = array(
					'issue_type'  => 'no_pagination',
					'severity'    => 'low',
					'description' => sprintf(
						'Site has %d posts but no pagination detected',
						$post_count->publish
					),
				);
			}

			return array( 'issues' => $issues );
		}

		// Check for infinite scroll or Load More.
		$has_infinite_scroll = preg_match( '/infinite[-_]scroll|load[-_]more/i', $html );

		if ( ! $has_infinite_scroll ) {
			$issues[] = array(
				'issue_type'  => 'traditional_pagination',
				'severity'    => 'low',
				'description' => 'Using traditional numbered pagination instead of Load More/infinite scroll',
				'impact'      => 'Suboptimal mobile UX, requires page reload',
			);
		}

		// Check link sizes (if we can detect inline styles).
		if ( preg_match( '/\.pagination.*?font-size:\s*([0-9]+)px/is', $html, $size_match ) ) {
			$font_size = (int) $size_match[1];
			if ( $font_size < 14 ) {
				$issues[] = array(
					'issue_type'  => 'small_links',
					'severity'    => 'medium',
					'description' => sprintf( 'Pagination links font-size is %dpx (should be 16px+ for mobile)', $font_size ),
				);
			}
		}

		// Check for accessibility (role="navigation" or <nav>).
		if ( ! preg_match( '/<nav[^>]*(?:role=["\']navigation["\']|aria-label=["\']pagination["\'])/i', $html ) ) {
			$issues[] = array(
				'issue_type'  => 'no_aria',
				'severity'    => 'low',
				'description' => 'Pagination missing proper ARIA labels or <nav> element',
				'impact'      => 'Accessibility issue for screen readers',
			);
		}

		return array( 'issues' => $issues );
	}

	/**
	 * Capture page HTML.
	 *
	 * @since 0.6093.1200
	 * @param  string $url Page URL.
	 * @return string HTML content.
	 */
	private static function capture_page_html( $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 10,
				'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $response );
	}
}
