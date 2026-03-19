<?php
/**
 * Custom Fields for Custom Post Types
 *
 * Provides metaboxes and custom fields for each CPT without requiring ACF.
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
 * CPT_Custom_Fields Class
 *
 * Manages custom fields for all Custom Post Types.
 *
 * @since 1.6093.1200
 */
class CPT_Custom_Fields {

	/**
	 * Initialize custom fields system.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta_boxes' ), 10, 2 );
	}

	/**
	 * Register all metaboxes.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register_meta_boxes() {
		// Testimonials.
		add_meta_box(
			'wpshadow_testimonial_details',
			__( 'Testimonial Details', 'wpshadow' ),
			array( __CLASS__, 'render_testimonial_fields' ),
			'testimonial',
			'normal',
			'high'
		);

		// Team Members.
		add_meta_box(
			'wpshadow_team_member_details',
			__( 'Team Member Details', 'wpshadow' ),
			array( __CLASS__, 'render_team_member_fields' ),
			'team_member',
			'normal',
			'high'
		);

		// Portfolio.
		add_meta_box(
			'wpshadow_portfolio_details',
			__( 'Portfolio Details', 'wpshadow' ),
			array( __CLASS__, 'render_portfolio_fields' ),
			'portfolio',
			'normal',
			'high'
		);

		// Events.
		add_meta_box(
			'wpshadow_event_details',
			__( 'Event Details', 'wpshadow' ),
			array( __CLASS__, 'render_event_fields' ),
			'event',
			'normal',
			'high'
		);

		// Resources.
		add_meta_box(
			'wpshadow_resource_details',
			__( 'Resource Details', 'wpshadow' ),
			array( __CLASS__, 'render_resource_fields' ),
			'resource',
			'normal',
			'high'
		);

		// Case Studies.
		add_meta_box(
			'wpshadow_case_study_details',
			__( 'Case Study Details', 'wpshadow' ),
			array( __CLASS__, 'render_case_study_fields' ),
			'case_study',
			'normal',
			'high'
		);

		// Services.
		add_meta_box(
			'wpshadow_service_details',
			__( 'Service Details', 'wpshadow' ),
			array( __CLASS__, 'render_service_fields' ),
			'service',
			'normal',
			'high'
		);

		// Locations.
		add_meta_box(
			'wpshadow_location_details',
			__( 'Location Details', 'wpshadow' ),
			array( __CLASS__, 'render_location_fields' ),
			'location',
			'normal',
			'high'
		);

		// Documentation.
		add_meta_box(
			'wpshadow_documentation_details',
			__( 'Documentation Details', 'wpshadow' ),
			array( __CLASS__, 'render_documentation_fields' ),
			'documentation',
			'normal',
			'high'
		);

		// Products.
		add_meta_box(
			'wpshadow_product_details',
			__( 'Product Details', 'wpshadow' ),
			array( __CLASS__, 'render_product_fields' ),
			'product',
			'normal',
			'high'
		);
	}

	/**
	 * Render testimonial fields.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public static function render_testimonial_fields( $post ) {
		wp_nonce_field( 'wpshadow_testimonial_meta', 'wpshadow_testimonial_nonce' );

		$rating      = get_post_meta( $post->ID, '_wpshadow_rating', true );
		$company     = get_post_meta( $post->ID, '_wpshadow_company', true );
		$company_url = get_post_meta( $post->ID, '_wpshadow_company_url', true );
		$position    = get_post_meta( $post->ID, '_wpshadow_position', true );
		$verified    = get_post_meta( $post->ID, '_wpshadow_verified', true );
		?>
		<table class="form-table">
			<tr>
				<th><label for="wpshadow_rating"><?php esc_html_e( 'Rating', 'wpshadow' ); ?></label></th>
				<td>
					<select name="wpshadow_rating" id="wpshadow_rating">
						<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
							<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $rating, $i ); ?>>
								<?php echo esc_html( str_repeat( '⭐', $i ) . ' (' . $i . ')' ); ?>
							</option>
						<?php endfor; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_company"><?php esc_html_e( 'Company', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_company" id="wpshadow_company" 
						   value="<?php echo esc_attr( $company ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_company_url"><?php esc_html_e( 'Company URL', 'wpshadow' ); ?></label></th>
				<td>
					<input type="url" name="wpshadow_company_url" id="wpshadow_company_url" 
						   value="<?php echo esc_url( $company_url ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_position"><?php esc_html_e( 'Position/Title', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_position" id="wpshadow_position" 
						   value="<?php echo esc_attr( $position ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_verified"><?php esc_html_e( 'Verified Testimonial', 'wpshadow' ); ?></label></th>
				<td>
					<input type="checkbox" name="wpshadow_verified" id="wpshadow_verified" value="1" 
						   <?php checked( $verified, '1' ); ?> />
					<span class="description"><?php esc_html_e( 'Mark this as a verified testimonial', 'wpshadow' ); ?></span>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render team member fields.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public static function render_team_member_fields( $post ) {
		wp_nonce_field( 'wpshadow_team_member_meta', 'wpshadow_team_member_nonce' );

		$job_title = get_post_meta( $post->ID, '_wpshadow_job_title', true );
		$email     = get_post_meta( $post->ID, '_wpshadow_email', true );
		$phone     = get_post_meta( $post->ID, '_wpshadow_phone', true );
		$linkedin  = get_post_meta( $post->ID, '_wpshadow_linkedin', true );
		$twitter   = get_post_meta( $post->ID, '_wpshadow_twitter', true );
		$github    = get_post_meta( $post->ID, '_wpshadow_github', true );
		?>
		<table class="form-table">
			<tr>
				<th><label for="wpshadow_job_title"><?php esc_html_e( 'Job Title', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_job_title" id="wpshadow_job_title" 
						   value="<?php echo esc_attr( $job_title ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_email"><?php esc_html_e( 'Email', 'wpshadow' ); ?></label></th>
				<td>
					<input type="email" name="wpshadow_email" id="wpshadow_email" 
						   value="<?php echo esc_attr( $email ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_phone"><?php esc_html_e( 'Phone', 'wpshadow' ); ?></label></th>
				<td>
					<input type="tel" name="wpshadow_phone" id="wpshadow_phone" 
						   value="<?php echo esc_attr( $phone ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_linkedin"><?php esc_html_e( 'LinkedIn URL', 'wpshadow' ); ?></label></th>
				<td>
					<input type="url" name="wpshadow_linkedin" id="wpshadow_linkedin" 
						   value="<?php echo esc_url( $linkedin ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_twitter"><?php esc_html_e( 'Twitter/X URL', 'wpshadow' ); ?></label></th>
				<td>
					<input type="url" name="wpshadow_twitter" id="wpshadow_twitter" 
						   value="<?php echo esc_url( $twitter ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_github"><?php esc_html_e( 'GitHub URL', 'wpshadow' ); ?></label></th>
				<td>
					<input type="url" name="wpshadow_github" id="wpshadow_github" 
						   value="<?php echo esc_url( $github ); ?>" class="regular-text" />
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render portfolio fields.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public static function render_portfolio_fields( $post ) {
		wp_nonce_field( 'wpshadow_portfolio_meta', 'wpshadow_portfolio_nonce' );

		$client      = get_post_meta( $post->ID, '_wpshadow_client', true );
		$project_url = get_post_meta( $post->ID, '_wpshadow_project_url', true );
		$tech_stack  = get_post_meta( $post->ID, '_wpshadow_tech_stack', true );
		$year        = get_post_meta( $post->ID, '_wpshadow_year', true );
		?>
		<table class="form-table">
			<tr>
				<th><label for="wpshadow_client"><?php esc_html_e( 'Client Name', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_client" id="wpshadow_client" 
						   value="<?php echo esc_attr( $client ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_project_url"><?php esc_html_e( 'Project URL', 'wpshadow' ); ?></label></th>
				<td>
					<input type="url" name="wpshadow_project_url" id="wpshadow_project_url" 
						   value="<?php echo esc_url( $project_url ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_tech_stack"><?php esc_html_e( 'Technologies Used', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_tech_stack" id="wpshadow_tech_stack" 
						   value="<?php echo esc_attr( $tech_stack ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Comma-separated list (e.g., WordPress, React, PHP)', 'wpshadow' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_year"><?php esc_html_e( 'Year Completed', 'wpshadow' ); ?></label></th>
				<td>
					<input type="number" name="wpshadow_year" id="wpshadow_year" 
						   value="<?php echo esc_attr( $year ); ?>" min="2000" max="2100" />
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render event fields.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public static function render_event_fields( $post ) {
		wp_nonce_field( 'wpshadow_event_meta', 'wpshadow_event_nonce' );

		$start_date     = get_post_meta( $post->ID, '_wpshadow_event_start_date', true );
		$end_date       = get_post_meta( $post->ID, '_wpshadow_event_end_date', true );
		$location       = get_post_meta( $post->ID, '_wpshadow_event_location', true );
		$registration   = get_post_meta( $post->ID, '_wpshadow_event_registration_url', true );
		$is_virtual     = get_post_meta( $post->ID, '_wpshadow_event_is_virtual', true );
		$virtual_url    = get_post_meta( $post->ID, '_wpshadow_event_virtual_url', true );
		?>
		<table class="form-table">
			<tr>
				<th><label for="wpshadow_event_start_date"><?php esc_html_e( 'Start Date & Time', 'wpshadow' ); ?></label></th>
				<td>
					<input type="datetime-local" name="wpshadow_event_start_date" id="wpshadow_event_start_date" 
						   value="<?php echo esc_attr( $start_date ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_event_end_date"><?php esc_html_e( 'End Date & Time', 'wpshadow' ); ?></label></th>
				<td>
					<input type="datetime-local" name="wpshadow_event_end_date" id="wpshadow_event_end_date" 
						   value="<?php echo esc_attr( $end_date ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_event_location"><?php esc_html_e( 'Location', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_event_location" id="wpshadow_event_location" 
						   value="<?php echo esc_attr( $location ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_event_registration_url"><?php esc_html_e( 'Registration URL', 'wpshadow' ); ?></label></th>
				<td>
					<input type="url" name="wpshadow_event_registration_url" id="wpshadow_event_registration_url" 
						   value="<?php echo esc_url( $registration ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_event_is_virtual"><?php esc_html_e( 'Virtual Event', 'wpshadow' ); ?></label></th>
				<td>
					<input type="checkbox" name="wpshadow_event_is_virtual" id="wpshadow_event_is_virtual" value="1" 
						   <?php checked( $is_virtual, '1' ); ?> />
					<span class="description"><?php esc_html_e( 'Check if this is a virtual/online event', 'wpshadow' ); ?></span>
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_event_virtual_url"><?php esc_html_e( 'Virtual Event URL', 'wpshadow' ); ?></label></th>
				<td>
					<input type="url" name="wpshadow_event_virtual_url" id="wpshadow_event_virtual_url" 
						   value="<?php echo esc_url( $virtual_url ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Zoom, Teams, or other video conference link', 'wpshadow' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render resource fields.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public static function render_resource_fields( $post ) {
		wp_nonce_field( 'wpshadow_resource_meta', 'wpshadow_resource_nonce' );

		$file_url     = get_post_meta( $post->ID, '_wpshadow_resource_file_url', true );
		$file_format  = get_post_meta( $post->ID, '_wpshadow_resource_file_format', true );
		$file_size    = get_post_meta( $post->ID, '_wpshadow_resource_file_size', true );
		$download_count = get_post_meta( $post->ID, '_wpshadow_resource_download_count', true );
		?>
		<table class="form-table">
			<tr>
				<th><label for="wpshadow_resource_file_url"><?php esc_html_e( 'File URL', 'wpshadow' ); ?></label></th>
				<td>
					<input type="url" name="wpshadow_resource_file_url" id="wpshadow_resource_file_url" 
						   value="<?php echo esc_url( $file_url ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Link to downloadable file', 'wpshadow' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_resource_file_format"><?php esc_html_e( 'File Format', 'wpshadow' ); ?></label></th>
				<td>
					<select name="wpshadow_resource_file_format" id="wpshadow_resource_file_format">
						<option value=""><?php esc_html_e( 'Select format', 'wpshadow' ); ?></option>
						<?php
						$formats = array( 'PDF', 'DOC', 'DOCX', 'XLS', 'XLSX', 'PPT', 'PPTX', 'ZIP', 'MP4', 'MP3', 'Other' );
						foreach ( $formats as $format ) {
							echo '<option value="' . esc_attr( $format ) . '" ' . selected( $file_format, $format, false ) . '>' . esc_html( $format ) . '</option>';
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_resource_file_size"><?php esc_html_e( 'File Size', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_resource_file_size" id="wpshadow_resource_file_size" 
						   value="<?php echo esc_attr( $file_size ); ?>" class="small-text" />
					<span class="description"><?php esc_html_e( 'e.g., 2.5 MB', 'wpshadow' ); ?></span>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Download Count', 'wpshadow' ); ?></th>
				<td>
					<strong><?php echo esc_html( $download_count ? $download_count : '0' ); ?></strong>
					<p class="description"><?php esc_html_e( 'Automatically tracked', 'wpshadow' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render case study fields.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public static function render_case_study_fields( $post ) {
		wp_nonce_field( 'wpshadow_case_study_meta', 'wpshadow_case_study_nonce' );

		$client     = get_post_meta( $post->ID, '_wpshadow_case_study_client', true );
		$challenge  = get_post_meta( $post->ID, '_wpshadow_case_study_challenge', true );
		$solution   = get_post_meta( $post->ID, '_wpshadow_case_study_solution', true );
		$results    = get_post_meta( $post->ID, '_wpshadow_case_study_results', true );
		$metric_1   = get_post_meta( $post->ID, '_wpshadow_case_study_metric_1', true );
		$metric_2   = get_post_meta( $post->ID, '_wpshadow_case_study_metric_2', true );
		$metric_3   = get_post_meta( $post->ID, '_wpshadow_case_study_metric_3', true );
		?>
		<table class="form-table">
			<tr>
				<th><label for="wpshadow_case_study_client"><?php esc_html_e( 'Client Name', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_case_study_client" id="wpshadow_case_study_client" 
						   value="<?php echo esc_attr( $client ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_case_study_challenge"><?php esc_html_e( 'Challenge', 'wpshadow' ); ?></label></th>
				<td>
					<textarea name="wpshadow_case_study_challenge" id="wpshadow_case_study_challenge" 
							  rows="4" class="large-text"><?php echo esc_textarea( $challenge ); ?></textarea>
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_case_study_solution"><?php esc_html_e( 'Solution', 'wpshadow' ); ?></label></th>
				<td>
					<textarea name="wpshadow_case_study_solution" id="wpshadow_case_study_solution" 
							  rows="4" class="large-text"><?php echo esc_textarea( $solution ); ?></textarea>
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_case_study_results"><?php esc_html_e( 'Results', 'wpshadow' ); ?></label></th>
				<td>
					<textarea name="wpshadow_case_study_results" id="wpshadow_case_study_results" 
							  rows="4" class="large-text"><?php echo esc_textarea( $results ); ?></textarea>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Key Metrics', 'wpshadow' ); ?></th>
				<td>
					<p>
						<input type="text" name="wpshadow_case_study_metric_1" 
							   value="<?php echo esc_attr( $metric_1 ); ?>" class="regular-text" 
							   placeholder="<?php esc_attr_e( 'e.g., 150% increase in conversions', 'wpshadow' ); ?>" />
					</p>
					<p>
						<input type="text" name="wpshadow_case_study_metric_2" 
							   value="<?php echo esc_attr( $metric_2 ); ?>" class="regular-text" 
							   placeholder="<?php esc_attr_e( 'e.g., 40% reduction in costs', 'wpshadow' ); ?>" />
					</p>
					<p>
						<input type="text" name="wpshadow_case_study_metric_3" 
							   value="<?php echo esc_attr( $metric_3 ); ?>" class="regular-text" 
							   placeholder="<?php esc_attr_e( 'e.g., 2x ROI in 6 months', 'wpshadow' ); ?>" />
					</p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render service fields.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public static function render_service_fields( $post ) {
		wp_nonce_field( 'wpshadow_service_meta', 'wpshadow_service_nonce' );

		$price     = get_post_meta( $post->ID, '_wpshadow_service_price', true );
		$duration  = get_post_meta( $post->ID, '_wpshadow_service_duration', true );
		$booking   = get_post_meta( $post->ID, '_wpshadow_service_booking_url', true );
		$features  = get_post_meta( $post->ID, '_wpshadow_service_features', true );
		?>
		<table class="form-table">
			<tr>
				<th><label for="wpshadow_service_price"><?php esc_html_e( 'Price', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_service_price" id="wpshadow_service_price" 
						   value="<?php echo esc_attr( $price ); ?>" class="regular-text" 
						   placeholder="<?php esc_attr_e( 'e.g., $99/month or Contact for pricing', 'wpshadow' ); ?>" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_service_duration"><?php esc_html_e( 'Duration', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_service_duration" id="wpshadow_service_duration" 
						   value="<?php echo esc_attr( $duration ); ?>" class="regular-text" 
						   placeholder="<?php esc_attr_e( 'e.g., 2 hours, 4 weeks, Ongoing', 'wpshadow' ); ?>" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_service_booking_url"><?php esc_html_e( 'Booking URL', 'wpshadow' ); ?></label></th>
				<td>
					<input type="url" name="wpshadow_service_booking_url" id="wpshadow_service_booking_url" 
						   value="<?php echo esc_url( $booking ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_service_features"><?php esc_html_e( 'Key Features', 'wpshadow' ); ?></label></th>
				<td>
					<textarea name="wpshadow_service_features" id="wpshadow_service_features" 
							  rows="6" class="large-text"><?php echo esc_textarea( $features ); ?></textarea>
					<p class="description"><?php esc_html_e( 'One feature per line', 'wpshadow' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render location fields.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public static function render_location_fields( $post ) {
		wp_nonce_field( 'wpshadow_location_meta', 'wpshadow_location_nonce' );

		$address   = get_post_meta( $post->ID, '_wpshadow_location_address', true );
		$city      = get_post_meta( $post->ID, '_wpshadow_location_city', true );
		$state     = get_post_meta( $post->ID, '_wpshadow_location_state', true );
		$zip       = get_post_meta( $post->ID, '_wpshadow_location_zip', true );
		$country   = get_post_meta( $post->ID, '_wpshadow_location_country', true );
		$phone     = get_post_meta( $post->ID, '_wpshadow_location_phone', true );
		$email     = get_post_meta( $post->ID, '_wpshadow_location_email', true );
		$hours     = get_post_meta( $post->ID, '_wpshadow_location_hours', true );
		$latitude  = get_post_meta( $post->ID, '_wpshadow_location_latitude', true );
		$longitude = get_post_meta( $post->ID, '_wpshadow_location_longitude', true );
		?>
		<table class="form-table">
			<tr>
				<th><label for="wpshadow_location_address"><?php esc_html_e( 'Street Address', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_location_address" id="wpshadow_location_address" 
						   value="<?php echo esc_attr( $address ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_location_city"><?php esc_html_e( 'City', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_location_city" id="wpshadow_location_city" 
						   value="<?php echo esc_attr( $city ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_location_state"><?php esc_html_e( 'State/Province', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_location_state" id="wpshadow_location_state" 
						   value="<?php echo esc_attr( $state ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_location_zip"><?php esc_html_e( 'Zip/Postal Code', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_location_zip" id="wpshadow_location_zip" 
						   value="<?php echo esc_attr( $zip ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_location_country"><?php esc_html_e( 'Country', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_location_country" id="wpshadow_location_country" 
						   value="<?php echo esc_attr( $country ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_location_phone"><?php esc_html_e( 'Phone', 'wpshadow' ); ?></label></th>
				<td>
					<input type="tel" name="wpshadow_location_phone" id="wpshadow_location_phone" 
						   value="<?php echo esc_attr( $phone ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_location_email"><?php esc_html_e( 'Email', 'wpshadow' ); ?></label></th>
				<td>
					<input type="email" name="wpshadow_location_email" id="wpshadow_location_email" 
						   value="<?php echo esc_attr( $email ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_location_hours"><?php esc_html_e( 'Hours of Operation', 'wpshadow' ); ?></label></th>
				<td>
					<textarea name="wpshadow_location_hours" id="wpshadow_location_hours" 
							  rows="4" class="large-text"><?php echo esc_textarea( $hours ); ?></textarea>
					<p class="description"><?php esc_html_e( 'e.g., Mon-Fri: 9am-5pm', 'wpshadow' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Coordinates', 'wpshadow' ); ?></th>
				<td>
					<p>
						<label for="wpshadow_location_latitude"><?php esc_html_e( 'Latitude:', 'wpshadow' ); ?></label>
						<input type="text" name="wpshadow_location_latitude" id="wpshadow_location_latitude" 
							   value="<?php echo esc_attr( $latitude ); ?>" class="regular-text" 
							   placeholder="<?php esc_attr_e( 'e.g., 40.7128', 'wpshadow' ); ?>" />
					</p>
					<p>
						<label for="wpshadow_location_longitude"><?php esc_html_e( 'Longitude:', 'wpshadow' ); ?></label>
						<input type="text" name="wpshadow_location_longitude" id="wpshadow_location_longitude" 
							   value="<?php echo esc_attr( $longitude ); ?>" class="regular-text" 
							   placeholder="<?php esc_attr_e( 'e.g., -74.0060', 'wpshadow' ); ?>" />
					</p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render documentation fields.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public static function render_documentation_fields( $post ) {
		wp_nonce_field( 'wpshadow_documentation_meta', 'wpshadow_documentation_nonce' );

		$version    = get_post_meta( $post->ID, '_wpshadow_doc_version', true );
		$difficulty = get_post_meta( $post->ID, '_wpshadow_doc_difficulty', true );
		$read_time  = get_post_meta( $post->ID, '_wpshadow_doc_read_time', true );
		?>
		<table class="form-table">
			<tr>
				<th><label for="wpshadow_doc_version"><?php esc_html_e( 'Version', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_doc_version" id="wpshadow_doc_version" 
						   value="<?php echo esc_attr( $version ); ?>" class="regular-text" 
						   placeholder="<?php esc_attr_e( 'e.g., 2.0, Latest', 'wpshadow' ); ?>" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_doc_difficulty"><?php esc_html_e( 'Difficulty Level', 'wpshadow' ); ?></label></th>
				<td>
					<select name="wpshadow_doc_difficulty" id="wpshadow_doc_difficulty">
						<option value="beginner" <?php selected( $difficulty, 'beginner' ); ?>><?php esc_html_e( 'Beginner', 'wpshadow' ); ?></option>
						<option value="intermediate" <?php selected( $difficulty, 'intermediate' ); ?>><?php esc_html_e( 'Intermediate', 'wpshadow' ); ?></option>
						<option value="advanced" <?php selected( $difficulty, 'advanced' ); ?>><?php esc_html_e( 'Advanced', 'wpshadow' ); ?></option>
						<option value="expert" <?php selected( $difficulty, 'expert' ); ?>><?php esc_html_e( 'Expert', 'wpshadow' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_doc_read_time"><?php esc_html_e( 'Estimated Read Time', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_doc_read_time" id="wpshadow_doc_read_time" 
						   value="<?php echo esc_attr( $read_time ); ?>" class="regular-text" 
						   placeholder="<?php esc_attr_e( 'e.g., 5 minutes', 'wpshadow' ); ?>" />
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render product fields.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public static function render_product_fields( $post ) {
		wp_nonce_field( 'wpshadow_product_meta', 'wpshadow_product_nonce' );

		$sku          = get_post_meta( $post->ID, '_wpshadow_product_sku', true );
		$price        = get_post_meta( $post->ID, '_wpshadow_product_price', true );
		$sale_price   = get_post_meta( $post->ID, '_wpshadow_product_sale_price', true );
		$in_stock     = get_post_meta( $post->ID, '_wpshadow_product_in_stock', true );
		$purchase_url = get_post_meta( $post->ID, '_wpshadow_product_purchase_url', true );
		?>
		<table class="form-table">
			<tr>
				<th><label for="wpshadow_product_sku"><?php esc_html_e( 'SKU', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_product_sku" id="wpshadow_product_sku" 
						   value="<?php echo esc_attr( $sku ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_product_price"><?php esc_html_e( 'Regular Price', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_product_price" id="wpshadow_product_price" 
						   value="<?php echo esc_attr( $price ); ?>" class="regular-text" 
						   placeholder="<?php esc_attr_e( 'e.g., 29.99', 'wpshadow' ); ?>" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_product_sale_price"><?php esc_html_e( 'Sale Price', 'wpshadow' ); ?></label></th>
				<td>
					<input type="text" name="wpshadow_product_sale_price" id="wpshadow_product_sale_price" 
						   value="<?php echo esc_attr( $sale_price ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_product_in_stock"><?php esc_html_e( 'In Stock', 'wpshadow' ); ?></label></th>
				<td>
					<input type="checkbox" name="wpshadow_product_in_stock" id="wpshadow_product_in_stock" value="1" 
						   <?php checked( $in_stock, '1' ); ?> />
					<span class="description"><?php esc_html_e( 'Check if product is currently in stock', 'wpshadow' ); ?></span>
				</td>
			</tr>
			<tr>
				<th><label for="wpshadow_product_purchase_url"><?php esc_html_e( 'Purchase URL', 'wpshadow' ); ?></label></th>
				<td>
					<input type="url" name="wpshadow_product_purchase_url" id="wpshadow_product_purchase_url" 
						   value="<?php echo esc_url( $purchase_url ); ?>" class="regular-text" />
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save all meta boxes.
	 *
	 * @since 1.6093.1200
	 * @param  int      $post_id Post ID.
	 * @param  \WP_Post $post    Post object.
	 * @return void
	 */
	public static function save_meta_boxes( $post_id, $post ) {
		// Check autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check post type and nonce based on CPT.
		$post_type = $post->post_type;

		switch ( $post_type ) {
			case 'testimonial':
				self::save_testimonial_meta( $post_id );
				break;
			case 'team_member':
				self::save_team_member_meta( $post_id );
				break;
			case 'portfolio':
				self::save_portfolio_meta( $post_id );
				break;
			case 'event':
				self::save_event_meta( $post_id );
				break;
			case 'resource':
				self::save_resource_meta( $post_id );
				break;
			case 'case_study':
				self::save_case_study_meta( $post_id );
				break;
			case 'service':
				self::save_service_meta( $post_id );
				break;
			case 'location':
				self::save_location_meta( $post_id );
				break;
			case 'documentation':
				self::save_documentation_meta( $post_id );
				break;
			case 'product':
				self::save_product_meta( $post_id );
				break;
		}
	}

	/**
	 * Save testimonial meta.
	 *
	 * @since 1.6093.1200
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	private static function save_testimonial_meta( $post_id ) {
		if ( ! isset( $_POST['wpshadow_testimonial_nonce'] ) || ! wp_verify_nonce( $_POST['wpshadow_testimonial_nonce'], 'wpshadow_testimonial_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'wpshadow_rating'      => 'absint',
			'wpshadow_company'     => 'sanitize_text_field',
			'wpshadow_company_url' => 'esc_url_raw',
			'wpshadow_position'    => 'sanitize_text_field',
			'wpshadow_verified'    => 'rest_sanitize_boolean',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = call_user_func( $sanitize_callback, wp_unslash( $_POST[ $field ] ) );
				update_post_meta( $post_id, '_' . $field, $value );
			} else {
				delete_post_meta( $post_id, '_' . $field );
			}
		}
	}

	/**
	 * Save team member meta.
	 *
	 * @since 1.6093.1200
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	private static function save_team_member_meta( $post_id ) {
		if ( ! isset( $_POST['wpshadow_team_member_nonce'] ) || ! wp_verify_nonce( $_POST['wpshadow_team_member_nonce'], 'wpshadow_team_member_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'wpshadow_job_title' => 'sanitize_text_field',
			'wpshadow_email'     => 'sanitize_email',
			'wpshadow_phone'     => 'sanitize_text_field',
			'wpshadow_linkedin'  => 'esc_url_raw',
			'wpshadow_twitter'   => 'esc_url_raw',
			'wpshadow_github'    => 'esc_url_raw',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = call_user_func( $sanitize_callback, wp_unslash( $_POST[ $field ] ) );
				update_post_meta( $post_id, '_' . $field, $value );
			}
		}
	}

	/**
	 * Save portfolio meta.
	 *
	 * @since 1.6093.1200
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	private static function save_portfolio_meta( $post_id ) {
		if ( ! isset( $_POST['wpshadow_portfolio_nonce'] ) || ! wp_verify_nonce( $_POST['wpshadow_portfolio_nonce'], 'wpshadow_portfolio_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'wpshadow_client'      => 'sanitize_text_field',
			'wpshadow_project_url' => 'esc_url_raw',
			'wpshadow_tech_stack'  => 'sanitize_text_field',
			'wpshadow_year'        => 'absint',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = call_user_func( $sanitize_callback, wp_unslash( $_POST[ $field ] ) );
				update_post_meta( $post_id, '_' . $field, $value );
			}
		}
	}

	/**
	 * Save event meta.
	 *
	 * @since 1.6093.1200
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	private static function save_event_meta( $post_id ) {
		if ( ! isset( $_POST['wpshadow_event_nonce'] ) || ! wp_verify_nonce( $_POST['wpshadow_event_nonce'], 'wpshadow_event_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'wpshadow_event_start_date'       => 'sanitize_text_field',
			'wpshadow_event_end_date'         => 'sanitize_text_field',
			'wpshadow_event_location'         => 'sanitize_text_field',
			'wpshadow_event_registration_url' => 'esc_url_raw',
			'wpshadow_event_is_virtual'       => 'rest_sanitize_boolean',
			'wpshadow_event_virtual_url'      => 'esc_url_raw',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = call_user_func( $sanitize_callback, wp_unslash( $_POST[ $field ] ) );
				update_post_meta( $post_id, '_' . $field, $value );
			} else {
				delete_post_meta( $post_id, '_' . $field );
			}
		}
	}

	/**
	 * Save resource meta.
	 *
	 * @since 1.6093.1200
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	private static function save_resource_meta( $post_id ) {
		if ( ! isset( $_POST['wpshadow_resource_nonce'] ) || ! wp_verify_nonce( $_POST['wpshadow_resource_nonce'], 'wpshadow_resource_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'wpshadow_resource_file_url'    => 'esc_url_raw',
			'wpshadow_resource_file_format' => 'sanitize_text_field',
			'wpshadow_resource_file_size'   => 'sanitize_text_field',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = call_user_func( $sanitize_callback, wp_unslash( $_POST[ $field ] ) );
				update_post_meta( $post_id, '_' . $field, $value );
			}
		}

		// Initialize download count if not exists.
		if ( ! get_post_meta( $post_id, '_wpshadow_resource_download_count', true ) ) {
			update_post_meta( $post_id, '_wpshadow_resource_download_count', 0 );
		}
	}

	/**
	 * Save case study meta.
	 *
	 * @since 1.6093.1200
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	private static function save_case_study_meta( $post_id ) {
		if ( ! isset( $_POST['wpshadow_case_study_nonce'] ) || ! wp_verify_nonce( $_POST['wpshadow_case_study_nonce'], 'wpshadow_case_study_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'wpshadow_case_study_client'   => 'sanitize_text_field',
			'wpshadow_case_study_challenge' => 'sanitize_textarea_field',
			'wpshadow_case_study_solution' => 'sanitize_textarea_field',
			'wpshadow_case_study_results'  => 'sanitize_textarea_field',
			'wpshadow_case_study_metric_1' => 'sanitize_text_field',
			'wpshadow_case_study_metric_2' => 'sanitize_text_field',
			'wpshadow_case_study_metric_3' => 'sanitize_text_field',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = call_user_func( $sanitize_callback, wp_unslash( $_POST[ $field ] ) );
				update_post_meta( $post_id, '_' . $field, $value );
			}
		}
	}

	/**
	 * Save service meta.
	 *
	 * @since 1.6093.1200
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	private static function save_service_meta( $post_id ) {
		if ( ! isset( $_POST['wpshadow_service_nonce'] ) || ! wp_verify_nonce( $_POST['wpshadow_service_nonce'], 'wpshadow_service_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'wpshadow_service_price'       => 'sanitize_text_field',
			'wpshadow_service_duration'    => 'sanitize_text_field',
			'wpshadow_service_booking_url' => 'esc_url_raw',
			'wpshadow_service_features'    => 'sanitize_textarea_field',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = call_user_func( $sanitize_callback, wp_unslash( $_POST[ $field ] ) );
				update_post_meta( $post_id, '_' . $field, $value );
			}
		}
	}

	/**
	 * Save location meta.
	 *
	 * @since 1.6093.1200
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	private static function save_location_meta( $post_id ) {
		if ( ! isset( $_POST['wpshadow_location_nonce'] ) || ! wp_verify_nonce( $_POST['wpshadow_location_nonce'], 'wpshadow_location_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'wpshadow_location_address'   => 'sanitize_text_field',
			'wpshadow_location_city'      => 'sanitize_text_field',
			'wpshadow_location_state'     => 'sanitize_text_field',
			'wpshadow_location_zip'       => 'sanitize_text_field',
			'wpshadow_location_country'   => 'sanitize_text_field',
			'wpshadow_location_phone'     => 'sanitize_text_field',
			'wpshadow_location_email'     => 'sanitize_email',
			'wpshadow_location_hours'     => 'sanitize_textarea_field',
			'wpshadow_location_latitude'  => 'sanitize_text_field',
			'wpshadow_location_longitude' => 'sanitize_text_field',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = call_user_func( $sanitize_callback, wp_unslash( $_POST[ $field ] ) );
				update_post_meta( $post_id, '_' . $field, $value );
			}
		}
	}

	/**
	 * Save documentation meta.
	 *
	 * @since 1.6093.1200
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	private static function save_documentation_meta( $post_id ) {
		if ( ! isset( $_POST['wpshadow_documentation_nonce'] ) || ! wp_verify_nonce( $_POST['wpshadow_documentation_nonce'], 'wpshadow_documentation_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'wpshadow_doc_version'    => 'sanitize_text_field',
			'wpshadow_doc_difficulty' => 'sanitize_text_field',
			'wpshadow_doc_read_time'  => 'sanitize_text_field',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = call_user_func( $sanitize_callback, wp_unslash( $_POST[ $field ] ) );
				update_post_meta( $post_id, '_' . $field, $value );
			}
		}
	}

	/**
	 * Save product meta.
	 *
	 * @since 1.6093.1200
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	private static function save_product_meta( $post_id ) {
		if ( ! isset( $_POST['wpshadow_product_nonce'] ) || ! wp_verify_nonce( $_POST['wpshadow_product_nonce'], 'wpshadow_product_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'wpshadow_product_sku'          => 'sanitize_text_field',
			'wpshadow_product_price'        => 'sanitize_text_field',
			'wpshadow_product_sale_price'   => 'sanitize_text_field',
			'wpshadow_product_in_stock'     => 'rest_sanitize_boolean',
			'wpshadow_product_purchase_url' => 'esc_url_raw',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = call_user_func( $sanitize_callback, wp_unslash( $_POST[ $field ] ) );
				update_post_meta( $post_id, '_' . $field, $value );
			} else {
				delete_post_meta( $post_id, '_' . $field );
			}
		}
	}

	/**
	 * Get meta value helper.
	 *
	 * @since 1.6093.1200
	 * @param  int    $post_id Post ID.
	 * @param  string $key     Meta key (without underscore prefix).
	 * @param  mixed  $default Default value if not found.
	 * @return mixed Meta value or default.
	 */
	public static function get_meta( $post_id, $key, $default = '' ) {
		$value = get_post_meta( $post_id, '_wpshadow_' . $key, true );
		return $value !== '' ? $value : $default;
	}
}
