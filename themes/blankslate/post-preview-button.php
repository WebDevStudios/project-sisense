<?php


/**
 * Customize the preview button in the WordPress admin to point to the headless client.
 * https://medium.com/kata-engineering/headless-wordpress-next-js-what-we-learned-c10abdf80f6a#3350
 *
 * @param  str $link The WordPress preview link.
 * @return str The headless WordPress preview link.
 */
function set_headless_preview_link( $link ) {
  $hostname = $_SERVER['SERVER_NAME'];
  $post = get_post( get_the_ID() );
  $parent = $post->post_parent > 0 ? get_post( $post->post_parent ) : false;
  $post_type = get_post_type();
  $preview_link = getenv('PREVIEW_LINK');

  if ( $preview_link === false ) {
    if ( strpos($hostname, 'local') !== false ) {
      $preview_link = 'http://localhost:3000';
    } else {
      $preview_link = 'https://sisense-nextjs.vercel.app/';
    }
  }

  if ( $post_type && $post_type !== 'post' && $post_type !== 'page' ) {
    $preview_link .= '/' . $post_type;
  } else if ( $parent ) {
    $preview_link .= '/' . $parent->post_name;
  }

  $preview_link .= '/' . $post->post_name;
  $preview_link .= '?nonce=' . wp_create_nonce( 'wp_rest' );

  return $preview_link;
}

add_filter( 'preview_post_link', 'set_headless_preview_link' );
add_filter( 'preview_page_link', 'set_headless_preview_link' );

add_action('admin_print_styles', 'remove_preview_unless_published');
function remove_preview_unless_published() {
  if ( get_post_status() === 'publish' ) return;
?>
  <style>
    #minor-publishing-actions {
      display:none;
    }
  </style>
<?php } ?>
