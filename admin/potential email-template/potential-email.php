<?php 
/* Custom Post Type For Potential Email templates */

function affiliates_potential_email_templates() {
// Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'Potential Emails', 'Post Type General Name', 'storefront' ),
        'singular_name'       => _x( 'Potential Email', 'Post Type Singular Name', 'storefront' ),
        'menu_name'           => __( 'Potential Emails', 'storefront' ),
        'parent_item_colon'   => __( 'Parent Potential Email', 'storefront' ),
        'all_items'           => __( 'All Potential Emails', 'storefront' ),
        'view_item'           => __( 'View Template', 'storefront' ),
        'add_new_item'        => __( 'Add New Template', 'storefront' ),
        'add_new'             => __( 'Add New', 'storefront' ),
        'edit_item'           => __( 'Edit Template', 'storefront' ),
        'update_item'         => __( 'Update Template', 'storefront' ),
        'search_items'        => __( 'Search Template', 'storefront' ),
        'not_found'           => __( 'Not Found', 'storefront' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'storefront' ),
    );
// Set other options for Custom Post Type
    $args = array(
        'label'               => __( 'template', 'storefront' ),
        'description'         => __( 'Potential Email Templates', 'storefront' ),
        'labels'              => $labels,  
        'supports'            => array( 'title' ),     
        //'taxonomies'          => array( 'genres' ),     
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
		'menu_icon'           => 'dashicons-email-alt',
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' => true, 
    );
	
	
    // Registering your Custom Post Type
    register_post_type( 'potential_email', $args );
}
add_action( 'init', 'affiliates_potential_email_templates', 0 );

//=========================Add Custom meta box in posts=========================//
add_action( 'add_meta_boxes', 'eoxys_add_custom_box' );
function eoxys_add_custom_box() {
    $screens = [ 'potential_email'];
    foreach ( $screens as $screen ) {
        add_meta_box(
            'potential_email_content',         
            'Email Template', 
            'affiliates_potential_email_box_callback',
            $screen                 
        );
		add_meta_box(
            'potential_email_key',         
            'Variable Reference Keys', 
            'affiliates_potential_email_keys_callback',
            $screen                 
        );
    }
}

//=========================Custom meta box html=========================//
function affiliates_potential_email_box_callback( $post ) {
    ?>
	<div class="eoxys_custom_fields">
		<div class="field_row">
			<textarea name="potential_content" rows="10" cols="100"><?php echo get_post_meta($post->ID, 'potential_content', true); ?></textarea>
		</div>  
	</div>
    <?php
}


//=========================Save custom fields in Template=========================//
add_action( 'save_post', 'affiliates_save_custom_fields',99,1 );
function affiliates_save_custom_fields( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	$potential_content = (isset($_POST['potential_content']) && $_POST['potential_content']!='') ? $_POST['potential_content'] : '';
	update_post_meta($post_id, 'potential_content', $potential_content);
}



function affiliates_potential_email_keys_callback(){
	?>
	<table class="widefat fixed striped">
		<tbody>
			<tr>
				<th><code>!~first_name~!</code></th>
				<td>Display First Name</td>
			</tr>
			<tr>
				<th><code>!~last_name~!</code></th>
				<td>Display Last Name</td>
			</tr>
			<tr>
				<th><code>!~mobile~!</code></th>
				<td>Display Mobile Number</td>
			</tr>
			<tr>
				<th><code>!~email~!</code></th>
				<td>Display Email Address</td>
			</tr>
			<tr>
				<th><code>!~country_code~!</code></th>
				<td>Display Country Code</td>
			</tr>
			<tr>
				<th><code>!~date_time~!</code></th>
				<td>Display Date And Time</td>
			</tr>							
		</tbody>
	</table>
	<?php 
}