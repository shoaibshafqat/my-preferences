<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<?php if(is_user_logged_in()){?>
<h3>You are not allowed to view this page.</h3>
<?php } else {?>
<h3>You have to be logged in to view this page.</h3>
<a href="<?php echo esc_url(wp_login_url( get_permalink() )); ?>" title="Login">Login</a>

<?php }?>