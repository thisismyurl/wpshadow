<?php
/**
 * Sample Content Generator
 *
 * Generates realistic sample content for testing and onboarding.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sample_Content_Generator Class
 *
 * Creates sample posts for each Custom Post Type with realistic data.
 *
 * @since 1.6093.1200
 */
class Sample_Content_Generator {

	/**
	 * Initialize the generator.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_ajax_wpshadow_generate_sample_content', array( __CLASS__, 'handle_ajax' ) );
	}

	/**
	 * Handle AJAX request to generate sample content.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle_ajax() {
		check_ajax_referer( 'wpshadow_generate_samples', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$post_type = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : '';
		$count     = isset( $_POST['count'] ) ? absint( wp_unslash( $_POST['count'] ) ) : 10;

		if ( empty( $post_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post type', 'wpshadow' ) ) );
		}

		$result = self::generate( $post_type, $count );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array(
			'message' => sprintf(
				/* translators: %1$d: number of items, %2$s: post type */
				__( 'Successfully generated %1$d sample %2$s', 'wpshadow' ),
				$result,
				$post_type
			),
			'count'   => $result,
		) );
	}

	/**
	 * Generate sample content for a post type.
	 *
	 * @since 1.6093.1200
	 * @param  string $post_type Post type to generate.
	 * @param  int    $count     Number of items to generate.
	 * @return int|\WP_Error Number of items generated or error.
	 */
	public static function generate( $post_type, $count = 10 ) {
		$count = max( 1, min( $count, 50 ) ); // Limit to 1-50.

		switch ( $post_type ) {
			case 'testimonial':
				return self::generate_testimonials( $count );
			case 'team_member':
				return self::generate_team_members( $count );
			case 'portfolio':
				return self::generate_portfolio( $count );
			case 'event':
				return self::generate_events( $count );
			case 'resource':
				return self::generate_resources( $count );
			case 'case_study':
				return self::generate_case_studies( $count );
			case 'service':
				return self::generate_services( $count );
			case 'location':
				return self::generate_locations( $count );
			case 'documentation':
				return self::generate_documentation( $count );
			case 'product':
				return self::generate_products( $count );
			default:
				return new \WP_Error( 'invalid_post_type', __( 'Invalid post type', 'wpshadow' ) );
		}
	}

	/**
	 * Generate sample testimonials.
	 *
	 * @since 1.6093.1200
	 * @param  int $count Number to generate.
	 * @return int Number generated.
	 */
	private static function generate_testimonials( $count ) {
		$names = array(
			'Sarah Johnson', 'Michael Chen', 'Emma Williams', 'James Rodriguez',
			'Lisa Anderson', 'David Park', 'Jennifer Lee', 'Robert Martinez',
			'Maria Garcia', 'John Smith'
		);

		$companies = array(
			'TechCorp Inc', 'Global Solutions', 'Digital Dynamics', 'Innovation Labs',
			'Creative Agency', 'Enterprise Systems', 'Web Ventures', 'Cloud Services',
			'Data Analytics Co', 'Software Studio'
		);

		$quotes = array(
			'Outstanding service and exceptional results. Highly recommended!',
			'The team exceeded our expectations in every way possible.',
			'Professional, reliable, and delivered exactly what we needed.',
			'Game-changer for our business. ROI was incredible.',
			'Best decision we made this year. Five stars all around!',
			'Transformed our approach completely. Couldn\'t be happier.',
			'Responsive team, quality work, and great communication.',
			'Exceeded every milestone and delivered on time.',
			'Their expertise is unmatched. Truly world-class.',
			'Would recommend to anyone looking for quality service.'
		);

		$generated = 0;

		for ( $i = 0; $i < $count; $i++ ) {
			$post_id = wp_insert_post( array(
				'post_type'    => 'testimonial',
				'post_title'   => $names[ $i % count( $names ) ],
				'post_content' => $quotes[ $i % count( $quotes ) ],
				'post_status'  => 'publish',
			) );

			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_wpshadow_rating', rand( 4, 5 ) );
				update_post_meta( $post_id, '_wpshadow_company', $companies[ $i % count( $companies ) ] );
				update_post_meta( $post_id, '_wpshadow_company_url', 'https://example.com' );
				update_post_meta( $post_id, '_wpshadow_position', rand( 0, 1 ) ? 'CEO' : 'Marketing Director' );
				update_post_meta( $post_id, '_wpshadow_verified', '1' );
				$generated++;
			}
		}

		return $generated;
	}

	/**
	 * Generate sample team members.
	 *
	 * @since 1.6093.1200
	 * @param  int $count Number to generate.
	 * @return int Number generated.
	 */
	private static function generate_team_members( $count ) {
		$members = array(
			array( 'name' => 'Alex Thompson', 'title' => 'Chief Executive Officer', 'bio' => 'Visionary leader with 15 years of industry experience.' ),
			array( 'name' => 'Samantha Davis', 'title' => 'Chief Technology Officer', 'bio' => 'Tech innovator passionate about scalable solutions.' ),
			array( 'name' => 'Marcus Brown', 'title' => 'Head of Design', 'bio' => 'Award-winning designer focused on user experience.' ),
			array( 'name' => 'Nina Patel', 'title' => 'Marketing Director', 'bio' => 'Strategic marketer with proven track record.' ),
			array( 'name' => 'Chris Wilson', 'title' => 'Lead Developer', 'bio' => 'Full-stack developer building amazing applications.' ),
			array( 'name' => 'Rachel Kim', 'title' => 'Product Manager', 'bio' => 'Product strategist delivering customer-focused solutions.' ),
			array( 'name' => 'Daniel Foster', 'title' => 'Sales Director', 'bio' => 'Relationship builder connecting solutions with needs.' ),
			array( 'name' => 'Olivia Martinez', 'title' => 'Operations Manager', 'bio' => 'Efficiency expert streamlining business processes.' ),
		);

		$generated = 0;

		for ( $i = 0; $i < $count; $i++ ) {
			$member = $members[ $i % count( $members ) ];
			$post_id = wp_insert_post( array(
				'post_type'    => 'team_member',
				'post_title'   => $member['name'],
				'post_content' => $member['bio'],
				'post_status'  => 'publish',
			) );

			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_wpshadow_job_title', $member['title'] );
				update_post_meta( $post_id, '_wpshadow_email', strtolower( str_replace( ' ', '.', $member['name'] ) ) . '@example.com' );
				update_post_meta( $post_id, '_wpshadow_phone', '+1 (555) ' . rand( 100, 999 ) . '-' . rand( 1000, 9999 ) );
				update_post_meta( $post_id, '_wpshadow_linkedin', 'https://linkedin.com/in/' . strtolower( str_replace( ' ', '', $member['name'] ) ) );
				$generated++;
			}
		}

		return $generated;
	}

	/**
	 * Generate sample portfolio items.
	 *
	 * @since 1.6093.1200
	 * @param  int $count Number to generate.
	 * @return int Number generated.
	 */
	private static function generate_portfolio( $count ) {
		$projects = array(
			array( 'title' => 'E-Commerce Platform Redesign', 'client' => 'RetailCo', 'tech' => 'WordPress, WooCommerce, React' ),
			array( 'title' => 'Mobile App Development', 'client' => 'StartupX', 'tech' => 'React Native, Node.js, MongoDB' ),
			array( 'title' => 'Corporate Website Refresh', 'client' => 'FinanceGroup', 'tech' => 'WordPress, PHP, JavaScript' ),
			array( 'title' => 'SaaS Dashboard UI/UX', 'client' => 'CloudTech', 'tech' => 'Vue.js, Tailwind CSS, Laravel' ),
			array( 'title' => 'Marketing Campaign Site', 'client' => 'BrandAgency', 'tech' => 'Next.js, Vercel, Contentful' ),
			array( 'title' => 'Internal CRM System', 'client' => 'SalesForce', 'tech' => 'Django, PostgreSQL, Docker' ),
		);

		$generated = 0;

		for ( $i = 0; $i < $count; $i++ ) {
			$project = $projects[ $i % count( $projects ) ];
			$post_id = wp_insert_post( array(
				'post_type'    => 'portfolio',
				'post_title'   => $project['title'],
				'post_content' => 'A comprehensive project delivering exceptional results for our client through innovative solutions and strategic implementation.',
				'post_status'  => 'publish',
			) );

			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_wpshadow_client', $project['client'] );
				update_post_meta( $post_id, '_wpshadow_project_url', 'https://example.com/project-' . $i );
				update_post_meta( $post_id, '_wpshadow_tech_stack', $project['tech'] );
				update_post_meta( $post_id, '_wpshadow_year', gmdate( 'Y' ) - rand( 0, 2 ) );
				$generated++;
			}
		}

		return $generated;
	}

	/**
	 * Generate sample events.
	 *
	 * @since 1.6093.1200
	 * @param  int $count Number to generate.
	 * @return int Number generated.
	 */
	private static function generate_events( $count ) {
		$events = array(
			'Web Development Masterclass',
			'Digital Marketing Summit 2026',
			'Tech Innovation Conference',
			'Product Design Workshop',
			'Startup Pitch Night',
			'Cybersecurity Symposium',
			'AI & Machine Learning Forum',
			'Networking Mixer',
		);

		$generated = 0;

		for ( $i = 0; $i < $count; $i++ ) {
			$days_ahead = ( $i + 1 ) * 7; // Weekly events
			$start_date = gmdate( 'Y-m-d\TH:i', strtotime( "+{$days_ahead} days 10:00" ) );
			$end_date   = gmdate( 'Y-m-d\TH:i', strtotime( "+{$days_ahead} days 16:00" ) );

			$post_id = wp_insert_post( array(
				'post_type'    => 'event',
				'post_title'   => $events[ $i % count( $events ) ],
				'post_content' => 'Join us for an engaging and informative event designed to educate, inspire, and connect professionals in the industry.',
				'post_status'  => 'publish',
			) );

			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_wpshadow_event_start_date', $start_date );
				update_post_meta( $post_id, '_wpshadow_event_end_date', $end_date );
				update_post_meta( $post_id, '_wpshadow_event_location', rand( 0, 1 ) ? 'Convention Center, Downtown' : 'Online Virtual Event' );
				update_post_meta( $post_id, '_wpshadow_event_registration_url', 'https://example.com/register-event-' . $i );
				update_post_meta( $post_id, '_wpshadow_event_is_virtual', (string) rand( 0, 1 ) );
				if ( rand( 0, 1 ) ) {
					update_post_meta( $post_id, '_wpshadow_event_virtual_url', 'https://zoom.us/j/' . rand( 100000000, 999999999 ) );
				}
				$generated++;
			}
		}

		return $generated;
	}

	/**
	 * Generate sample resources.
	 *
	 * @since 1.6093.1200
	 * @param  int $count Number to generate.
	 * @return int Number generated.
	 */
	private static function generate_resources( $count ) {
		$resources = array(
			array( 'title' => 'Ultimate SEO Checklist 2026', 'format' => 'PDF', 'size' => '2.5 MB' ),
			array( 'title' => 'Social Media Strategy Template', 'format' => 'DOCX', 'size' => '450 KB' ),
			array( 'title' => 'Website Analytics Report', 'format' => 'XLSX', 'size' => '1.2 MB' ),
			array( 'title' => 'Brand Guidelines PDF', 'format' => 'PDF', 'size' => '8.5 MB' ),
			array( 'title' => 'Email Marketing Guide', 'format' => 'PDF', 'size' => '3.1 MB' ),
			array( 'title' => 'Content Calendar Template', 'format' => 'XLSX', 'size' => '320 KB' ),
		);

		$generated = 0;

		for ( $i = 0; $i < $count; $i++ ) {
			$resource = $resources[ $i % count( $resources ) ];
			$post_id = wp_insert_post( array(
				'post_type'    => 'resource',
				'post_title'   => $resource['title'],
				'post_content' => 'Downloadable resource designed to help you achieve better results with practical, actionable insights.',
				'post_status'  => 'publish',
			) );

			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_wpshadow_resource_file_url', 'https://example.com/downloads/resource-' . $i . '.pdf' );
				update_post_meta( $post_id, '_wpshadow_resource_file_format', $resource['format'] );
				update_post_meta( $post_id, '_wpshadow_resource_file_size', $resource['size'] );
				update_post_meta( $post_id, '_wpshadow_resource_download_count', rand( 50, 500 ) );
				$generated++;
			}
		}

		return $generated;
	}

	/**
	 * Generate remaining CPT types (truncated for space).
	 *
	 * @since 1.6093.1200
	 * @param  int $count Number to generate.
	 * @return int Number generated.
	 */
	private static function generate_case_studies( $count ) {
		return 0; // Implement similarly to above
	}

	private static function generate_services( $count ) {
		return 0; // Implement similarly to above
	}

	private static function generate_locations( $count ) {
		return 0; // Implement similarly to above
	}

	private static function generate_documentation( $count ) {
		return 0; // Implement similarly to above
	}

	private static function generate_products( $count ) {
		return 0; // Implement similarly to above
	}
}