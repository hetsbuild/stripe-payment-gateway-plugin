<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://example.com/
 * @since      1.0.0
 *
 * @package    Pg
 * @subpackage Pg/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Pg
 * @subpackage Pg/admin
 * @author     Het Agravat <het.agravat@gmail.com>
 */
class Pg_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_post_pg_test_stripe_connection',	array($this, 'test_stripe_connection'));
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pg_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pg_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/pg-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pg_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pg_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/pg-admin.js', array('jquery'), $this->version, false);
	}

	/**
	 * Add plugin admin menu.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu()
	{

		add_menu_page(
			__('Payment Gateway', 'pg'),
			__('Payment Gateway', 'pg'),
			'manage_options',
			'pg',
			array($this, 'display_admin_page'),
			'dashicons-money-alt',
			25
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function register_settings()
	{

		register_setting(
			'pg_stripe_settings',
			'pg_stripe_settings',
			array(
				'sanitize_callback' => array($this, 'sanitize_settings'),
			)
		);

		add_settings_section(
			'pg_stripe_section',
			__('Stripe Settings', 'pg'),
			array($this, 'stripe_section_callback'),
			'pg'
		);

		add_settings_field(
			'test_mode',
			__('Test Mode', 'pg'),
			array($this, 'test_mode_callback'),
			'pg',
			'pg_stripe_section'
		);

		add_settings_field(
			'publishable_key',
			__('Publishable Key', 'pg'),
			array($this, 'publishable_key_callback'),
			'pg',
			'pg_stripe_section'
		);

		
		add_settings_field(
			'secret_key',
			__('Secret Key', 'pg'),
			array($this, 'secret_key_callback'),
			'pg',
			'pg_stripe_section'
		);
	}

	/**
	 * Display admin page.
	 *
	 * @since 1.0.0
	 */
	public function display_admin_page()
	{

		require_once plugin_dir_path(__FILE__) . 'partials/pg-admin-display.php';
	}

	//Test Mode
	public function test_mode_callback()
	{

		$options = get_option('pg_stripe_settings', array());

?>

		<input
			type="checkbox"
			name="pg_stripe_settings[test_mode]"
			value="1"
			<?php checked(1, $options['test_mode'] ?? 0); ?>>

		<span>
			<?php esc_html_e('Use Stripe Test Mode', 'pg'); ?>
		</span>

	<?php
	}

	//Publishable Key
	public function publishable_key_callback()
	{

		$options = get_option('pg_stripe_settings', array());

	?>

		<input
			type="text"
			name="pg_stripe_settings[publishable_key]"
			value="<?php echo esc_attr($options['publishable_key'] ?? ''); ?>"
			class="regular-text">

	<?php
	}

	//secret key
	public function secret_key_callback()
	{

		$options = get_option('pg_stripe_settings', array());

	?>

		<input
			type="password"
			name="pg_stripe_settings[secret_key]"
			value="<?php echo esc_attr($options['secret_key'] ?? ''); ?>"
			class="regular-text">

<?php
	}

	//section callback
	public function stripe_section_callback()
	{

		echo '<p>';
		esc_html_e(
			'Configure your Stripe API credentials below.',
			'pg'
		);
		echo '</p>';
	}

	/**
	 * Sanitize plugin settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $input Settings input.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings($input)
	{

		$output = array();

		$output['test_mode'] = ! empty($input['test_mode']) ? 1 : 0;

		$output['publishable_key'] = sanitize_text_field(
			$input['publishable_key'] ?? ''
		);

		$output['secret_key'] = sanitize_text_field(
			$input['secret_key'] ?? ''
		);

		return $output;
	}


	/**
	 * Test Stripe API connection.
	 *
	 * @since 1.0.0
	 */
	public function test_stripe_connection() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'pg' ) );
		}

		check_admin_referer( 'pg_test_stripe_connection' );

		$options = get_option( 'pg_stripe_settings', array() );

		$secret_key = $options['secret_key'] ?? '';

		if ( empty( $secret_key ) ) {

			wp_safe_redirect(
				add_query_arg(
					array(
						'page'  => 'pg',
						'status' => 'missing_key',
					),
					admin_url( 'admin.php' )
				)
			);

			exit;
		}

		try {

			\Stripe\Stripe::setApiKey( $secret_key );

			$account = \Stripe\Account::retrieve();

			wp_safe_redirect(
				add_query_arg(
					array(
						'page'   => 'pg',
						'status' => 'success',
					),
					admin_url( 'admin.php' )
				)
			);

			exit;

		} catch ( \Exception $e ) {

			wp_safe_redirect(
				add_query_arg(
					array(
						'page'   => 'pg',
						'status' => 'error',
					),
					admin_url( 'admin.php' )
				)
			);

			exit;
		}
	}
}
