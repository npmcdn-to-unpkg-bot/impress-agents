<?php
/**
 * Holds miscellaneous functions for use in the IMPress Agents plugin
 *
 */
add_action( 'pre_get_posts', 'impa_change_sort_order' );
/**
 * Add pagination and sort by menu order for employee archives
 */
function impa_change_sort_order( $query ) {

    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    if( $query->is_main_query() && !is_admin() && is_post_type_archive( 'employee' ) || is_tax() ) {
        $query->set( 'orderby', 'menu_order' );
        $query->set( 'order', 'ASC' );
        $query->set( 'paged', $paged );
    }
}

add_image_size( 'employee-thumbnail', 150, 200, true );

add_filter( 'template_include', 'impress_agents_template_include' );
function impress_agents_template_include( $template ) {

	global $wp_query;

	$post_type = 'employee';

	if ( $wp_query->is_search && get_post_type() == 'employee' ) {
		if ( file_exists(get_stylesheet_directory() . '/search-' . $post_type . '.php') ) {
			$template = get_stylesheet_directory() . '/search-' . $post_type . '.php';
			return $template;
		} elseif ( file_exists(get_stylesheet_directory() . '/search.php' ) ) {
			return get_stylesheet_directory() . '/search.php';
		} else {
			return dirname( __FILE__ ) . '/views/archive-' . $post_type . '.php';
		}
	}
    if ( impress_agents_is_taxonomy_of($post_type) ) {
    	if ( file_exists(get_stylesheet_directory() . '/taxonomy-' . $post_type . '.php' ) ) {
    	    return get_stylesheet_directory() . '/taxonomy-' . $post_type . '.php';
    	} elseif ( file_exists(get_stylesheet_directory() . '/archive-' . $post_type . '.php' ) ) {
        	return get_stylesheet_directory() . '/archive-' . $post_type . '.php';
        } else {
            return dirname( __FILE__ ) . '/views/archive-' . $post_type . '.php';
        }
    }

	if ( is_post_type_archive( $post_type ) ) {
		if ( file_exists(get_stylesheet_directory() . '/archive-' . $post_type . '.php') ) {
			$template = get_stylesheet_directory() . '/archive-' . $post_type . '.php';
			return $template;
		} else {
			return dirname( __FILE__ ) . '/views/archive-' . $post_type . '.php';
		}
	}

	if ( is_single() && $post_type == get_post_type() ) {
		if( file_exists(get_stylesheet_directory() . '/single-' . $post_type . '.php') )
			return $template;
		else
			return dirname( __FILE__ ) . '/views/single-' . $post_type . '.php';
	}

	return $template;
}

function impa_employee_details() {
	global $post;

    $output = '';

    if (get_post_meta($post->ID, '_employee_title') != '')
        $output .= sprintf('<p class="title" itemprop="jobTitle">%s</p>', get_post_meta($post->ID, '_employee_title') );

    if (get_post_meta($post->ID, '_employee_license') != '')
        $output .= sprintf('<p class="license">%s</p>', get_post_meta($post->ID, '_employee_license') );

    if (get_post_meta($post->ID, '_employee_designations') != '')
        $output .= sprintf('<p class="designations" itemprop="awards">%s</p>', get_post_meta($post->ID, '_employee_designations') );

    if (get_post_meta($post->ID, '_employee_phone') != '')
        $output .= sprintf('<p class="tel" itemprop="telephone"><span class="type">Office</span>: <span class="value">%s</span></p>', get_post_meta($post->ID, '_employee_phone') );

    if (get_post_meta($post->ID, '_employee_mobile') != '')
        $output .= sprintf('<p class="tel" itemprop="telephone"><span class="type">Cell</span>: <span class="value">%s</span></p>', get_post_meta($post->ID, '_employee_mobile') );

    if (get_post_meta($post->ID, '_employee_fax') != '')
        $output .= sprintf('<p class="tel fax" itemprop="faxNumber"><span class="type">Fax</span>: <span class="value">%s</span></p>', get_post_meta($post->ID, '_employee_fax') );

    if (get_post_meta($post->ID, '_employee_email') != '')
        $email = get_post_meta($post->ID, '_employee_email');
        $output .= sprintf('<p><a class="email" itemprop="email" href="mailto:%s">%s</a></p>', antispambot($email), antispambot($email) );

    if (get_post_meta($post->ID, '_employee_website') != '')
        $output .= sprintf('<p><a class="website" itemprop="url" href="http://%s">%s</a></p>', get_post_meta($post->ID, '_employee_website'), get_post_meta($post->ID, '_employee_website') );

    if (get_post_meta($post->ID, '_employee_city') != '' || get_post_meta($post->ID, '_employee_address') != '' || get_post_meta($post->ID, '_employee_state') != '' || get_post_meta($post->ID, '_employee_zip') != '' ) {

        $address = '<p class="adr" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';

        if (get_post_meta($post->ID, '_employee_address') != '') {
            $address .= '<span class="street-address" itemprop="streetAddress">' . get_post_meta($post->ID, '_employee_address') . '</span><br />';
        }

        if (get_post_meta($post->ID, '_employee_city') != '') {
            $address .= '<span class="locality" itemprop="addressLocality">' . get_post_meta($post->ID, '_employee_city') . '</span>, ';
        }

        if (get_post_meta($post->ID, '_employee_state') != '') {
            $address .= '<abbr class="region" itemprop="addressRegion">' . get_post_meta($post->ID, '_employee_state') . '</abbr> ';
        }

        if (get_post_meta($post->ID, '_employee_zip') != '') {
            $address .= '<span class="postal-code" itemprop="postalCode">' . get_post_meta($post->ID, '_employee_zip') . '</span>';
        }

        $address .= '</p>';

        if (get_post_meta($post->ID, '_employee_address') != '' || get_post_meta($post->ID, '_employee_city') != '' || get_post_meta($post->ID, '_employee_state') != '' || get_post_meta($post->ID, '_employee_zip') != '' ) {
            $output .= $address;
        }
    }

    return $output;
}

function impa_employee_social() {

    if (get_post_meta($post->ID, '_employee_facebook') != '' || get_post_meta($post->ID, '_employee_twitter') != '' || get_post_meta($post->ID, '_employee_linkedin') != '' || get_post_meta($post->ID, '_employee_googleplus') != '' || get_post_meta($post->ID, '_employee_pinterest') != '' || get_post_meta($post->ID, '_employee_youtube') != '' || get_post_meta($post->ID, '_employee_instagram') != '') {

        $output = '<div class="agent-social-profiles">';

        if (get_post_meta($post->ID, '_employee_facebook') != '') {
            $output .= sprintf('<a class="icon-facebook" rel="me" itemprop="sameAs" href="%s" title="Facebook Profile"></a>', get_post_meta($post->ID, '_employee_facebook'));
        }

        if (get_post_meta($post->ID, '_employee_twitter') != '') {
            $output .= sprintf('<a class="icon-twitter" rel="me" itemprop="sameAs" href="%s" title="Twitter Profile"></a>', get_post_meta($post->ID, '_employee_twitter'));
        }

        if (get_post_meta($post->ID, '_employee_linkedin') != '') {
            $output .= sprintf('<a class="icon-linkedin" rel="me" itemprop="sameAs" href="%s" title="LinkedIn Profile"></a>', get_post_meta($post->ID, '_employee_linkedin'));
        }

        if (get_post_meta($post->ID, '_employee_googleplus') != '') {
            $output .= sprintf('<a class="icon-gplus" rel="me" itemprop="sameAs" href="%s" title="Google+ Profile"></a>', get_post_meta($post->ID, '_employee_googleplus'));
        }

        if (get_post_meta($post->ID, '_employee_pinterest') != '') {
            $output .= sprintf('<a class="icon-pinterest" rel="me" itemprop="sameAs" href="%s" title="Pinterest Profile"></a>', get_post_meta($post->ID, '_employee_pinterest'));
        }

        if (get_post_meta($post->ID, '_employee_youtube') != '') {
            $output .= sprintf('<a class="icon-youtube" rel="me" itemprop="sameAs" href="%s" title="YouTube Profile"></a>', get_post_meta($post->ID, '_employee_youtube'));
        }

        if (get_post_meta($post->ID, '_employee_instagram') != '') {
            $output .= sprintf('<a class="icon-instagram" rel="me" itemprop="sameAs" href="%s" title="Instagram Profile"></a>', get_post_meta($post->ID, '_employee_instagram'));
        }

        $output .= '</div><!-- .employee-social-profiles -->';

        return $output;
    }
}

/**
 * Displays the job type of a employee
 */
function impress_agents_get_job_types($post_id = null) {

	if ( null == $post_id ) {
		global $post;
		$post_id = $post->ID;
	}

	$employee_job_types = wp_get_object_terms($post_id, 'job-types');

	if ( empty($employee_job_types) || is_wp_error($employee_job_types) ) {
		return;
	}

	foreach($employee_job_types as $type) {
		return $type->name;
	}
}

/**
 * Displays the office of a employee
 */
function impress_agents_get_offices($post_id = null) {

	if ( null == $post_id ) {
		global $post;
		$post_id = $post->ID;
	}

	$employee_occifcs = wp_get_object_terms($post_id, 'occifcs');

	if ( empty($employee_occifcs) || is_wp_error($employee_occifcs) ) {
		return;
	}

	foreach($employee_occifcs as $office) {
		return $office->name;
	}
}

function impress_agents_post_number( $query ) {

	if ( !$query->is_main_query() || is_admin() || !is_post_type_archive('employee') ) {
		return;
	}

	$options = get_option('plugin_impress_agents_settings');

	$archive_posts_num = $options['impress_agents_archive_posts_num'];

	if ( empty($archive_posts_num) ) {
		$archive_posts_num = '9';
	}

	$query->query_vars['posts_per_page'] = $archive_posts_num;

}
add_action( 'pre_get_posts', 'impress_agents_post_number' );

/**
 * Add Employees to "At a glance" Dashboard widget
 */
add_filter( 'dashboard_glance_items', 'impress_agents_glance_items', 10, 1 );
function impress_agents_glance_items( $items = array() ) {

    $post_types = array( 'employee' );

    foreach( $post_types as $type ) {

        if( ! post_type_exists( $type ) ) continue;

        $num_posts = wp_count_posts( $type );

        if( $num_posts ) {

            $published = intval( $num_posts->publish );
            $post_type = get_post_type_object( $type );

            $text = _n( '%s ' . $post_type->labels->singular_name, '%s ' . $post_type->labels->name, $published, 'impress_agents' );
            $text = sprintf( $text, number_format_i18n( $published ) );

            if ( current_user_can( $post_type->cap->edit_posts ) ) {
                $items[] = sprintf( '<a class="%1$s-count" href="edit.php?post_type=%1$s">%2$s</a>', $type, $text ) . "\n";
            } else {
                $items[] = sprintf( '<span class="%1$s-count">%2$s</span>', $type, $text ) . "\n";
            }
        }
    }

    return $items;
}

/**
 * Add Employees to Jetpack Omnisearch
 */
if ( class_exists( 'Jetpack_Omnisearch_Posts' ) ) {
	new Jetpack_Omnisearch_Posts( 'employee' );
}

/**
 * Function to return term image for use on front end
 * @param  num  $term_id the id of the term
 * @param  boolean $html    use html wrapper with wp_get_attachment_image
 * @return mixed  the image with html markup or the image id
 */
function impress_agents_term_image( $term_id, $html = true, $size = 'full' ) {
	$image_id = get_term_meta( $term_id, 'impa_term_image', true );
	return $image_id && $html ? wp_get_attachment_image( $image_id, $size, false, array('class' => 'impress-agents-term-image') ) : $image_id;
}
