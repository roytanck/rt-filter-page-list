<?php
/**
 * Plugin Name: RT Filter Page List
 * Plugin URI: http://www.roytanck.com
 * Description: Filters the output of wp_list_pages, so that pages that are usually hidden through CSS are stripped from the DOM. This helps performance on sites with many pages. Requires DOMDocument.
 * Version: 1.0
 * Author: Roy Tanck
 * Author URI: http://www.roytanck.com
 * License: GPL3
*/

// if called without WordPress, exit
if( !defined('ABSPATH') ){ exit; }

// include the options page and the widget code
require_once('inc/rt-filter-page-list-options.php');
require_once('inc/rt-filter-page-list-widget.php');


if( !class_exists('RT_Filter_Page_List') ){

	class RT_Filter_Page_List {

		/**
		 * Constructor
		 */
		public function __construct(){
			// check whether DOMDocument is available before doing anything substantial
			if( class_exists('DOMDocument') ){
				// get the options
				$options = get_option( 'rt_filter_page_list_option' );

				// get the filter type, apply default if needed
				$filtertype = in_array( $options['filtertype'], array( 'widget', 'global', 'none' ) ) ? $options['filtertype'] : 'widget';

				switch( $filtertype ){

					case 'widget':
						// register the widget with WordPress
						add_action( 'widgets_init', array( $this, 'register_widget' ));
						break;

					case 'global':
						// register action
						add_filter( 'wp_list_pages', array( $this, 'filter_pages_html' ) );
						break;

				}

			} else {
				error_log( 'DOMDocument not available. Please install DOMDocument to use the RT Filter Page List plugin.' );
			}
		}


		/**
		 * Replace teh default pages widget with our modified version
		 */
		function register_widget(){
			// unregister the default widget
			unregister_widget('WP_Widget_Pages');
			// register the modified widget
			register_widget('RT_Widget_Pages_Filtered');
		}


		/**
		 * Callback function for the wp_list_pages hook
		 */
		function filter_pages_html( $html ){
			return self::filter_html( $html );
		}


		/**
		 * Function that filters out parts of the page tree that should be hidden anyway. This helps
		 * performance on slow browsers when there are a lot of pages in the site.
		 */
		public static function filter_html( $html ){

			// this is intended solely for the front-end of the site
			if( is_admin() ){
				return $html;
			}

			// collect some statistics so we can show off later
			$orglength = strlen( $html );
			$starttime = microtime(true);

			// create a DOM Document object for the page list's HTML
			$dom = new DOMDocument();

			// Use a dirty trick to make PHP DOM behave with utf-8 content.
			// Apperently DOM defaults to Latin1 when no metatag is found and it
			// ignores setting the encoding. This is a workaround which should
			// prevent from displaying 'weird' characters to the users.
			// It forces DOM to treat the loaded html file as a utf-8 encoded xml
			// file, removes the added xml encoding node and sets the encoding to
			// utf-8.
			// Thanks: http://www.php.net/manual/en/domdocument.loadhtml.php#95251
			$dom->loadHTML( '<?xml encoding="UTF-8">' . $html );

			// define the classes that we'll look for on order to determine visibility
			$parentClasses = array( 'current_page_ancestor', 'current_page_parent', 'current_page_item' );

			// find all list items (the li's contain the class attribute)
			$pagelinks = $dom->getElementsByTagName('li');

			// loop through the list items containing the a tags
			foreach( $pagelinks as $pagelink ){

				// get the parent li item
				$parentli = self::find_parent_node( $pagelink, 'li' );

				// if parent li found, look into it, if not, this is a root node, keep it.
				if( $parentli ){

					// compare classes with our parentClasses array to see if these nodes need to be visible				
					$classes = $parentli->getAttribute('class');
					$keep = false;
					foreach( $parentClasses as $parentClass ){
						$pos = strpos( $classes, $parentClass );
						if( $pos !== false ){
							$keep = true;
						}
					}

				} else {
					$keep = true;
				}

				// if keep not true, schedule for deletion by adding an attribute to the parent ul element
				if( !$keep ){
					$parentul = self::find_parent_node( $pagelink, 'ul' );
					$parentul->setAttribute( 'tobedeleted', 'true' );
				}
			}

			// remove the marked elements by recursively going through the entire DOM
			self::recursive_delete( $dom );

			// get the processed content without unwanted extra html doctype and tags
			// Thanks: http://nl.php.net/manual/en/domdocument.savehtml.php#85165
			$html = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<?xml encoding="UTF-8">','<html>', '</html>', '<body>', '</body>'), array('','', '', '', ''), $dom->saveHTML()));

			// show off
			$html .= '<!-- filtered ' . ( $orglength - strlen($html) ) . ' characters from the page list (' . round( ( ( $orglength - strlen($html) ) / $orglength ) * 100 ) . '%) in ' . round(microtime(true)-$starttime,2) . ' msec -->';

			// return the completed HTML output
			return $html;
		}


		/**
		 * Recursive function to delete nodes from the DOM
		 * This makes sure all child elements that are marked for deletion are removed before
		 * deleting the parent element. Other approaches resulted in orphaned elements and
		 * tons of PHP warnings.
		 */
		public static function recursive_delete( $node ){
			// if there are child elements, find them and loop though them
			if( $node->hasChildNodes() ){
				$children = $node->childNodes;
				// fire this function for all child element
				foreach( $children as $child ){
					self::recursive_delete( $child );
				}
			}
			// if node is of the correct type and marked for deletion, make it so
			if( get_class($node) == 'DOMElement' ){
				if( $node->hasAttribute('tobedeleted') ){
					$node->parentNode->removeChild($node);
				}
			}
		}


		/**
		 * Function to find the closest node of a certain type, searching upward in the document tree
		 */
		public static function find_parent_node( $n, $nodetype ){
			if( $n && get_class($n) == 'DOMElement' ){
				$node = $n->parentNode;
				while( $node != null ){
					if( $node->nodeName != $nodetype ){
						$node = $node->parentNode;
					} else {
						return $node;
					}
				}
				return $node;
			} else {
				return null;
			}
		}


	}

}


/**
 * Create an instance of the class, so it actually does something
 */

if(class_exists('RT_Filter_Page_List')){
    // instantiate the plugin class
    $rt_filter_page_list = new RT_Filter_Page_List();
}

?>