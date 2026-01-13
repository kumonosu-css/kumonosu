<?php

// =============================================================
// セキュリティ対策関連 ※基本的に共通必須項目
// =============================================================

//著者アーカイブにアクセスがあった場合、リダイレクト
function theme_slug_redirect_author_archive() {
  if (is_author() ) {
      wp_redirect( home_url());
      exit;
  }
}
add_action( 'template_redirect', 'theme_slug_redirect_author_archive' );


//REST API のユーザー情報に関するエンドポイントを無効化
add_filter( 'rest_endpoints', function( $endpoints ){
  if ( isset( $endpoints['/wp/v2/users'] ) ) {
      unset( $endpoints['/wp/v2/users'] );
  }
  if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
      unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
  }
  if ( isset( $endpoints['/wp/v2/users/me'] ) ) {
      unset( $endpoints['/wp/v2/users/me'] );
  }
  return $endpoints;
});

//feedの出力内容を全て無効化（必要になった場合、出力内容は確認しておく）
add_action( 'parse_query', function ( $obj ) {
  if ( $obj->is_comment_feed || $obj->is_feed ) {
      wp_die('', '', array( 'response' => 404, "back_link" => true ));
  }
});

//wp_head内の不要なタグを削除（セキュリティ面から）
add_action( 'after_setup_theme', function() {
  remove_action('wp_head', 'wp_generator');// generator（WPバージョン情報など）を非表示にする
  remove_action('wp_head', 'rsd_link');// EditURIを非表示にする
  remove_action('wp_head', 'wlwmanifest_link');//Windows Live Writerリンク
  remove_action( 'wp_head', 'feed_links', 2 );//自動フィードリンクしない
  remove_action( 'wp_head', 'feed_links_extra', 3 );//自動フィードリンクしない
});