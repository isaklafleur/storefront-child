<?php 

class MlmSoftOptions {

	
	public $options = array();

	public function __construct() {

		/*  Setting up actions... */	
		add_action( 'admin_menu', array( $this, 'mlmsoft_add_options' )  );
		add_action( 'admin_init', array( $this, 'mlmsoft_register_settings' ) );

		$this->init();

	} 


	/**
	 * Get Wp options from Custom Settings page
	 */
	public function init() {  
 	
 		$this->options['plugin_prefix'] = 'MlmSoftPlugin_';
 		$this->options['plugin_path'] = plugin_dir_path( __DIR__ ); 
 		$this->options['blog_email'] = 'help@mlm-soft.com';
 		$this->options['blog_name'] = get_bloginfo('name'); 

		$this->options['mlm_soft_project_url'] = array('id' => $this->options['plugin_prefix'].'mlm_soft_project_url', 'label' => 'MLM Soft Project URL', 'type' => 'textfield', 'after_label' => '', 'value' => false, 'section' => 1);

		$this->options['online_office_url'] = array('id' => $this->options['plugin_prefix'].'online_office_url', 'label' => 'Online Office URL', 'type' => 'textfield', 'after_label' => '', 'value' => false, 'section' => 1);

		$this->options['api_token'] = array('id' => $this->options['plugin_prefix'].'api_token', 'label' => 'API Token', 'type' => 'textfield', 'after_label' => '', 'value' => false, 'section' => 1);

		$this->options['automatic_affiliate_header'] = array('id' => $this->options['plugin_prefix'].'automatic_affiliate_header', 'label' => 'Automatic Affiliate Header', 'type' => 'checkbox', 'after_label' => '','value' => false, 'section' => 1);

		$this->options['automatic_authorized_user_header'] = array('id' => $this->options['plugin_prefix'].'automatic_authorized_user_header', 'label' => 'Automatic Authorized User Header', 'type' => 'checkbox', 'after_label' => '','value' => false, 'section' => 1);

        $this->options['pmpro_integration'] = array('id' => $this->options['plugin_prefix'].'pmpro_integration', 'label' => 'Enable PaidMembershipsPro integration', 'type' => 'checkbox', 'after_label' => '','value' => false, 'section' => 2);		$this->options['automatic_authorized_user_header'] = array('id' => $this->options['plugin_prefix'].'automatic_authorized_user_header', 'label' => 'Automatic Authorized User Header', 'type' => 'checkbox', 'after_label' => '','value' => false, 'section' => 1);
        $this->options['wc_integration'] = array('id' => $this->options['plugin_prefix'].'wc_integration', 'label' => 'Enable WooCommerce integration', 'type' => 'checkbox', 'after_label' => '','value' => false, 'section' => 2);		$this->options['automatic_authorized_user_header'] = array('id' => $this->options['plugin_prefix'].'automatic_authorized_user_header', 'label' => 'Automatic Authorized User Header', 'type' => 'checkbox', 'after_label' => '','value' => false, 'section' => 1);
        $this->options['sale_property_alias'] = array('id' => $this->options['plugin_prefix'].'sale_property_alias', 'label' => 'CP property alias receiving volume sum', 'type' => 'textfield', 'after_label' => '','value' => false, 'section' => 2);
        $this->options['product_volume_attr'] = array('id' => $this->options['plugin_prefix'].'product_volume_attr', 'label' => 'Product volume attribute', 'type' => 'textfield', 'after_label' => '','value' => false, 'section' => 2);
        $this->options['rank_property_alias'] = array('id' => $this->options['plugin_prefix'].'rank_property_alias', 'label' => 'CP property alias for changing rank', 'type' => 'textfield', 'after_label' => '','value' => false, 'section' => 2);
        $this->options['rank_values'] = array('id' => $this->options['plugin_prefix'].'rank_values', 'label' => 'SKU/Rank associations', 'type' => 'textareafield', 'after_label' => '','value' => false, 'section' => 2);

        foreach ($this->options as $key => $option) {
			if (is_array($this->options[$key])) {
				$this->options[$key]['value'] = get_option($option['id']);
			}
		}

		return $this->options;
	}


	public function mlmsoft_add_options() {
		add_menu_page( 'Mlm Soft options', 'Mlm Soft options', 'manage_options', 'mlmsoft_options_page', array( $this, 'mlmsoft_options_callback' ), '', 4);
	}



	public function mlmsoft_options_callback() {
 	
 	?>
 		<div class="mlmsoft_options">
	 		<h1> MlmSoft Integration Settings </h1>
	 		<h2> </h2>
			<form action="options.php" class="repeater" method="POST">
				<?php
					settings_fields("mlmsoft_options");     // add hidden nonces etc.. Used when register options in DB
					do_settings_sections("mlmsoft_options_page"); // add sections with options
					submit_button();
				?>
			</form>
		</div>

		<style type="text/css">
			
			/* WP options */

			.mlmsoft_options{
			    padding:30px;
			    padding-top:50px;
			}

			.mlmsoft_label{
				margin-left: 30px;
			}

		</style>

	<?php

	}



	public function mlmsoft_register_settings()
	{

	    //Add sections
		add_settings_section( 'mlmsoft_section_1', 'Basic plugin settings', '', 'mlmsoft_options_page' );
        add_settings_section( 'mlmsoft_section_2', '3rd-party integrations', '', 'mlmsoft_options_page' );

		foreach ($this->options as $key => $option) {
			
			if (is_array($this->options[$key])) {

				// register options into DB
		    	register_setting( 'mlmsoft_options', $option['id'] );

			 	//add a particual field #1

				add_settings_field( 
					$option['id'], 
					(isset($option['label'])) ? $option['label'] : $key, 
					array( $this, 'callback_for_'.$option['type']), 
					'mlmsoft_options_page',
					'mlmsoft_section_' . $option['section'],
					array( 
						'id' => $option['id'], 
						'after_label' => $option['after_label']
					)
				);

			}

		} 

	}




	/**
	  Callback for particular option # 1
	*/

	public function callback_for_textfield( $arg ){ 
			
			?><input type="text" name="<?php echo $arg['id'] ?>" id="<?php echo $arg['id'] ?>" value="<?php echo esc_attr( get_option($arg['id']) ) ?>" size="40" /> <?php
			
			if ($arg['after_label']) {
				?>  <span class="after_label"> <?php echo $arg['after_label']; ?> </span> <?php
			}

		}

    public function callback_for_textareafield( $arg ){

        ?><textarea name="<?php echo $arg['id'] ?>" id="<?php echo $arg['id'] ?>" cols="40" rows="4"><?php echo esc_attr( get_option($arg['id']) ) ?></textarea><?php

        if ($arg['after_label']) {
            ?>  <span class="after_label"> <?php echo $arg['after_label']; ?> </span> <?php
        }

    }


	/**
	  Callback for particular option # 2
	*/

	public function callback_for_password( $arg ){ 
			
			?><input type="password" name="<?php echo $arg['id'] ?>" id="<?php echo $arg['id'] ?>" value="<?php echo esc_attr( get_option($arg['id']) ) ?>" /> <?php 
			
			if ($arg['after_label']) {
				?>  <span class="after_label"> <?php echo $arg['after_label']; ?> </span> <?php
			}

		}


	/**
	  Callback for particular option # 3
	*/

	public function callback_for_checkbox( $arg ){ 

			?><input type="checkbox" id="<?php echo $arg['id'] ?>" name="<?php echo $arg['id'] ?>" value="1" <?php checked( '1' == get_option($arg['id']) ); ?> /> <?php
			
			if ($arg['after_label']) {
				?> <span class="after_label"> <?php echo $arg['after_label']; ?> </span> <?php
			}
		}







}

