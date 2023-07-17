<?php
/*
Plugin Name: Generating Faq's
Description: Generating faq's by shortcode.
Version: 1.0
Author: Syed Mustajab
Author URI: Your Website
*/

function faq_generator_register_post_type() {
    $labels = array(
        'name'               => 'FAQs',
        'singular_name'      => 'FAQ',
        'add_new'            => 'Add New FAQ',
        'add_new_item'       => 'Add New FAQ',
        'edit_item'          => 'Edit FAQ',
        'new_item'           => 'New FAQ',
        'view_item'          => 'View FAQ',
        'search_items'       => 'Search FAQs',
        'not_found'          => 'No FAQs found',
        'not_found_in_trash' => 'No FAQs found in trash',
        'parent_item_colon'  => '',
        'menu_name'          => 'FAQs'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'rewrite'             => array( 'slug' => 'faq' ),
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => null,
        'supports'            => array( 'title', 'editor' ),
    );

    register_post_type( 'faq', $args );
}
add_action( 'init', 'faq_generator_register_post_type' );

function faq_generator_meta_boxes() {
    add_meta_box(
        'faq-generator-meta',
        'FAQ Details',
        'faq_generator_meta_box_callback',
        'faq',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'faq_generator_meta_boxes' );

function faq_generator_meta_box_callback( $post ) {
    wp_nonce_field( 'faq_generator_save_meta', 'faq_generator_meta_nonce' );

    $question = get_post_meta( $post->ID, 'faq_question', true );
    $answer = get_post_meta( $post->ID, 'faq_answer', true );
    ?>
    <p>
        <label for="faq_question">Question:</label>
        <input type="text" id="faq_question" name="faq_question" value="<?php echo esc_attr( $question ); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="faq_answer">Answer:</label>
        <textarea id="faq_answer" name="faq_answer" style="width: 100%; height: 150px;"><?php echo esc_textarea( $answer ); ?></textarea>
    </p>
    <?php
}

function faq_generator_save_meta( $post_id ) {
    if ( ! isset( $_POST['faq_generator_meta_nonce'] ) || ! wp_verify_nonce( $_POST['faq_generator_meta_nonce'], 'faq_generator_save_meta' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['faq_question'] ) ) {
        update_post_meta( $post_id, 'faq_question', sanitize_text_field( $_POST['faq_question'] ) );
    }

    if ( isset( $_POST['faq_answer'] ) ) {
        update_post_meta( $post_id, 'faq_answer', wp_kses_post( $_POST['faq_answer'] ) );
    }
}
add_action( 'save_post_faq', 'faq_generator_save_meta' );

function faq_generator_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'count' => -1,
    ), $atts );

    $args = array(
        'post_type'      => 'faq',
        'posts_per_page' => $atts['count'],
    );

    $faqs = get_posts( $args );

    ob_start();
    ?>
    <div class="faq-list">
        <?php foreach ( $faqs as $faq ) : ?>
            <div class="faq">
                <h3><?php echo esc_html( get_post_meta( $faq->ID, 'faq_question', true ) ); ?></h3>
                <div class="answer"><?php echo wp_kses_post( get_post_meta( $faq->ID, 'faq_answer', true ) ); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'faq_generator', 'faq_generator_shortcode' );
