<!-- サイドバー -->
<aside class="l-main-aside" aria-label="Sidebar">
  <?php get_template_part('searchform', 'php'); ?>
  
  <?php
  // ----------------------------------------------------------------
  // 現在の投稿タイプに紐付く「全ての」カテゴリ・タグを取得する処理
  // ----------------------------------------------------------------

  // 1. 投稿タイプを取得
  $current_post_type = get_post_type();

  // 2. この投稿タイプに関連するタクソノミー（分類）情報をすべて取得
  $taxonomies = get_object_taxonomies( $current_post_type, 'objects' );

  $all_cats = array(); // カテゴリ（階層あり）用の配列
  $all_tags = array(); // タグ（階層なし）用の配列

  if ( ! empty( $taxonomies ) ) {
      foreach ( $taxonomies as $tax_slug => $tax_obj ) {
          // 公開されていないタクソノミーは除外（必要に応じて削除可）
          if ( ! $tax_obj->public ) continue;

          // ★ここで「全てのターム」を取得します
          $terms = get_terms( array(
              'taxonomy'   => $tax_slug,
              'hide_empty' => true, // 投稿が1件もないカテゴリは隠す（表示したい場合は false に）
          ) );

          if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
              // 階層の有無で振り分け
              if ( $tax_obj->hierarchical ) {
                  $all_cats = array_merge( $all_cats, $terms );
              } else {
                  $all_tags = array_merge( $all_tags, $terms );
              }
          }
      }
  }
  ?>

  <!-- ▼ カテゴリ一覧（Category） -->
  <?php if ( ! empty( $all_cats ) ) : ?>
  <section class="c-aside-block c-aside-category">
    <h3 class="c-aside-title">Category</h3>
    <ul>
      <?php foreach ( $all_cats as $term ) : ?>
        <li>
          <a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
            <?php echo esc_html( $term->name ); ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <!-- ▼ タグ一覧（Tags） -->
  <?php if ( ! empty( $all_tags ) ) : ?>
  <section class="c-aside-block c-aside-tag">
    <h3 class="c-aside-title">Tags</h3>
    <ul>
      <?php foreach ( $all_tags as $term ) : ?>
        <li>
          <a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
            <?php echo esc_html( $term->name ); ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <div class="l-aside-sticky">
    <section class="c-aside-block">
      <h3 class="c-aside-title">Contents</h3>
      <?php echo '[PAGE_TOC]'; ?>
    </section>

    <section class="c-aside-block share-block">
      <h3 class="c-aside-title">Share</h3>
      <ul class="c-aside-list">
        <li><a href="#" aria-label="リンクURL"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/link_icon.svg" alt="X"></a></li>
        <li><a href="#" aria-label="LINE"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/line_icon.svg" alt="X"></a></li>
        <li><a href="#" aria-label="X"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/x_icon.svg" alt="X"></a></li>
      </ul>
    </section>
  </div>

</aside>