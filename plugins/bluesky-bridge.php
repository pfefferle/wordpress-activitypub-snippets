<?php

/**
 * Plugin Name:      ActivityPub Bluesky Bridge
 * Description:      A plugin to bridge Bluesky and WordPress.
 * Plugin URI:       https://github.com/pfefferle/wordpress-activitypub-snippets
 * Version:          1.0.0
 * Author:           Matthias Pfefferle
 * Author URI:       https://notiz.blog
 * Text Domain:      activitypub-bluesky-bridge
 * License:          GPL-2.0-or-later
 * License URI:      https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP:     7.4
 * Requires Plugins: activitypub
 *
 * @package ActivityPub_Bluesky_Bridge
 * @license GPL-2.0-or-later
 */


namespace Activitypub\Snippets;

use Activitypub\Activity\Activity;
use Activitypub\Collection\Actors;

use function Activitypub\add_to_outbox;

define( 'ACTIVITYPUB_BRIDGE_ACTOR_ID', 'https://bsky.brid.gy/bsky.brid.gy' );

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize the plugin.
 */
function activation_hook() {
	// Get every Actor with the `activitypub` capability.
	$users = \get_users(
		array(
			'capability__in' => array( 'activitypub' ),
		)
	);

	foreach ( $users as $user ) {
		send_follow_request( $user->ID );
	}

	send_follow_request( Actors::BLOG_USER_ID );
}
\register_activation_hook( __FILE__, __NAMESPACE__ . '\activation_hook' );

/**
 * Send a follow request to the Bluesky Bridge actor.
 *
 * @param int $user_id The user ID.
 */
function send_follow_request( $user_id ) {
	$actor = Actors::get_by_id( $user_id );

	if ( ! $actor || \is_wp_error( $actor ) ) {
		return;
	}

	$activity = new Activity();
	$activity->set_type( 'Follow' );
	$activity->set_actor( $actor->get_id() );
	$activity->set_object( ACTIVITYPUB_BRIDGE_ACTOR_ID );
	$activity->set_to( array( ACTIVITYPUB_BRIDGE_ACTOR_ID ) );

	add_to_outbox( $activity, null, $user_id, ACTIVITYPUB_CONTENT_VISIBILITY_PRIVATE );
}

/**
 * Check the inbox for a follow request and add the user to the Bluesky group.
 *
 * @param array $data The data.
 * @param int $user_id The user ID.
 */
function check_inbox( $data, $user_id ) {
	if ( ! isset( $data['actor'] ) ) {
		return;
	}

	if ( ACTIVITYPUB_BRIDGE_ACTOR_ID !== $data['actor']['id'] ) {
		return;
	}

	$user = \get_user_by( 'id', $user_id );

	if ( ! $user ) {
		return;
	}

	$user->add_cap( 'bluesky' );
}
add_action( 'activitypub_inbox_follow', __NAMESPACE__ . '\check_inbox', 10, 2 );
