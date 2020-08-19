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

// load the default widgets so we can extend WP_Widget_Pages
require_once( ABSPATH . WPINC . '/default-widgets.php' );


/**
 * Extend Pages Widget 
 *
 * Adds filtering to the default Pages widget
 */
Class RT_Widget_Pages_Filtered extends WP_Widget_Pages {

 	/**
 	 * This is a copy of the 'widget' method in the default WP_Widget_Pages widget, with a single line added to run the filter
 	 */
	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Pages' ) : $instance['title'], $instance, $this->id_base);
		$sortby = empty( $instance['sortby'] ) ? 'menu_order' : $instance['sortby'];
		$exclude = empty( $instance['exclude'] ) ? '' : $instance['exclude'];

		if ( $sortby == 'menu_order' )
			$sortby = 'menu_order, post_title';

		$out = wp_list_pages( apply_filters('widget_pages_args', array('title_li' => '', 'echo' => 0, 'sort_column' => $sortby, 'exclude' => $exclude) ) );

		// run the filter
		$out = RT_Filter_Page_List::filter_html( $out );

		if ( !empty( $out ) ) {
			echo $before_widget;
			if ( $title)
				echo $before_title . $title . $after_title;
		?>
		<ul>
			<?php echo $out; ?>
		</ul>
		<?php
			echo $after_widget;
		}
	}

}

?>