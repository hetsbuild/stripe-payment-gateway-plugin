<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Pg
 * @subpackage Pg/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-specific stylesheet and JavaScript.
 *
 * @package    Pg
 * @subpackage Pg/public
 * @author     Het Agravat <het.agravat@gmail.com>
 */
class Pg_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name The name of this plugin.
	 * @param    string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		add_shortcode(
			'pg_payment',
			array( $this, 'payment_shortcode' )
		);

		add_action(
			'admin_post_pg_create_checkout',
			array( $this, 'create_checkout' )
		);

		add_action(
			'admin_post_nopriv_pg_create_checkout',
			array( $this, 'create_checkout' )
		);

		add_shortcode(
			'pg_payment_result',
			array( $this, 'payment_result_shortcode' )
		);

		add_shortcode(
			'pg_payment_cancelled',
			array( $this, 'payment_cancelled_shortcode' )
		);

		add_action(
			'template_redirect',
			array( $this, 'send_no_cache_headers' )
		);

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/pg-public.css',
			array(),
			$this->version,
			'all'
		);

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/pg-public.js',
			array( 'jquery' ),
			$this->version,
			false
		);

	}

	/**
	 * Display payment button.
	 *
	 * Shortcode: [pg_payment]
	 *
	 * @since 1.0.0
 *
	 * @return string Payment button HTML.
	 */
	public function payment_shortcode() {

		ob_start();

		?>

		<div class="pg-payment">

			<h3>
				<?php esc_html_e( 'Test Product', 'pg' ); ?>
			</h3>

			<p>
				<?php esc_html_e( 'Price: ₹500', 'pg' ); ?>
			</p>

			<a
				href="<?php
				echo esc_url(
					wp_nonce_url(
						admin_url(
							'admin-post.php?action=pg_create_checkout'
						),
						'pg_create_checkout'
					)
				);
				?>"
				class="button"
			>
				<?php esc_html_e( 'Pay ₹500', 'pg' ); ?>
			</a>

		</div>

		<?php

		return ob_get_clean();

	}

	/**
	 * Create Stripe Checkout Session.
	 *
	 * @since 1.0.0
	 */
	public function create_checkout() {

		/*
		 * Verify nonce.
		 */
		check_admin_referer( 'pg_create_checkout' );

		/*
		 * Get Stripe secret key.
		 */
		$secret_key = $this->get_secret_key();

		/*
		 * Make sure the secret key exists.
		 */
		if ( empty( $secret_key ) ) {

			wp_die(
				esc_html__(
					'Stripe secret key is not configured.',
					'pg'
				)
			);

		}

		try {

			/*
			 * Configure Stripe with the secret key.
			 */
			\Stripe\Stripe::setApiKey( $secret_key );

			/*
			 * Create Stripe Checkout Session.
			 */
			$session = \Stripe\Checkout\Session::create(
				array(
					'mode' => 'payment',

					'line_items' => array(
						array(
							'price_data' => array(
								'currency' => 'inr',

								'product_data' => array(
									'name' => 'Test Product',
								),

								/*
								 * ₹500 = 50000 paise.
								 */
								'unit_amount' => 50000,
							),

							'quantity' => 1,
						),
					),

					/*
					 * Customer is redirected here after
					 * successful payment.
					 */
					'success_url' => home_url(
						'/payment-success/?session_id={CHECKOUT_SESSION_ID}'
					),

					/*
					 * Customer is redirected here if
					 * checkout is cancelled.
					 */
					'cancel_url' => home_url(
						'/payment-cancelled/'
					),
				)
			);

			/*
			 * Redirect customer to Stripe Checkout.
			 */
			wp_redirect( esc_url_raw( $session->url ) );
			exit;

		} catch ( \Exception $e ) {

			/*
			 * Display Stripe/API error.
			 */
			wp_die(
				esc_html(
					$e->getMessage()
				)
			);

		}

	}

	/**
	 * Get the configured Stripe secret key.
	 *
	 * @since 1.0.0
	 *
	 * @return string Stripe secret key.
	 */
	private function get_secret_key() {

		$options = get_option(
			'pg_stripe_settings',
			array()
		);

		return (string) ( $options['secret_key'] ?? '' );

	}

	/**
	 * Format a Stripe amount for display.
	 *
	 * Stripe returns amounts in the currency's minor unit, so the
	 * number of decimals depends on the currency.
	 *
	 * @since 1.0.0
	 *
	 * @param int|null $amount   Amount in the currency's minor unit.
	 * @param string   $currency Three-letter ISO currency code.
	 * @return string Formatted amount.
	 */
	private function format_amount( $amount, $currency ) {

		$currency = strtolower( (string) $currency );

		$zero_decimal = array(
			'bif',
			'clp',
			'djf',
			'gnf',
			'jpy',
			'kmf',
			'krw',
			'mga',
			'pyg',
			'rwf',
			'ugx',
			'vnd',
			'vuv',
			'xaf',
			'xof',
			'xpf',
		);

		$three_decimal = array(
			'bhd',
			'jod',
			'kwd',
			'lyd',
			'omr',
			'tnd',
		);

		if ( in_array( $currency, $zero_decimal, true ) ) {
			$decimals = 0;
		} elseif ( in_array( $currency, $three_decimal, true ) ) {
			$decimals = 3;
		} else {
			$decimals = 2;
		}

		return number_format(
			(int) $amount / pow( 10, $decimals ),
			$decimals,
			'.',
			','
		);

	}

	/**
	 * Prevent caching on pages that verify a payment.
	 *
	 * The result depends on a query argument, so a cached response
	 * could report the wrong outcome.
	 *
	 * @since 1.0.0
	 */
	public function send_no_cache_headers() {

		$queried = get_queried_object();

		if ( ! $queried instanceof WP_Post ) {
			return;
		}

		if ( ! has_shortcode( $queried->post_content, 'pg_payment_result' ) ) {
			return;
		}

		nocache_headers();

	}

	/**
	 * Display the payment result, verified against Stripe.
	 *
	 * Shortcode: [pg_payment_result]
	 *
	 * @since 1.0.0
	 *
	 * @return string Payment result HTML.
	 */
	public function payment_result_shortcode() {

		$session_id = isset( $_GET['session_id'] )
			? sanitize_text_field( wp_unslash( $_GET['session_id'] ) )
			: '';

		$state = 'invalid';
		$data  = array();

		/*
		 * Stripe session ids always start with "cs_". Reject
		 * anything else before spending an API call on it.
		 */
		if ( preg_match( '/^cs_[A-Za-z0-9_]+$/', $session_id ) ) {

			$secret_key = $this->get_secret_key();

			if ( '' === $secret_key ) {

				$state = 'error';

			} else {

				try {

					\Stripe\Stripe::setApiKey( $secret_key );

					$session = \Stripe\Checkout\Session::retrieve(
						$session_id
					);

					$data = array(
						'amount'   => $this->format_amount(
							$session->amount_total,
							$session->currency
						),
						'currency' => strtoupper(
							(string) $session->currency
						),
					);

					if (
						in_array(
							$session->payment_status,
							array(
								\Stripe\Checkout\Session::PAYMENT_STATUS_PAID,
								\Stripe\Checkout\Session::PAYMENT_STATUS_NO_PAYMENT_REQUIRED,
							),
							true
						)
					) {
						$state = 'paid';
					} else {
						$state = 'unpaid';
					}

				} catch ( \Stripe\Exception\InvalidRequestException $e ) {

					/*
					 * A 404 means the session does not exist, which is
					 * not the same as Stripe being unreachable.
					 */
					$state = 404 === $e->getHttpStatus()
						? 'invalid'
						: 'error';

				} catch ( \Exception $e ) {

					$state = 'error';

				}
			}
		}

		ob_start();

		include plugin_dir_path( __FILE__ ) . 'partials/pg-payment-result.php';

		return ob_get_clean();

	}

	/**
	 * Display the cancelled payment notice.
	 *
	 * Shortcode: [pg_payment_cancelled]
	 *
	 * @since 1.0.0
	 *
	 * @return string Cancelled notice HTML.
	 */
	public function payment_cancelled_shortcode() {

		ob_start();

		include plugin_dir_path( __FILE__ ) . 'partials/pg-payment-cancelled.php';

		return ob_get_clean();

	}

}