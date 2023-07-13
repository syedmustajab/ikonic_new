<?php 
function redirect_user_by_ip() {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $ip_prefix = '77.29.';
    
    if (strpos($ip_address, $ip_prefix) === 0) {
        wp_redirect('hhttps://www.google.com/');
        exit();
    }
}
add_action('wp_head', 'redirect_user_by_ip');

function create_projects_post_type() {
    $labels = array(
        'name'               => 'Projects',
        'singular_name'      => 'Project',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Project',
        'edit_item'          => 'Edit Project',
        'new_item'           => 'New Project',
        'view_item'          => 'View Project',
        'search_items'       => 'Search Projects',
        'not_found'          => 'No projects found',
        'not_found_in_trash' => 'No projects found in trash',
        'parent_item_colon'  => '',
        'menu_name'          => 'Projects'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'publicly_queryable'  => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'projects'),
        'capability_type'     => 'post',
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions'),
        'taxonomies'          => array('project_type'),
        'menu_icon'           => 'dashicons-clipboard',
        'show_in_rest'        => true 
    );

    register_post_type('project', $args);
}
add_action('init', 'create_projects_post_type');

function create_project_type_taxonomy() {
    $labels = array(
        'name'              => 'Project Types',
        'singular_name'     => 'Project Type',
        'search_items'      => 'Search Project Types',
        'all_items'         => 'All Project Types',
        'parent_item'       => 'Parent Project Type',
        'parent_item_colon' => 'Parent Project Type:',
        'edit_item'         => 'Edit Project Type',
        'update_item'       => 'Update Project Type',
        'add_new_item'      => 'Add New Project Type',
        'new_item_name'     => 'New Project Type Name',
        'menu_name'         => 'Project Types'
    );

    $args = array(
        'labels'       => $labels,
        'hierarchical' => true,
        'rewrite'      => array('slug' => 'project-type'),
        'show_admin_column' => true,
        'show_in_rest' => true 
    );

    register_taxonomy('project_type', 'project', $args);
}
add_action('init', 'create_project_type_taxonomy');

function custom_ajax_projects_callback($request) {
    $project_type = 'architecture'; // Project type slug

    $posts_per_page = 3; // Number of posts to display

    if (is_user_logged_in()) {
        wp_set_current_user(get_current_user_id()); // Set current user
        $posts_per_page = 6; // Update the number of posts if the user is logged in
    }

    $args = array(
        'post_type'      => 'project',
        'posts_per_page' => $posts_per_page,
        'tax_query'      => array(
            array(
                'taxonomy' => 'project_type',
                'field'    => 'slug',
                'terms'    => $project_type
            )
        )
    );

    $projects = get_posts($args);

    $response = array(
        'success' => true,
        'data'    => array(),
        'logged_in' => is_user_logged_in() // Add a 'logged_in' field indicating the user login status
    );

    if ($projects) {
        foreach ($projects as $project) {
            $project_id = $project->ID;
            $project_title = $project->post_title;
            $project_link = get_permalink($project_id);

            $response['data'][] = array(
                'id'    => $project_id,
                'title' => $project_title,
                'link'  => $project_link
            );
        }
    }

    return $response;
}

function register_custom_ajax_projects_endpoint() {
    register_rest_route('custom/v1', '/ajax-projects', array(
        'methods'  => 'GET',
        'callback' => 'custom_ajax_projects_callback',
    ));
}
add_action('rest_api_init', 'register_custom_ajax_projects_endpoint');


function custom_ajax_projects() {
    $project_type = 'architecture'; // Project type slug

    $args = array(
        'post_type'      => 'project',
        'posts_per_page' => is_user_logged_in() ? 6 : 3, // Adjust the number of posts based on user login status
        'tax_query'      => array(
            array(
                'taxonomy' => 'project_type',
                'field'    => 'slug',
                'terms'    => $project_type
            )
        )
    );

    $projects = new WP_Query($args);

    $response = array(
        'success' => true,
        'data'    => array()
    );

    if ($projects->have_posts()) {
        while ($projects->have_posts()) {
            $projects->the_post();
            $project_id = get_the_ID();
            $project_title = get_the_title();
            $project_link = get_permalink();

            $response['data'][] = array(
                'id'    => $project_id,
                'title' => $project_title,
                'link'  => $project_link
            );
        }
    }

    wp_reset_postdata();

    wp_send_json($response);
}
add_action('wp_ajax_nopriv_custom_ajax_projects', 'custom_ajax_projects'); // For non-logged-in users
add_action('wp_ajax_custom_ajax_projects', 'custom_ajax_projects'); // For logged-in users


// function enqueue_custom_scripts() {
//     wp_enqueue_script('custom-script', get_stylesheet_directory_uri() . '/scripts.js', array('jquery'), '1.0', true);
// }
// add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

function enqueue_custom_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('custom-script', get_stylesheet_directory_uri() . '/scripts.js', array('jquery'), '1.0', true);

    // Pass the ajaxurl value to the custom script
    wp_localize_script('custom-script', 'custom_script_vars', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

function hs_give_me_coffee() {
    $api_url = 'https://coffee.alexflipnote.dev/random.json'; // API endpoint URL

    // Make the API request
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return 'Unable to fetch coffee data.';
    }

    // Get the response body
    $body = wp_remote_retrieve_body($response);

    // Decode the JSON response
    $data = json_decode($body);

    if ($data && isset($data->file)) {
        return $data->file; // Return the coffee description
    } else {
        return 'No coffee found.';
    }
}

 ?>