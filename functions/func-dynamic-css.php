<?php
/* =============================================
ページスラッグに基づいて動的にCSSファイルを読み込む
（読み込み順制御の為、wp_enqueue_styleは未使用）
============================================= */

function print_dynamic_page_css() {

    // フロントページ専用
    if ( is_front_page() ) {
        $front_file = get_template_directory() . "/assets/css/project/page-front.css";
        $front_url  = get_template_directory_uri() . "/assets/css/project/page-front.css";

        if ( file_exists( $front_file ) ) {
            echo '<link rel="stylesheet" href="' . esc_url($front_url) . '?ver=' . filemtime($front_file) . '" media="all">' . "\n";
        }
        return;
    }

    // 固定ページ専用
    if ( is_page() ) {

        // ① 固定ページ共通：page.css を常に読む
        $common_file = get_template_directory() . "/assets/css/project/page.css";
        $common_url  = get_template_directory_uri() . "/assets/css/project/page.css";

        if ( file_exists( $common_file ) ) {
            echo '<link rel="stylesheet" href="' . esc_url( $common_url ) . '?ver=' . filemtime( $common_file ) . '" media="all">' . "\n";
        }

        // ② 固定ページ個別：page-{slug}.css があれば追加で読む（例：page-contact.css）
        $slug = get_post_field( 'post_name', get_queried_object_id() );
        $page_file = get_template_directory() . "/assets/css/project/page-{$slug}.css";
        $page_url  = get_template_directory_uri() . "/assets/css/project/page-{$slug}.css";

        if ( file_exists( $page_file ) ) {
            echo '<link rel="stylesheet" href="' . esc_url( $page_url ) . '?ver=' . filemtime( $page_file ) . '" media="all">' . "\n";
        }
    }

    // アーカイブ個別：archive-{post_type}.css があれば追加で読む
    // 投稿一覧(is_home)の場合は 'post'
    if ( is_home() ) {
    $post_type = 'post';
    } else {
    $pt = get_query_var('post_type');

    if ( is_array($pt) ) {
        $pt = reset($pt);
    }

    // post_type が取れないケース（0件 / taxアーカイブ等）を補完
    if ( empty($pt) ) {
        if ( is_post_type_archive('css') || is_tax(['css-category', 'css-tag']) ) {
        $pt = 'css';
        }
    }

    $post_type = $pt ?: 'post';
    }

    $type_file = get_template_directory() . "/assets/css/project/archive-{$post_type}.css";
    $type_url  = get_template_directory_uri() . "/assets/css/project/archive-{$post_type}.css";

    if ( file_exists( $type_file ) ) {
    echo '<link rel="stylesheet" href="' . esc_url( $type_url ) . '?ver=' . filemtime( $type_file ) . '" media="all">' . "\n";
    }

    // カスタム投稿の詳細ページだけ（post / page は除外）
    if ( is_singular() ) {

        $post_type = get_post_type();

        // post と page は除外（＝カスタム投稿のみ対象）
        if ( $post_type && ! in_array( $post_type, [ 'post', 'page' ], true ) ) {

            // ① 共通：single.css
            $common_file = get_template_directory() . '/assets/css/project/single.css';
            $common_url  = get_template_directory_uri() . '/assets/css/project/single.css';

            if ( file_exists( $common_file ) ) {
                echo '<link rel="stylesheet" href="' . esc_url( $common_url ) . '?ver=' . filemtime( $common_file ) . '" media="all">' . "\n";
            }

            // ② 個別：single-{slug}.css
            $type_file = get_template_directory() . "/assets/css/project/single-{$post_type}.css";
            $type_url  = get_template_directory_uri() . "/assets/css/project/single-{$post_type}.css";

            if ( file_exists( $type_file ) ) {
                echo '<link rel="stylesheet" href="' . esc_url( $type_url ) . '?ver=' . filemtime( $type_file ) . '" media="all">' . "\n";
            }

        }
    }

}
