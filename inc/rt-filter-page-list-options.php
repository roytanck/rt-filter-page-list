<?php

/*
Copyright 2014 Roy Tanck (email: roy.tanck@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

// if called without WordPress, exit
if( !defined('ABSPATH') ){ exit; }


class RTFilterPageListSettingsPage {

	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct(){
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_action( 'admin_notices', array( $this, 'show_messages' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page(){
		// create the new options page
		add_options_page(
			__( 'RT Filter Page List Settings', 'rt-filter-page-list' ),
			__( 'RT Filter Page List', 'rt-filter-page-list' ), 
			'manage_options', 
			'rt-filter-page-list-settings', 
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page()
	{
		global $wp_version;
		$this->options = get_option( 'rt_filter_page_list_option' );
		// start output
		echo '<div class="wrap">';
		// add a screen icon for older, still supported WP versions
		if( version_compare( $wp_version, '3.8', '<' ) ){
			screen_icon();
		}
		// check the WP version to output the right type of main page heading
		if( version_compare( $wp_version, '4.3', '>=' ) ){
			echo '<h1>' . __( 'RT Filter Page List', 'rt-filter-page-list' ) . '</h1>';
		} else {
			echo '<h2>' . __( 'RT Filter Page List', 'rt-filter-page-list' ) . '</h2>';
		}
		// render the form
		echo '<form method="post" action="options.php">';
		settings_fields( 'rt_filter_page_list_option_group' );   
		do_settings_sections( 'rt-filter-page-list-options' );
		submit_button();
		echo '</form>';
		// wrap up
		echo '</div>';
	}

	/**
	 * Register and add settings
	 */
	public function page_init()
	{
		register_setting(
			'rt_filter_page_list_option_group', // Option group
			'rt_filter_page_list_option', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'rt_filter_page_list_general', // ID
			__( 'General settings', 'rt-filter-page-list' ), // Title
			array( $this, 'print_section_info' ), // Callback
			'rt-filter-page-list-options' // Page
		);

		add_settings_field(
			'filtertype', // ID
			__('Filter type', 'rt-filter-page-list' ), // Title
			array( $this, 'filtertype_callback' ), // Callback
			'rt-filter-page-list-options', // Page
			'rt_filter_page_list_general' // Section           
		);   
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input )
	{
		$new_input = array();

		if( isset( $input['filtertype'] ) ){
			$new_input['filtertype'] = in_array( $input['filtertype'], array( 'widget', 'global', 'none' ) ) ? $input['filtertype'] : 'widget';
		}

		return $new_input;
	}


	/** 
	 * Print the Section text
	 */
	public function print_section_info()
	{
		//print __( 'Enter your settings below:' 'rt-filter-page-list' );
	}


	/** 
	 * Get the settings option array and print one of its values
	 */
	function filtertype_callback() { 
		$val = isset( $this->options['filtertype'] ) ? $this->options['filtertype'] : 'widget';
		$html =  '<p><input type="radio" id="rt_filter_page_list_option_filtertype_widget" name="rt_filter_page_list_option[filtertype]" value="widget"' . checked( 'widget', $val, false ) . '/>';
		$html .= '<label for="rt_filter_page_list_option_filtertype_widget">' . __( 'Modify the behavior of the Pages widget', 'rt-filter-page-list' ) . '</label></p>';
		$html .= '<p><input type="radio" id="rt_filter_page_list_option_filtertype_global" name="rt_filter_page_list_option[filtertype]" value="global"' . checked( 'global', $val, false ) . '/>';
		$html .= '<label for="rt_filter_page_list_option_filtertype_global">' . __( 'Modify wp_list_pages globally', 'rt-filter-page-list' ) . '</label></p>';
		$html .= '<p><input type="radio" id="rt_filter_page_list_option_filtertype_none" name="rt_filter_page_list_option[filtertype]" value="none"' . checked( 'none', $val, false ) . '/>';
		$html .= '<label for="rt_filter_page_list_option_filtertype_none">' . __( 'None', 'rt-filter-page-list' ) . '</label></p>';
		echo $html;
	}

	function show_messages(){
		global $current_screen;
		if( $current_screen->id == 'settings_page_rt-filter-page-list-settings' && !class_exists('DOMDocument') ){
			echo '<div class="error"><p>';
			_e( 'It appears that DOMDocument is not installed on the server, please install DOMDocument to use this plugin.', 'rt-filter-page-list' );
			echo '</p></div>';
		}
	}

}

// create an instance
if( is_admin() ){
	$rt_filter_page_list_settings_page = new RTFilterPageListSettingsPage();
}