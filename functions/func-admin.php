<?php

/*
// 【管理画面】管理者以外の投稿メニューを非表示
if (!current_user_can('administrator')) { // 管理者以外を対象
    function remove_menus () {
        global $menu;
        remove_menu_page('edit.php?post_type=banner-head'); // バナーヘッド
        remove_menu_page('edit.php?post_type=banner-side'); // バナーサイド
        remove_menu_page('edit.php?post_type=banner-slider'); // バナースライド
        remove_menu_page('edit.php?post_type=page'); // 固定ページ
        remove_menu_page('themes.php'); // 外観
        remove_menu_page('tools.php'); // ツール
        remove_menu_page('plugins.php'); // Plugins
        remove_menu_page('edit-comments.php'); // コメントメニュー
        remove_menu_page('users.php'); // ユーザー
        remove_menu_page('options-general.php'); // 設定
        remove_menu_page( 'edit.php?post_type=acf-field-group' ); // Advanced Custom Fields.
        remove_menu_page( 'wp-ulike-settings#tab=configuration' ); // Advanced Custom Fields.
        remove_menu_page('kusanagi-core/core.php'); // sakunagai
    }
    add_action('admin_menu', 'remove_menus', 99);
}

// 編集者にユーザー追加・編集権限を与える
function add_theme_caps() {
    // 投稿者の権限グループを取得
    $role = get_role( 'author' );
    // これは、クラスインスタンスにアクセスする場合のみ機能します。
    // 現在のテーマにおいてのみ、投稿者は他の人の投稿を編集することができます。
    $role->add_cap( 'edit_others_posts' );
}
add_action( 'admin_init', 'add_theme_caps');

// 投稿一覧ページにアイキャッチ画像用の列を追加
add_filter( 'manage_posts_columns', 'add_custom_post_columns');    //投稿 & カスタム投稿
add_filter( 'manage_pages_columns', 'add_custom_post_columns' );   //固定ページ
if ( !function_exists( 'add_custom_post_columns' ) ) {
    function add_custom_post_columns( $columns ) {
        global $post_type;
        if( in_array( $post_type, array('post', 'page') ) ) {
            $columns['pv_count_total'] = 'view数';    //カラム表示名
            $columns['thumbnail'] = '画像';    //カラム表示名
        }
        return $columns;
    }
}

// サムネイル画像を表示
add_action( 'manage_posts_custom_column', 'output_custom_post_columns', 10, 2 );  //投稿 & カスタム投稿(階層構造が無効)
add_action( 'manage_pages_custom_column', 'output_custom_post_columns', 10, 2 );  //固定ページ & カスタム投稿(階層構造が有効)
if ( !function_exists( 'output_custom_post_columns' ) ) {
    function output_custom_post_columns( $column_name, $post_id ) {
        if ( 'pv_count_total' == $column_name ) {
            $stitle = get_post_meta($post_id, 'pv_count_total', true);
        }
        if ( isset($stitle) && $stitle ) {
            echo esc_attr($stitle);
        }
        if ( 'thumbnail' === $column_name ) {
            $thumb_id  = get_post_thumbnail_id( $post_id );
            if ( $thumb_id ) {
                $thumb_img = wp_get_attachment_image_src( $thumb_id, 'medium' );
                echo '<img src="',$thumb_img[0],'" width="100px">';
            } else {
                echo 'アイキャッチ画像が設定されていません';
            }
        }
    }
}

// カラム（項目）の順序を変更する
function sort_column($columns){
    $columns = array(
        'cb' => '<input type="checkbox" />',
        'thumbnail' => 'サムネイル',
        'title' => 'タイトル',
        'categories' => 'カテゴリ',
        'tags' => 'タグ',
        'author' => '投稿者',
        'pv_count_total' => 'view数',
        'date' => '日時'
    );
    return $columns;
}
add_filter( 'manage_edit-post_columns', 'sort_column' );

// 管理画面 css調整：新規バナー
function my_admin_style(){
//  if( get_post_type() === 'banner'){}
    wp_enqueue_style( 'my_admin_style', get_template_directory_uri().'/assets/css/admin.css' );
}
// 投稿画面
add_action("admin_head-post.php", "my_admin_style");
// 新規
add_action("admin_head-post-new.php", "my_admin_style");
// 一覧
add_action("admin_head-edit.php", "my_admin_style");

if ( ! current_user_can( 'manage_options' ) ) {
    show_admin_bar( false );
}

// すべてのユーザーで一般公開されているページにはツールバーを表示させない
//add_filter('show_admin_bar', '__return_false');

//function console_log( $data ){
//    echo '<script>';
//    echo 'console.log('. json_encode( $data ) .')';
//    echo '</script>';
//}
*/


/**
 * ------------------------------------------------------------
 * ブロックエディター「エディターへようこそ」(Welcome Guide) を強制的に無効化
 * ------------------------------------------------------------
 */
add_action('enqueue_block_editor_assets', function () {
    // エディターで必要な依存を読み込んだ後に実行
    wp_add_inline_script('wp-edit-post', <<<JS
wp.domReady(function () {
  try {
    // 新しめの方法
    wp.data.dispatch('core/edit-post').disableFeature('welcomeGuide');
  } catch (e) {}

  try {
    // バージョン差の保険（環境によってはこちらが効く）
    wp.data.dispatch('core/edit-post').setIsWelcomeGuideVisible(false);
  } catch (e) {}

  try {
    // 永続設定として false を保存（これが効く環境も多い）
    wp.data.dispatch('core/preferences').set('core/edit-post', 'welcomeGuide', false);
  } catch (e) {}
});
JS
    , 'after');
});

/**
 * ------------------------------------------------------------
 * お問い合わせ
 * ------------------------------------------------------------
 */
add_action('add_meta_boxes', function () {
  add_meta_box('contact_detail', 'お問い合わせ内容', function($post){
    $name = get_post_meta($post->ID,'name',true);
    $email = get_post_meta($post->ID,'email',true);
    $message = get_post_meta($post->ID,'message',true);

    echo '<p><strong>お名前：</strong>'.esc_html($name).'</p>';
    echo '<p><strong>メール：</strong>'.esc_html($email).'</p>';
    echo '<p><strong>本文：</strong><br><pre style="white-space:pre-wrap;margin:0;">'.esc_html($message).'</pre></p>';
  }, 'contact', 'normal', 'high');
});

/**
 * ------------------------------------------------------------
 * カスタム投稿CSS・投稿用 カスタムフィールド設定 (管理画面表示スリム化版)
 * ------------------------------------------------------------
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// 1. メタボックス登録
if ( ! function_exists( 'kumonosu_register_custom_metabox' ) ) {
    add_action( 'add_meta_boxes', 'kumonosu_register_custom_metabox' );
    function kumonosu_register_custom_metabox() {
        $screens = array( 'css', 'post' );
        foreach ( $screens as $screen ) {
            add_meta_box(
                'kumonosu_custom_fields',
                'コンテンツ・オプション',
                'kumonosu_metabox_html',
                $screen,
                'normal',
                'high'
            );
        }
    }
}

// 2. メタボックスHTML
if ( ! function_exists( 'kumonosu_metabox_html' ) ) {
    function kumonosu_metabox_html( $post ) {
        wp_nonce_field( 'kumonosu_save_meta', 'kumonosu_nonce' );

        // データの取得
        $intro       = get_post_meta( $post->ID, '_kumonosu_intro', true );
        $tech_stack  = get_post_meta( $post->ID, '_kumonosu_tech_stack', true ) ?: array();
        $code_html   = get_post_meta( $post->ID, '_kumonosu_code_html', true );
        $code_css    = get_post_meta( $post->ID, '_kumonosu_code_css', true );
        $code_js     = get_post_meta( $post->ID, '_kumonosu_code_js', true );
        $script_type = get_post_meta( $post->ID, '_kumonosu_script_type', true ) ?: 'classic';
        $code_config = get_post_meta( $post->ID, '_kumonosu_code_config', true );
        $ext_js      = get_post_meta( $post->ID, '_kumonosu_ext_js', true );
        $ext_css     = get_post_meta( $post->ID, '_kumonosu_ext_css', true );
        $blog_summary = get_post_meta( $post->ID, '_kumonosu_blog_summary', true );

        $permissions  = get_post_meta( $post->ID, '_kumonosu_permissions', true ) ?: array();
        if ( ! is_array($permissions) ) $permissions = array();

        $free_texts  = get_post_meta( $post->ID, '_kumonosu_free_texts', true ) ?: array();
        $related_posts = get_post_meta( $post->ID, '_kumonosu_related_posts', true ) ?: array();

        ?>
        <div id="kumonosu-custom-ui" class="kumonosu-wrapper">
            <ul class="kumonosu-tabs">
                <li class="active" data-tab="tab-intro">導入文</li>
                <li data-tab="tab-code">コード設定</li>
                <li data-tab="tab-youtube">動画</li>
                <li data-tab="tab-freetext">フリーテキスト</li>
                <li data-tab="tab-related">関連記事</li>
                <li data-tab="tab-summary">まとめ</li>
            </ul>

            <div class="kumonosu-content">

                <!-- 【タブ1】導入文 & 技術スタック -->
                <div id="tab-intro" class="kumonosu-tab-panel active">
                    <div class="kumonosu-section">
                        <label class="kumonosu-label">使用言語・技術（複数選択可）</label>
                        <div class="kumonosu-checkbox-group">
                            <?php $tech_options = array(
                                    'html' => 'HTML',
                                    'css'  => 'CSS',
                                    'js'   => 'JavaScript',
                                    'ts'   => 'TypeScript', // こうやって足すだけ
                                );
                            foreach($tech_options as $val => $label): ?>
                                <label><input type="checkbox" name="kumonosu_tech_stack[]" value="<?php echo $val; ?>" <?php checked( in_array($val, $tech_stack) ); ?>> <?php echo $label; ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="kumonosu-section">
                        <label class="kumonosu-label">記事冒頭の導入文</label>
                        <textarea name="kumonosu_intro" rows="5" class="kumonosu-textarea"><?php echo esc_textarea( $intro ); ?></textarea>
                    </div>
                </div>

                <!-- 【タブ2】コード設定 (2カラムレイアウト / 管理画面行数表示削除) -->
                <div id="tab-code" class="kumonosu-tab-panel">
                    <div class="kumonosu-grid-container">
                        
                        <div class="kumonosu-grid-item">
                            <label class="kumonosu-label">HTML</label>
                            <textarea name="kumonosu_code_html" rows="12" class="kumonosu-textarea code-font"><?php echo esc_textarea($code_html); ?></textarea>
                        </div>
                        <div class="kumonosu-grid-item">
                            <label class="kumonosu-label">CSS</label>
                            <textarea name="kumonosu_code_css" rows="12" class="kumonosu-textarea code-font"><?php echo esc_textarea($code_css); ?></textarea>
                        </div>

                        <div class="kumonosu-grid-item">
                            <div class="kumonosu-label-with-side">
                                <label class="kumonosu-label">JavaScript</label>
                                <label class="kumonosu-side-check">
                                    <input type="checkbox" name="kumonosu_is_module" value="yes" <?php checked($script_type, 'module'); ?>> Moduleとして扱う
                                </label>
                            </div>
                            <textarea name="kumonosu_code_js" rows="12" class="kumonosu-textarea code-font"><?php echo esc_textarea($code_js); ?></textarea>
                        </div>
                        <div class="kumonosu-grid-item">
                            <label class="kumonosu-label">設定 JSON (Config)</label>
                            <textarea name="kumonosu_code_config" rows="12" class="kumonosu-textarea code-font" placeholder='{"settings": true}'><?php echo esc_textarea($code_config); ?></textarea>
                        </div>

                        <div class="kumonosu-grid-item is-full">
                        <label class="kumonosu-label">iframe 権限（Preview 用）</label>

                        <div class="kumonosu-checkbox-group kumonosu-permissions">
                            <?php
                            $perm_options = array(
                            'camera'     => 'camera（カメラ）',
                            'microphone' => 'microphone（マイク）',
                            'autoplay'   => 'autoplay（自動再生）',
                            'fullscreen' => 'fullscreen（全画面）',
                            'clipboard'  => 'clipboard-write（クリップボード書き込み）',
                            );

                            foreach ($perm_options as $val => $label) :
                            ?>
                            <label>
                                <input
                                type="checkbox"
                                name="kumonosu_permissions[]"
                                value="<?php echo esc_attr($val); ?>"
                                <?php checked( in_array($val, $permissions, true) ); ?>
                                >
                                <?php echo esc_html($label); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>

                        <p class="description" style="margin-top:6px;">
                            Preview(iframe) に必要なときだけONにしてください。例：カメラ使用時は camera をON。
                        </p>
                        </div>

                        <div class="kumonosu-grid-item">
                            <label class="kumonosu-label">外部スクリプト (JS URL) ※複数行入力可</label>
                            <textarea name="kumonosu_ext_js" rows="4" class="kumonosu-textarea" placeholder="https://example.com/script.js"><?php echo esc_textarea($ext_js); ?></textarea>
                        </div>
                        <div class="kumonosu-grid-item">
                            <label class="kumonosu-label">外部スタイル (CSS URL) ※複数行入力可</label>
                            <textarea name="kumonosu_ext_css" rows="4" class="kumonosu-textarea" placeholder="https://example.com/style.css"><?php echo esc_textarea($ext_css); ?></textarea>
                        </div>

                    </div>
                </div>

                <!-- 【タブ：動画】 -->
                <div id="tab-youtube" class="kumonosu-tab-panel">
                <div class="kumonosu-section">
                    <label class="kumonosu-label">YouTube iframe</label>

                    <textarea
                    name="kumonosu_youtube_iframe"
                    rows="6"
                    class="kumonosu-textarea code-font"
                    placeholder='<iframe width="560" height="315" src="https://www.youtube.com/embed/XXXX" frameborder="0" allowfullscreen></iframe>'
                    ><?php
                    echo esc_textarea(
                        get_post_meta($post->ID, '_kumonosu_youtube_iframe', true)
                    );
                    ?></textarea>

                    <p class="description" style="margin-top:6px;">
                    URLの後ろに「?autoplay=1&mute=1&playsinline=1&controls=0&loop=1&playlist=動画ID」
                    </p>
                </div>
                </div>

                <!-- 【タブ3】フリーテキスト -->
                <div id="tab-freetext" class="kumonosu-tab-panel">
                    <div id="kumonosu-freetext-repeater" class="kumonosu-sortable-area">
                        <?php foreach ( $free_texts as $ft ) : ?>
                        <div class="kumonosu-repeater-item freetext-item">
                            <div class="kumonosu-item-handle">⋮⋮</div>
                            <div class="kumonosu-item-fields">
                                <div class="kumonosu-row">
                                    <input type="text" name="kumonosu_ft_title_en[]" value="<?php echo esc_attr($ft['title_en']); ?>" placeholder="Title (EN)">
                                    <input type="text" name="kumonosu_ft_title_jp[]" value="<?php echo esc_attr($ft['title_jp']); ?>" placeholder="タイトル (JP)">
                                </div>
                                <textarea name="kumonosu_ft_body[]" rows="3" placeholder="本文..."><?php echo esc_textarea($ft['body']); ?></textarea>
                            </div>
                            <button type="button" class="kumonosu-remove-btn">×</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="add-freetext-item" class="button button-large">＋ 追加</button>
                </div>

                <!-- 【タブ4】関連記事 -->
                <div id="tab-related" class="kumonosu-tab-panel">
                    <div id="kumonosu-related-repeater" class="kumonosu-sortable-area">
                        <?php foreach ( $related_posts as $rid ) : ?>
                            <div class="kumonosu-repeater-item related-item">
                                <div class="kumonosu-item-handle">⋮⋮</div>
                                <input type="text" name="kumonosu_related_ids[]" value="<?php echo esc_attr($rid); ?>" placeholder="ID または URL">
                                <button type="button" class="kumonosu-remove-btn">×</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="add-related-item" class="button button-large">＋ 追加</button>
                </div>

                <!-- 【タブ：まとめ】ブログまとめ用テキスト -->
                <div id="tab-summary" class="kumonosu-tab-panel">
                <div class="kumonosu-section">
                    <label class="kumonosu-label">ブログのまとめ用</label>
                    <textarea
                    name="kumonosu_blog_summary"
                    rows="8"
                    class="kumonosu-textarea"
                    placeholder="例：この記事のポイント、結論、次に読むべき記事など…"
                    ><?php echo esc_textarea( $blog_summary ); ?></textarea>
                </div>
                </div>

            </div>
        </div>

        <style>
            #kumonosu_custom_fields { border: none; background: transparent; box-shadow: none; }
            #kumonosu_custom_fields .postbox-header, #kumonosu_custom_fields .handlediv { display: none !important; }
            #kumonosu_custom_fields .inside { padding: 0 !important; margin: 0 !important; }

            .kumonosu-wrapper { background: #fff; border: 1px solid #dcdcde; border-radius: 8px; overflow: hidden; font-family: sans-serif; color: #1d2327; }
            .kumonosu-tabs { display: flex; background: #f0f0f1; margin: 0; padding: 0; list-style: none; border-bottom: 1px solid #dcdcde; }
            .kumonosu-tabs li { padding: 12px 20px; cursor: pointer; font-weight: 600; color: #646970; border-right: 1px solid #dcdcde; margin-bottom: 0; }
            .kumonosu-tabs li.active { background: #fff; color: #2271b1; box-shadow: inset 0 3px 0 #2271b1; }

            .kumonosu-content { padding: 20px; }
            .kumonosu-tab-panel { display: none; }
            .kumonosu-tab-panel.active { display: block; }

            .kumonosu-grid-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
            .kumonosu-grid-item { display: flex; flex-direction: column; }
            .kumonosu-grid-item.is-full { grid-column: 1 / -1; }

            .kumonosu-section { margin-bottom: 20px; }
            .kumonosu-label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px; }
            
            .kumonosu-label-with-side { display: flex; align-items: center; gap: 15px; margin-bottom: 8px; }
            .kumonosu-side-check { font-size: 12px; font-weight: normal; background: #f0f6fb; padding: 2px 8px; border-radius: 4px; color: #2271b1; border: 1px solid #c2d7e9; cursor: pointer; }
            .kumonosu-side-check input { vertical-align: middle; margin: -2px 4px 0 0; }

            .kumonosu-textarea { width: 100%; border: 1px solid #8c8f94; border-radius: 4px; padding: 10px; box-sizing: border-box; }
            .code-font { font-family: 'Consolas', 'Monaco', monospace; background: #f9f9f9; font-size: 13px; }

            .kumonosu-repeater-item { display: flex; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; padding: 12px; margin-bottom: 12px; gap: 10px; }
            .kumonosu-item-handle { cursor: grab; color: #a7aaad; font-size: 20px; }
            .kumonosu-item-fields { flex: 1; display: flex; flex-direction: column; gap: 8px; }
            .kumonosu-row { display: flex; gap: 10px; }
            .kumonosu-row input { flex: 1; }
            .kumonosu-remove-btn { color: #d63638; border: 1px solid #d63638; border-radius: 4px; width: 28px; height: 28px; cursor: pointer; background: #fff; }
        </style>

        <script>
            jQuery(function($) {
                $('.kumonosu-tabs li').on('click', function() {
                    $('.kumonosu-tabs li').removeClass('active');
                    $(this).addClass('active');
                    $('.kumonosu-tab-panel').removeClass('active');
                    $('#' + $(this).data('tab')).addClass('active');
                });
                $('#add-freetext-item').on('click', function() {
                    $('#kumonosu-freetext-repeater').append('<div class="kumonosu-repeater-item"><div class="kumonosu-item-handle">⋮⋮</div><div class="kumonosu-item-fields"><div class="kumonosu-row"><input type="text" name="kumonosu_ft_title_en[]" placeholder="EN"><input type="text" name="kumonosu_ft_title_jp[]" placeholder="JP"></div><textarea name="kumonosu_ft_body[]" rows="3"></textarea></div><button type="button" class="kumonosu-remove-btn">×</button></div>');
                });
                $('#add-related-item').on('click', function() {
                    $('#kumonosu-related-repeater').append('<div class="kumonosu-repeater-item"><div class="kumonosu-item-handle">⋮⋮</div><input type="text" name="kumonosu_related_ids[]" style="flex:1;"><button type="button" class="kumonosu-remove-btn">×</button></div>');
                });
                $(document).on('click', '.kumonosu-remove-btn', function() { if(confirm('削除しますか？')) $(this).closest('.kumonosu-repeater-item').remove(); });
                $('.kumonosu-sortable-area').sortable({ handle: '.kumonosu-item-handle' });
            });
        </script>
        <?php
    }
}

// 3. 保存処理 & 行数カウント (カウント機能は保持)
if ( ! function_exists( 'kumonosu_save_custom_meta' ) ) {
    add_action( 'save_post', 'kumonosu_save_custom_meta' );
    function kumonosu_save_custom_meta( $post_id ) {
        if ( ! isset( $_POST['kumonosu_nonce'] ) || ! wp_verify_nonce( $_POST['kumonosu_nonce'], 'kumonosu_save_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        $fields = array(
            'intro'       => '_kumonosu_intro',
            'code_html'   => '_kumonosu_code_html',
            'code_css'    => '_kumonosu_code_css',
            'code_js'     => '_kumonosu_code_js',
            'code_config' => '_kumonosu_code_config',
            'ext_js'      => '_kumonosu_ext_js',
            'ext_css'     => '_kumonosu_ext_css',
            'youtube_iframe' => '_kumonosu_youtube_iframe',
            'blog_summary'=> '_kumonosu_blog_summary',
        );

        $total_lines = 0;
        foreach ( $fields as $key => $meta_key ) {
            if ( isset( $_POST['kumonosu_' . $key] ) ) {
                $val = $_POST['kumonosu_' . $key];
                update_post_meta( $post_id, $meta_key, $val );

                // 行数カウント
                if ( in_array($key, array('code_html', 'code_css', 'code_js', 'code_config')) ) {
                    $trimmed = trim($val);
                    if (!empty($trimmed)) $total_lines += count(explode("\n", $trimmed));
                }
            }
        }

        update_post_meta( $post_id, '_kumonosu_script_type', isset($_POST['kumonosu_is_module']) ? 'module' : 'classic' );
        update_post_meta( $post_id, '_kumonosu_total_lines', $total_lines );
        update_post_meta( $post_id, '_kumonosu_tech_stack', isset($_POST['kumonosu_tech_stack']) ? (array)$_POST['kumonosu_tech_stack'] : array() );

        $perms = array();
        if ( isset($_POST['kumonosu_permissions']) && is_array($_POST['kumonosu_permissions']) ) {
        foreach ($_POST['kumonosu_permissions'] as $p) {
            $p = sanitize_text_field($p);
            if ($p !== '') $perms[] = $p;
        }
        }
        $perms = array_values(array_unique($perms));
        update_post_meta( $post_id, '_kumonosu_permissions', $perms );

        // リピーター系
        $ft_data = array();
        if ( isset($_POST['kumonosu_ft_title_en']) ) {
            foreach ( $_POST['kumonosu_ft_title_en'] as $i => $title ) {
                $ft_data[] = array('title_en' => sanitize_text_field($title), 'title_jp' => sanitize_text_field($_POST['kumonosu_ft_title_jp'][$i]), 'body' => $_POST['kumonosu_ft_body'][$i]);
            }
        }
        update_post_meta( $post_id, '_kumonosu_free_texts', $ft_data );

        $related_ids = array();
        if ( isset($_POST['kumonosu_related_ids']) ) {
            foreach ( $_POST['kumonosu_related_ids'] as $input ) {
                $id = is_numeric($input) ? intval($input) : url_to_postid($input);
                if ($id) $related_ids[] = $id;
            }
        }
        update_post_meta( $post_id, '_kumonosu_related_posts', array_unique($related_ids) );
    }
}

// 4. フロントエンド用関数
if ( ! function_exists( 'get_kumonosu_field' ) ) {
    /**
     * フロントページで $lines = get_kumonosu_field('total_lines'); のように呼び出せます。
     */
    function get_kumonosu_field( $key = null, $post_id = null ) {
        if ( ! $post_id ) $post_id = get_the_ID();
        $data = array(
            'intro'        => get_post_meta( $post_id, '_kumonosu_intro', true ),
            'tech_stack'   => get_post_meta( $post_id, '_kumonosu_tech_stack', true ) ?: array(),
            'code_html'    => get_post_meta( $post_id, '_kumonosu_code_html', true ),
            'code_css'     => get_post_meta( $post_id, '_kumonosu_code_css', true ),
            'code_js'      => get_post_meta( $post_id, '_kumonosu_code_js', true ),
            'script_type'  => get_post_meta( $post_id, '_kumonosu_script_type', true ) ?: 'classic',
            'code_config'  => get_post_meta( $post_id, '_kumonosu_code_config', true ),
            'permissions' => get_post_meta( $post_id, '_kumonosu_permissions', true ) ?: array(),
            'ext_js'       => get_post_meta( $post_id, '_kumonosu_ext_js', true ),
            'ext_css'      => get_post_meta( $post_id, '_kumonosu_ext_css', true ),
            'total_lines'  => get_post_meta( $post_id, '_kumonosu_total_lines', true ) ?: 0,
            'free_texts'   => get_post_meta( $post_id, '_kumonosu_free_texts', true ) ?: array(),
            'related_posts'=> get_post_meta( $post_id, '_kumonosu_related_posts', true ) ?: array(),
            'blog_summary' => get_post_meta( $post_id, '_kumonosu_blog_summary', true ),
        );
        return $key ? (isset($data[$key]) ? $data[$key] : null) : $data;
    }
}

// 互換性維持 (500エラー防止)
if ( ! function_exists( 'get_kumonosu_page_data' ) ) {
    function get_kumonosu_page_data( $post_id = null ) {
        $fields = get_kumonosu_field(null, $post_id);
        $fields['code_blocks'] = array(
            array('title' => 'HTML', 'code' => $fields['code_html']),
            array('title' => 'CSS', 'code' => $fields['code_css']),
            array('title' => 'JavaScript', 'code' => $fields['code_js']),
        );
        return $fields;
    }
}

if ( ! function_exists( 'get_kumonosu_related_posts_data' ) ) {
    function get_kumonosu_related_posts_data( $post_id = null ) {
        if ( ! $post_id ) $post_id = get_the_ID();
        $ids = get_post_meta( $post_id, '_kumonosu_related_posts', true );
        if ( empty($ids) || !is_array($ids) ) return array();
        $results = array();
        foreach ( $ids as $rid ) {
            if ( get_post_status($rid) ) {
                $results[] = array(
                    'id' => $rid, 'permalink' => get_permalink($rid), 'title' => get_the_title($rid),
                    'post_type' => get_post_type($rid), 'thumbnail' => get_the_post_thumbnail_url($rid, 'medium'),
                );
            }
        }
        return $results;
    }
}
/**
 * カスタム投稿「css」の全記事の合計行数を取得する
 */
function get_kumonosu_total_lines_all_posts() {
    $args = array(
        'post_type'      => 'css',
        'posts_per_page' => -1,      // 全記事対象
        'post_status'    => 'publish', // 公開済みの記事のみ
        'fields'         => 'ids',     // IDだけ取得（高速化）
    );

    $css_posts = get_posts( $args );
    $grand_total = 0;

    if ( ! empty( $css_posts ) ) {
        foreach ( $css_posts as $post_id ) {
            // 各記事の保存済み行数を取得して加算
            $lines = get_post_meta( $post_id, '_kumonosu_total_lines', true );
            $grand_total += (int)$lines;
        }
    }

    return $grand_total;
}

/**
 * ------------------------------------------------------------
 * カスタム投稿 blog 用：まとめ掲載 & 関連記事（タブUI版）
 *  - タブ①：まとめに掲載する記事（ID/URL）
 *  - タブ②：関連記事（ID/URL）
 *  - 保存時にURL→IDへ変換
 * ------------------------------------------------------------
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// ----------------------------------------------------------------
// 1. メタボックス登録 (blog専用)
// ----------------------------------------------------------------
if ( ! function_exists( 'kumonosu_blog_register_metabox' ) ) {
  add_action( 'add_meta_boxes', 'kumonosu_blog_register_metabox' );
  function kumonosu_blog_register_metabox() {
    add_meta_box(
      'kumonosu_blog_bundle_fields',
      'まとめ・関連記事',
      'kumonosu_blog_metabox_html',
      'blog',
      'normal',
      'high'
    );
  }
}

// ----------------------------------------------------------------
// 2. メタボックスHTML（タブUI）
// ----------------------------------------------------------------
if ( ! function_exists( 'kumonosu_blog_metabox_html' ) ) {
  function kumonosu_blog_metabox_html( $post ) {
    wp_nonce_field( 'kumonosu_blog_save_meta', 'kumonosu_blog_nonce' );

    // 保存データの取得
    $blog_intro   = get_post_meta( $post->ID, '_kumonosu_blog_intro', true );
    $pick_posts    = get_post_meta( $post->ID, '_kumonosu_pick_posts', true ) ?: array();
    $related_posts = get_post_meta( $post->ID, '_kumonosu_related_posts', true ) ?: array();

    if ( ! is_array( $pick_posts ) ) $pick_posts = array();
    if ( ! is_array( $related_posts ) ) $related_posts = array();
    ?>
    <div id="kumonosu-blog-ui" class="kumonosu-wrapper">

      <ul class="kumonosu-tabs">
        <li class="active" data-tab="tab-intro">導入文</li>
        <li data-tab="tab-pick">まとめ記事</li>
        <li data-tab="tab-related">一緒に読まれている記事</li>
      </ul>

      <div class="kumonosu-content">
        <!-- 【タブ0】導入文 -->
        <div id="tab-intro" class="kumonosu-tab-panel active">
            <div class="kumonosu-section">
                <label class="kumonosu-label">導入文（まとめ記事の冒頭）</label>
                <textarea
                name="kumonosu_blog_intro"
                rows="6"
                style="width:100%;"
                placeholder="このまとめ記事の概要や狙いを書いてください"
                ><?php echo esc_textarea( $blog_intro ); ?></textarea>

                <p class="description" style="margin-top:8px;">
                この文章はまとめ記事ページの冒頭に表示されます。
                </p>
            </div>
        </div>

        <!-- 【タブ1】まとめに掲載する記事 -->
        <div id="tab-pick" class="kumonosu-tab-panel">
          <div class="kumonosu-section">
            <label class="kumonosu-label">まとめに掲載する記事（ID または URL）</label>

            <div id="kumonosu-pick-repeater" class="kumonosu-sortable-area">
              <?php if ( ! empty( $pick_posts ) ) : ?>
                <?php foreach ( $pick_posts as $pid ) : ?>
                  <div class="kumonosu-repeater-item pick-item">
                    <div class="kumonosu-item-handle">⋮⋮</div>
                    <input type="text" name="kumonosu_pick_ids[]" value="<?php echo esc_attr( $pid ); ?>" placeholder="記事ID または URL">
                    <button type="button" class="kumonosu-remove-btn">×</button>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <button type="button" id="add-pick-item" class="button button-large">＋ 追加</button>

            <p class="description" style="margin-top:8px;">
              保存時にURLは自動的に記事IDへ変換されます（css / blog 両方OK）。
            </p>
          </div>
        </div>

        <!-- 【タブ2】関連記事 -->
        <div id="tab-related" class="kumonosu-tab-panel">
          <div class="kumonosu-section">
            <label class="kumonosu-label">関連記事（ID または URL）</label>

            <div id="kumonosu-related-repeater" class="kumonosu-sortable-area">
              <?php if ( ! empty( $related_posts ) ) : ?>
                <?php foreach ( $related_posts as $rid ) : ?>
                  <div class="kumonosu-repeater-item related-item">
                    <div class="kumonosu-item-handle">⋮⋮</div>
                    <input type="text" name="kumonosu_related_ids[]" value="<?php echo esc_attr( $rid ); ?>" placeholder="記事ID または URL">
                    <button type="button" class="kumonosu-remove-btn">×</button>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <button type="button" id="add-related-item" class="button button-large">＋ 追加</button>

            <p class="description" style="margin-top:8px;">
              保存時にURLは自動的に記事IDへ変換されます。
            </p>
          </div>
        </div>

      </div><!-- /.kumonosu-content -->
    </div><!-- /.kumonosu-wrapper -->

    <!-- JS Template（まとめ掲載） -->
    <script type="text/template" id="tmpl-pick-item">
      <div class="kumonosu-repeater-item pick-item">
        <div class="kumonosu-item-handle">⋮⋮</div>
        <input type="text" name="kumonosu_pick_ids[]" value="" placeholder="記事ID または URL">
        <button type="button" class="kumonosu-remove-btn">×</button>
      </div>
    </script>

    <!-- JS Template（関連記事） -->
    <script type="text/template" id="tmpl-related-item">
      <div class="kumonosu-repeater-item related-item">
        <div class="kumonosu-item-handle">⋮⋮</div>
        <input type="text" name="kumonosu_related_ids[]" value="" placeholder="記事ID または URL">
        <button type="button" class="kumonosu-remove-btn">×</button>
      </div>
    </script>

    <style>
      /* メタボックスの見た目をスリム化（添付コードと同じ思想） */
      #kumonosu_blog_bundle_fields { border: none; background: transparent; box-shadow: none; }
      #kumonosu_blog_bundle_fields .postbox-header,
      #kumonosu_blog_bundle_fields .handlediv { display: none !important; }
      #kumonosu_blog_bundle_fields .inside { padding: 0 !important; margin: 0 !important; }

      .kumonosu-wrapper { background: #fff; border: 1px solid #dcdcde; border-radius: 8px; overflow: hidden; font-family: sans-serif; color: #1d2327; }
      .kumonosu-tabs { display: flex; background: #f0f0f1; margin: 0; padding: 0; list-style: none; border-bottom: 1px solid #dcdcde; }
      .kumonosu-tabs li { padding: 12px 20px; cursor: pointer; font-weight: 600; color: #646970; border-right: 1px solid #dcdcde; margin-bottom: 0; }
      .kumonosu-tabs li.active { background: #fff; color: #2271b1; box-shadow: inset 0 3px 0 #2271b1; }

      .kumonosu-content { padding: 20px; }
      .kumonosu-tab-panel { display: none; }
      .kumonosu-tab-panel.active { display: block; }

      .kumonosu-section { margin-bottom: 0; }
      .kumonosu-label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px; }

      /* リピーター */
      .kumonosu-repeater-item { display: flex; align-items: center; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; padding: 12px; margin-bottom: 12px; gap: 10px; }
      .kumonosu-item-handle { cursor: grab; color: #a7aaad; font-size: 20px; user-select: none; }
      .kumonosu-repeater-item input { flex: 1; border: 1px solid #8c8f94; border-radius: 4px; padding: 8px 10px; box-sizing: border-box; }

      .kumonosu-remove-btn { color: #d63638; border: 1px solid #d63638; border-radius: 4px; width: 28px; height: 28px; cursor: pointer; background: #fff; line-height: 1; }
      .kumonosu-remove-btn:hover { background: #d63638; color: #fff; }

      .ui-sortable-helper { opacity: 0.85; box-shadow: 0 4px 10px rgba(0,0,0,0.12); }
      .ui-sortable-placeholder { border: 2px dashed #c3c4c7; visibility: visible !important; margin-bottom: 12px; border-radius: 6px; height: 44px; }
    </style>

    <script>
      jQuery(function($) {
        const $wrap = $('#kumonosu-blog-ui');
        if (!$wrap.length || $wrap.data('initialized')) return;

        // タブ切り替え（添付コードと同じ）
        $('.kumonosu-tabs li').on('click', function() {
          $('.kumonosu-tabs li').removeClass('active');
          $(this).addClass('active');
          $('.kumonosu-tab-panel').removeClass('active');
          $('#' + $(this).data('tab')).addClass('active');
        });

        // 並び替え（両方）
        $('.kumonosu-sortable-area').sortable({
          handle: '.kumonosu-item-handle',
          placeholder: 'ui-sortable-placeholder'
        });

        // 追加（まとめ掲載）
        $('#add-pick-item').on('click', function() {
          $('#kumonosu-pick-repeater').append($('#tmpl-pick-item').html());
        });

        // 追加（関連記事）
        $('#add-related-item').on('click', function() {
          $('#kumonosu-related-repeater').append($('#tmpl-related-item').html());
        });

        // 削除（共通）
        $(document).on('click', '.kumonosu-remove-btn', function() {
          if (confirm('削除しますか？')) {
            $(this).closest('.kumonosu-repeater-item').remove();
          }
        });

        $wrap.data('initialized', true);
      });
    </script>
    <?php
  }
}

// ----------------------------------------------------------------
// 3. 保存処理 (save_post) - まとめ掲載＆関連記事 両方保存
// ----------------------------------------------------------------
if ( ! function_exists( 'kumonosu_blog_save_meta' ) ) {
  add_action( 'save_post', 'kumonosu_blog_save_meta' );
  function kumonosu_blog_save_meta( $post_id ) {
    if ( get_post_type( $post_id ) !== 'blog' ) return;

    if ( ! isset( $_POST['kumonosu_blog_nonce'] ) || ! wp_verify_nonce( $_POST['kumonosu_blog_nonce'], 'kumonosu_blog_save_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // 導入文（空でも保存できるように）
    $intro = isset($_POST['kumonosu_blog_intro'])
    ? sanitize_textarea_field($_POST['kumonosu_blog_intro'])
    : '';

    update_post_meta($post_id, '_kumonosu_blog_intro', $intro);


    // 入力配列 → 有効なID配列へ（URL→ID変換）
    $normalize_to_ids = function( $raw_list ) {
      $ids = array();
      if ( isset( $raw_list ) && is_array( $raw_list ) ) {
        foreach ( $raw_list as $input ) {
          $input = trim( (string) $input );
          if ( $input === '' ) continue;

          $target_id = 0;
          if ( is_numeric( $input ) ) {
            $target_id = (int) $input;
          } else {
            $target_id = url_to_postid( $input );
          }

          if ( $target_id && get_post_status( $target_id ) ) {
            $ids[] = $target_id;
          }
        }
      }
      return array_values( array_unique( $ids ) );
    };

    // まとめ掲載
    $pick_ids = $normalize_to_ids( $_POST['kumonosu_pick_ids'] ?? array() );
    update_post_meta( $post_id, '_kumonosu_pick_posts', $pick_ids );

    // 関連記事
    $related_ids = $normalize_to_ids( $_POST['kumonosu_related_ids'] ?? array() );
    update_post_meta( $post_id, '_kumonosu_related_posts', $related_ids );
  }
}

// ----------------------------------------------------------------
// 4. フロントで「まとめ掲載」用のデータを返す（タイトル/プレビュー/まとめ文）
// ----------------------------------------------------------------
if ( ! function_exists( 'get_kumonosu_blog_pick_posts_data' ) ) {
  function get_kumonosu_blog_pick_posts_data( $post_id = null ) {
    if ( ! $post_id ) $post_id = get_the_ID();

    $ids = get_post_meta( $post_id, '_kumonosu_pick_posts', true );
    if ( empty( $ids ) || ! is_array( $ids ) ) return array();

    $results = array();

    foreach ( $ids as $id ) {
      if ( ! get_post_status( $id ) ) continue;

      $type      = get_post_type( $id );
      $permalink = get_permalink( $id );
      $thumb     = get_the_post_thumbnail_url( $id, 'large' );

      // まとめ文：get_kumonosu_field があるなら優先
      $summary = '';
      if ( function_exists( 'get_kumonosu_field' ) ) {
        $maybe = get_kumonosu_field( 'blog_summary', $id );
        if ( is_string( $maybe ) ) $summary = $maybe;
      }
      if ( $summary === '' ) {
        $summary = (string) get_post_meta( $id, '_kumonosu_blog_summary', true );
      }

      // プレビュー：css→iframe / それ以外→サムネ＋抜粋
      $preview_html = '';
      if ( $type === 'css' ) {
        $src = add_query_arg( 'preview', '', $permalink );
        $preview_html = '<div class="c-blog-pick-preview c-blog-pick-preview--css">'
          . '<iframe src="' . esc_url( $src ) . '" loading="lazy" frameborder="0"></iframe>'
          . '</div>';
      } else {
        $excerpt = get_the_excerpt( $id );
        $preview_html = '<div class="c-blog-pick-preview c-blog-pick-preview--post">'
          . ( $thumb ? '<div class="c-blog-pick-thumb"><img src="' . esc_url( $thumb ) . '" alt=""></div>' : '' )
          . ( $excerpt ? '<p class="c-blog-pick-excerpt">' . esc_html( $excerpt ) . '</p>' : '' )
          . '</div>';
      }

      $results[] = array(
        'id'           => $id,
        'post_type'    => $type,
        'permalink'    => $permalink,
        'title'        => get_the_title( $id ),
        'thumbnail'    => $thumb,
        'preview_html' => $preview_html,
        'summary'      => $summary,
      );
    }

    return $results;
  }
}

/**
 * ------------------------------------------------------------
 * SEO用ディスクリプション設定
 * ------------------------------------------------------------
 */

// メタボックス登録
add_action( 'add_meta_boxes', 'kumonosu_register_description_metabox' );
function kumonosu_register_description_metabox() {
    $screens = array( 'page', 'post', 'css', 'blog' );
    foreach ( $screens as $screen ) {
        add_meta_box(
            'kumonosu_description_meta',
            'SEO設定',
            'kumonosu_description_html',
            $screen,
            'normal',
            'low'
        );
    }
}

// メタボックスHTML
function kumonosu_description_html( $post ) {
    wp_nonce_field( 'kumonosu_save_desc', 'kumonosu_desc_nonce' );
    $value = get_post_meta( $post->ID, '_kumonosu_description', true );
    ?>
    <div style="padding: 10px 0;">
        <label style="display:block; margin-bottom: 8px; font-weight: bold;">メタ・ディスクリプション (meta description)</label>
        <textarea name="kumonosu_description" rows="3" style="width: 100%; border: 1px solid #8c8f94; border-radius: 4px;" placeholder="検索結果に表示される説明文を入力してください（推奨：120文字程度）"><?php echo esc_textarea( $value ); ?></textarea>
        <p class="description">空欄の場合は、記事本文の抜粋が自動的に使用されます。</p>
    </div>
    <?php
}

// 保存処理
add_action( 'save_post', 'kumonosu_save_description_meta' );
function kumonosu_save_description_meta( $post_id ) {
    if ( ! isset( $_POST['kumonosu_desc_nonce'] ) || ! wp_verify_nonce( $_POST['kumonosu_desc_nonce'], 'kumonosu_save_desc' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( isset( $_POST['kumonosu_description'] ) ) {
        update_post_meta( $post_id, '_kumonosu_description', sanitize_textarea_field( $_POST['kumonosu_description'] ) );
    }
}

// 取得用関数
function get_kumonosu_description( $post_id = null ) {
    if ( ! $post_id ) $post_id = get_the_ID();
    return get_post_meta( $post_id, '_kumonosu_description', true );
}

/**
 * header.php で呼び出すためのディスクリプション出力関数
 */
function the_kumonosu_description() {
    // 個別ページ（投稿・固定ページ・CSS）以外では出力しない場合はチェック
    if ( ! is_singular() ) return;

    $post_id = get_the_ID();
    $desc = get_post_meta( $post_id, '_kumonosu_description', true );

    // カスタムフィールドが空の場合の代替処理（抜粋または本文先頭）
    if ( empty( $desc ) ) {
        $post = get_post( $post_id );
        if ( ! empty( $post->post_excerpt ) ) {
            $desc = $post->post_excerpt;
        } else {
            // 本文からタグを除去して120文字抽出
            $desc = wp_trim_words( strip_shortcodes( $post->post_content ), 120, '...' );
        }
    }

    if ( ! empty( $desc ) ) {
        // 改行や余計な空白を綺麗にする
        $desc = str_replace( array("\r\n", "\r", "\n"), '', $desc );
        $desc = esc_attr( trim( $desc ) );

        // メタタグを出力
        echo $desc;
    }
}

/**
 * =========================================================
 * Blog Featured 管理（一覧でON/OFF・並び替え対応）
 *  - postmeta: _is_featured (1/0)
 * =========================================================
 */

/** 1) 一覧に「Featured」列を追加 */
add_filter('manage_blog_posts_columns', function ($cols) {
  $cols['kumonosu_featured'] = 'Featured';
  return $cols;
});

/** 2) 列の中身（☑︎ / — 表示） */
add_action('manage_blog_posts_custom_column', function ($col, $post_id) {
  if ($col !== 'kumonosu_featured') return;

  $v = get_post_meta($post_id, '_is_featured', true);
  // JSで判定しやすいようにdata属性を付与し、記号を ☑︎ に統一
  if ($v) {
    echo '<span class="js-featured-status" data-featured="1">☑︎</span>';
  } else {
    echo '<span class="js-featured-status" data-featured="0">—</span>';
  }
}, 10, 2);

/** 3) 並び替え機能を追加 */
add_filter('manage_edit-blog_sortable_columns', function ($sortable_columns) {
  // '列のID' => '並び替えの識別子'
  $sortable_columns['kumonosu_featured'] = 'kumonosu_featured';
  return $sortable_columns;
});

/** 4) 並び替えのクエリを処理 */
add_action('pre_get_posts', function ($query) {
  if (!is_admin() || !$query->is_main_query()) return;

  if ($query->get('orderby') === 'kumonosu_featured') {
    $query->set('meta_key', '_is_featured');
    $query->set('orderby', 'meta_value_num'); // 1と0の数値で並び替え
  }
});

/** 5) クイック編集にチェックを追加 */
add_action('quick_edit_custom_box', function ($col, $post_type) {
  if ($post_type !== 'blog' || $col !== 'kumonosu_featured') return;
  ?>
  <fieldset class="inline-edit-col-right">
    <div class="inline-edit-col">
      <label class="alignleft">
        <input type="checkbox" name="kumonosu_is_featured" value="1">
        <span class="checkbox-title">ピックアップする</span>
      </label>
    </div>
  </fieldset>
  <?php
}, 10, 2);

/** 6) クイック編集で現在値を反映するJS */
add_action('admin_footer-edit.php', function () {
  $screen = get_current_screen();
  if (!$screen || $screen->post_type !== 'blog') return;
  ?>
  <script>
  (function(){
    const $ = window.jQuery;
    if (!$) return;

    const original = inlineEditPost.edit;
    inlineEditPost.edit = function(id) {
      original.apply(this, arguments);

      let postId = 0;
      if (typeof id === 'object') postId = parseInt(this.getId(id), 10);
      if (!postId) return;

      const $row = $('#post-' + postId);
      // spanのdata属性から 1(ON) か 0(OFF) を取得
      const isFeatured = $row.find('.js-featured-status').data('featured') == 1;

      const $editRow = $('#edit-' + postId);
      $editRow.find('input[name="kumonosu_is_featured"]').prop('checked', isFeatured);
    };
  })();
  </script>
  <?php
});

/** 7) 保存処理 */
add_action('save_post_blog', function ($post_id) {
  if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;
  if (!current_user_can('edit_post', $post_id)) return;

  // ✅ クイック編集（inline-save）の時だけ更新する
  if (isset($_POST['action']) && $_POST['action'] === 'inline-save') {
    $is_featured = isset($_POST['kumonosu_is_featured']) ? 1 : 0;
    update_post_meta($post_id, '_is_featured', $is_featured);
  }

  // ❌ 通常の編集画面では kumonosu_is_featured が送られないので何もしない
}, 10);

/** 8) Featured Topics 取得関数 */
function kumonosu_get_featured_blogs(int $limit = 4): array {
  return get_posts([
    'post_type'      => 'blog',
    'post_status'    => 'publish',
    'posts_per_page' => $limit,
    'meta_key'       => '_is_featured',
    'meta_value'     => 1,
    'orderby'        => 'date',
    'order'          => 'DESC',
  ]);
}

/* =====================================================
 * Headタグ管理ページを追加
 * ===================================================== */
add_action('admin_menu', function () {
    add_menu_page(
        'Headタグ管理',
        'Headタグ',
        'manage_options',
        'kumonosu-head-tags',
        'kumonosu_render_head_tags_page',
        'dashicons-editor-code',
        80
    );
});

function kumonosu_render_head_tags_page() {
    if (!current_user_can('manage_options')) return;

    // 保存処理
    if (isset($_POST['kumonosu_head_tags_nonce']) &&
        wp_verify_nonce($_POST['kumonosu_head_tags_nonce'], 'save_head_tags')
    ) {
        $tags = array_values(array_filter($_POST['head_tags'] ?? [], function ($item) {
            return !empty($item['code']);
        }));
        update_option('kumonosu_head_tags', $tags);
        echo '<div class="updated"><p>保存しました。</p></div>';
    }

    $head_tags = get_option('kumonosu_head_tags', []);
    ?>

    <div class="wrap">
        <h1>Headタグ管理</h1>

        <form method="post">
            <?php wp_nonce_field('save_head_tags', 'kumonosu_head_tags_nonce'); ?>

            <table class="widefat" id="head-tags-table">
                <thead>
                    <tr>
                        <th style="width:20%">タイトル</th>
                        <th>Head内に出力するタグ</th>
                        <th style="width:60px"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($head_tags as $i => $tag): ?>
                    <tr>
                        <td>
                            <input type="text"
                                   name="head_tags[<?= $i ?>][title]"
                                   value="<?= esc_attr($tag['title'] ?? '') ?>"
                                   class="regular-text">
                        </td>
                        <td>
                            <textarea name="head_tags[<?= $i ?>][code]"
                                      rows="4"
                                      style="width:100%; font-family: monospace;"><?= esc_textarea($tag['code'] ?? '') ?></textarea>
                        </td>
                        <td>
                            <button type="button" class="button remove-row">削除</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <p>
                <button type="button" class="button" id="add-head-tag">＋ タグを追加</button>
            </p>

            <?php submit_button(); ?>
        </form>
    </div>

    <script>
    document.getElementById('add-head-tag').addEventListener('click', () => {
        const tbody = document.querySelector('#head-tags-table tbody');
        const index = tbody.children.length;

        tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <td><input type="text" name="head_tags[${index}][title]" class="regular-text"></td>
            <td><textarea name="head_tags[${index}][code]" rows="4" style="width:100%; font-family: monospace;"></textarea></td>
            <td><button type="button" class="button remove-row">削除</button></td>
        </tr>
        `);
    });

    document.addEventListener('click', e => {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
        }
    });
    </script>

<?php }

/* =====================================================
 * Headタグを出力
 * ===================================================== */
add_action('wp_head', function () {

    // iframe プレビュー・埋め込みは除外
    if (isset($_GET['preview']) || is_embed()) {
        return;
    }

    $tags = get_option('kumonosu_head_tags', []);
    if (empty($tags)) return;

    foreach ($tags as $tag) {
        if (!empty($tag['code'])) {
            $title = esc_html($tag['title'] ?? '');
            echo "\n<!-- {$title} -->\n";
            echo $tag['code'] . "\n";
        }
    }

}, 5);
