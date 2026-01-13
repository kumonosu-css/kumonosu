<?php

/* 投稿アーカイブを有効にしてスラッグを指定する */
function post_has_archive( $args, $post_type ) {
  if ( 'post' == $post_type ) {
    $args['rewrite'] = true;
    $args['has_archive'] = 'archive';
  }
  return $args;
}
add_filter( 'register_post_type_args', 'post_has_archive', 10, 2 );
