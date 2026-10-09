<?php

/**
 * Admin page for the Payment Gateway plugin.
 *
 * @package    Pg
 * @subpackage Pg/admin/partials
 */

?>

<div class="wrap">

    <h1>
        <?php esc_html_e( 'Payment Gateway', 'pg' ); ?>
    </h1>

    <?php if ( isset( $_GET['status'] ) ) : ?>

        <?php if ( 'success' === $_GET['status'] ) : ?>

            <div class="notice notice-success is-dismissible">
                <p>
                    <?php esc_html_e( 'Stripe connection successful.', 'pg' ); ?>
                </p>
            </div>

        <?php elseif ( 'missing_key' === $_GET['status'] ) : ?>

            <div class="notice notice-error is-dismissible">
                <p>
                    <?php esc_html_e( 'Stripe secret key is missing.', 'pg' ); ?>
                </p>
            </div>

        <?php elseif ( 'error' === $_GET['status'] ) : ?>

            <div class="notice notice-error is-dismissible">
                <p>
                    <?php esc_html_e( 'Stripe connection failed. Please check your secret key.', 'pg' ); ?>
                </p>
            </div>

        <?php endif; ?>

    <?php endif; ?>


    <form method="post" action="options.php">

        <?php

        settings_fields( 'pg_stripe_settings' );

        do_settings_sections( 'pg' );

        submit_button();
        ?>
    </form>
        <p>
            <a
                href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=pg_test_stripe_connection' ), 'pg_test_stripe_connection' ) ); ?>"
                class="button button-secondary">
                <?php esc_html_e( 'Test Stripe Connection', 'pg' ); ?>
            </a>
        </p>
        
        
        
</div>