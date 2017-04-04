<?php
/* Plugin Name: WB Mailer API
Plugin URI: http://websiteblue.com/
Description: Custom mailer
Version: 1.0
Author: Jerome David
License: GPLv2 or later
*/

/*
 * Check if code runs within wordpress
 *
 */
if ( !function_exists( 'add_action' ) ) {
	echo 'This is a restricted zone area!.';
	exit;
}


add_action('wp_enqueue_scripts','mailer_init');

function mailer_init() { ?>
<script>
	var base_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
</script>
<?php
    wp_enqueue_script( 'mailer-js', plugins_url( 'inc/form.js', __FILE__ ));
}

function set_content_type($content_type){
return 'text/html';
}

add_action("wp_ajax_my_mailer", "my_mailer");
add_action('wp_ajax_nopriv_my_mailer', 'my_mailer');

function my_mailer() {

// setting up environment
$email_from 		=	$_POST['mail_email'];
$email_to 			=	$_POST['mail_to'];
$name 				=	$_POST['mail_name'];
$messages 			=	$_POST['mail_message'];

$propid 			=	$_POST['prop_id'];
// $cc 				=	'highton@hayeswinckle.com.au';

$location 			=	$_POST['property_location'];
$suburb 			=   $_POST['property_suburb'];


if($email_from != '' && $email_to != '' && $name != '' && $messages != ''){

	// $recipients = array($email_to, $cc);

	$to = $email_to; // implode(',', $recipients);
	$subject = "Lucy Cole Customer Enquiry";
	$content .= "Name :".$name."<br/>
				Email : ".$email_from."<br/>";
	if(!empty($location)){
		$content .= "Location : ".$location."<br>";
	}	
	$content .= "Message : ".$messages;

	$headers = array(
		'From: '.$name.' <'.$email_from.'>',
	    'Reply-To' => $name . '<' . $email_from . '>',
	);

	add_filter('wp_mail_content_type','set_content_type');

	$status = wp_mail( $to, $subject, $content, $headers );


	// If status correct then redirect the user to the product page again
	if ( $status ){
	   auto_response($email_from, $location, $propid, $suburb);
	   $message = 'Form has been successfully sent.';
	   echo json_encode(array('status' => true, 'message' => $message));
           
	} else {
	    // if the status of the email is false do something
	    $message = $GLOBALS['phpmailer']->ErrorInfo;
	    echo json_encode(array('status' => false, 'message' => $message));
	            // var_dump($GLOBALS['phpmailer']->ErrorInfo);
	}

}else{
	$message = $email_to == '' ? 'Can\'t find agent email!' : 'All fields are required!';
	echo json_encode(array(
		'status'		=>	false,
		'message'		=>	$message
	));
}


   die();

}

function auto_response($user_email, $prop_add, $propid, $suburb){
	if(!empty($user_email) && !empty($prop_add) && !empty($propid)){

		$agent_name1  = '';
		$agent_email1 = '';
		//$agent_phone = get_post_meta($prodid, '_agent_phone', true);

		$listing_agents = wp_get_object_terms( $propid,  'listing-agents' );
   		$_agents = "";
   		$total_agents = count($listing_agents);
		if ( ! empty( $listing_agents ) ) {
	    	    if ( ! is_wp_error( $listing_agents ) ) {
	    	$c=0;
			foreach( $listing_agents as $term ) {
				$c++;
				list($_agent_id, $_extra) = explode('-', $term->slug);
				$agent_info = get_userdata($_agent_id);
				$single_quote = count($listing_agents) > 1 && $c != $total_agents? ',' : '';

				$agent_phone = ($agent_info->phone) ? 'mobile: '.$agent_info->phone.' and ' : '';
				$agent_email = ($agent_info->user_email) ? ' email: ' . $agent_info->user_email : '';

				$_agents .= ' ' . $agent_info->display_name . ' - ' . $agent_phone . $agent_email . $single_quote; 

				$agent_name1 = $agent_info->display_name;
				$agent_email1 = $agent_info->user_email;
				
			}
		    }
		}


		$to = $user_email;
		$subject = 'Lucy Cole Enquiry Information';

		//$messages = 'Thank you for your enquiry on '.$prop_add.'.<br/>The sales representative ' . substr($_agents, 0 , -1) . '  will in contact with you within 24 hours';
		$messages = 'Hello,<br/><br/>
 
					Thank you for your enquiry on '.$prop_add.', '.$suburb.'. We will be in contact in relation to this property within 24 hours.<br/><br/>

					The sales representative contact details for your reference are ' . $_agents . '<br/><br/>

					If you have any additional questions or queries, Please do not hesitate to contact the sales agent.<br/><br/>

					Kind Regards,<br/><br/>

					Lucy Cole Prestige Properties';

		$content .= $messages;

		$headers = array(
			'From: Name : '.$agent_name1.' - Prestige Properties <'.$agent_email1.'>',
		    'Reply-To' => 'Name : '.$agent_name1.' - Prestige Properties <'.$agent_email1.'>',
		);

		add_filter('wp_mail_content_type','set_content_type');

		wp_mail( $to, $subject, $content, $headers );
	}
}