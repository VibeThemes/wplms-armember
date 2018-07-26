<?php
/*
Plugin Name: WPLMS ARMember
Plugin URI: https://wplms.io
Description: WPLMS Integration with ARMember
Author: VibeThemes
Version: 1.0
Author URI: https://www.wplms.io
Text Domain: wplms-ar
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) exit;




class WPLMS_ARMember_Init{


	public static $instance;

    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_ARMember_Init();
        return self::$instance;
    }

    private function __construct(){

    

			//if( !defined('ARM_HOME_URL')){

		        add_action('wplms_the_course_button',array($this,'check_arm_membership'),10,2);
			      
		        add_filter('lms_general_settings',array($this,'ar_member_buy_page'));
		        add_filter('wplms_course_product_metabox',array($this,'wplms_metabox'),999,1);
		      	add_filter('wplms_private_course_button',array($this,'wplms_check_armember_button'));
		      	add_filter('wplms_private_course_button_label',array($this,'wplms_check_armember_course_button'));

		      	add_action('wplms_front_end_pricing_content',array($this,'front_end'),10,1);
	      	//}else{

		      //	add_action('admin_notices',array($this,'show_arm_notice'));
	      	//}
      	
    }

    function ar_member_buy_page($settings){
    	$settings[]=array(
					'label' => __('ARMember Purchase link ','vibe-customtypes'),
					'name' =>'ar_member_link',
					'type' => 'text',
					'desc' =>__('(set complete url )','vibe-customtypes'),
					);
    	return $settings;
    }
    function show_arm_notice(){
    	?>
    	<div class="message error">
    		<p>WPLMS ARMember Addon requires AR Member plugin to be installed !</p>
    	</div>
    	<?php
    }

    function front_end($course_id = null){


		global $arm_subscription_plans;

          $all_plans = $arm_subscription_plans->arm_get_all_subscription_plans();

          ?>
          <li class="course_membership"><strong><?php _e('Set Course ARMemberships','wplms-front-end' ); ?><span>
                  <select id="vibe_ar_membership" class="chosen" multiple>
                      <?php


                      	if(!empty($all_plans) && is_array($all_plans)){
                          	foreach($all_plans as $p){

                              	if(!is_Array($course_pricing['vibe_ar_membership_plan_ids']))
                                  $course_pricing['vibe_ar_membership_plan_ids'] = array();
                              
                          
                              	echo '<option value="'.$p['arm_subscription_plan_id'].'" '.(in_array($p['arm_subscription_plan_id'],$course_pricing['vibe_ar_membership_plan_ids'])?'selected':'').'>'.stripslashes(esc_attr($p['arm_subscription_plan_name'])).'</option>';
                          
                      	}
                      ?>
                  </select>
              </span>
              </strong>
          </li>
      <?php    
      }
	}

	function wplms_metabox($settings){

		global $arm_subscription_plans;
		$aplans = array();
		if(!empty($arm_subscription_plans)){
	        $all_plans = $arm_subscription_plans->arm_get_all_subscription_plans();
	        if(!empty($all_plans) && is_array($all_plans)){
	          	foreach($all_plans as $p){
	          		$aplans[]=array('value'=>$p['arm_subscription_plan_id'],'label'=>stripslashes(esc_attr($p['arm_subscription_plan_name'])));
	          	}
	      	}
      	}

      	if(!empty($aplans)){
	      	$settings[] = array( // Text Input
				'label'	=> __('Select ARMemberships ','vibe-customtypes'), // <label>
				'desc'	=> __('select ARMemberships','vibe-customtypes'), // description
				'id'	=> 'vibe_ar_membership_plan_ids', // field id and name
				'type'	=> 'select', // type of field
		        'options' => $aplans,
		        'std'   => 'H'
			);
      }
		return $settings;
	}

    function check_arm_membership($course_id,$user_id){

          
    	if(!is_user_logged_in())
    		return;

        $current_user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);

        $course_ar_plan = vibe_sanitize(get_post_meta($course_id,'vibe_ar_membership_plan_ids',true));

        if(!empty($current_user_plans) && !empty($course_ar_plans) && in_array($course_ar_plan,$current_user_plans)){
        	//foreach($course_ar_plans as $course_plan_id){
        	//	if(in_array($course_plan_id,$current_user_plans)){

    				$duration=get_post_meta($course_id,'vibe_duration',true);
	                $course_duration_parameter = apply_filters('vibe_course_duration_parameter',86400,$course_id);

	                $new_duration = time()+$course_duration_parameter*$duration;
        			
        			
	                if(function_exists('bp_course_add_user_to_course')){
	                  bp_course_add_user_to_course($user_id,$course_id,$new_duration);
	                }

	              //  break;
        		//}
        	//}
        }

      
    }


    function wplms_check_armember_button($link){
        $course_id = get_the_ID();
        
        $link = '#';
        $tips = WPLMS_tips::init();
        if(!empty($tips->settings['ar_member_link'])){
        	$link = $tips->settings['ar_member_link'];
        }
        return $link;
    }

    function wplms_check_armember_course_button($label){
      	$course_id = get_the_ID();
      
        $membership_ids=get_post_meta($course_id,'vibe_ar_membership_plan_ids',true);
        
        if(!empty($membership_ids) && !is_array($membership_ids)){
        	$label = apply_filters('wplms_take_this_course_button_label',__('TAKE THIS COURSE','vibe'),$course_id);	
        }
      
      	return $label;
    }
}

WPLMS_ARMember_Init::init();