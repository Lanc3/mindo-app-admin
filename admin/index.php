<?php // Silence is golden
function plugin_settings_page_content() {
	if( $_POST['updated'] === 'true' ){
        handle_form();
    } ?>
    <div class="wrap">
        <h2>Compose Push Message</h2>
        <form method="POST">
			<input type="hidden" name="updated" value="true" />
			<?php wp_nonce_field( 'awesome_update', 'awesome_form' ); ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><label for="username">Message Title</label></th>
                        <td><input name="username" id="username" type="text" value="" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><label for="email">Message Body</label></th>
                        <td><input name="email" id="email" type="text" value="" class="regular-text" /></td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Send Notifications">
            </p>
        </form>
    </div> <?php
}

function handle_form() {
    if(
        ! isset( $_POST['awesome_form'] ) ||
        ! wp_verify_nonce( $_POST['awesome_form'], 'awesome_update' )
    ){ ?>
        <div class="error">
           <p>Sorry, your nonce was not correct. Please try again.</p>
        </div> <?php
        exit;
    } else {

		$username = sanitize_text_field( $_POST['username'] );
		$email = sanitize_text_field( $_POST['email'] );
    	update_option( 'awesome_username', $username );
    	update_option( 'awesome_email', $email );
		send_to_expo_server($username,$email);
    }
}

function send_to_expo_server($title,$body){
	global $wpdb;
	$table_name = $wpdb->prefix . 'registered_devices';
	$list_of_tokens = get_registered_device();
	$url = "https://exp.host/--/api/v2/push/send";
	foreach ($list_of_tokens as $token) {
		$response = wp_remote_post( $url, array(
	'method' => 'POST',
	'timeout' => 45,
	'redirection' => 5,
	'httpversion' => '1.0',
	'blocking' => true,
	'headers' => array(),
	'body' => array( 'to' => $token->expo_push_id, 'title' => $title , 'body' => $body ),
	'cookies' => array()
    )
);
	}

if ( is_wp_error( $response ) ) {
   $error_message = $response->get_error_message();
   echo "Something went wrong: $error_message";
} else {
	//if success
	?>
    	<div class="updated">
    	    <p>Your Message Was Sent!</p>
			<p>
				Title:
				<?php echo $title;?>
			</p>
			<p>
				Body:
				<?php echo $body;?>
			</p>
    	</div>
	<?php
}

}


add_action('admin_menu', 'mindo_plugin_menu');
function mindo_plugin_menu() {
    add_options_page('Mindo Push Notification Options', 'Mindo Plugin', 'administrator', 'your-unique-identifier', 'register_push_settings');
	add_action('admin_init','register_push_settings');
}

function register_push_settings(){
		//register our settings
	register_setting( 'mindo-push-plugin-settings-group', 'push_title' );
	register_setting( 'mindo-push-plugin-settings-group', 'push_body' );
}

function my_cool_plugin_settings_page() {
?>
<div class="wrap">
<h1>Your Plugin Name</h1>

<form method="post" action="options.php">
    <?php settings_fields( 'mindo-push-plugin-settings-group' ); ?>
    <?php do_settings_sections( 'mindo-push-plugin-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Title</th>
        <td><input type="text" name="push_title" value="<?php echo esc_attr( get_option('push_title') ); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">Body</th>
        <td><input type="text" name="push_body" value="<?php echo esc_attr( get_option('push_body') ); ?>" /></td>
        </tr>
    </table>
    <?php submit_button(); ?>

</form>
</div>
<?php }



add_action('init', 'my_plugin_handler');
function my_plugin_handler(){
    if(isset($_POST['submit_push'])){
        $val_a = $_POST['body'];
        $val_b = $_POST['title'];

        echo "<h1>".$val_a.', '.$val_b."</h1>";
    }
	echo "<h1>test</h1>";
}

function get_registered_device(){
	global $wpdb;
	$table_name = $wpdb->prefix . 'registered_devices';
	$result = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $table_name"));
	if($result)
	{
		return $result;
	}
	else
	{
		echo "Failed to get data";
	}
	return apply_filters('get_registered_device', $result);
}

function get_registered_device_id(){
	global $wpdb;
	$sendList = array();
	$table_name = $wpdb->prefix . 'registered_devices';
	$result = $wpdb->get_results( $wpdb->prepare("SELECT expo_push_id FROM $table_name"));
	foreach ($result as $row)  {
		array_push($sendList,$row->expo_push_id);
	}
	if($result)
	{
		echo sizeof($sendList);
		return $sendList;
	}
	else
	{
		echo "Failed to get data";
	}
	return apply_filters('get_registered_device', $result);
}

function pp_admin_tabs(){
	$tabs = array(
		'register' 	=> 'Registered Devices',
		'send' 	=> 'Send Push Notification',
		'home'	=> 'Home Page Data',
	);
	return apply_filters('pp_admin_tabs', $tabs);
}
$tabs = pp_admin_tabs();
$expo_ids = get_registered_device();

$current = sanitize_text_field($_GET['tab']);

add_action( 'template_redirect', 'mindo_push_form', 11 );
echo get_option('body');
function mindo_push_form() {
	echo "test";
    if( ! isset( $_POST['submit_push'] ) ) {
        return;
    }

    if( ! wp_verify_nonce( $_POST['name_of_your_nonce_field'], 'name_of_your_nonce_action' ) ) {
        return;
    }

    // Then you can handle all post data ($_POST) and save those data in db
    //plugin_dir_path( __FILE__ ) .'admin/index.php'
}
?>

<div class="wrap">
	<h2><?php echo get_admin_page_title(); ?></h2>
    <h3 class="nav-tab-wrapper">
    	<?php if(!empty($tabs)){
					foreach($tabs as $key => $value){
					$class = ( $key == $current ) ? ' nav-tab-active' : '';
		?>
        	<a href="?page=mindo-plugin&tab=<?php echo $key; ?>" class="nav-tab<?php echo $class; ?>"><?php echo $value; ?></a>
		<?php
					}
			}
		?>
    </h3>
</div>
<div class="pp-admin-content">
    	<?php
			switch($current){
				case 'register':
					?>
<?php if (sizeof($expo_ids) > 0): ?>
   <table class="wp-list-table widefat fixed posts">
     <tr>
       <th><?php _e('ID', 'pp-admin-ui'); ?></th>
       <th><?php _e('Time', 'pp-admin-ui'); ?></th>
       <th><?php _e('Expo Token', 'pp-admin-ui'); ?></th>
</tr>
  <?php
    foreach ($expo_ids as $row)  {
    ?>
    <tr>
    <td><?php echo (string)$row->id;?></td>
	<td><?php echo (string)$row->time;?></td>
	<td><?php echo (string)$row->expo_push_id;?></td>
    </tr>
        <?php }
  ?>

<?php endif; ?>
					<?php
				break;
				case 'send':
					?>
	   				<?php
					plugin_settings_page_content();
				break;
				case 'home':
				break;
			}
		?>
        <?php do_action('pp_admin_ui_extend', $current); ?>
</div>