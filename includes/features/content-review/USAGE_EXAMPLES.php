<?php
/**
 * Content Review System - Usage Examples
 *
 * This file contains practical examples of how to use and extend
 * the Content Review system in your plugins and themes.
 *
 * @package    WPShadow
 * @subpackage Examples
 * @since      1.6034.0000
 */

// ====================================================================
// EXAMPLE 1: Getting Content Diagnostics for a Post
// ====================================================================

use WPShadow\Features\ContentReview\Content_Review_Manager;

// Get all diagnostics for a post
$post_id = 123;
$diagnostics = Content_Review_Manager::get_content_diagnostics( $post_id );

// Output example:
// [
//     'seo' => [
//         [
//             'slug' => 'content-missing-meta-descriptions',
//             'class' => 'WPShadow\Diagnostics\Diagnostic_Content_Missing_Meta_Descriptions',
//             'finding' => [ 'title' => '...', 'description' => '...', ... ],
//             'severity' => 'medium'
//         ],
//         ...
//     ],
//     'accessibility' => [ ... ],
//     'readability' => [ ... ],
// ]

// Use in your own report or analysis
foreach ( $diagnostics as $family => $family_diagnostics ) {
	echo "Family: $family\n";
	echo "Issues: " . count( $family_diagnostics ) . "\n";
}

// ====================================================================
// EXAMPLE 2: Managing User Preferences
// ====================================================================

$user_id = get_current_user_id();

// Get all user preferences
$preferences = Content_Review_Manager::get_user_preferences( $user_id );

// Check if a specific diagnostic is skipped
if ( Content_Review_Manager::is_diagnostic_skipped( 'keyword-stuffing', $user_id ) ) {
	echo "User is skipping keyword-stuffing diagnostic\n";
}

// Skip a diagnostic for the user
Content_Review_Manager::skip_diagnostic( 'keyword-stuffing', $user_id );

// Hide a tip for the user
Content_Review_Manager::hide_tip( 'seo-title-tags', $user_id );

// Manually save updated preferences
$new_prefs = array(
	'hide_tips'        => array( 'seo-title-tags', 'meta-descriptions' ),
	'skip_diagnostics' => array( 'keyword-stuffing' ),
	'show_ai_tips'     => true,
	'show_kb_links'    => true,
);
Content_Review_Manager::save_user_preferences( $user_id, $new_prefs );

// ====================================================================
// EXAMPLE 3: Integrating KB Articles with Diagnostics
// ====================================================================

// Hook into the KB article system to provide articles for diagnostics
add_filter(
	'wpshadow_kb_articles_for_diagnostic',
	function( $articles, $slug ) {
		// Provide custom KB articles for specific diagnostics
		if ( 'content-missing-alt-text' === $slug ) {
			$articles[] = array(
				'title'   => 'How to Add Alt Text to Images in WordPress',
				'url'     => 'https://wpshadow.com/kb/alt-text/',
				'excerpt' => 'Alt text improves accessibility and SEO by describing images to screen readers and search engines.',
			);
		}

		if ( 'keyword-stuffing' === $slug ) {
			$articles[] = array(
				'title'   => 'Natural Keyword Usage for Better SEO',
				'url'     => 'https://wpshadow.com/kb/keyword-usage/',
				'excerpt' => 'Learn how to use keywords naturally without compromising readability.',
			);
		}

		return $articles;
	},
	10,
	2
);

// ====================================================================
// EXAMPLE 4: Providing Training Courses for Families
// ====================================================================

// Hook into the training system to provide courses for diagnostic families
add_filter(
	'wpshadow_training_courses_for_family',
	function( $courses, $family ) {
		// Provide training for specific families
		if ( 'seo' === $family ) {
			$courses[] = array(
				'title'       => 'WordPress SEO Fundamentals',
				'url'         => 'https://wpshadow.com/academy/courses/wordpress-seo/',
				'duration'    => '45 minutes',
				'description' => 'Learn on-page SEO optimization, keyword research, and technical SEO fundamentals for WordPress.',
				'thumbnail'   => 'https://wpshadow.com/assets/course-seo.jpg',
			);
		}

		if ( 'accessibility' === $family ) {
			$courses[] = array(
				'title'       => 'WordPress Accessibility & WCAG Compliance',
				'url'         => 'https://wpshadow.com/academy/courses/wcag-compliance/',
				'duration'    => '60 minutes',
				'description' => 'Make your WordPress site accessible to all users, including those with disabilities.',
				'thumbnail'   => 'https://wpshadow.com/assets/course-a11y.jpg',
			);
		}

		if ( 'readability' === $family ) {
			$courses[] = array(
				'title'       => 'Writing Readable Content for the Web',
				'url'         => 'https://wpshadow.com/academy/courses/readability/',
				'duration'    => '30 minutes',
				'description' => 'Write clear, scannable, accessible web content that engages readers.',
				'thumbnail'   => 'https://wpshadow.com/assets/course-readability.jpg',
			);
		}

		return $courses;
	},
	10,
	2
);

// ====================================================================
// EXAMPLE 5: Reacting to Report Generation
// ====================================================================

// Hook into report generation to log, email, or integrate with external services
add_action(
	'wpshadow_content_report_generated',
	function( $post_id, $report ) {
		// Example 1: Log to external analytics service
		error_log(
			sprintf(
				'Content report generated for post %d (%s). Total issues: %d (Critical: %d, High: %d)',
				$post_id,
				$report['post']['title'],
				$report['total_issues'],
				$report['severity_counts']['critical'],
				$report['severity_counts']['high']
			)
		);

		// Example 2: Send email to site admin if critical issues found
		if ( $report['severity_counts']['critical'] > 0 ) {
			$admin_email = get_option( 'admin_email' );
			wp_mail(
				$admin_email,
				sprintf(
					'Critical issues found in post: %s',
					$report['post']['title']
				),
				sprintf(
					"Post URL: %s\nCritical Issues: %d\nTotal Issues: %d",
					$report['post']['url'],
					$report['severity_counts']['critical'],
					$report['total_issues']
				)
			);
		}

		// Example 3: Store in custom table for history tracking
		// global $wpdb;
		// $wpdb->insert(
		//     'wp_content_reports',
		//     [
		//         'post_id' => $post_id,
		//         'total_issues' => $report['total_issues'],
		//         'critical_count' => $report['severity_counts']['critical'],
		//         'generated_at' => current_time( 'mysql' ),
		//     ]
		// );
	},
	10,
	2
);

// ====================================================================
// EXAMPLE 6: Custom Report Generation in Your Theme/Plugin
// ====================================================================

// Generate a content report for display
function my_theme_get_post_content_report( $post_id ) {
	$diagnostics = Content_Review_Manager::get_content_diagnostics( $post_id );

	// Count issues by severity
	$severity_counts = array(
		'critical' => 0,
		'high'     => 0,
		'medium'   => 0,
		'low'      => 0,
	);

	$total_issues = 0;
	foreach ( $diagnostics as $family_diagnostics ) {
		foreach ( $family_diagnostics as $diagnostic ) {
			$severity = $diagnostic['severity'] ?? 'medium';
			if ( isset( $severity_counts[ $severity ] ) ) {
				$severity_counts[ $severity ]++;
			}
			$total_issues++;
		}
	}

	return array(
		'post_id'           => $post_id,
		'total_issues'      => $total_issues,
		'severity_counts'   => $severity_counts,
		'diagnostics'       => $diagnostics,
		'health_percentage' => max( 0, 100 - ( $total_issues * 5 ) ),
	);
}

// Use it
$report = my_theme_get_post_content_report( 123 );
echo "Post Health: " . $report['health_percentage'] . "%\n";
echo "Critical Issues: " . $report['severity_counts']['critical'] . "\n";

// ====================================================================
// EXAMPLE 7: Displaying Content Health Widget
// ====================================================================

// Create a simple content health widget
function my_theme_content_health_dashboard() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	// Get recent posts
	$recent_posts = get_posts(
		array(
			'posts_per_page' => 5,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		)
	);

	echo '<div class="wpshadow-health-dashboard">';
	echo '<h3>Recent Post Health</h3>';
	echo '<ul>';

	foreach ( $recent_posts as $post ) {
		$report = my_theme_get_post_content_report( $post->ID );

		printf(
			'<li>%s: %d%% healthy (%d issues)</li>',
			esc_html( $post->post_title ),
			$report['health_percentage'],
			$report['total_issues']
		);
	}

	echo '</ul>';
	echo '</div>';
}

// ====================================================================
// EXAMPLE 8: Cloud AI Integration Check
// ====================================================================

use WPShadow\Integration\Cloud\Cloud_Service_Connector;

// Check if cloud service is available
if ( Cloud_Service_Connector::is_registered() ) {
	echo "Cloud AI features are available\n";

	// Get API key (for verification)
	$api_key = Cloud_Service_Connector::get_api_key();
	if ( $api_key ) {
		echo "Cloud service connected successfully\n";
	}
} else {
	echo "Cloud service not registered. Suggest registration to user.\n";
}

// ====================================================================
// EXAMPLE 9: Programmatically Triggering Review
// ====================================================================

// You could create a custom endpoint or command that triggers review
function my_custom_content_audit() {
	// Get all posts
	$posts = get_posts(
		array(
			'posts_per_page' => -1,
			'post_type'      => 'post',
		)
	);

	$results = array();

	foreach ( $posts as $post ) {
		$diagnostics = Content_Review_Manager::get_content_diagnostics( $post->ID );

		$issue_count = array_sum(
			array_map(
				function( $family_diags ) {
					return count( $family_diags );
				},
				$diagnostics
			)
		);

		$results[] = array(
			'post_id' => $post->ID,
			'title'   => $post->post_title,
			'issues'  => $issue_count,
		);
	}

	return $results;
}

// Usage
// $audit_results = my_custom_content_audit();
// foreach ( $audit_results as $result ) {
//     echo $result['title'] . ': ' . $result['issues'] . " issues\n";
// }

// ====================================================================
// EXAMPLE 10: Adding Custom Diagnostic to Reviews
// ====================================================================

// Your custom diagnostic will automatically appear in reviews
// because it uses the filter hook.

// When you register your diagnostic with the Diagnostic_Registry,
// it will be picked up by the content review system automatically
// if it's in one of these families:
// - 'content'
// - 'seo'
// - 'accessibility'
// - 'readability'
// - 'code-quality'

// Example: Create a custom diagnostic
class Diagnostic_My_Custom_Check extends Diagnostic_Base {
	protected static $slug = 'my-custom-check';
	protected static $title = 'My Custom Check';
	protected static $description = 'Checks for something specific';
	protected static $family = 'seo'; // Add to SEO family for content review

	public static function check() {
		// Your logic here
		return array(
			'title'       => 'Issue Found',
			'description' => 'Here is the issue...',
			'severity'    => 'medium',
		);
	}
}

// Register it
// Diagnostic_Registry::register( 'my-custom-check', Diagnostic_My_Custom_Check::class );

// It will now appear in content reviews automatically!
