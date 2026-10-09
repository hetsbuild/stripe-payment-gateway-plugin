<?php

/**
 * Payment result view.
 *
 * Expects $state and $data to be set by
 * Pg_Public::payment_result_shortcode().
 *
 * @package    Pg
 * @subpackage Pg/public/partials
 */

defined( 'ABSPATH' ) || exit;

$pg_messages = array(

	'paid'    => array(
		'heading' => __( 'Payment successful', 'pg' ),
		'body'    => __( 'Thank you for your payment. Your order has been received.', 'pg' ),
		'class'   => 'pg-result--paid',
	),

	'unpaid'  => array(
		'heading' => __( 'Payment not completed', 'pg' ),
		'body'    => __( 'Your payment has not been completed. If you were charged, the amount will be returned to your card.', 'pg' ),
		'class'   => 'pg-result--unpaid',
	),

	'invalid' => array(
		'heading' => __( 'Invalid payment link', 'pg' ),
		'body'    => __( 'This link is missing or is no longer valid. Please contact support if you were charged.', 'pg' ),
		'class'   => 'pg-result--invalid',
	),

	'error'   => array(
		'heading' => __( 'Unable to verify payment', 'pg' ),
		'body'    => __( 'We could not confirm your payment right now. Please try again or contact support.', 'pg' ),
		'class'   => 'pg-result--error',
	),

);

$pg_message = isset( $pg_messages[ $state ] )
	? $pg_messages[ $state ]
	: $pg_messages['error'];

?>
<div class="pg-result <?php echo esc_attr( $pg_message['class'] ); ?>" role="status">

	<h2 class="pg-result__heading">
		<?php echo esc_html( $pg_message['heading'] ); ?>
	</h2>

	<?php if ( 'paid' === $state && '' !== $data['currency'] ) : ?>

		<p class="pg-result__amount">
			<?php
			printf(
				/* translators: 1: formatted amount, 2: three-letter currency code. */
				esc_html__( 'Amount paid: %1$s %2$s', 'pg' ),
				esc_html( $data['amount'] ),
				esc_html( $data['currency'] )
			);
			?>
		</p>

	<?php endif; ?>

	<p class="pg-result__body">
		<?php echo esc_html( $pg_message['body'] ); ?>
	</p>

	<p class="pg-result__actions">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( 'Return to home', 'pg' ); ?>
		</a>
	</p>

</div>