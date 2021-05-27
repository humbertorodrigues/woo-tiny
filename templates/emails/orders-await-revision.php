<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php echo wptexturize( wpautop( $await_revision_message ) ); ?>

<?php
/**
 * Email footer.
 *
 * @hooked WC_Emails::email_footer() Output the email footer.
 */
do_action( 'woocommerce_email_footer', $email );
