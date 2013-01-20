<?php
/*
Plugin Name: Ajax Contact Form
Author: takien
Author URI: http://takien.com/
*/

/*
1. define some constants.
=========================
*/

define('ACF_PAGE_SLUG', 'contact'); /* page slug dimana nanti form akan dirender*/
define('ACF_EMAIL_RECEIVER','mymail@example.com'); /* email penerima contact*/
define('ACF_SUCCESS_MESSAGE','Pesan anda berhasil dikirim, terimakasih.');
define('ACF_ERROR_MESSAGE','Terjadi kesalahan, email tidak terkirim.');
/*
2. Render langsung script nya di footer, kalau udah Advanced sebaiknya pakai wp_enqueue_script (external file)
=================
*/

add_action('wp_footer', 'acf_form_js');
function acf_form_js(){ ?>
<script>
jQuery(document).ready(function($) {
var ajaxurl = '<?php echo admin_url('admin-ajax.php');?>';
$('#contact-form').submit(function(){
  var data = $(this).serialize();
	$.post(ajaxurl, data, function(response) {

		if(response.code == 1){
			/* jika susccess*/
			alert(response.message);
		}
		else {
			/*jika error*/
			alert(response.message);
		}
	});
	return false;
});
});
</script>
<?php
}
/*
	3. Form HTML nya,
	=================
/* pake filter the_content, supaya nantinya form akan otomatis nempel di page dengan slug 'contact' atau whatever terserah anda, sesuai dengan ACF_PAGE_SLUG di atas*/
add_filter('the_content','render_contact_form');
function render_contact_form($content) {
	if(is_page(ACF_PAGE_SLUG)) {
	?>
	<form id="contact-form" action="" method="post">
	<label for="nama">Nama:</label><input id="nama" type="text" required="required"value="" name="nama" /><br>
	<label for="email">Email:</label><input id="email" type="email" required="required" value="" name="email" /><br>
	<label for="subject">Subject:</label><input id="subject" type="text" required="required" value="" name="subject" /><br>
	<label for="message">Message:</label><textarea required="required" name="message"></textarea><br>
	<input type="hidden" name="action" value="acf_form_submit"/>
	<input type="submit" value="Send" name="submit_form" />
	</form>
	<?php
	}
	else {
		return $content;
	}
}
/*
	3. Sekarang bikin Ajax Callback nya.
	====================================
 */
	
add_action('wp_ajax_acf_form_submit',        'acf_callback');
add_action('wp_ajax_nopriv_acf_form_submit', 'acf_callback');
function acf_callback() {
	if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
		$name       = trim(strip_tags($_POST['name']));
		$email      = trim(strip_tags($_POST['email']));
		$subject    = trim(strip_tags($_POST['subject']));
		$message    = trim(htmlentities($_POST['message']));
		$to         = ACF_EMAIL_RECEIVER;
		$header     = "From: $email\r\n" .
		"Reply-To: $email\r\n";
		$result = Array(
			'code'=>0,
			'message'=>ACF_ERROR_MESSAGE);
			
		if(!empty($email) AND !empty($message)){
			if(@mail($to,$subject,$message,$header)){
				$result['code']    = 1;
				$result['message'] = ACF_SUCCESS_MESSAGE;
			}
		} 
	header('content-type: application/json; charset=utf-8');
	echo json_encode($result);
	}
	exit;
}
