<?php
/*
Plugin Name:	WP Custom Sitemap
Plugin URI:		
Description:	WP Custom Sitemap display the content as a single linked list of authors, posts and or pages!
Version:			0.1.0
Author:				Oscar Chavez
Author URI:		
Text Domain:	wp-custom-sitemap
*/

/*
	Copyright 2016 Oscar Chavez (email : chavez.oe@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

/* wpcsm_ prefix is derived from [W]ord[P]ress [c]ustom [s]ite[m]ap. */
add_shortcode('wp-custom-sitemap', 'wpcsm_render_sitemap' );
add_shortcode('wp-custom-sitemap-categories', 'wpcsm_render_sitemap_categories' );
add_action('admin_init', 'wpcsm_init' );
add_action('admin_menu', 'wpcsm_add_options_page' );
add_filter('plugin_action_links', 'wpcsm_plugin_settings_link', 10, 2 );
add_filter('widget_text', 'do_shortcode' ); // make sitemap shortcode work in text widgets
add_action('plugins_loaded', 'wpcsm_localize_plugin' );
add_action('admin_notices', 'wpcsm_admin_notice' );
register_activation_hook( __FILE__, 'wpcsm_admin_notice_set_transient' );

/* Runs only when the plugin is activated. */
function wpcsm_admin_notice_set_transient() {
	/* Create transient data */
	set_transient( 'wpcsm-admin-notice', true, 5 );
}

/* Admin Notice on Activation. */
function wpcsm_admin_notice(){
	/* Check transient, if available display notice */
	if( get_transient( 'wpcsm-admin-notice' ) ){
		?>
		<div class="updated notice is-dismissible">
			<p>Welcome to WP Custom Sitemap
<!--
				<a href="https://wpgoplugins.com/plugins/wp-custom-sitemap-pro/" target="_blank"><strong>WP Custom Sitemap PRO</strong></a> is now available! Access great new features such as sitemap image icons, captions, and beautiful responsive tabbed layouts. <b>Try risk free today with our 100% money back guarantee! <span class="dashicons dashicons-smiley"></span></b>
-->
			</p>
		</div>
		<?php
		/* Delete transient, only display this notice once. */
		delete_transient( 'wpcsm-admin-notice' );
	}
}

/* Init plugin options to white list our options. */
function wpcsm_init() {
	register_setting( 'wpcsm_plugin_options', 'wpcsm_options', 'wpcsm_validate_options' );
}

/* Add menu page. */
function wpcsm_add_options_page() {
	add_options_page( __( 'WP Custom Sitemap Options Page', 'wp-custom-sitemap' ), __( 'WP Custom Sitemap', 'wp-custom-sitemap' ), 'manage_options', __FILE__, 'wpcsm_render_form' );
}

/* Draw the menu page itself. */
function wpcsm_render_form() {
	?>
	<div class="wrap">

		<h2><?php _e('WP Custom Sitemap Options', 'wp-custom-sitemap'); ?></h2>
		<div class="notice" style="border: 2px #DAA520 solid;margin: 20px 0;">
			<p>Welcome to WP Custom Sitemap</p>
		</div>
		<p><?php _e('First version 0.1.0 of WP Custom Sitemap. Plugin has been rewritten to provide much more flexibility. You now have access to a range of shortcode attributes to customize how the sitemap renders.', 'wp-custom-sitemap'); ?></p>

		<h2><?php _e('By default is set to display types="post"', 'wp-custom-sitemap' ); ?></h2>
		<div style="background:#fff;border: 1px dashed #ccc;font-size: 13px;margin: 20px 0 10px 0;padding: 5px 0 5px 8px;">
			<?php
			printf(__('To display WP Custom Sitemap on a post, page, or sidebar (via a Text widget), enter the following shortcode (default = post):<br><br>', 'wp-custom-sitemap')); ?> 
			<code>[wp-custom-sitemap]</code><br><br>
		</div>

		<h2><?php _e('Choose the Post Types to Display', 'wp-custom-sitemap'); ?></h2>
		<p><?php _e('You now have full control over what post types are displayed as well as the order they are rendered.', 'wp-custom-sitemap'); ?></p>
		<div style="background:#fff;border: 1px dashed #ccc;font-size: 13px;margin: 20px 0 10px 0;padding: 5px 0 5px 8px;">
			<?php printf(__('Specify post types and order.<br>', 'wp-custom-sitemap')); ?>
			<br><code>e.g. [wp-custom-sitemap types="post, page, testimonial, download"]</code><br><br>
			<?php printf(__('Choose from any of the following registered post types currently available:<br><br>', 'wp-custom-sitemap')); ?>
			<?php
			$registered_post_types = get_post_types();
			$registered_post_types_str = implode(', ', $registered_post_types);
			echo '<code>' . $registered_post_types_str . '</code><br><br>';
			?>
		</div>

		<h2><?php _e('Display post grouped by categories with the option to exclude any category', 'wp-custom-sitemap'); ?></h2>
		<div style="background:#fff;border: 1px dashed #ccc;font-size: 13px;margin: 20px 0 10px 0;padding: 5px 0 5px 8px;">
			<?php
			printf(__('To display WP Custom Sitemap posts grouped by categories on a post, page, or sidebar (via a Text widget), enter the following shortcode:<br><br>', 'wp-custom-sitemap' ) ); ?>
			<code>[wp-custom-sitemap-categories exclude='1, 6, 9']</code><br><br>
		</div>

		<h2><?php _e( 'Formatting the Sitemap Output', 'wp-custom-sitemap' ); ?></h2>

		<p><?php _e( 'You have various options for controlling how your sitemap displays.', 'wp-custom-sitemap' ); ?></p>

		<div style="background:#fff;border: 1px dashed #ccc;font-size: 13px;margin: 20px 0 10px 0;padding: 5px 0 5px 8px;">
			<?php printf(__('Show a heading label for each post type as well as display a list of links or plain text. If you are outputting pages then you can also control page depth too (for page hierarchies).<br>', 'wp-custom-sitemap' ) ); ?>
			<br>For the <code>order</code> attribute specify <code>asc</code> for ascending, or <code>desc</code> for descending post sort order. As for the <code>orderby</code> attribute you can filter posts by any of the <code>orderby</code> paramters used in the <code>WP_Query</code> class such as <code>title</code>, <code>date</code>, <code>author</code>, <code>ID</code>, <code>menu_order</code> etc. See the full list <a href="https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank">here</a>. The <code>exclude</code> attribute simply takes a comma separated list of post IDs.
			<br><br><code>[wp-custom-sitemap show_label="true" links="true" page_depth="1" order="asc" orderby="title" exclude="1,2,3"]</code>
			<br><br><b>defaults:<br>
			show_label="true"<br>
			links="true"<br>
			page_depth="0"<br>
			order="asc"<br>
			orderby="title"<br>
			exclude=""<br><br></b>
		</div>
		<div style="clear:both;"></div>
	</div>
<?php
}

/* Shortcode function. */
function wpcsm_render_sitemap_categories($args){
	/* Get slider attributes from the shortcode */
	extract(shortcode_atts(array(
		'taxonomy'	=> '', // (string|array) Taxonomy name, or array of taxonomies, to which results should be limited.
		'orderby'		=> '', // (string) ('name', 'slug', 'term_group', 'term_id', 'id', 'description') - defaults 'name'
		'order'			=> '', // (string) 'ASC' - 'DESC' - default 'ASC'
		'exclude'		=> '', // (array|string) Array or comma/space-separated string of term ids to exclude. If $include is non-empty, $exclude is ignored.
	), $args));

	// escape tag names
	$title_tag			= tag_escape( $title_tag );
	$excerpt_tag		= tag_escape( $excerpt_tag );
	$post_type_tag	= tag_escape( $post_type_tag );

	$cats = get_categories($args);
	$html = "";
	$html .=
	'<h2 id="posts">Posts</h2>' .
	'<ul>';
	foreach ($cats as $cat){
		$html .=
		"<li>" .
			"<h3>" . $cat->cat_name . "</h3>";
		$html .=
			"<ul>";
		query_posts('posts_per_page=-1&cat=' . $cat->cat_ID);
		while(have_posts()){
			the_post();
			$category = get_the_category();
			// Only display a post link once, even if it's in multiple categories
			if($category[0]->cat_ID == $cat->cat_ID){
				$html .=
				'<li>' . '<a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
			}
		}
		$html .=
			"</ul>";
		$html .=
		"</li>";
	}
	$html .=
	'</ul>';
/* End		- Display Posts */
	return $html;
}

/* Shortcode function. */
function wpcsm_render_sitemap($args) {
	/* Get slider attributes from the shortcode. */
	extract(shortcode_atts(array(
		'types'					=> 'post',
		'show_excerpt'	=> 'false',
		'title_tag'			=> '',
		'excerpt_tag'		=> 'div',
		'post_type_tag'	=> 'h2',
		'show_label'		=> 'true',
		'links'					=> 'true',
		'page_depth'		=> 0,
		'order'					=> 'asc',
		'orderby'				=> 'title',
		'exclude'				=> '1'
	), $args));

	// escape tag names
	$title_tag			= tag_escape( $title_tag );
	$excerpt_tag		= tag_escape( $excerpt_tag );
	$post_type_tag	= tag_escape( $post_type_tag );

	$page_depth = intval( $page_depth );
	$post_types = $types; // allows the use of the shorter 'types' rather than 'post_types' in the shortcode

	// Start output caching (so that existing content in the [wp-custom-sitemap] post doesn't get shoved to the bottom of the post
	ob_start();

	// *************
	// CONTENT START
	// *************

	$post_types							= array_map( 'trim', explode( ',', $post_types ) ); // convert comma separated string to array
	$exclude								= array_map( 'trim', explode( ',', $exclude) ); // must be array to work in the post query
	$registered_post_types	= get_post_types();

	//echo "<pre>";
	//print_r($registered_post_types);
	//print_r($post_types);
	//print_r($exclude);
	//echo "</pre>";

	foreach( $post_types as $post_type ) :

		// generate <ul> element class
		$ul_class = 'wp-custom-sitemap-' . $post_type;

		// bail if post type isn't valid
		if( !array_key_exists( $post_type, $registered_post_types ) ) {
			break;
		}

		// set opening and closing title tag
		if( !empty($title_tag) ) {
			$title_open = '<' . $title_tag . '>';
			$title_close = '</' . $title_tag . '>';
		}
		else {
			$title_open = $title_close = '';
		}

		// conditionally show label for each post type
		if( $show_label == 'true' ) {
			$post_type_obj  = get_post_type_object( $post_type );
			$post_type_name = $post_type_obj->labels->name;
			echo '<' . $post_type_tag . '>' . esc_html($post_type_name) . '</' . $post_type_tag . '>';
		}

		$query_args = array(
			'posts_per_page' => -1,
			'post_type' => $post_type,
			'order' => $order,
			'orderby' => $orderby,
			'post__not_in' => $exclude
		);

		// use custom rendering for 'page' post type to properly render sub pages
		if( $post_type == 'page' ) {
			$arr = array(
				'title_tag' => $title_tag,
				'links' => $links,
				'title_open' => $title_open,
				'title_close' => $title_close,
				'page_depth' => $page_depth,
				'exclude' => $exclude
			);
			echo '<ul class="' . esc_attr($ul_class) . '">';
			wpcsm_list_pages($arr, $query_args);
			echo '</ul>';
			continue;
		}

		//post query
		$sitemap_query = new WP_Query( $query_args );

		if ( $sitemap_query->have_posts() ) :

			echo '<ul class="' . esc_attr($ul_class) . '">';

			// start of the loop
			while ( $sitemap_query->have_posts() ) : $sitemap_query->the_post();

				// title
				$title_text = get_the_title();

				if( !empty( $title_text ) ) {
					if ( $links == 'true' ) {
						$title = $title_open . '<a href="' . esc_url(get_permalink()) . '">' . esc_html($title_text) . '</a>' . $title_close;
					} else {
						$title = $title_open . esc_html($title_text) . $title_close;
					}
				}
				else {
					if ( $links == 'true' ) {
						$title = $title_open . '<a href="' . esc_url(get_permalink()) . '">' . '(no title)' . '</a>' . $title_close;
					} else {
						$title = $title_open . '(no title)' . $title_close;
					}
				}

				// excerpt
				$excerpt = $show_excerpt == 'true' ? '<' . $excerpt_tag . '>' . esc_html(get_the_excerpt()) . '</' . $excerpt_tag . '>' : '';

				// render list item
				echo '<li>';
				echo $title;
				echo $excerpt;
				echo '</li>';

			endwhile; // end of post loop -->

			echo '</ul>';

			// put pagination functions here
			wp_reset_postdata();

		else:

			echo '<p>' . __( 'Sorry, no posts matched your criteria.', 'wpgo-wp-custom-sitemap-pro' ) . '</p>';

		endif;

	endforeach;

	// ***********
	// CONTENT END
	// ***********

	$sitemap = ob_get_contents();
	ob_end_clean();

	return wp_kses_post($sitemap);
}

function wpcsm_list_pages( $arr, $query_args ) {

	$map_args = array(
		'title' => 'post_title',
		'date' => 'post_date',
		'author' => 'post_author',
		'modified' => 'post_modified'
	);

	// modify the query args for get_pages() if necessary
	$orderby = array_key_exists( $query_args['orderby'], $map_args ) ? $map_args[$query_args['orderby']] : $query_args['orderby'];

	$r = array(
		'depth' => $arr['page_depth'],
		'show_date' => '',
		'date_format' => get_option( 'date_format' ),
		'child_of' => 0,
		'exclude' => $arr['exclude'],
		'echo' => 1,
		'authors' => '',
		'sort_column' => $orderby,
		'sort_order' => $query_args['order'],
		'link_before' => '',
		'link_after' => '',
		'walker' => '',
	);

	$output = '';
	$current_page = 0;
	$r['exclude'] = preg_replace( '/[^0-9,]/', '', $r['exclude'] ); // sanitize, mostly to keep spaces out

	// Query pages.
	$r['hierarchical'] = 0;
	$pages = get_pages( $r );

	if ( ! empty( $pages ) ) {
		global $wp_query;
		if ( is_page() || is_attachment() || $wp_query->is_posts_page ) {
			$current_page = get_queried_object_id();
		} elseif ( is_singular() ) {
			$queried_object = get_queried_object();
			if ( is_post_type_hierarchical( $queried_object->post_type ) ) {
				$current_page = $queried_object->ID;
			}
		}

		$output .= walk_page_tree( $pages, $r['depth'], $current_page, $r );
	}

	// remove links
	if( $arr['links'] != 'true' )
		$output = preg_replace('/<a href=\"(.*?)\">(.*?)<\/a>/', "\\2", $output);

	if ( $r['echo'] ) {
		echo $output;
	} else {
		return $output;
	}
}

/* Display a Settings link on the main Plugins page */
function wpcsm_plugin_settings_link( $links, $file ) {

	if ( $file == plugin_basename( __FILE__ ) ) {
		$posk_links .= '<a href="' . esc_url(get_admin_url() . 'options-general.php?page=wp-custom-sitemap/wp-custom-sitemap.php' ) . '">' . __( 'Settings', 'wp-custom-sitemap' ) . '</a>';
	}

	return $links;
}

/* Sanitize and validate input. Accepts an array, return a sanitized array. */
function wpcsm_validate_options( $input ) {
	// Strip html from textboxes
	// e.g. $input['textbox'] =  wp_filter_nohtml_kses($input['textbox']);
	$input['txt_page_ids'] = sanitize_text_field( $input['txt_page_ids'] );

	return $input;
}

/* Add Plugin localization support. */
function wpcsm_localize_plugin() {
	load_plugin_textdomain( 'wp-custom-sitemap', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
