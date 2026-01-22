<?php
//-----------------------------------------------------
// デフォルトの投稿を非表示
//-----------------------------------------------------

function remove_menus () {
  remove_menu_page( 'edit.php' );
}
add_action('admin_menu', 'remove_menus');

/*==========================================
カスタム投稿タイプの追加（CSS一覧）
==========================================*/

add_action( 'init', 'create_CPT_css' );
function create_CPT_css() {
  register_post_type( 'css',
    array(
      'label' => 'CSS',
      'labels' => array(
        'singular_name' => 'CSS',
        'menu_name' => 'CSS',
        'add_new_item' => 'CSSを追加',
        'add_new' => '新規追加',
        'new_item' => '新規投稿',
        'edit_item'=>'CSSを編集',
        'view_item' => '投稿を表示',
        'not_found' => '投稿は見つかりませんでした',
        'not_found_in_trash' => 'ゴミ箱に投稿はありません。',
        'search_items' => 'CSSを検索',
      ),
      'public' => true,
      'menu_position' => 5,
      'show_in_rest'  => true,
      'publicly_queryable' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'query_var' => true,
      'hierarchical' => false,
      'rewrite' => true,
      'supports' => array(
        'title',
        'editor',
        'thumbnail',
        'revisions',
      ),
      'has_archive'   => true,
    )
  );
}

/* カスタムタクソノミー：CSSカテゴリー */
add_action('init', 'custom_taxonomy_css_cat');
function custom_taxonomy_css_cat(){
  register_taxonomy(
    'css-category',
    'css',
    array(
      'label' => 'カテゴリー',
      'public' => true,
      'hierarchical' => true,
      'show_in_rest' => true,
    )
  );
}

/* カスタムタクソノミー：CSSタグ */
add_action('init', 'custom_taxonomy_css_tag');
function custom_taxonomy_css_tag(){
  register_taxonomy(
    'css-tag',
    'css',
    array(
      'label' => 'タグ',
      'public' => true,
      'hierarchical' => false,
      'show_in_rest' => true,
      'update_count_callback' => '_update_post_term_count',
    )
  );
}

/*==========================================
カスタム投稿タイプの追加（特集&まとめ）
==========================================*/

add_action( 'init', 'create_CPT_blog' );
function create_CPT_blog() {
  register_post_type( 'blog',
    array(
      'label' => '特集&まとめ',
      'labels' => array(
        'singular_name' => '特集&まとめ',
        'menu_name' => '特集&まとめ',
        'add_new_item' => '特集&まとめを追加',
        'add_new' => '新規追加',
        'new_item' => '新規投稿',
        'edit_item'=>'特集&まとめを編集',
        'view_item' => '投稿を表示',
        'not_found' => '投稿は見つかりませんでした',
        'not_found_in_trash' => 'ゴミ箱に投稿はありません。',
        'search_items' => '特集&まとめを検索',
      ),
      'public' => true,
      'menu_position' => 6,
      'show_in_rest'  => true,
      'publicly_queryable' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'query_var' => true,
      'hierarchical' => false,
      'rewrite' => true,
      'supports' => array(
        'title',
        'editor',
        'thumbnail',
        'revisions',
      ),
      'has_archive'   => true,
    )
  );
}

/* カスタムタクソノミー：特集&まとめカテゴリー */
add_action('init', 'custom_taxonomy_blog_cat');
function custom_taxonomy_blog_cat(){
  register_taxonomy(
    'blog-category',
    'blog',
    array(
      'label' => 'カテゴリー',
      'public' => true,
      'hierarchical' => true,
      'show_in_rest' => true,
    )
  );
}

/* カスタムタクソノミー：特集&まとめタグ */
add_action('init', 'custom_taxonomy_blog_tag');
function custom_taxonomy_blog_tag(){
  register_taxonomy(
    'blog-tag',
    'blog',
    array(
      'label' => 'タグ',
      'public' => true,
      'hierarchical' => false,
      'show_in_rest' => true,
      'update_count_callback' => '_update_post_term_count',
    )
  );
}

/*==========================================
カスタム投稿タイプの追加（ツール）
==========================================*/
add_action( 'init', 'create_CPT_tool' );
function create_CPT_tool() {
  register_post_type( 'tool',
    array(
      'label' => 'ツール',
      'labels' => array(
        'singular_name' => 'ツール',
        'menu_name' => 'ツール',
        'add_new_item' => 'ツールを追加',
        'add_new' => '新規追加',
        'new_item' => '新規投稿',
        'edit_item'=>'ツールを編集',
        'view_item' => '投稿を表示',
        'not_found' => '投稿は見つかりませんでした',
        'not_found_in_trash' => 'ゴミ箱に投稿はありません。',
        'search_items' => 'ツールを検索',
      ),
      'public' => true,
      'menu_position' => 6,
      'show_in_rest'  => true,
      'publicly_queryable' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'query_var' => true,
      'hierarchical' => false,
      'rewrite' => true,
      'supports' => array(
        'title',
        'editor',
        'thumbnail',
        'revisions',
      ),
      'has_archive'   => true,
    )
  );
}

/*==========================================
カスタム投稿タイプの追加（お問い合わせ）
==========================================*/
add_action('init', 'create_CPT_contact');
function create_CPT_contact() {
  register_post_type('contact', [
    'label' => 'お問い合わせ',
    'labels' => [
      'singular_name' => 'お問い合わせ',
      'menu_name'     => 'お問い合わせ',
      'add_new_item'  => 'お問い合わせを追加',
      'add_new'       => '新規追加',
      'new_item'      => '新規投稿',
      'edit_item'     => 'お問い合わせを編集',
      'view_item'     => '投稿を表示',
      'not_found'     => '投稿は見つかりませんでした',
      'not_found_in_trash' => 'ゴミ箱に投稿はありません。',
      'search_items'  => 'お問い合わせを検索',
    ],

    // フロントに一切出さない
    'public'              => false,
    'publicly_queryable'  => false,
    'exclude_from_search' => true,
    'has_archive'         => false,
    'rewrite'             => false,
    'query_var'           => false,

    // 管理画面のみ
    'show_ui'      => true,
    'show_in_menu' => true,
    'menu_position'=> 6,
    'menu_icon'    => 'dashicons-email-alt',

    // 受信箱なのでREST不要（必要になったら true）
    'show_in_rest' => false,

    // 受信箱なので最低限
    'hierarchical' => false,
    'supports'     => ['title'],
  ]);
}


/*==========================================
【追加】カスタム投稿の記事URLをID（数字）にする
==========================================*/

add_action( 'save_post', 'auto_post_slug_to_id', 10, 3 );
function auto_post_slug_to_id( $post_id, $post, $update ) {
    // URLを数字にしたいカスタム投稿タイプをここに記述
    $target_types = array( 'css', 'blog', 'tool' );

    // 対象外の投稿タイプ、またはリビジョン（下書き保存履歴）なら何もしない
    if ( ! in_array( $post->post_type, $target_types ) || wp_is_post_revision( $post_id ) ) {
        return;
    }

    // スラッグが既にIDと同じなら何もしない（無限ループ防止）
    if ( $post->post_name == $post_id ) {
        return;
    }

    // フックを一時的に解除して、スラッグをIDで更新
    remove_action( 'save_post', 'auto_post_slug_to_id', 10, 3 );
    wp_update_post( array(
        'ID'        => $post_id,
        'post_name' => $post_id // ここでスラッグをIDにする
    ));
    // フックを戻す
    add_action( 'save_post', 'auto_post_slug_to_id', 10, 3 );
}


/*==========================================
出力画像のカスタマイズ
==========================================*/
// srcset を無効化
add_filter( 'wp_calculate_image_srcset', '__return_false' );

// sizes を無効化
add_filter( 'wp_calculate_image_sizes', '__return_false' );