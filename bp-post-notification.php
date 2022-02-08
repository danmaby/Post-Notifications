<?php
/**
 * Plugin Name:     Post Notification
 * Description:     Send notifications via BuddyPress when a post is published
 * Author:          Dan Maby
 * Author URI:      https://www.blue37.com
 * Version:         1.0.0
 */
 /**
  * @param array $component_names
  *
  * @return array
  * Add a custom component for notification.
  *
  */
 function add_custom_notification_component( $component_names = array() ) {
	 // Force $component_names to be an array.
	 if ( ! is_array( $component_names ) ) {
		 $component_names = array();
	 }
	 // Add 'custom' component to registered components array.
	 array_push( $component_names, 'custom' );
	 // Return component's with 'custom' appended.
	 return $component_names;
 }
 add_filter( 'bp_notifications_get_registered_components', 'add_custom_notification_component' );
 
 /**
  * @param $post_id
  * @param $post
  *
  * Add custom notification to database.
  */
 function bp_post_published_notification( $post_id, $post ) {
	 $author_id = $post->post_author; /* Post author ID. */
	 $users     = get_users();
	 /* Loop all users */
	 foreach ( $users as $user ) {
		 if ( bp_is_active( 'notifications' ) ) {
			 bp_notifications_add_notification(
				 array(
					 'user_id'          => $user->ID,
					 'item_id'          => $post_id,
					 'component_name'   => 'custom',
					 'component_action' => 'custom_action',
					 'date_notified'    => bp_core_current_time(),
					 'is_new'           => 1,
				 )
			 );
		 }
	 }
 }
 add_action( 'publish_post', 'bp_post_published_notification', 99, 2 );
 
 /**
  * @param $action
  * @param $item_id
  * @param $total_items
  * @param string $format
  * @return mixed
  *
  * Display notification on member notifications list and admin bar.
  */
 function custom_format_buddypress_notifications( $action, $item_id, $total_items, $format = 'string' ) {
	 // New custom notifications.
	 if ( 'custom_action' === $action ) {
		 $post = get_post( $item_id );
		 $custom_title = $post->post_title;
		 $custom_link  = get_permalink( $post->ID );
		 $author_name  = get_the_author_meta( 'display_name', $post->post_author );
		 $custom_text = $author_name . ' published a post ' . $custom_title;
		 // WordPress Toolbar.
		 if ( 'string' === $format ) {
			 $return = apply_filters( 'custom_filter', '' . esc_html( $custom_text ) . '', $custom_text, $custom_link );
			 // Deprecated BuddyBar.
		 } else {
			 $return = apply_filters(
				 'custom_filter',
				 array(
					 'text' => $custom_text,
					 'link' => $custom_link,
				 ),
				 $custom_link,
				 (int) $total_items,
				 $custom_text,
				 $custom_title
			 );
		 }
		 return $return;
	 }
 }
 add_filter( 'bp_notifications_get_notifications_for_user', 'custom_format_buddypress_notifications', 10, 5 );
