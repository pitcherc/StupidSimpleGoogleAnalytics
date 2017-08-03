<?php
$ssga_options = get_option('ssga_options');
$ssga_id = isset($ssga_options['tracking_id']) ? $ssga_options['tracking_id'] : array();
?>

<div class="wrap">
    <h2><?php _e('Stupid Simple Google Analytics Settings', 'ssga'); ?></h2>
    <?php if ($_SESSION['status']) : ?>
        <?php flash( 'status' ); ?>
    <?php endif; ?>
    <p>If you need a tracking ID, login to your Google account at <a href="https://analytics.google.com">analytics.google.com</a></p>
    <br>
    <form method="post">

        <?php if (function_exists('wp_nonce_field')) wp_nonce_field('nonce_ssga'); ?>

        <div id="scporder_select_objects">

            <label><?php _e('Google Analytics Tracking ID', 'ssga') ?></label><br>
            <input type="tracking_id" name="tracking_id" placeholder="UA-XXXX-XX" value="<?php echo $ssga_id; ?>">

        </div>

        <p class="submit">
            <input type="submit" class="button-primary" name="ssga_submit" value="<?php _e('Update', 'ssga'); ?>">
        </p>

    </form>


</div>