<?php

// 不要なheadタグ内の出力制御
remove_action( 'wp_head', 'feed_links', 2 ); //サイト全体のフィード
remove_action( 'wp_head', 'feed_links_extra', 3 ); //その他のフィード
remove_action( 'wp_head', 'rsd_link' ); //Really Simple Discoveryリンク
remove_action( 'wp_head', 'wlwmanifest_link' ); //Windows Live Writerリンク
// remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head'd, 10, 0 ); //前後の記事リンク
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 ); //ショートリンク
remove_action( 'wp_head', 'rel_canonical' ); //canonical属性
remove_action( 'wp_head', 'wp_generator' ); //WPバージョン
remove_action( 'wp_head', 'print_emoji_detection_script', 7 ); // emoji
remove_action( 'wp_print_styles', 'print_emoji_styles' ); // emoji
remove_action( 'wp_head', 'wp_oembed_add_discovery_links'); // oembed削除
remove_action('wp_head', 'wp_oembed_add_host_js'); // oembed削除（フッター）
//remove_action( 'wp_head','rest_output_link_wp_head'); //WP REST API

function custom_wp_head() {
  // 不要スタイル削除
  wp_dequeue_style('wp-ulike');
  wp_dequeue_style('wp-ulike-custom');
  wp_dequeue_style('wp-block-library');
}
add_action( 'wp_enqueue_scripts', 'custom_wp_head' );

function remove_global_styles() {
    // global-styles-inline-css（色のプリセット等）を削除
    wp_dequeue_style( 'global-styles' );
    // クラシックテーマ用のスタイル（classic-theme-styles-inline-css）も不要なら削除
    wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'remove_global_styles', 100 );

function remove_block_inline_styles() {
    // 見出し (heading)
    wp_dequeue_style( 'wp-block-heading' );
    // 段落 (paragraph)
    wp_dequeue_style( 'wp-block-paragraph' );
    // リスト (list)
    wp_dequeue_style( 'wp-block-list' );

    // その他、必要に応じて追加（画像や引用など）
    wp_dequeue_style( 'wp-block-image' );
    wp_dequeue_style( 'wp-block-quote' );
}
// 優先度を高く（100以上）設定して確実に実行させます
add_action( 'wp_enqueue_scripts', 'remove_block_inline_styles', 100 );

// レンダリングブロックしているJavascriptの読み込みを遅らせる
function move_scripts_head_to_footer_ex(){
  remove_action('wp_head', 'wp_print_scripts');
  remove_action('wp_head', 'wp_print_head_scripts', 9);
  remove_action('wp_head', 'wp_enqueue_scripts', 1);
  add_action('wp_footer', 'wp_print_scripts', 5);
  add_action('wp_footer', 'wp_print_head_scripts', 5);
  add_action('wp_footer', 'wp_enqueue_scripts', 5);
}
add_action( 'wp_enqueue_scripts', 'move_scripts_head_to_footer_ex' );

/**
 * =========================================================
 * WordPress埋め込みがあるページだけ wp-embed を読み込む
 * ※ deregister は絶対にしない
 * =========================================================
 */
add_action('wp_enqueue_scripts', function () {

    // デフォルトでは enqueue しない
    wp_dequeue_script('wp-embed');

    $post = get_queried_object();
    if ( ! ($post instanceof WP_Post) ) return;

    $content = (string) $post->post_content;

    $has_wp_embed =
        has_block('core-embed/wordpress', $post) ||
        strpos($content, 'wp-embedded-content') !== false ||
        strpos($content, 'wp-block-embed') !== false;

    if ( $has_wp_embed ) {
        wp_enqueue_script('wp-embed');
    }

}, 20);


// アイキャッチ画像有効化
add_theme_support('post-thumbnails');

// メインJSを読み込む
function kumonosu_enqueue_main_script() {

    wp_enqueue_script(
        'kumonosu-main',
        get_template_directory_uri() . '/assets/js/main.js',
        array(),
        filemtime( get_template_directory() . '/assets/js/main.js' ),
        true // フッター読み込み
    );
}
add_action( 'wp_enqueue_scripts', 'kumonosu_enqueue_main_script' );

// kumonosu-main を type="module" で出力する
function kumonosu_main_script_as_module( $tag, $handle, $src ) {

    if ( 'kumonosu-main' === $handle ) {
        // 必要なら defer などもここで付けられる
        $tag = sprintf(
            '<script type="module" src="%s" id="%s-js"></script>' . "\n",
            esc_url( $src ),
            esc_attr( $handle )
        );
    }

    return $tag;
}
add_filter( 'script_loader_tag', 'kumonosu_main_script_as_module', 10, 3 );

/*==================================================
  簡易パンくずリスト出力関数
==================================================*/
function my_custom_breadcrumb() {
    // トップページでは何も表示しない
    if ( is_front_page() || is_home() ) return;

    // パンくずの開始タグ
    echo '<div class="l-breadcrumb-list">';

    // HOMEへのリンク
    echo '<a href="' . esc_url( home_url() ) . '">Home</a>';
    echo '<span class="sep">/</span>';

    if ( is_single() ) {
        if ( is_single() ) {

            // 今の投稿タイプ取得（例：css, blog など）
            $post_type = get_post_type();

            // 一覧ページURL（例：/css, /blog）
            $archive_url = get_post_type_archive_link( $post_type );

            // 投稿タイプオブジェクトからラベルを取得
            $post_type_obj = get_post_type_object( $post_type );
            $archive_name  = $post_type_obj && ! is_wp_error( $post_type_obj )
                ? $post_type_obj->labels->name   // 例：「CSS」「ブログ」など
                : '';

            // 名前がちゃんと取れていればリンクを出力
            if ( $archive_url && $archive_name ) {
                echo '<a href="' . esc_url( $archive_url ) . '">' . esc_html( $archive_name ) . '</a>';
                echo '<span class="sep">/</span>';
            }

            echo '<span class="breadcrumb-title">';
            the_title();
            echo '</span>';
        }
    } elseif ( is_page() ) {
        // 固定ページ
        echo '<span class="breadcrumb-title">';
        the_title();
        echo '</span>';
    } elseif ( is_post_type_archive() ) {
        // カスタム投稿アーカイブ
        echo '<span class="breadcrumb-title">';
        post_type_archive_title();
        echo '</span>';
    } elseif ( is_tax() ) {
        // カスタムタクソノミー
        echo '<span class="breadcrumb-title">';
        single_term_title();
        echo '</span>';
    } elseif ( is_search() ) {
        echo 'Search Results';
    } elseif ( is_404() ) {
        echo '404 Not Found';
    }

    echo '</div>';
}

/* =========================================================
 * Robots meta 制御（noindex 一括管理）
 * ========================================================= */
add_filter('wp_robots', function ($robots) {

  // 共通 noindex セット
  $apply_noindex = function () use (&$robots) {
    $robots['noindex']   = true;
    $robots['nofollow']  = true;
    $robots['noarchive'] = true;
  };

  /*
   * ----------------------------------
   * 1) 404 / 検索結果ページ
   * ----------------------------------
   */
  if (is_404() || is_search()) {
    $apply_noindex();
    return $robots;
  }

  /*
   * ----------------------------------
   * 2) プレビュー（例：/css/{id}/?preview）
   * ----------------------------------
   */
  if (is_singular('css') && isset($_GET['preview'])) {
    $apply_noindex();
    return $robots;
  }

  /*
   * ----------------------------------
   * 3) クエリ付きURL全般（utm / ref など）
   * ----------------------------------
   * ※ preview は上で個別処理済み
   */
  if (!empty($_GET)) {
    $apply_noindex();
    return $robots;
  }

  /*
   * ----------------------------------
   * 4) thanks / 完了ページ（固定ページ）
   * ----------------------------------
   */
  if (is_page()) {
    $slug = get_post_field('post_name', get_queried_object_id());

    // 例：/contact/thanks/ を除外したいなら 'thanks' だけでは足りないので下の方式推奨
    $noindex_page_paths = [
      'contact/thanks',
      'thanks',
      'contact-thanks',
    ];

    $path = trim(parse_url(get_permalink(), PHP_URL_PATH), '/');

    if (in_array($path, $noindex_page_paths, true)) {
      $apply_noindex();
      return $robots;
    }
  }

  /*
   * ----------------------------------
   * 5) それ以外は index 前提（何もしない）
   * ----------------------------------
   */
  return $robots;
});
