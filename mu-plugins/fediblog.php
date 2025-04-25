<?php
/**
 * Plugin Name:       FediBlog Tag
 * Plugin URI:        https://github.com/pfefferle/wordpress-activitypub-snippets
 * Description:       Automatically adds the `FediBlog` tag to standard format blog posts.
 * Version:           1.0.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Matthias Pfefferle
 * Author URI:        https://notiz.blog/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       activitypub-snippets
 */

namespace Activitypub\Snippets;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add fediblog tag to standard format posts.
 *
 * @param int $post_id The post ID.
 * @param WP_Post $post The post object.
 */
function add_fediblog_tag( $post_id ) {
	// Check if this is a post and not a revision.
	if ( \wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Check if the post has standard format.
	if ( false === \get_post_format( $post_id ) ) {
		// Add the fediblog tag.
		\wp_set_post_tags( $post_id, 'FediBlog', true );
	}
}

// Hook into the save_post action.
\add_action( 'save_post', __NAMESPACE__ . '\add_fediblog_tag' );
