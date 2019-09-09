<?php

/*
Plugin Name: Email Template
Author: sewakram.gsm@gmail.com
*/
class Emailtemplate

{
	
	

	function __construct()

	{

	add_action( 'admin_enqueue_scripts', array($this, 'template_enqueue_script') );
	add_action('admin_menu',array($this, 'template_modifymenu'));	
	add_shortcode( 'email_form', array($this, 'email_form_shortcode') );

	}
public function template_enqueue_script()

	{
		
		 wp_enqueue_style( 'jquerytecss', plugins_url('jquery-te-1.4.0.css', __FILE__) );
		 // wp_enqueue_script( 'jquery-script', 'http://code.jquery.com/jquery.min.js' );

		 wp_enqueue_script( 'jqueryte-script', plugins_url( 'jquery-te-1.4.0.min.js', __FILE__ ) , array( 'jquery' ),false,true);
		 

 	}
	public function email_form_shortcode() {
			global $wpdb;
			
			if(isset($_POST['submit'])) {
			$headers = "MIME-Version: 1.0" . "\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\n"; 
			$headers .= 'From:"Consumer TV"<info@consumer.tv>'. "\n";
			// $headers .= "Bcc:".$_POST['email_ids']. "\n";
			$singledata = $wpdb->get_row("SELECT * FROM email_template WHERE id =1",OBJECT);
			 
			foreach (explode(',',$_POST['email_ids']) as $value) {
				$msg=$singledata->template;
			$msg.='<html><body>
<table width="600" align="center"> 
			      <tr>
						<td colspan="3" style="padding: 10px 5px 10px 5px;text-align:center;" valign="top">
						<p style="font-size:17px;margin: 7px 0px;"><span style="color:#00B4FF; font-family: Helvetica;">View this email in your browser</span></p>		
						<p style="font-size:15px;color:#757374; margin: 5px 0px;font-family: Helvetica;">You are receiving this email because of your relationship with consumer.tv. Please<span href="" style="color:#00B4FF;"> reconfirm</span> your interest in receiving emails from us. If you do not wish to receive any more emails, you can <a href="'. site_url().'/email-unsubscription?token='.base64_encode($value).'" style="color:#00B4FF;" >Unsubscribe here.</a></p>
						
						<p style="font-size:12px;color:#757374; margin: 5px 0px;font-family: Helvetica;margin-top:15px;line-height: 19px;">This message was sent to info@consumer.tv  by noreply@consumer.tv 
                        </p>
						</td>
						</tr>
             </tbody>			   
			</table></body></html>';
			if(count($wpdb->get_row("SELECT * FROM unsubscribers WHERE email ='".$value."'",OBJECT))==0){
				wp_mail( $value, "Hey, Congratulations! Your Free Trial Offer Has Arrived!", $msg,$headers );
			}
			}
			}
		?>
			<form class="contactform" name="contactform" method="post" action="">
			<input type="text" name="email_ids" placeholder="Comma seperated email ids">
			<input type="submit" value="Submit" name="submit">
			</form>
	<?php } 

public function template_modifymenu() {
	
	//this is the main item for the menu
	add_menu_page(
        'Email template',
        'Email template',
        'manage_options',
        'email-template',
        array($this, 'template_create'),
        plugins_url( 'myplugin/images/icon.png' ),
        100
    );

    add_submenu_page('email-template','Unsubscribed email list','Unsubscribed emails','activate_plugins','unsubscribed_emails','unsubscribed_emails');

}

public function admin_unsubscribers_list() {
	require_once 'admin/list-table-example.php';
}

public function template_create() {
	global $wpdb;
	
  if(isset($_POST['submit'])) {
  	 
  	
	
	$wpdb->update( "email_template", array( 'template' => stripslashes( $_POST['mydata'] )), array('id' =>1) );

  		}
	$singledata = $wpdb->get_row("SELECT * FROM email_template WHERE id =1",OBJECT);
	
	?>
	
	<div class="wrap">
        <h2>Email template</h2>
       
        <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['REQUEST_URI']; ?>"> 
            
                    <div><textarea name="mydata" class="jqte-test"><?php echo $singledata->template; ?></textarea></div>
               
            <input type="submit" value="Submit" name="submit">
        </form>
    </div>
    <script>
    jQuery(document).ready(function( $ ) {
	
	$('.jqte-test').jqte();
	
	
	});	
	
	</script>
	<?php
}

}


if(class_exists('Emailtemplate'))

{
	$t = new Emailtemplate();
	
	 $t->admin_unsubscribers_list();

	

}


