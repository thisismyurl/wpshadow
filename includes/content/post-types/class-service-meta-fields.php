<?php
/**
 * Service post type meta field definitions and helpers.
 *
 * Provides structured meta field definitions for Service CPT including
 * pricing, duration, status, and deliverables.
 *
 * @package WPShadow
 * @subpackage Content\Post_Types
 * @since 0.6096
 */

declare(strict_types=1);

namespace WPShadow\Content\Post_Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service meta field definitions and retrieval helpers.
 */
class Service_Meta_Fields {

	/**
	 * Meta prefix for all service fields.
	 */
	private const META_PREFIX = '_service_';

	/**
	 * Available meta field keys.
	 */
	private const META_FIELDS = array(
		'status',
		'pricing',
		'duration',
		'deliverables',
	);

	/**
	 * Service status values.
	 */
	public const STATUS_ACTIVE      = 'active';
	public const STATUS_COMING_SOON = 'coming_soon';
	public const STATUS_SEASONAL    = 'seasonal';
	public const STATUS_ENDED       = 'ended';

	/**
	 * Ensure hooks are only added once.
	 *
	 * @var bool
	 */
	private static $bootstrapped = false;

	/**
	 * Wire REST meta registration.
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( self::$bootstrapped ) {
			return;
		}

		self::$bootstrapped = true;

		add_action( 'init', array( __CLASS__, 'register_meta_fields' ), 10 );
		add_action( 'add_meta_boxes_service', array( __CLASS__, 'register_meta_box' ) );
		add_action( 'save_post_service', array( __CLASS__, 'save_meta_box' ), 10, 2 );
	}

	/**
	 * Register Service details metabox.
	 *
	 * @return void
	 */
	public static function register_meta_box(): void {
		add_meta_box(
			'wpshadow-service-details',
			__( 'Service Details', 'wpshadow' ),
			array( __CLASS__, 'render_meta_box' ),
			'service',
			'normal',
			'default'
		);
	}

	/**
	 * Render Service details metabox.
	 *
	 * @param \WP_Post $post Current post.
	 * @return void
	 */
	public static function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'wpshadow_service_meta_box', 'wpshadow_service_meta_box_nonce' );

		$status       = self::get_status( $post->ID );
		$pricing      = self::get_pricing( $post->ID );
		$duration     = self::get_duration( $post->ID );
		$deliverables = self::get_deliverables( $post->ID );
		$packages     = self::get_packages( $post->ID );

		$currency   = isset( $pricing['currency'] ) ? (string) $pricing['currency'] : 'USD';
		$base_price = isset( $pricing['base_price'] ) ? (string) $pricing['base_price'] : '';
		$min_price  = isset( $pricing['min_price'] ) ? (string) $pricing['min_price'] : '';
		$max_price  = isset( $pricing['max_price'] ) ? (string) $pricing['max_price'] : '';

		$duration_value = isset( $duration['value'] ) ? (string) $duration['value'] : '';
		$duration_unit  = isset( $duration['unit'] ) ? (string) $duration['unit'] : 'days';

		$deliverables_text = implode( "\n", $deliverables );

		if ( empty( $packages ) ) {
			$packages = array(
				array(
					'name'        => '',
					'price'       => '',
					'description' => '',
				),
			);
		}
		?>
		<p>
			<label for="wpshadow_service_status"><strong><?php esc_html_e( 'Service Status', 'wpshadow' ); ?></strong></label><br />
			<select id="wpshadow_service_status" name="wpshadow_service_status">
				<?php foreach ( self::get_status_options() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $status, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<hr />
		<p><strong><?php esc_html_e( 'Pricing', 'wpshadow' ); ?></strong></p>
		<p>
			<label for="wpshadow_service_currency"><?php esc_html_e( 'Currency', 'wpshadow' ); ?></label><br />
			<input type="text" id="wpshadow_service_currency" name="wpshadow_service_currency" value="<?php echo esc_attr( $currency ); ?>" maxlength="6" style="width: 100px;" />
		</p>
		<p>
			<label for="wpshadow_service_base_price"><?php esc_html_e( 'Base Price', 'wpshadow' ); ?></label><br />
			<input type="number" step="0.01" min="0" id="wpshadow_service_base_price" name="wpshadow_service_base_price" value="<?php echo esc_attr( $base_price ); ?>" style="width: 180px;" />
		</p>
		<p>
			<label for="wpshadow_service_min_price"><?php esc_html_e( 'Price Range (Min / Max)', 'wpshadow' ); ?></label><br />
			<input type="number" step="0.01" min="0" id="wpshadow_service_min_price" name="wpshadow_service_min_price" value="<?php echo esc_attr( $min_price ); ?>" style="width: 120px;" />
			<input type="number" step="0.01" min="0" id="wpshadow_service_max_price" name="wpshadow_service_max_price" value="<?php echo esc_attr( $max_price ); ?>" style="width: 120px;" />
		</p>

		<hr />
		<p><strong><?php esc_html_e( 'Service Packages', 'wpshadow' ); ?></strong></p>
		<table class="widefat striped" id="wpshadow-service-packages-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Package Name', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Price', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Description', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Remove', 'wpshadow' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $packages as $package ) : ?>
					<tr>
						<td><input type="text" name="wpshadow_service_packages[name][]" value="<?php echo esc_attr( isset( $package['name'] ) ? (string) $package['name'] : '' ); ?>" style="width: 100%;" /></td>
						<td><input type="number" step="0.01" min="0" name="wpshadow_service_packages[price][]" value="<?php echo esc_attr( isset( $package['price'] ) ? (string) $package['price'] : '' ); ?>" style="width: 120px;" /></td>
						<td><input type="text" name="wpshadow_service_packages[description][]" value="<?php echo esc_attr( isset( $package['description'] ) ? (string) $package['description'] : '' ); ?>" style="width: 100%;" /></td>
						<td><button type="button" class="button wpshadow-remove-package"><?php esc_html_e( 'Remove', 'wpshadow' ); ?></button></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p><button type="button" class="button" id="wpshadow-add-package"><?php esc_html_e( 'Add Package', 'wpshadow' ); ?></button></p>

		<hr />
		<p><strong><?php esc_html_e( 'Estimated Duration', 'wpshadow' ); ?></strong></p>
		<p>
			<input type="number" min="1" step="1" name="wpshadow_service_duration_value" value="<?php echo esc_attr( $duration_value ); ?>" style="width: 120px;" />
			<select name="wpshadow_service_duration_unit">
				<option value="days" <?php selected( $duration_unit, 'days' ); ?>><?php esc_html_e( 'Days', 'wpshadow' ); ?></option>
				<option value="weeks" <?php selected( $duration_unit, 'weeks' ); ?>><?php esc_html_e( 'Weeks', 'wpshadow' ); ?></option>
				<option value="months" <?php selected( $duration_unit, 'months' ); ?>><?php esc_html_e( 'Months', 'wpshadow' ); ?></option>
			</select>
		</p>

		<hr />
		<p><strong><?php esc_html_e( 'Deliverables', 'wpshadow' ); ?></strong></p>
		<p>
			<textarea name="wpshadow_service_deliverables" rows="6" style="width: 100%;"><?php echo esc_textarea( $deliverables_text ); ?></textarea><br />
			<small><?php esc_html_e( 'Enter one deliverable per line.', 'wpshadow' ); ?></small>
		</p>

		<script>
		(function() {
			const table = document.getElementById('wpshadow-service-packages-table');
			const addButton = document.getElementById('wpshadow-add-package');
			if (!table || !addButton) {
				return;
			}

			const makeRow = () => {
				const tr = document.createElement('tr');
				tr.innerHTML = '<td><input type="text" name="wpshadow_service_packages[name][]" value="" style="width: 100%;" /></td>' +
					'<td><input type="number" step="0.01" min="0" name="wpshadow_service_packages[price][]" value="" style="width: 120px;" /></td>' +
					'<td><input type="text" name="wpshadow_service_packages[description][]" value="" style="width: 100%;" /></td>' +
					'<td><button type="button" class="button wpshadow-remove-package">Remove</button></td>';
				return tr;
			};

			addButton.addEventListener('click', () => {
				table.querySelector('tbody').appendChild(makeRow());
			});

			table.addEventListener('click', (event) => {
				if (!event.target.classList.contains('wpshadow-remove-package')) {
					return;
				}

				const row = event.target.closest('tr');
				if (!row) {
					return;
				}

				const rows = table.querySelectorAll('tbody tr');
				if (rows.length <= 1) {
					row.querySelectorAll('input').forEach((input) => {
						input.value = '';
					});
					return;
				}

				row.remove();
			});
		})();
		</script>
		<?php
	}

	/**
	 * Save Service details from metabox.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public static function save_meta_box( int $post_id, \WP_Post $post ): void {
		if ( ! isset( $_POST['wpshadow_service_meta_box_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['wpshadow_service_meta_box_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'wpshadow_service_meta_box' ) ) {
			return;
		}

		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( 'service' !== $post->post_type || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$status_options = array_keys( self::get_status_options() );
		$raw_status     = isset( $_POST['wpshadow_service_status'] ) ? sanitize_key( wp_unslash( $_POST['wpshadow_service_status'] ) ) : self::STATUS_ACTIVE;
		$status         = in_array( $raw_status, $status_options, true ) ? $raw_status : self::STATUS_ACTIVE;
		self::set_status( $post_id, $status );

		$currency   = isset( $_POST['wpshadow_service_currency'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['wpshadow_service_currency'] ) ) ) : 'USD';
		$base_price = isset( $_POST['wpshadow_service_base_price'] ) ? (float) wp_unslash( $_POST['wpshadow_service_base_price'] ) : 0.0;
		$min_price  = isset( $_POST['wpshadow_service_min_price'] ) ? (float) wp_unslash( $_POST['wpshadow_service_min_price'] ) : 0.0;
		$max_price  = isset( $_POST['wpshadow_service_max_price'] ) ? (float) wp_unslash( $_POST['wpshadow_service_max_price'] ) : 0.0;

		$packages = self::sanitize_packages_from_request();

		$pricing = array(
			'model'    => ! empty( $packages ) ? 'tiered' : 'standard',
			'currency' => $currency,
			'tiers'    => $packages,
		);

		if ( $base_price > 0 ) {
			$pricing['base_price'] = $base_price;
		}

		if ( $min_price > 0 ) {
			$pricing['min_price'] = $min_price;
		}

		if ( $max_price > 0 ) {
			$pricing['max_price'] = $max_price;
		}

		self::set_pricing( $post_id, $pricing );

		$duration_value = isset( $_POST['wpshadow_service_duration_value'] ) ? absint( wp_unslash( $_POST['wpshadow_service_duration_value'] ) ) : 0;
		$duration_unit  = isset( $_POST['wpshadow_service_duration_unit'] ) ? sanitize_key( wp_unslash( $_POST['wpshadow_service_duration_unit'] ) ) : 'days';
		if ( ! in_array( $duration_unit, array( 'days', 'weeks', 'months' ), true ) ) {
			$duration_unit = 'days';
		}

		if ( $duration_value > 0 ) {
			self::set_duration(
				$post_id,
				array(
					'value' => $duration_value,
					'unit'  => $duration_unit,
				)
			);
		} else {
			self::set_duration( $post_id, array() );
		}

		$deliverables_raw = isset( $_POST['wpshadow_service_deliverables'] ) ? (string) wp_unslash( $_POST['wpshadow_service_deliverables'] ) : '';
		$lines            = preg_split( '/\r\n|\r|\n/', $deliverables_raw );
		$deliverables     = array();

		if ( is_array( $lines ) ) {
			foreach ( $lines as $line ) {
				$clean = sanitize_text_field( trim( (string) $line ) );
				if ( '' !== $clean ) {
					$deliverables[] = $clean;
				}
			}
		}

		self::set_deliverables( $post_id, $deliverables );
	}

	/**
	 * Sanitize package rows from request payload.
	 *
	 * @return array<int,array{name:string,price:string,description:string}>
	 */
	private static function sanitize_packages_from_request(): array {
		if ( ! isset( $_POST['wpshadow_service_packages'] ) || ! is_array( $_POST['wpshadow_service_packages'] ) ) {
			return array();
		}

		$raw           = wp_unslash( $_POST['wpshadow_service_packages'] );
		$names         = isset( $raw['name'] ) && is_array( $raw['name'] ) ? $raw['name'] : array();
		$prices        = isset( $raw['price'] ) && is_array( $raw['price'] ) ? $raw['price'] : array();
		$descriptions  = isset( $raw['description'] ) && is_array( $raw['description'] ) ? $raw['description'] : array();
		$count         = max( count( $names ), count( $prices ), count( $descriptions ) );
		$clean_packages = array();

		for ( $i = 0; $i < $count; $i++ ) {
			$name        = isset( $names[ $i ] ) ? sanitize_text_field( (string) $names[ $i ] ) : '';
			$price_value = isset( $prices[ $i ] ) ? (float) $prices[ $i ] : 0.0;
			$description = isset( $descriptions[ $i ] ) ? sanitize_text_field( (string) $descriptions[ $i ] ) : '';

			if ( '' === $name && 0.0 === $price_value && '' === $description ) {
				continue;
			}

			$clean_packages[] = array(
				'name'        => $name,
				'price'       => $price_value > 0 ? (string) $price_value : '',
				'description' => $description,
			);
		}

		return $clean_packages;
	}

	/**
	 * Get service package list.
	 *
	 * @param int $post_id Post ID.
	 * @return array<int,array{name?:string,price?:string,description?:string}>
	 */
	public static function get_packages( int $post_id ): array {
		$pricing = self::get_pricing( $post_id );
		if ( empty( $pricing['tiers'] ) || ! is_array( $pricing['tiers'] ) ) {
			return array();
		}

		$packages = array();
		foreach ( $pricing['tiers'] as $tier ) {
			if ( ! is_array( $tier ) ) {
				continue;
			}

			$packages[] = array(
				'name'        => isset( $tier['name'] ) ? (string) $tier['name'] : '',
				'price'       => isset( $tier['price'] ) ? (string) $tier['price'] : '',
				'description' => isset( $tier['description'] ) ? (string) $tier['description'] : '',
			);
		}

		return $packages;
	}

	/**
	 * Register meta fields for REST API and WordPress.
	 *
	 * @return void
	 */
	public static function register_meta_fields(): void {
		$args = array(
			'object_subtype' => 'service',
			'type'           => 'string',
			'description'    => 'Service metadata',
			'single'         => true,
			'show_in_rest'   => true,
		);

		// Register each meta field.
		foreach ( self::META_FIELDS as $field ) {
			register_meta( 'post', self::META_PREFIX . $field, $args );
		}
	}

	/**
	 * Get service status.
	 *
	 * @param int $post_id Post ID.
	 * @return string One of: active, coming_soon, seasonal, ended.
	 */
	public static function get_status( int $post_id ): string {
		$status = get_post_meta( $post_id, self::META_PREFIX . 'status', true );
		return ! empty( $status ) ? $status : self::STATUS_ACTIVE;
	}

	/**
	 * Set service status.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $status  Status value.
	 * @return int|bool Meta ID or false.
	 */
	public static function set_status( int $post_id, string $status ) {
		return update_post_meta( $post_id, self::META_PREFIX . 'status', $status );
	}

	/**
	 * Get service pricing information.
	 *
	 * @param int $post_id Post ID.
	 * @return array{model?:string,base_price?:float,min_price?:float,max_price?:float,currency?:string,tiers?:array}
	 */
	public static function get_pricing( int $post_id ): array {
		$pricing = get_post_meta( $post_id, self::META_PREFIX . 'pricing', true );
		if ( empty( $pricing ) ) {
			return array();
		}

		if ( is_string( $pricing ) ) {
			$pricing = json_decode( $pricing, true );
		}

		return is_array( $pricing ) ? $pricing : array();
	}

	/**
	 * Set service pricing information.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $pricing Pricing array.
	 * @return int|bool Meta ID or false.
	 */
	public static function set_pricing( int $post_id, array $pricing ) {
		return update_post_meta( $post_id, self::META_PREFIX . 'pricing', wp_json_encode( $pricing ) );
	}

	/**
	 * Get formatted pricing for display.
	 *
	 * @param int $post_id Post ID.
	 * @return string Formatted pricing string.
	 */
	public static function get_pricing_display( int $post_id ): string {
		$pricing = self::get_pricing( $post_id );

		if ( empty( $pricing ) ) {
			return 'Contact for pricing';
		}

		$currency = $pricing['currency'] ?? 'USD';
		$symbol   = self::get_currency_symbol( $currency );

		if ( ! empty( $pricing['base_price'] ) ) {
			return sprintf( '%s%s', $symbol, number_format( (float) $pricing['base_price'], 0 ) );
		}

		if ( ! empty( $pricing['min_price'] ) && ! empty( $pricing['max_price'] ) ) {
			return sprintf(
				'%s%s - %s%s',
				$symbol,
				number_format( (float) $pricing['min_price'], 0 ),
				$symbol,
				number_format( (float) $pricing['max_price'], 0 )
			);
		}

		return 'Contact for pricing';
	}

	/**
	 * Get service duration.
	 *
	 * @param int $post_id Post ID.
	 * @return array{value?:int,unit?:string}
	 */
	public static function get_duration( int $post_id ): array {
		$duration = get_post_meta( $post_id, self::META_PREFIX . 'duration', true );
		if ( empty( $duration ) ) {
			return array();
		}

		if ( is_string( $duration ) ) {
			$duration = json_decode( $duration, true );
		}

		return is_array( $duration ) ? $duration : array();
	}

	/**
	 * Set service duration.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $duration Duration array with 'value' and 'unit'.
	 * @return int|bool Meta ID or false.
	 */
	public static function set_duration( int $post_id, array $duration ) {
		return update_post_meta( $post_id, self::META_PREFIX . 'duration', wp_json_encode( $duration ) );
	}

	/**
	 * Get formatted duration for display.
	 *
	 * @param int $post_id Post ID.
	 * @return string Formatted duration string.
	 */
	public static function get_duration_display( int $post_id ): string {
		$duration = self::get_duration( $post_id );

		if ( empty( $duration ) || empty( $duration['value'] ) ) {
			return '';
		}

		$value = (int) $duration['value'];
		$unit  = $duration['unit'] ?? 'days';

		return sprintf( '%d %s', $value, esc_html( $unit ) );
	}

	/**
	 * Get service deliverables.
	 *
	 * @param int $post_id Post ID.
	 * @return array<string>
	 */
	public static function get_deliverables( int $post_id ): array {
		$deliverables = get_post_meta( $post_id, self::META_PREFIX . 'deliverables', true );
		if ( empty( $deliverables ) ) {
			return array();
		}

		if ( is_string( $deliverables ) ) {
			$deliverables = json_decode( $deliverables, true );
		}

		return is_array( $deliverables ) ? $deliverables : array();
	}

	/**
	 * Set service deliverables.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $deliverables Array of deliverable descriptions.
	 * @return int|bool Meta ID or false.
	 */
	public static function set_deliverables( int $post_id, array $deliverables ) {
		return update_post_meta( $post_id, self::META_PREFIX . 'deliverables', wp_json_encode( $deliverables ) );
	}

	/**
	 * Get currency symbol from currency code.
	 *
	 * @param string $currency Currency code (USD, EUR, GBP, etc.).
	 * @return string Currency symbol.
	 */
	private static function get_currency_symbol( string $currency ): string {
		$symbols = array(
			'USD' => '$',
			'EUR' => '€',
			'GBP' => '£',
			'JPY' => '¥',
			'INR' => '₹',
			'AUD' => 'A$',
			'CAD' => 'C$',
			'CHF' => 'CHF ',
			'CNY' => '¥',
			'SEK' => 'kr',
		);

		return $symbols[ strtoupper( $currency ) ] ?? $currency . ' ';
	}

	/**
	 * Get all available status options.
	 *
	 * @return array<string,string>
	 */
	public static function get_status_options(): array {
		return array(
			self::STATUS_ACTIVE      => __( 'Active', 'wpshadow' ),
			self::STATUS_COMING_SOON => __( 'Coming Soon', 'wpshadow' ),
			self::STATUS_SEASONAL    => __( 'Seasonal', 'wpshadow' ),
			self::STATUS_ENDED       => __( 'Ended / Discontinued', 'wpshadow' ),
		);
	}
}
