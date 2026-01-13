<?php
global $post;
if(is_single()) {
  $content = $post->post_content ;//本文取得
  $content = strip_shortcodes( $content );//ショートコ―ド除去
  $content = wp_strip_all_tags( $content,true );//htmlタグ周り除去
  $content = str_replace('&nbsp; ', '', $content);
  $content = mb_substr($content,0,100).'...';//抜粋
}

if(is_front_page() || is_category() || is_tag()): ?>
<meta name="twitter:card" content="summary_large_image" />
<meta property="og:image" content="<?= esc_url( get_template_directory_uri() ); ?>/assets/img/common/ogp.jpg">
<meta property="og:title" content="<?php echo esc_attr( get_bloginfo('name') ); ?>" />
<meta property="og:description" content="<?php bloginfo('description'); ?>" />
<meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo('name') ); ?>" />
<meta property="og:locale" content="ja_JP" />
<meta property="og:type" content="website" />
<meta property="og:url" content="<?= (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"]. $_SERVER["REQUEST_URI"]; ?>" />
<?php elseif(is_single() && !is_singular('actress')): ?>
<meta name="twitter:card" content="summary_large_image" />
<?php if(has_post_thumbnail()):
  $image_id = get_post_thumbnail_id();
  $image = wp_get_attachment_image_src($image_id, 'full'); ?>
  <meta property="og:image" content="<?= $image[0] ; ?>">
<?php else: //アイキャッチがない場合?>
  <meta property="og:image" content="<?= esc_url( get_template_directory_uri() ); ?>/assets/img/common/ogp.jpg">
<?php endif; ?>
<?php if(!is_singular('actress')): ?>
  <meta property="og:title" content="<?= the_title('') . '【KUMONOSU】'; ?>" />
  <meta property="og:description" content="<?php the_kumonosu_description(); ?>"/>
  <meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo('name') ); ?>" />
  <meta property="og:locale" content="ja_JP" />
  <meta property="og:type" content="article" />
  <meta property="og:url" content="<?php the_permalink() ; ?>"/>
<?php endif; ?>
<?php endif; ?>
<?php if(is_post_type_archive('css')): ?>
  <meta name="twitter:card" content="summary_large_image" />
  <meta property="og:image" content="<?= esc_url( get_template_directory_uri() ); ?>/assets/img/common/ogp.jpg">
  <meta property="og:title" content="CSS一覧【KUMONOSU】" />
  <meta property="og:description" content="CSSやJavaScriptを使ったアニメーションを一覧で紹介しています。動きやパーツ別にアイデア探しに活用できます。"/>
  <meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo('name') ); ?>" />
  <meta property="og:locale" content="ja_JP" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?php echo esc_url( (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ); ?>"/>
<?php endif; ?>
<?php if(is_post_type_archive('blog')): ?>
  <meta name="twitter:card" content="summary_large_image" />
  <meta property="og:image" content="<?= esc_url( get_template_directory_uri() ); ?>/assets/img/common/ogp.jpg">
  <meta property="og:title" content="特集&まとめ【KUMONOSU】" />
  <meta property="og:description" content="CSS・JavaScriptについての特集やまとめの記事を一覧で紹介しています。アイデア探しや参考に活用できます。"/>
  <meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo('name') ); ?>" />
  <meta property="og:locale" content="ja_JP" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?php echo esc_url( (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ); ?>"/>
<?php endif; ?>
<?php if(is_page()): ?>
  <meta name="twitter:card" content="summary_large_image" />
  <meta property="og:image" content="<?= esc_url( get_template_directory_uri() ); ?>/assets/img/common/ogp.jpg">
  <meta property="og:title" content="<?= the_title('') . '【KUMONOSU】'; ?>" />
  <meta property="og:description" content="<?php the_kumonosu_description(); ?>"/>
  <meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo('name') ); ?>" />
  <meta property="og:locale" content="ja_JP" />
  <meta property="og:type" content="article" />
  <meta property="og:url" content="<?php the_permalink() ; ?>"/>
<?php endif; ?>