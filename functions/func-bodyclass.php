<?php
/* =============================================
全ページタイプ対応の body_class 拡張
ページや投稿のスラッグ、タクソノミー名などを自動追加
============================================= */
function add_dynamic_body_classes($classes) {
    if (is_singular()) {
        // 投稿タイプ取得
        $post = get_queried_object();
        $post_type = $post->post_type;
        $slug = $post->post_name;

        // 固定ページ・投稿・カスタム投稿タイプにスラッグクラス追加
        $classes[] = "page-{$slug}";
        $classes[] = "post-type-{$post_type}";
    } elseif (is_home() || is_front_page()) {
        $classes[] = 'home';
        if (is_front_page()) {
            $classes[] = 'front-page';
        }
    } elseif (is_category()) {
        $cat = get_queried_object();
        $classes[] = 'category-' . $cat->slug;
    } elseif (is_tag()) {
        $tag = get_queried_object();
        $classes[] = 'tag-' . $tag->slug;
    } elseif (is_tax()) {
        $tax = get_queried_object();
        $classes[] = 'taxonomy-' . $tax->taxonomy;
        $classes[] = 'term-' . $tax->slug;
    } elseif (is_archive()) {
        $classes[] = 'archive';
    } elseif (is_search()) {
        $classes[] = 'search';
    } elseif (is_404()) {
        $classes[] = 'error-404';
    }

    return $classes;
}
add_filter('body_class', 'add_dynamic_body_classes');
