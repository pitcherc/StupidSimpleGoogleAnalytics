<?php
/**
* Plugin Name: Stupid Simple Google Analytics
* Plugin URI: 
* Description: Add Google Analytics tracking without a bloated plugin
* Version: 1.0
* Author: Charles Pitcher
**/
define('SSGA_URL', plugins_url('', __FILE__));
define('SSGA_DIR', plugin_dir_path(__FILE__));

//Ensure that a session exists
if( !session_id() )
{
    session_start();
}

$ssga = new SSGA_Engine();

class SSGA_Engine {
	function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'update_options'));
    }

	function admin_menu() {
	    add_options_page(__('SS Google Analytics', 'ssga'), __('SS Google Analytics', 'ssga'), 'manage_options', 'ssga-settings', array($this, 'admin_page'));
	}

	function admin_page() {
        require SSGA_DIR . 'settings.php';
    }

    /**
	 * Update option on submit
	 **/
    function update_options() {
        global $wpdb;

        if (!isset($_POST['ssga_submit']))
            return false;

        check_admin_referer('nonce_ssga');

        $input_id = array();
        $input_id['tracking_id'] = isset($_POST['tracking_id']) ? $_POST['tracking_id'] : '';

        if (!$this->isAnalytics($input_id['tracking_id']) && $input_id['tracking_id'] != '') {
        	flash( 'status', '<code>' . $input_id['tracking_id'] . '</code> is not a valid tracking ID.', 'error');
        	return wp_redirect('options-general.php?page=ssga-settings');
        }

        if (get_option('ssga_options')['tracking_id'] == $input_id['tracking_id']) {
        	flash( 'status', 'Settings Updated.' );
        	return wp_redirect('options-general.php?page=ssga-settings');
	    }

	    if (update_option('ssga_options', $input_id)) {
        	flash( 'status', 'Settings Updated.' );
        } else {
        	flash( 'status', 'Settings failed to update.', 'error' );
        }

        wp_redirect('options-general.php?page=ssga-settings');
    }

    /**
	 * Validate Google Analytics tracking ID
	 **/
    function isAnalytics($str){
	    return preg_match('/^ua-\d{4,9}-\d{1,4}$/i', strval($str));
	}
}

/**
 * Flash message to session
 **/
function flash( $name = '', $message = '', $class = 'updated fadeout-message' ) {
    //We can only do something if the name isn't empty
    if( !empty( $name ) )
    {
    	
        //No message, create it
        if( !empty( $message ) && empty( $_SESSION[$name] ) )
        {
            if( !empty( $_SESSION[$name] ) )
            {
                unset( $_SESSION[$name] );
            }
            if( !empty( $_SESSION[$name.'_class'] ) )
            {
                unset( $_SESSION[$name.'_class'] );
            }

            $_SESSION[$name] = $message;
            $_SESSION[$name.'_class'] = $class;
        }
        //Message exists, display it
        elseif( !empty( $_SESSION[$name] ) && empty( $message ) )
        {
            $class = !empty( $_SESSION[$name.'_class'] ) ? $_SESSION[$name.'_class'] : 'updated';
            echo "<div id='message' class='{$class}'>";
            	echo "<p>{$_SESSION[$name]}</p>";
        	echo '</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name.'_class']);
        }
    }
}

/**
 * Add Google Analytics script tag to wp_head
 **/
function ssga_header_hook() {
	if (get_option('ssga_options')['tracking_id']) :
	    ?>
	        <script>
				(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

				ga('create', '<?php echo get_option('ssga_options')['tracking_id']; ?>', 'auto');
				ga('send', 'pageview');
			</script>
	    <?php
    endif;
}
add_action('wp_head', 'ssga_header_hook');

/**
 * Add Settings link to plugins page
 **/
function plugin_add_settings_link( $links ) {
    $updated_links = ['settings' => '<a href="options-general.php?page=ssga-settings">' . __( 'Settings' ) . '</a>'];
    return array_merge( $updated_links, $links );
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link' );
