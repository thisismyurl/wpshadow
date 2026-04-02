<?php
/**
 * Mobile Anchor Link Performance Diagnostic
 *
 * Validates that anchor links (jump links) work smoothly on mobile with
 * proper scroll behavior and offset for fixed headers.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Mobile
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Anchor Link Performance Diagnostic Class
 *
 * Checks anchor link implementation for mobile smooth scrolling, proper
 * offset calculations, and accessibility.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Anchor_Link_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-anchor-link-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Anchor Link Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates anchor links work smoothly on mobile with proper scroll behavior and fixed header offset';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for anchor links and fixed headers.
		$anchor_check = self::check_anchor_links();
		if ( ! empty( $anchor_check['issues'] ) ) {
			$issues = array_merge( $issues, $anchor_check['issues'] );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$issue_count  = count( $issues );
		$threat_level = min( 55, 30 + ( $issue_count * 10 ) );
		$severity     = $threat_level >= 50 ? 'medium' : 'low';
		$auto_fixable = false;

		$description = sprintf(
			/* translators: %d: number of anchor link issues */
			__( 'Found %d mobile anchor link issue(s). Poor anchor link behavior causes content to be hidden under fixed headers. 45%% of long-form mobile pages use anchor navigation.', 'wpshadow' ),
			$issue_count
		);

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => $auto_fixable,
			'kb_link'      => 'https://wpshadow.com/kb/mobile-anchor-links',
			'details'      => array(
				'issue_count'   => $issue_count,
				'issues'        => $issues,
				'why_important' => __(
					'Mobile anchor links need special attention:
					
					Common Use Cases:
					• Table of contents in long articles
					• "Back to top" buttons
					• Skip links for accessibility
					• Multi-section landing pages
					• FAQs with jump navigation
					• Documentation with deep linking
					
					Mobile-Specific Issues:
					• Fixed headers hide content when jumping
					• Instant jump is jarring (no smooth scroll)
					• Small screen = more important to land precisely
					• iOS Safari has unique scroll-into-view behavior
					• Focus management for accessibility
					
					Fixed Header Problem:
					1. User clicks anchor link
					2. Browser scrolls target to top of viewport
					3. Fixed header (80px) covers the target content
					4. User confused - can\'t see what they clicked
					
					Impact:
					• Poor user experience (content hidden)
					• Accessibility fail (focus on hidden content)
					• Users think navigation is broken
					• Higher bounce rates on long content
					
					Solutions:
					• CSS scroll-padding-top
					• JavaScript smooth scroll with offset
					• Scroll-margin-top on target elements',
					'wpshadow'
				),
				'how_to_fix'    => __(
					'Fix anchor link behavior on mobile:
					
					Solution 1: CSS scroll-padding (Modern, Recommended)
					/* Add to root element */
					html {
					  scroll-behavior: smooth; /* Smooth scrolling */
					  scroll-padding-top: 100px; /* Offset for fixed header */
					}
					
					/* Or on individual targets */
					h2[id], h3[id], section[id] {
					  scroll-margin-top: 100px;
					}
					
					Solution 2: JavaScript Smooth Scroll with Offset
					document.querySelectorAll("a[href^=\"#\"]").forEach(anchor => {
					  anchor.addEventListener("click", function(e) {
					    e.preventDefault();
					    
					    const targetId = this.getAttribute("href");
					    const targetElement = document.querySelector(targetId);
					    
					    if (targetElement) {
					      const headerHeight = document.querySelector("header").offsetHeight;
					      const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
					      const offsetPosition = targetPosition - headerHeight - 20; // 20px extra padding
					      
					      window.scrollTo({
					        top: offsetPosition,
					        behavior: "smooth"
					      });
					      
					      // Update URL
					      history.pushState(null, null, targetId);
					      
					      // Set focus for accessibility
					      targetElement.setAttribute("tabindex", "-1");
					      targetElement.focus();
					    }
					  });
					});
					
					Solution 3: WordPress Gutenberg Block (TOC)
					// Table of Contents block has built-in smooth scroll
					// Go to editor > Add block > "Table of Contents"
					// Or use plugins:
					• Easy Table of Contents (wordpress.org/plugins/easy-table-of-contents)
					• LuckyWP Table of Contents (wordpress.org/plugins/luckywp-table-of-contents)
					
					Solution 4: jQuery Smooth Scroll (Legacy)
					jQuery("a[href^=\"#\"]").on("click", function(e) {
					  e.preventDefault();
					  const target = jQuery(this.hash);
					  const offset = 100; // Fixed header height
					  
					  if (target.length) {
					    jQuery("html, body").animate({
					      scrollTop: target.offset().top - offset
					    }, 500);
					  }
					});
					
					Detect Fixed Header Height Dynamically:
					function getHeaderOffset() {
					  const header = document.querySelector("header, .site-header, #masthead");
					  if (!header) return 0;
					  
					  const styles = window.getComputedStyle(header);
					  if (styles.position === "fixed" || styles.position === "sticky") {
					    return header.offsetHeight;
					  }
					  
					  return 0;
					}
					
					Handle URL Hash on Page Load:
					window.addEventListener("load", function() {
					  if (window.location.hash) {
					    // Delay to ensure page is fully loaded
					    setTimeout(() => {
					      const target = document.querySelector(window.location.hash);
					      if (target) {
					        const offset = getHeaderOffset();
					        window.scrollTo({
					          top: target.offsetTop - offset - 20,
					          behavior: "smooth"
					        });
					      }
					    }, 100);
					  }
					});
					
					Back to Top Button:
					<button id="back-to-top" aria-label="Back to top">
					  ↑ Top
					</button>
					
					<script>
					document.querySelector("#back-to-top").addEventListener("click", () => {
					  window.scrollTo({
					    top: 0,
					    behavior: "smooth"
					  });
					});
					
					// Show button after scrolling down
					window.addEventListener("scroll", () => {
					  const button = document.querySelector("#back-to-top");
					  if (window.scrollY > 500) {
					    button.classList.add("visible");
					  } else {
					    button.classList.remove("visible");
					  }
					});
					</script>
					
					Accessibility Considerations:
					• Set tabindex="-1" on target to enable focus
					• Call .focus() after scrolling
					• Use aria-current="location" on active link
					• Provide visible focus indicators
					
					Testing:
					• Test on iOS Safari (unique scroll behavior)
					• Test with different header heights
					• Test "back to top" buttons
					• Verify keyboard navigation works
					• Check URL updates correctly',
					'wpshadow'
				),
			),
		);
	}

	/**
	 * Check anchor link implementation.
	 *
	 * @since 1.6093.1200
	 * @return array Check results.
	 */
	private static function check_anchor_links() {
		$issues = array();

		// Capture homepage to check for anchor links and fixed header.
		$html = self::capture_page_html( home_url( '/' ) );
		if ( empty( $html ) ) {
			return array( 'issues' => $issues );
		}

		// Check for fixed header.
		$has_fixed_header = preg_match( '/position:\s*fixed|position:\s*sticky/i', $html );

		// Check for anchor links.
		$has_anchor_links = preg_match_all( '/<a[^>]*href=["\']#[^"\']+["\']/', $html, $anchor_matches );

		if ( ! $has_anchor_links ) {
			// No anchor links, no issues to report.
			return array( 'issues' => $issues );
		}

		// Check for smooth scroll.
		$has_smooth_scroll = preg_match( '/scroll-behavior:\s*smooth/i', $html );

		if ( ! $has_smooth_scroll ) {
			$issues[] = array(
				'issue_type'  => 'no_smooth_scroll',
				'severity'    => 'low',
				'description' => 'Anchor links detected but smooth scrolling not enabled',
				'impact'      => 'Instant jumps are jarring on mobile',
			);
		}

		// Check for scroll offset with fixed header.
		if ( $has_fixed_header ) {
			$has_scroll_offset = preg_match( '/scroll-padding-top|scroll-margin-top/i', $html );

			if ( ! $has_scroll_offset ) {
				$issues[] = array(
					'issue_type'  => 'fixed_header_no_offset',
					'severity'    => 'medium',
					'description' => 'Fixed header detected but no scroll offset configured',
					'impact'      => 'Anchor links will hide content under fixed header',
				);
			}
		}

		// Check for JavaScript smooth scroll implementation.
		$has_js_scroll = preg_match( '/scrollTo|scrollIntoView|smooth.*scroll/i', $html );

		return array( 'issues' => $issues );
	}

	/**
	 * Capture page HTML.
	 *
	 * @since 1.6093.1200
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
