<?php


/**
 * Plugin Name: TFC Custom Meta Box
 * Plugin URI: http://uly.me
 * Description: Creates a custom meta box for posts and pages.
 * Version: 1.0
 * Author: Ulysses Ronquillo
 * Author URI: http://uly.me
 * License: GPL2
 */


/**
 * Adds a box to the main column on the Post and Page edit screens.
 */

function tfc_add_custom_meta_box() {
	$screens = array( 'post', 'page' );
	foreach ( $screens as $screen ) {
		add_meta_box(
			'tfc_section_id',
			__( 'Custom Meta Box : ', 'tfc_textdomain' ),
			'tfc_custom_meta_box_callback',	
			$screen
		);
	}
}
add_action( 'add_meta_boxes', 'tfc_add_custom_meta_box' );

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */

function tfc_custom_meta_box_callback( $post ) {

	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'tfc_custom_meta_box', 'tfc_custom_meta_box_nonce' );

	/*
	 * Use get_post_meta() to retrieve an existing value
	 * from the database and use the value for the form.
	 */

	$message_speaker = get_post_meta( $post->ID, '_my_meta_value_speaker_key', true );
	$message_date = get_post_meta( $post->ID, '_my_meta_value_date_key', true );
	$message_description = get_post_meta( $post->ID, '_my_meta_value_description_key', true );
	$article_author = get_post_meta( $post->ID, '_my_meta_value_author_key', true );


	echo '<label class="tfc_message" for="tfc_message">';
	_e( 'Speaker: ', 'tfc_textdomain' );
	echo '</label>';
	echo '<input type="text" id="tfc_message_speaker" name="tfc_message_speaker" value="' . esc_attr( $message_speaker ) . '" size="30" />';
	echo '<br/>';	
	
	echo '<label class="tfc_message" for="tfc_message">';
	_e( 'Date: (Format: Dec 20, 2014) ', 'tfc_textdomain' );
	echo '</label>';
	echo '<input type="text" id="tfc_message_date" name="tfc_message_date" value="' . esc_attr( $message_date ) . '" size="30" />';
	echo '<br/>';

	echo '<label class="tfc_message" for="tfc_message">';
	_e( 'Author: ', 'tfc_textdomain' );
	echo '</label>';
	echo '<input type="text" id="tfc_article_author" name="tfc_article_author" value="' . esc_attr( $article_author ) . '" size="30" />';
	echo '<br/>';

	echo '<label class="tfc_message" for="tfc_message">';
	_e( 'Message Description: ', 'tfc_textdomain' );
	echo '</label>';
	echo '<textarea rows="4" cols="50" id="tfc_message_description" name="tfc_message_description">'. esc_attr( $message_description ) . '</textarea>';
	echo '<br/><br/>';

}

// add style to the admin dashboard

function tfc_custom_labels() {
	?>
  <style type="text/css">
  .tfc_message {
  	float:left;
  	text-align:left;
  	width:220px;
  	padding-top:5px;
  } 
  </style>
  <?php
}
add_action('admin_head', 'tfc_custom_labels');

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */

function tfc_save_meta_box_data( $post_id ) {

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['tfc_custom_meta_box_nonce'] ) ) { return; }

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['tfc_custom_meta_box_nonce'], 'tfc_custom_meta_box' ) ) { return; }

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) { return; }
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
	}

	/* OK, it's safe for us to save the data now. */
	
	// Make sure that it is set.
	if ( ! isset( $_POST['tfc_message_speaker'] ) ) { return; }
	if ( ! isset( $_POST['tfc_message_date'] ) ) { return; }
	if ( ! isset( $_POST['tfc_article_author'] ) ) { return; }
	if ( ! isset( $_POST['tfc_message_description'] ) ) { return; }


	// Sanitize user input.
	$my_tfc_message_speaker = sanitize_text_field( $_POST['tfc_message_speaker'] );
	$my_tfc_message_date = sanitize_text_field( $_POST['tfc_message_date'] );
	$my_tfc_article_author = sanitize_text_field( $_POST['tfc_article_author'] );
	$my_tfc_message_description = sanitize_text_field( $_POST['tfc_message_description'] );	

	// Update the meta field in the database.
	update_post_meta( $post_id, '_my_meta_value_speaker_key', $my_tfc_message_speaker );
	update_post_meta( $post_id, '_my_meta_value_date_key', $my_tfc_message_date);
	update_post_meta( $post_id, '_my_meta_value_author_key', $my_tfc_article_author);
	update_post_meta( $post_id, '_my_meta_value_description_key', $my_tfc_message_description);	

}
add_action( 'save_post', 'tfc_save_meta_box_data' );


// short codes!!!!

function show_tfc_message_speaker($atts, $content = NULL) {

	global $post;
	extract (shortcode_atts(array('param' => '',),$atts));
	$message_speaker = get_post_meta( $post->ID, '_my_meta_value_speaker_key', true );
	return $message_speaker;
}
add_shortcode ('show_custom_speaker', 'show_tfc_message_speaker');

function show_tfc_message_date($atts, $content = NULL) {

	global $post;
	extract (shortcode_atts(array('param' => '',),$atts));
	$message_date = get_post_meta( $post->ID, '_my_meta_value_date_key', true );
	return $message_date;
}
add_shortcode ('show_custom_date', 'show_tfc_message_date');

function show_tfc_article_author($atts, $content = NULL) {

	global $post;
	extract (shortcode_atts(array('param' => '',),$atts));
	$article_author = get_post_meta( $post->ID, '_my_meta_value_author_key', true );
	return $article_author;
}
add_shortcode ('show_article_author', 'show_tfc_article_author');

function show_tfc_message_description($atts, $content = NULL) {

	global $post;
	extract (shortcode_atts(array('param' => '',),$atts));
	$message_description = get_post_meta( $post->ID, '_my_meta_value_description_key', true );
	return $message_description;
}
add_shortcode ('show_message_description', 'show_tfc_message_description');
