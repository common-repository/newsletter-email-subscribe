<?php
/**
 * Plugin Name: Newsletter Email Subscribe
 * Author: Anil Ankola
 * Version: 2.4
 * Description: A WordPress newsletter plugin created by Anil Ankola for your website email subscribers list manage to WP Admin panel and download CSV file.
 * Text Domain: nels
 */
if(!defined('ABSPATH')) exit; // Prevent Direct Browsing

if ( ! function_exists( 'nels_script_method' ) ) {
	function nels_script_method() {
		wp_enqueue_script('jquery' );
		wp_enqueue_style( 'custom-style-css', plugin_dir_url( __FILE__ ) . 'css/style.css',false , '1.0', 'all' );
		wp_enqueue_script( 'nels-jquery-validate-min-js', plugin_dir_url( __FILE__). 'js/jquery.validate.min.js',false , '1.0', 'all'  );
		wp_enqueue_script( 'nels-form-validation-js', plugin_dir_url( __FILE__). 'js/newsletters_formValidation.js',false , '1.0', 'all'  );
	}
}
add_action( 'wp_footer', 'nels_script_method' );

if ( ! function_exists( 'nels_form' ) ) {
	function nels_form(){
		$dir_file = plugin_dir_url( __FILE__ );
		$mailchimp_audience_id = get_option('mailchimp_audience_id');
		$mailchimp_api_key = get_option('mailchimp_api_key');
		$receive_email_address = get_option('receive_email_address');?>
    	<div class="newsletter-email-main">
            <form class="newsletter" method="post" name="newsletter" id="newsletter" enctype="multipart/form-data">
                <input type="hidden" name="base" id="base" value="<?php echo $dir_file; ?>">
                <input type="hidden" name="m_a_id" id="m_a_id" value="<?php echo $mailchimp_audience_id; ?>">
                <input type="hidden" name="m_a_k" id="m_a_k" value="<?php echo $mailchimp_api_key; ?>">
                <input type="hidden" name="r_e_a" id="r_e_a" value="<?php echo $receive_email_address; ?>">
                <div class="formfield">
	                <input type="email" id="newsletter_email" name="newsletter_email" class="newsletter_email_field" placeholder="Enter email address" />
	            </div>
	            <div class="formfield">
	                <input type="submit" name="submit" id="nels_submit_btn" class="newsletter-submit-form"  value="<?php echo esc_html__('subscribe','nels');?>">
	                <img class="formloader" src="<?php echo $dir_file; ?>images/loader.gif" alt="loader" style="display: none;" />
	            </div>
            </form>
            <div id="Success" class="alert alert-success"  style=" display:none;">Thank you for contacting us. We will get back to you soon.</div>
            <div id="Error" class="alert alert-danger"  style=" display:none;">Oops! Something went wrong. Please resubmit the form.</div>
            <div id="AllreadyEmailError" class="alert alert-danger"  style=" display:none;">Already email exist.</div>
        </div>
        <?php
	}
}

////////////////////////////////// nels_ajax_obj Start //////////////////////////////////
function nels_ajax_enqueue() {
    wp_enqueue_script( 'nels-ajax-script', plugin_dir_url( __FILE__). 'js/nels-cust-ajax.js', array('jquery') );
    wp_localize_script( 'nels-ajax-script', 'nels_ajax_obj', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'nels_ajax_enqueue' );

//include( plugin_dir_url( __FILE__) . 'nels_functions.php');
include( 'nels_functions.php');

// Newsletter Form shortcode: [newsletter_email_subscribe]
add_shortcode( 'newsletter_email_subscribe', 'shortcode_nels_form' ); 
// The callback function
function shortcode_nels_form() {
    ob_start();
    nels_form();
    return ob_get_clean();
}

// Create Newsletter Menu Dashboard Sidepanel Start
function nels_create_menu(){
    add_menu_page('Email Newsletter', 'Newsletter', 'manage_options', 'newsletter', 'nels_listing_page', 'dashicons-email-alt' );
    add_submenu_page('newsletter','Settings','Settings','manage_options','newsletter-settings','nels_settings_page');
}
add_action( 'admin_menu', 'nels_create_menu' );
// Create Newsletter Menu Dashboard Sidepanel End

//Download Csv File Function Start
add_action("admin_init", "download_csv");
function download_csv()
{
	if(isset($_POST['download_csv'])) {
		global $wpdb;
		$delimiter = ",";
		$filename = "newsletter_email_" . date('Y-m-d') . ".csv";
		$f = fopen('php://output', 'w');
		$fields = array('ID', 'Email', 'Created Date');
		fputcsv($f, $fields, $delimiter);
		$query = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."newsletter_email");
		$rowNumber=0;
		foreach($query as $val){
			$rowNumber++;
			$Email = $val->email;
			$Date = $val->created_date;
			$lineData = array($rowNumber, $Email, $Date);	
			fputcsv($f, $lineData, $delimiter);
		}
		//fseek($f, 0);
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="' . $filename . '";');
		fpassthru($f);
		exit;
	}
}
//Download Csv File Function End

function nels_settings_page(){
	global $wpdb;
	$message = '';
	if(isset($_POST['submit'])) 
	{
		if(!wp_verify_nonce('nels_settings_submit_nonce','nels_settings_submit'))
		{
			$mailchimp_audience_id= sanitize_text_field( $_POST['mailchimp_audience_id'] );
			$mailchimp_api_key= sanitize_text_field( $_POST['mailchimp_api_key'] );
			$receive_email_address= sanitize_text_field( $_POST['receive_email_address'] );
			$saved= sanitize_text_field( $_POST['saved'] );

			if(isset($mailchimp_audience_id) ) {
				update_option('mailchimp_audience_id', $mailchimp_audience_id);
			}
			if(isset($mailchimp_api_key) ) {
				update_option('mailchimp_api_key', $mailchimp_api_key);
			}
			if(isset($receive_email_address) ) {
				update_option('receive_email_address', $receive_email_address);
			}

			if($saved==true) {
				$message='saved';
			}
		}
	}
	if ( $message == 'saved' ) {
		echo ' <div class="updated settings-error"><p><strong>Settings Saved.</strong></p></div>';
	}
	?>
	<div class="wrap">
        <h2>Newsletter Email Settings</h2>
    </div>
    <div class="wrap newsletter-setting">
	    <form method="post" id="nelsSettingForm" action="">
	    	<table class="form-table">
	    		<h3>Use this shortcode [newsletter_email_subscribe] show the newsletter form.</h3>
	    		<h4>Mailchimp and Form Email Receiver Setting</h4>
	    		<tr valign="top">
					<th scope="row">
						<label><?php echo esc_html__('Mailchimp Audience ID','nels');?></label>
					</th>
					<td>
					<input name="mailchimp_audience_id" type="text" value="<?php echo esc_html__(get_option('mailchimp_audience_id'),'nels');?>"  />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label><?php echo esc_html__('Mailchimp API Key','nels');?></label>
					</th>
					<td>
					<input name="mailchimp_api_key" type="text" value="<?php echo esc_html__(get_option('mailchimp_api_key'),'nels');?>"  />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label><?php echo esc_html__('Receive Email Address','nels');?></label>
					</th>
					<td>
					<input name="receive_email_address" type="text" value="<?php echo esc_html__(get_option('receive_email_address'),'nels');?>"  />
					</td>
				</tr>
	    	</table>
	    	<p class="submit">
				<input type="hidden" name="saved" value="saved"/>
				<input type="submit" name="submit" class="button-primary" value="Save Changes" />
				<?php wp_nonce_field( 'nels_settings_submit', 'nels_settings_submit_nonce' );?>
			</p>
	    </form>
	</div>
    <?php
}
// Create Newsletter Email List Function Start
function nels_listing_page(){?>
	<div class="wrap">
        <h2>Newsletter Email Listing</h2>
    </div>
    <div class="email-listing">
    	<form class="exportcsv" method="post" name="exportcsv" id="exportcsv" action="">
            <input type="submit" name="download_csv" class="btn-export"  value="<?php echo esc_html__('export to csv','nels');?>">
        </form>       
		<?php 
		global $wpdb;	
    	$result = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."newsletter_email");?>
    	<table id="myTable" class="table" width="100%" border="1" rules="all">    
            <thead>
              <tr>
                <td><strong><?php echo esc_html__('ID','nels');?></strong></td>
                <td><strong><?php echo esc_html__('EMAIL ADRRESS','nels');?></strong></td>
                <td><strong><?php echo esc_html__('STATUS','nels');?></strong></td>
                <td><strong><?php echo esc_html__('CREATED DATE','nels');?></strong></td>
              </tr>
            </thead>
            <tbody>
                <?php foreach($result as $row) { 
                    echo "<tr>
                        <td>$row->id</td>
                        <td>$row->email</td>
                        <td>$row->status</td>
                        <td>$row->created_date</td>
                    </tr>";
                 } ?>
            </tbody>
        </table>
    </div>
    <script>
    jQuery(document).ready(function(){
        jQuery('#myTable').DataTable({
            "order":[[0,'DESC']],
            "bStateSave":true
        });
    });
    </script>
	<?php
}
// Create Newsletter Email List Function End

// Admin Panel Css Add Function Start Here
add_action('admin_head', 'nles_listing_admin_css');
function nles_listing_admin_css() {
	wp_enqueue_style( 'newsletter-admin-css', plugin_dir_url( __FILE__ ) . 'css/email-list.css',false , '1.0', 'all' );
  	wp_enqueue_style( 'newsletter-datatable-css', plugin_dir_url( __FILE__ ) . 'css/jquery.dataTables.min.css',false , '1.0', 'all' );
  	wp_enqueue_script( 'newsletter-datatable-min-js', plugin_dir_url( __FILE__ ) . 'js/jquery.dataTables.min.js',false, '1.0', 'all' );
}
// Admin Panel Css Add Function End Here

// Create Database Table Function Start Here
function nles_create_table() 
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'newsletter_email';
	$sql = "CREATE TABLE $table_name (
		id int(10) NOT NULL AUTO_INCREMENT,
		email varchar(100) NOT NULL,
		status varchar(100) NOT NULL DEFAULT 'active',
		created_date datetime NOT NULL,
		PRIMARY KEY  (id)
	);";
 	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
 	dbDelta( $sql );
} 
register_activation_hook( __FILE__, 'nles_create_table' );
// Create Database Table Function End Here