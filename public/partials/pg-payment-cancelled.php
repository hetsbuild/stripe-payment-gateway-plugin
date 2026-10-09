<?php

/**
 * Cancelled payment view.
 *
 * @package    Pg
 * @subpackage Pg/public/partials
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="pg-result pg-result--cancelled" role="status">

	<h2 class="pg-result__heading">
		<?php esc_html_e( 'Payment cancelled', 'pg' ); ?>
	</h2>

	<p class="pg-result__body">
		<?php esc_html_e( 'Your payment was not completed and you have not been charged.', 'pg' ); ?>
	</p>

	<p class="pg-result__actions">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( 'Return to home', 'pg' ); ?>
		</a>
	</p>

</div>