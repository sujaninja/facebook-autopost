<?php
/*
Plugin Name: Facebook Auto Poster
Author: Sujan Dhakal
Plugin URL: http://sujandhakal.com.np/
*/
@session_start();
include_once 'autoload.php';

/** FB Autoposter Settings Global **/
$defaults = array('wpsujan_facebook_appid'=>'','wpsujan_facebook_appsecret'=>'','wpsujan_facebook_appaccesstoken'=>'');
$settings_from_wp = get_option('wpsujan_fbap_options');

if(isset($_SESSION['wpsujan']['token'])){
	$new_options = array_merge( $settings_from_wp, array('wpsujan_facebook_appaccesstoken'=>$_SESSION['wpsujan']['token']) );
	update_option('wpsujan_fbap_options',$new_options);
	$settings_from_wp = $new_options;
	unset($_SESSION['wpsujan']);
}

if(is_array($settings_from_wp)){
	$settings_from_wp = array_merge($defaults,$settings_from_wp);
}else{
	$settings_from_wp = $defaults;
}


$facebook_account = array('access_token'=>$settings_from_wp['wpsujan_facebook_appaccesstoken'],
						  'app_id'=>$settings_from_wp['wpsujan_facebook_appid'],
	                      'app_secret'=>$settings_from_wp['wpsujan_facebook_appsecret']);

$default_settings = array('post_types'=>array('post','page','any_type'),
						  'things_to_post'=>array('title','url','excerpt'),
						  'shorten_url'=>false);

$_SESSION['wpsujan']['fb_settings'] = array_merge($facebook_account,array('redirect_uri'=>admin_url().'options-general.php?page=wpsujan-facebook-autoposter-settings'));
/*** WP Hooks */
$settingsObject = new Core_Settings($settings_from_wp);
add_action('admin_init',function(){
	global $settingsObject;
	$settingsObject->init();
});
add_action('admin_menu',function(){
	global $settingsObject;
	$settingsObject->addMenuPage();
});
if(!empty($facebook_account['access_token']) && !empty($facebook_account['app_id']) && !empty($facebook_account['app_secret']) ){
		//hook in to the before post save and try to post to the facebook profile/ Page;
		$fbPoster = new Core_FacebookPoster($facebook_account);
		add_action('publish_post',function($id){
			global $fbPoster;
			$post = get_post($id);
			$data = array( "message" => mb_substr($post->post_content, 0,100)."...",
				  "link" => get_permalink($id),
		//		  "picture" => "http://i.imgur.com/lHkOsiH.png",
				  //"name" => $post->post_title,
				  "caption" => $post->post_title,
				  //"description" => "wordpress.dev"
				  );
			$fbPoster->postToFacebook($data);
		});
}else{
	//print "";
	add_action('admin_notices', function(){
		$html = '<div id="message" class="error"><p><strong>Facebook Autoposter</strong> Plugin Temporarily Disabled void of access token, Go to <a href="options-general.php?page=wpsujan-facebook-autoposter-settings">Settings Page</a> to fix this.</p></div>';
		echo $html;
	});
}


