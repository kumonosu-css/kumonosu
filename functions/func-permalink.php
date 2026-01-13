<?php

// カテゴリベースの(/category/)を削除
function remcat_function($link) {
  return str_replace("/category/", "/", $link);
}
add_filter('user_trailingslashit', 'remcat_function');

// タグベースの(/tag/)を削除
function remtag_function($link) {
  return str_replace("/tag/", "/", $link);
}
add_filter('user_trailingslashit', 'remtag_function');
add_filter('actress_cat_rewrite_rules', '__return_empty_array');
add_filter('shop_rewrite_rules', '__return_empty_array');

// $wp_rewrite 更新関数
function remcat_flush_rules() {
  global $wp_rewrite;
  $wp_rewrite->flush_rules();
}
add_action('init', 'remcat_flush_rules');

define("DEFINE_KEY", "page");
define("DEFINE_CAT", "(category[0-9]{1,})"); //1桁以上の半角数字
define("DEFINE_TAG", "(tag[^/]+)");

//var_dump($wp_rewrite->preg_index(2));
function my_rewrite_rule() {
  add_rewrite_rule('av/(pref[0-9]+)/page/?([0-9]{1,})/?$', 'index.php?actress_area=$matches[1]&page=$matches[2]', 'top');
  add_rewrite_rule('av/(pref[0-9]+)/(dep[0-9]+)/page/?([0-9]{1,})/?$', 'index.php?actress_area=$matches[2]&page=$matches[3]', 'top');
  add_rewrite_rule('av/(pref[0-9]+)/?$', 'index.php?actress_area=$matches[1]', 'top');
  add_rewrite_rule('av/(pref[0-9]+)/(dep[0-9]+)/?$', 'index.php?actress_area=$matches[2]', 'top');
  add_rewrite_rule('av/([0-9]+)/?$', 'index.php?post_type=actress&p=$matches[1]', 'top');
  add_rewrite_rule('av/([^/]+)/?', 'index.php?actress_cat=$matches[1]', 'top');
  #add_rewrite_rule('av/([^/]+)/?' . DEFINE_KEY . '([^/]*)/?', 'index.php?actress_cat=$matches[1]&' . DEFINE_KEY . '=$matches[2]', 'top');
  add_rewrite_rule(DEFINE_CAT . '/' . DEFINE_KEY . '([^/]*)/?','index.php?category_name=$matches[1]&' . DEFINE_KEY . '=$matches[2]','top');
  add_rewrite_rule(DEFINE_CAT . '/?' ,'index.php?category_name=$matches[1]','top');
  add_rewrite_rule(DEFINE_TAG . '/' . DEFINE_KEY . '([^/]*)/?','index.php?tag=$matches[1]&' . DEFINE_KEY . '=$matches[2]','top');
  add_rewrite_rule(DEFINE_TAG . '/?' ,'index.php?tag=$matches[1]','top');
  #add_rewrite_rule('gravure/?'. DEFINE_KEY . '([^/]*)/?' ,'index.php?post_type=gravure&' . DEFINE_KEY . '=$matches[1]','top');
  #add_rewrite_rule('gravure/?' ,'index.php?post_type=gravure','top');
  #add_rewrite_rule('av/?'. DEFINE_KEY . '([^/]*)/?' ,'index.php?post_type=av&' . DEFINE_KEY . '=$matches[1]','top');
  #add_rewrite_rule('av/?' ,'index.php?post_type=av','top');
  add_rewrite_rule('archive/?' . DEFINE_KEY . '([^/]*)/?','index.php?post_type=post&' . DEFINE_KEY . '=$matches[1]','top');
}
add_action('init', 'my_rewrite_rule', 10, 0);

//function my_rewrite_rules_array( $rules ) {
//  $new_rules = array(
//    //アーカイブページ送り
//    'my_post(?:/([0-9]+))?/?$' => 'index.php?post_type=my_post&paged=$matches[1]',
//    //個別記事
//    'my_post/(.+?)/([0-9]+)$' => 'index.php?post_type=my_post&p=$matches[2]',
//    //アーカイブ
//    'my_post/([^/]+)(?:/([0-9]+))?/?$' => 'index.php?my_category=$matches[1]&paged=$matches[2]',
//  );
//  return $new_rules + $rules;
//}
//add_filter( 'rewrite_rules_array', 'my_rewrite_rules_array' );

/**
 * カテゴリ・タグをurlクエリ形式に
 */
add_action( 'template_redirect', function () {

    if ( ! is_tax() ) {
        return;
    }

    $term = get_queried_object();

    if ( ! $term || is_wp_error( $term ) ) {
        return;
    }

    $taxonomy = $term->taxonomy;

    // ★ すでにクエリに同じ taxonomy が付いている場合は何もしない（ループ防止）
    if ( isset( $_GET[ $taxonomy ] ) ) {
        return;
    }

    // タクソノミー → post_type の対応表
    $map = array(
        'css-category'  => 'css',
        'css-tag'       => 'css',
        'blog-category' => 'blog',
        'blog-tag'      => 'blog',
    );

    // 対象外は何もしない
    if ( ! isset( $map[ $taxonomy ] ) ) {
        return;
    }

    $post_type   = $map[ $taxonomy ];
    $archive_url = get_post_type_archive_link( $post_type );

    if ( ! $archive_url ) {
        return;
    }

    // 例）/css/?css-category=animation
    $url = add_query_arg(
        array(
            $taxonomy => $term->slug,
        ),
        $archive_url
    );

    wp_redirect( $url, 301 );
    exit;
});


/**
 * Custom sitemap.xml (plugin-free)
 * 出力対象：
 * - トップページ
 * - 固定ページ（thanks / sitemap 除外）
 * - カスタム投稿アーカイブ（lastmod 付き）
 * - カスタム投稿の記事
 */

// ----------------------------------
// 1) /sitemap.xml を受ける
// ----------------------------------
add_action('init', function () {
  add_rewrite_rule('^sitemap\.xml$', 'index.php?kumonosu_sitemap=1', 'top');
  add_rewrite_tag('%kumonosu_sitemap%', '1');
});

// ----------------------------------
// 2) sitemap.xml を出力
// ----------------------------------
add_action('template_redirect', function () {
  if (get_query_var('kumonosu_sitemap') != 1) return;

  header('Content-Type: application/xml; charset=UTF-8');
  header('X-Robots-Tag: noindex', true);

  // ▼ 対象のカスタム投稿タイプ
  $cpt_types = [
    'css',
    'blog',
  ];

  // ▼ sitemap に含めない固定ページ（パス）
  $exclude_page_slugs = [
    'contact/thanks',
    'thanks',
    'sitemap',
  ];

  // URL正規化（クエリ削除）
  $normalize_url = function ($url) {
    return esc_url(strtok((string) $url, '?'));
  };

  // ISO8601
  $iso = function ($ts) {
    return gmdate('c', (int) $ts);
  };

  // 重複防止
  $seen = [];

  $add_url = function ($loc, $lastmod_ts = null) use (&$seen, $normalize_url, $iso) {
    $loc = $normalize_url($loc);
    if (!$loc || isset($seen[$loc])) return;
    $seen[$loc] = true;

    echo "  <url>\n";
    echo "    <loc>{$loc}</loc>\n";
    if ($lastmod_ts) {
      echo "    <lastmod>" . esc_html($iso($lastmod_ts)) . "</lastmod>\n";
    }
    echo "  </url>\n";
  };

  echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
  echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

  // ----------------------------------
  // A) トップページ
  // ----------------------------------
  $add_url(home_url('/'), time());

  // ----------------------------------
  // B) 固定ページ（除外あり）
  // ----------------------------------
  $pages = get_posts([
    'post_type'      => 'page',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'modified',
    'order'          => 'DESC',
    'no_found_rows'  => true,
    'fields'         => 'ids',
  ]);

  foreach ($pages as $pid) {
    $permalink = get_permalink($pid);
    $path = trim(parse_url($permalink, PHP_URL_PATH), '/');

    if (in_array($path, $exclude_page_slugs, true)) {
      continue;
    }

    $add_url(
      $permalink,
      (int) get_post_modified_time('U', true, $pid)
    );
  }

  // ----------------------------------
  // C) カスタム投稿アーカイブ（lastmod付き）
  // ----------------------------------
  foreach ($cpt_types as $pt) {
    $archive = get_post_type_archive_link($pt);
    if (!$archive) continue;

    // その投稿タイプの最新更新日時を取得
    $latest = get_posts([
      'post_type'      => $pt,
      'post_status'    => 'publish',
      'posts_per_page' => 1,
      'orderby'        => 'modified',
      'order'          => 'DESC',
      'no_found_rows'  => true,
      'fields'         => 'ids',
    ]);

    $lastmod = $latest
      ? (int) get_post_modified_time('U', true, $latest[0])
      : null;

    $add_url($archive, $lastmod);
  }

  // ----------------------------------
  // D) カスタム投稿の記事
  // ----------------------------------
  foreach ($cpt_types as $pt) {
    $posts = get_posts([
      'post_type'      => $pt,
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'orderby'        => 'modified',
      'order'          => 'DESC',
      'no_found_rows'  => true,
      'fields'         => 'ids',
    ]);

    foreach ($posts as $id) {
      $add_url(
        get_permalink($id),
        (int) get_post_modified_time('U', true, $id)
      );
    }
  }

  echo "</urlset>\n";
  exit;
});