<?php
/*
Plugin Name: LibraryThing Recently Reviewed widget
Description: Adds a sidebar widget to display your recently reviewed books at LibraryThing
Author: Luke Rodgers
Version: 1.0
Author URI: http://lukerodgers.ca
*/


/*  Copyright 2009  Luke Rodgers  (email : lukeasrodgers AT gmail DOT com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License Version 3 as published by
    the Free Software Foundation. (http://www.fsf.org/licensing/licenses/gpl.html)

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

*/


include_once "lastRSS.php"; // requires lastRSS parser

function widget_libraryThing_init() {
	
	if ( !function_exists('register_sidebar_widget') )
		return;

	function widget_libraryThing($args) {
		
		extract($args);

		$options = get_option('widget_libraryThing');
		$title = $options['title'];
		$titleLink = $options['titleLink'];
		$userName = $options['userName'];
		$numReviews = $options['numReviews'];
		$onlyCovers = $options['onlyCovers'] ? '1' : '0';
		
		$rss = new lastRSS;
		echo $before_widget . $before_title . '<a href="' . $titleLink . '">' . $title . '</a>' . $after_title;
		
		echo ($onlyCovers == 1) ? '' : '<ul>';
		
		if ($rs = $rss->get('http://www.librarything.com/rss/reviews/'.$userName)) { 
			$i=0;
			foreach($rs['items'] as $item) {
				preg_match('/&lt;img.+.jpg/', $item['description'], $matches);
				$matches[0] = html_entity_decode($matches[0]);
				if ($onlyCovers == 1) {
					if ($matches[0] != "") {						
						$matches[0] = $matches[0].'" alt="" />';
						echo '<a href="' . $item['link'].'">' . $matches[0] . '</a> &nbsp;';
						$i++;
					}
				}
				else {
					echo '<li><a href="' . $item['link'] . '">' . $item['title'] . '</a>';
					$i++;
				}
				if ($i == $numReviews) { break; }
			}	
		}
		echo ($onlyCovers == 1) ? '' : '</ul>';
		echo $after_widget;
	}

	// Outputs the form to let users configure the widget
	function widget_libraryThing_control() {

		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_libraryThing');
		
		if ( !is_array($options) )
			$options = array('title'=>'', 'titleLink'=>'', 'userName'=>'', 'numReviews'=>'', 'onlyCovers'=>'');
			
		if ( $_POST['libraryThing-submit'] ) {

			// Remember to sanitize and format user input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['libraryThing-title']));
			$options['titleLink'] = strip_tags(stripslashes($_POST['libraryThing-titleLink']));
			$options['userName'] = strip_tags(stripslashes($_POST['libraryThing-userName']));
			$options['numReviews'] = (int) ($_POST['libraryThing-numReviews']);
			$options['onlyCovers'] = isset($_POST['libraryThing-onlyCovers']);
			
			update_option('widget_libraryThing', $options);

		}

		// Format options to be valid HTML attributes.
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$titleLink = htmlspecialchars($options['titleLink'], ENT_QUOTES);
		$userName = htmlspecialchars($options['userName'], ENT_QUOTES);
		$numReviews = htmlspecialchars($options['numReviews'], ENT_QUOTES);
		$onlyCovers = (bool) $options['onlyCovers'];
		
		// Form HTML
		?>
		
		<p style="text-align:right;"><label for="libraryThing-title">Title: <input style="width: 200px;" id="libraryThing-title" name="libraryThing-title" type="text" value="<?php echo $title; ?>" /></label></p>
		<p style="text-align:right;"><label for="libraryThing-titleLink">Title Link: <input style="width: 200px;" id="libraryThing-titleLink" name="libraryThing-titleLink" type="text" value="<?php echo $titleLink; ?>" /></label></p>
		<p style="text-align:right;"><label for="libraryThing-userName">User name: <input style="width: 200px;" id="libraryThing-userName" name="libraryThing-userName" type="text" value="<?php echo $userName; ?>" /></label></p>
		<p style="text-align:right;"><label for="libraryThing-numReviews">Number of reviews: <input style="width: 200px;" id="libraryThing-numReviews" name="libraryThing-numReviews" type="text" value="<?php echo $numReviews; ?>" /></label></p>
		<p style="text-align:right;"><label for="libraryThing-onlyCovers">Only show covers: <input class="checkbox" <?php checked( $onlyCovers, true ); ?> id="libraryThing-onlyCovers" name="libraryThing-onlyCovers" type="checkbox" /></label></p>
		<input type="hidden" id="libraryThing-submit" name="libraryThing-submit" value="1" />
		
		<?php 
	}
	
	// Register the widget
	register_sidebar_widget(array('Library Thing', 'widgets'), 'widget_libraryThing');

	// Register the widget form control
	register_widget_control(array('Library Thing', 'widgets'), 'widget_libraryThing_control', 300, 100);
}

// Run the code later in case this loads prior to any required plugins.
add_action('widgets_init', 'widget_libraryThing_init');

?>