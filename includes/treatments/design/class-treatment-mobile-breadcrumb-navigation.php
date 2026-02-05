<?php
/**
 * Mobile Breadcrumb Navigation Treatment
 *
 * Validates that breadcrumb navigation is mobile-friendly with proper
 * sizing, structured data, and accessibility.
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since      1.602.1235
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Breadcrumb Navigation Treatment Class
 *
 * Checks breadcrumb implementation for mobile usability, structured data,
 * and accessibility compliance.
 *
 * @since 1.602.1235
 */
class Treatment_Mobile_Breadcrumb_Navigation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-breadcrumb-navigation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Breadcrumb Navigation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates breadcrumb navigation is mobile-friendly with proper sizing and structured data';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1235
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if breadcrumbs exist.
		$breadcrumb_check = self::check_breadcrumbs();
		if ( ! empty( $breadcrumb_check['issues'] ) ) {
			$issues = array_merge( $issues, $breadcrumb_check['issues'] );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$issue_count  = count( $issues );
		$threat_level = min( 60, 35 + ( $issue_count * 8 ) );
		$severity     = $threat_level >= 55 ? 'medium' : 'low';
		$auto_fixable = false;

		$description = sprintf(
			/* translators: %d: number of breadcrumb issues */
			__( 'Found %d mobile breadcrumb navigation issue(s). Breadcrumbs improve mobile navigation by 25%% and are essential for deep-link SEO. 70%% of users expect breadcrumbs on content pages.', 'wpshadow' ),
			$issue_count
		);

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => $auto_fixable,
			'kb_link'      => 'https://wpshadow.com/kb/mobile-breadcrumbs',
			'details'      => array(
				'issue_count'   => $issue_count,
				'issues'        => $issues,
				'why_important' => __(
					'Breadcrumbs are critical for mobile navigation and SEO:
					
					Navigation Benefits:
					• Shows user location in site hierarchy
					• Quick access to parent pages
					• Reduces Back button dependency
					• Improves navigation by 25% (Nielsen Norman)
					• 70% of users expect breadcrumbs on content pages
					
					Mobile-Specific Benefits:
					• Compensates for hidden/collapsed main nav
					• Provides context when deep-linked from Google
					• Easier than menu navigation on small screens
					• Reduces bounce rate from search results
					
					SEO Benefits:
					• Google shows breadcrumbs in search results
					• Improves site structure understanding
					• Better internal linking
					• Rich snippets in search
					• Reduces pogo-sticking
					
					Accessibility:
					• Screen reader navigation landmark
					• Keyboard navigation support
					• Clear page hierarchy for all users
					
					Without Breadcrumbs:
					• Users get lost in deep content
					• Higher bounce rates from Google
					• More reliance on browser back button
					• Lost SEO opportunity',
					'wpshadow'
				),
				'how_to_fix'    => __(
					'Implement mobile-friendly breadcrumbs:
					
					Option 1: Yoast SEO (Built-in)
					1. Install Yoast SEO plugin
					2. Go to SEO > Search Appearance > Breadcrumbs
					3. Enable breadcrumbs
					4. Add to theme:
					   <?php
					   if ( function_exists( "yoast_breadcrumb" ) ) {
					       yoast_breadcrumb( "<nav id=\"breadcrumbs\">", "</nav>" );
					   }
					   ?>
					
					Option 2: RankMath SEO (Built-in)
					1. Install Rank Math plugin
					2. Go to Rank Math > General Settings > Breadcrumbs
					3. Enable breadcrumbs
					4. Add to theme:
					   <?php
					   if ( function_exists( "rank_math_the_breadcrumbs" ) ) {
					       rank_math_the_breadcrumbs();
					   }
					   ?>
					
					Option 3: Breadcrumb NavXT (Dedicated Plugin)
					1. Install "Breadcrumb NavXT" plugin
					2. Configure in Settings > Breadcrumb NavXT
					3. Add to theme:
					   <?php
					   if ( function_exists( "bcn_display" ) ) {
					       bcn_display();
					   }
					   ?>
					
					Option 4: Custom Implementation
					<?php
					function custom_breadcrumbs() {
					    if ( is_front_page() ) return;
					    
					    echo "<nav aria-label=\"Breadcrumb\" class=\"breadcrumbs\">";
					    echo "<ol itemscope itemtype=\"https://schema.org/BreadcrumbList\">";
					    
					    // Home
					    echo "<li itemprop=\"itemListElement\" itemscope itemtype=\"https://schema.org/ListItem\">";
					    echo "<a itemprop=\"item\" href=\"" . home_url() . "\"><span itemprop=\"name\">Home</span></a>";
					    echo "<meta itemprop=\"position\" content=\"1\" />";
					    echo "</li>";
					    
					    // Categories/Parents
					    if ( is_category() || is_single() ) {
					        $categories = get_the_category();
					        if ( $categories ) {
					            $category = $categories[0];
					            echo "<li itemprop=\"itemListElement\" itemscope itemtype=\"https://schema.org/ListItem\">";
					            echo "<a itemprop=\"item\" href=\"" . get_category_link( $category->term_id ) . "\">";
					            echo "<span itemprop=\"name\">" . $category->name . "</span></a>";
					            echo "<meta itemprop=\"position\" content=\"2\" />";
					            echo "</li>";
					        }
					    }
					    
					    // Current page
					    if ( is_single() || is_page() ) {
					        echo "<li itemprop=\"itemListElement\" itemscope itemtype=\"https://schema.org/ListItem\">";
					        echo "<span itemprop=\"name\">" . get_the_title() . "</span>";
					        echo "<meta itemprop=\"position\" content=\"3\" />";
					        echo "</li>";
					    }
					    
					    echo "</ol></nav>";
					}
					?>
					
					Mobile-Optimized CSS:
					@media (max-width: 768px) {
					  .breadcrumbs {
					    font-size: 14px;
					    padding: 12px 16px;
					    overflow-x: auto;
					    white-space: nowrap;
					  }
					  .breadcrumbs li {
					    display: inline-block;
					  }
					  .breadcrumbs a {
					    padding: 8px 4px;
					    min-height: 44px; /* Touch target */
					  }
					  /* Truncate long titles on mobile */
					  .breadcrumbs li:last-child {
					    max-width: 150px;
					    overflow: hidden;
					    text-overflow: ellipsis;
					  }
					}
					
					Best Practices:
					• Always include Schema.org structured data
					• Make links 44x44px minimum (touch targets)
					• Use aria-label="Breadcrumb" on <nav>
					• Truncate or hide middle crumbs on mobile if needed
					• Show current page (non-linked) at end
					• Use > or / separators clearly visible
					• Test with Google Rich Results Test',
					'wpshadow'
				),
			),
		);
	}

	/**
	 * Check breadcrumb implementation.
	 *
	 * @since  1.602.1235
	 * @return array Check results.
	 */
	private static function check_breadcrumbs() {
		$issues = array();

		// Check for common breadcrumb plugins.
		$active_plugins    = get_option( 'active_plugins', array() );
		$has_breadcrumbs   = false;
		$breadcrumb_source = '';

		// Yoast SEO.
		if ( defined( 'WPSEO_VERSION' ) ) {
			$yoast_breadcrumbs = get_option( 'wpseo_internallinks', array() );
			if ( isset( $yoast_breadcrumbs['breadcrumbs-enable'] ) && $yoast_breadcrumbs['breadcrumbs-enable'] ) {
				$has_breadcrumbs   = true;
				$breadcrumb_source = 'Yoast SEO';
			}
		}

		// Rank Math.
		if ( defined( 'RANK_MATH_VERSION' ) ) {
			$rm_breadcrumbs = get_option( 'rank-math-options-general', array() );
			if ( isset( $rm_breadcrumbs['breadcrumbs'] ) && $rm_breadcrumbs['breadcrumbs'] ) {
				$has_breadcrumbs   = true;
				$breadcrumb_source = 'Rank Math';
			}
		}

		// Breadcrumb NavXT.
		foreach ( $active_plugins as $plugin ) {
			if ( strpos( $plugin, 'breadcrumb-navxt' ) !== false ) {
				$has_breadcrumbs   = true;
				$breadcrumb_source = 'Breadcrumb NavXT';
				break;
			}
		}

		// Check HTML for breadcrumbs.
		$test_url = get_permalink( get_option( 'page_for_posts' ) ?: 1 );
		if ( $test_url ) {
			$html = self::capture_page_html( $test_url );
			if ( ! empty( $html ) ) {
				// Look for breadcrumb patterns.
				if ( preg_match( '/<nav[^>]*(?:breadcrumb|aria-label=["\']breadcrumb["\'])/i', $html ) ||
					 preg_match( '/itemtype=["\']https?:\/\/schema\.org\/BreadcrumbList["\']/', $html ) ) {
					$has_breadcrumbs   = true;
					$breadcrumb_source = 'Custom Implementation';
				}
			}
		}

		// If no breadcrumbs detected.
		if ( ! $has_breadcrumbs ) {
			$issues[] = array(
				'issue_type'  => 'no_breadcrumbs',
				'severity'    => 'medium',
				'description' => 'No breadcrumb navigation detected',
				'impact'      => 'Poor mobile navigation, missed SEO opportunity',
			);
			return array( 'issues' => $issues );
		}

		// Check if breadcrumbs have structured data.
		if ( $has_breadcrumbs && ! empty( $html ) ) {
			if ( ! preg_match( '/itemtype=["\']https?:\/\/schema\.org\/BreadcrumbList["\']/', $html ) ) {
				$issues[] = array(
					'issue_type'  => 'no_structured_data',
					'severity'    => 'low',
					'description' => 'Breadcrumbs lack Schema.org structured data',
					'impact'      => 'Won\'t appear as rich snippets in Google search',
					'source'      => $breadcrumb_source,
				);
			}

			// Check mobile sizing (font-size should be 14px+).
			if ( preg_match( '/<style[^>]*>.*breadcrumb.*font-size:\s*([0-9]+)px/is', $html, $size_match ) ) {
				$font_size = (int) $size_match[1];
				if ( $font_size < 14 ) {
					$issues[] = array(
						'issue_type'  => 'text_too_small',
						'severity'    => 'low',
						'description' => sprintf( 'Breadcrumb font-size is %dpx (recommend 14px+ for mobile)', $font_size ),
					);
				}
			}
		}

		return array( 'issues' => $issues );
	}

	/**
	 * Capture page HTML.
	 *
	 * @since  1.602.1235
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
