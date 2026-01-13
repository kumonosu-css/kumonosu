<?php
/**
 * @package WordPress
 * @subpackage notebook Original Theme
 */
//single専用固定ページテーマ

get_header();
$category = get_the_category();
$slug = esc_html(get_post_type_object(get_post_type())->name);
?>

<!-- 本文エリア -->
<div class="l-main-content">
  <!-- Thumbnail -->
  <figure class="l-section l-section--thumbnail">
    <?php the_post_thumbnail('full', [
      'alt' => get_the_title(),
      'loading' => 'lazy',
      'decoding' => 'async',
    ]); ?>
  </figure>
  <div class="l-main-content-inner">
    <!-- Entry Header -->
    <section class="l-section l-section-header">
      <div class="c-tag">
        <?php
        $terms = get_the_terms(get_the_ID(), 'blog-category');

        if (!empty($terms) && !is_wp_error($terms)) :
            foreach ($terms as $term) :
                ?>
                <p class="c-card-category-name"><?php echo esc_html($term->name); ?></p>
                <?php
            endforeach;
        endif;
        ?>
      </div>
      <h1 class="c-entry-title"><?php the_title(); ?></h1>
      <div class="c-date-list">
        <p class="date"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_date.svg" alt="投稿日" loading="lazy"><?php echo esc_html( get_the_date('Y/m/d') ); ?></p>
        <p class="date"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_update.svg" alt="更新日" loading="lazy"><?php the_modified_date(); ?></p>
      </div>
      <?php
      $terms = get_the_terms(get_the_ID(), 'blog-tag');
      if (!is_wp_error($terms) && !empty($terms)) : ?>
      <ul class="c-tag-list">
        <?php foreach ($terms as $term) : ?>
          <li class="c-css-tag">
            <a href="<?php echo esc_url(get_term_link($term)); ?>">
                #<?php echo esc_html($term->name); ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </section>

    <section class="l-section l-section--intro is-visible">
      <p class="c-intro-content"><?php
      $intro = get_post_meta( get_the_ID(), '_kumonosu_blog_intro', true );

      if ( $intro ) {
        echo nl2br( esc_html( $intro ) );
      }
      ?>
      </p>
    </section>

    <!-- Toc -->
    <section class="l-section l-section--toc">
      <div class="c-toc-inner">
        <h3 class="c-toc-title">目次</h3>
        <?php echo '[PAGE_TOC]'; ?>
      </div>
    </section>

    <section class="l-section l-section--about">
      <div class="c-section-content">
        <?php the_content();?>

        <?php
        $items = get_kumonosu_blog_pick_posts_data();

        if ( ! empty( $items ) ) : ?>
          <?php foreach ( $items as $item ) : ?>
            <article class="c-blog-pick">

              <!-- タイトル -->
              <h2 class="c-blog-pick-title">
                <a href="<?php echo esc_url( $item['permalink'] ); ?>">
                  <?php echo esc_html( $item['title'] ); ?>
                </a>
              </h2>

              <!-- CSS記事のみプレビュー ＋ コード表示 -->
              <?php if ( $item['post_type'] === 'css' ) : ?>

                <?php
                  // ------------------------------------------------------------
                  // 1) Preview iframe 用
                  // ------------------------------------------------------------
                  $preview_url = add_query_arg( 'preview', '', $item['permalink'] );

                  $sandbox_tokens = array(
                    'allow-scripts',
                    'allow-same-origin',
                    'allow-forms',
                    'allow-modals'
                  );
                  $iframe_allow_value = 'fullscreen';

                  // ------------------------------------------------------------
                  // 2) CSS記事ID（あなたのデータ構造は id が本命）
                  // ------------------------------------------------------------
                  $css_post_id = (int) ( $item['id'] ?? 0 );

                  // ------------------------------------------------------------
                  // 3) single-css.php と同じ関数でフィールド取得（超重要）
                  //    ※ get_kumonosu_field() が get_the_ID() 依存の可能性が高いので
                  //      一時的に $post を差し替えて setup_postdata する
                  // ------------------------------------------------------------
                  $kumonosu = array();

                  if ( $css_post_id ) {
                    global $post;
                    $backup_post = $post;

                    $post = get_post( $css_post_id );

                    if ( $post ) {
                      setup_postdata( $post );
                      $kumonosu = get_kumonosu_field();
                      wp_reset_postdata();
                    }

                    $post = $backup_post;
                  }

                  // ------------------------------------------------------------
                  // 4) コードが1つでもあるか判定
                  // ------------------------------------------------------------
                  $has_code = false;
                  $code_keys = array('code_html','code_css','code_js','code_config','ext_css','ext_js');

                  foreach ( $code_keys as $k ) {
                    if ( ! empty( $kumonosu[$k] ) ) { $has_code = true; break; }
                  }

                  // ------------------------------------------------------------
                  // 5) タブ・コピーのID衝突回避（投稿IDでユニーク化）
                  // ------------------------------------------------------------
                  $tab_name = 'preview-tab-' . $css_post_id;
                  $suffix   = '-' . $css_post_id;
                ?>

                <!-- Preview -->
                <div class="c-blog-pick-preview c-blog-pick-preview--css">
                  <iframe
                    class="c-preview-iframe"
                    sandbox="<?php echo esc_attr( implode( ' ', $sandbox_tokens ) ); ?>"
                    allow="<?php echo esc_attr( $iframe_allow_value ); ?>"
                    allowfullscreen
                    src="<?php echo esc_url( $preview_url ); ?>"
                    loading="lazy"
                    referrerpolicy="same-origin"
                    title="<?php echo esc_attr( $item['title'] . ' preview' ); ?>"
                  ></iframe>
                </div>

                <!-- Code -->
                <?php if ( $has_code ) : ?>
                  <div class="c-code c-code--in-blog">
                    <div class="c-preview-tab">

                      <!-- HTML -->
                      <?php if ( ! empty( $kumonosu['code_html'] ) ) : ?>
                        <label>
                          <input type="radio" name="<?php echo esc_attr($tab_name); ?>" checked>
                          HTML
                        </label>
                        <div class="c-code-panel" id="panel-html<?php echo esc_attr($suffix); ?>">
                          <button type="button" class="c-code-copy" data-copy-target="#code-html<?php echo esc_attr($suffix); ?>" aria-label="HTMLをコピー">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_copy.svg" alt="HTMLをコピー">
                          </button>
                          <pre class="c-code-code" id="code-html<?php echo esc_attr($suffix); ?>"><code class="language-html"><?php echo esc_html( $kumonosu['code_html'] ); ?></code></pre>
                        </div>
                      <?php endif; ?>

                      <!-- CSS -->
                      <?php if ( ! empty( $kumonosu['code_css'] ) ) : ?>
                        <label>
                          <input type="radio" name="<?php echo esc_attr($tab_name); ?>" <?php echo empty($kumonosu['code_html']) ? 'checked' : ''; ?>>
                          CSS
                        </label>
                        <div class="c-code-panel" id="panel-css<?php echo esc_attr($suffix); ?>">
                          <button type="button" class="c-code-copy" data-copy-target="#code-css<?php echo esc_attr($suffix); ?>" aria-label="CSSをコピー">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_copy.svg" alt="CSSをコピー">
                          </button>
                          <pre class="c-code-code" id="code-css<?php echo esc_attr($suffix); ?>"><code class="language-css"><?php echo esc_html( $kumonosu['code_css'] ); ?></code></pre>
                        </div>
                      <?php endif; ?>

                      <!-- JavaScript -->
                      <?php if ( ! empty( $kumonosu['code_js'] ) ) : ?>
                        <label>
                          <input type="radio" name="<?php echo esc_attr($tab_name); ?>" <?php echo (empty($kumonosu['code_html']) && empty($kumonosu['code_css'])) ? 'checked' : ''; ?>>
                          JavaScript
                        </label>
                        <div class="c-code-panel" id="panel-js<?php echo esc_attr($suffix); ?>">
                          <button type="button" class="c-code-copy" data-copy-target="#code-js<?php echo esc_attr($suffix); ?>" aria-label="JavaScriptをコピー">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_copy.svg" alt="JavaScriptをコピー">
                          </button>
                          <pre class="c-code-code" id="code-js<?php echo esc_attr($suffix); ?>"><code class="language-javascript"><?php echo esc_html( $kumonosu['code_js'] ); ?></code></pre>
                        </div>
                      <?php endif; ?>

                      <!-- Config (JSON) -->
                      <?php if ( ! empty( $kumonosu['code_config'] ) ) : ?>
                        <label>
                          <input type="radio" name="<?php echo esc_attr($tab_name); ?>">
                          Config
                        </label>
                        <div class="c-code-panel" id="panel-config<?php echo esc_attr($suffix); ?>">
                          <button type="button" class="c-code-copy" data-copy-target="#code-config<?php echo esc_attr($suffix); ?>" aria-label="Configをコピー">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_copy.svg" alt="Configをコピー">
                          </button>
                          <pre class="c-code-code" id="code-config<?php echo esc_attr($suffix); ?>"><code class="language-json"><?php echo esc_html( $kumonosu['code_config'] ); ?></code></pre>
                        </div>
                      <?php endif; ?>

                      <!-- 外部CSS -->
                      <?php if ( ! empty( $kumonosu['ext_css'] ) ) : ?>
                        <label>
                          <input type="radio" name="<?php echo esc_attr($tab_name); ?>">
                          外部CSS
                        </label>
                        <div class="c-code-panel" id="panel-extcss<?php echo esc_attr($suffix); ?>">
                          <button type="button" class="c-code-copy" data-copy-target="#code-extcss<?php echo esc_attr($suffix); ?>" aria-label="外部CSSをコピー">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_copy.svg" alt="外部CSSをコピー">
                          </button>
                          <pre class="c-code-code" id="code-extcss<?php echo esc_attr($suffix); ?>"><code class="language-text"><?php echo esc_html( $kumonosu['ext_css'] ); ?></code></pre>
                        </div>
                      <?php endif; ?>

                      <!-- 外部JS -->
                      <?php if ( ! empty( $kumonosu['ext_js'] ) ) : ?>
                        <label>
                          <input type="radio" name="<?php echo esc_attr($tab_name); ?>">
                          外部JS
                        </label>
                        <div class="c-code-panel" id="panel-extjs<?php echo esc_attr($suffix); ?>">
                          <button type="button" class="c-code-copy" data-copy-target="#code-extjs<?php echo esc_attr($suffix); ?>" aria-label="外部JSをコピー">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_copy.svg' ); ?>" alt="外部JSをコピー">
                          </button>
                          <pre class="c-code-code" id="code-extjs<?php echo esc_attr($suffix); ?>"><code class="language-text"><?php echo esc_html( $kumonosu['ext_js'] ); ?></code></pre>
                        </div>
                      <?php endif; ?>

                    </div>
                  </div>
                <?php endif; ?>

              <?php endif; ?>

              <!-- まとめ文 -->
              <?php if ( ! empty( $item['summary'] ) ) : ?>
                <div class="c-blog-summary">
                  <?php echo nl2br( esc_html( $item['summary'] ) ); ?>
                </div>
              <?php endif; ?>

              <!-- CSS記事のみ CTA -->
              <?php if ( $item['post_type'] === 'css' ) : ?>
                <div class="c-blog-pick-action">
                  <a href="<?php echo esc_url( $item['permalink'] ); ?>" class="gradient-btn">
                    <span class="gradient-btn__text">詳しい説明を見る</span>
                    <span class="gradient-btn__icon">»</span>
                  </a>
                </div>
              <?php endif; ?>

            </article>
          <?php endforeach; ?>
        <?php endif; ?>

      </div>
    </section>

    <!-- Together -->
    <?php
    // functions.phpで定義した関数からデータを取得
    $related_posts = get_kumonosu_related_posts_data();

    if ( ! empty( $related_posts ) ) : ?>
    <section class="l-section l-section--together">
        <h2 class="c-section-title">
          <span class="c-section-title-main" data-text="Together">Together</span>
          <span class="c-section-title-sub" data-text="一緒に読まれている記事">一緒に読まれている記事</span>
        </h2>
        <div class="l-card-grid">
            <?php
            // グローバル変数を宣言
            global $post;

            foreach ( $related_posts as $item ) :
              // 1. IDから記事オブジェクトを取得して $post にセット
              $post = get_post($item['id']);

              // 2. テンプレートタグ(the_permalink等)がこの記事を向くようにセットアップ
              setup_postdata($post);

              // 3. カードテンプレートを呼び出し
              get_template_part('templates/parts/c-card');

            endforeach;

            // 4. 重要: メインページのデータに戻す
            wp_reset_postdata();
            ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Share -->
    <section class="l-section l-section--share">
        <h2 class="c-section-title">Share</h2>
        <ul class="c-share-list">
          <li class="c-share-item url"><a href="#" class="js-copy-url" aria-label="リンクURLをコピー"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_link.svg" alt="URLをコピー"><span class="c-share-text">URLをコピー</span></a></li>
          <li class="c-share-item line"><a href="https://line.me/R/share?text=<?php echo rawurlencode( get_the_title() . "\n" . get_permalink() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="LINEでシェア"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_line.svg" alt="LINE"><span class="c-share-text">LINE</span></a></li>
          <li class="c-share-item x"><a href="https://x.com/intent/tweet?url=<?php echo rawurlencode( get_permalink() ); ?>&text=<?php echo rawurlencode( get_the_title() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Xでシェア"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_x.svg" alt="X"><span class="c-share-text">X</span></a></li>
        </ul>
    </section>

  </div>
</div>


<!-- Prev/Next -->
<?php
// 同じ投稿タイプ内で前後に移動（通常投稿/カスタム投稿どちらでもOK）
$prev_post = get_previous_post();
$next_post = get_next_post();

// アーカイブURL（投稿タイプに応じて自動）
$post_type   = get_post_type();
$archive_url = get_post_type_archive_link($post_type);
?>

<nav class="c-post-nav" aria-label="前後の記事ナビゲーション">
  <div class="c-post-nav__inner">

    <!-- Prev -->
    <div class="c-post-nav__item c-post-nav__item--prev">
      <?php if ( $prev_post ) : ?>
        <a class="c-post-nav__link" href="<?php echo esc_url( get_permalink($prev_post) ); ?>">
          <span class="c-post-nav__arrow" aria-hidden="true">«</span>
          <span class="c-post-nav__label">Prev</span>
        </a>
      <?php endif; ?>
    </div>

    <!-- Center (Grid / Archive) -->
    <div class="c-post-nav__item c-post-nav__item--center">
      <?php if ( $archive_url ) : ?>
        <a class="c-post-nav__grid" href="<?php echo esc_url($archive_url); ?>" aria-label="一覧へ戻る">
          <span class="c-post-nav__dots" aria-hidden="true">
            <i></i><i></i><i></i>
            <i></i><i></i><i></i>
            <i></i><i></i><i></i>
          </span>
        </a>
      <?php endif; ?>
    </div>

    <!-- Next -->
    <div class="c-post-nav__item c-post-nav__item--next">
      <?php if ( $next_post ) : ?>
        <a class="c-post-nav__link" href="<?php echo esc_url( get_permalink($next_post) ); ?>">
          <span class="c-post-nav__label">Next</span>
          <span class="c-post-nav__arrow" aria-hidden="true">»</span>
        </a>
      <?php endif; ?>
    </div>

  </div>
</nav>

<?php
// ----------------------------------------------------------------
// 事前準備：関連記事があるかチェック
// ----------------------------------------------------------------
$taxonomy_slug   = 'blog-category'; // タクソノミー設定
$current_post_id = get_the_ID();
$current_terms   = get_the_terms( $current_post_id, $taxonomy_slug );
$related_query   = false; // 初期化

if ( $current_terms && ! is_wp_error( $current_terms ) ) {
    $term_ids = array();
    foreach ( $current_terms as $term ) {
        $term_ids[] = $term->term_id;
    }

    $args = array(
        'post_type'      => get_post_type(),
        'posts_per_page' => 8,
        'post__not_in'   => array( $current_post_id ), // 現在の記事を除外
        'orderby'        => 'date',
        'order'          => 'DESC',
        'tax_query'      => array(
            array(
                'taxonomy' => $taxonomy_slug,
                'field'    => 'term_id',
                'terms'    => $term_ids,
            ),
        ),
    );

    $related_query = new WP_Query( $args );
}

// ----------------------------------------------------------------
// 記事がある場合のみセクションごと出力
// ----------------------------------------------------------------
if ( $related_query && $related_query->have_posts() ) : ?>

  <!-- Related -->
  <section class="l-section l-section--related">
    <h2 class="c-section-title">
      <span class="c-section-title-main" data-text="Related CSS">Related CSS</span>
      <span class="c-section-title-sub" data-text="関連記事">関連記事</span>
    </h2>

    <div class="l-card-grid">
      <?php while ( $related_query->have_posts() ) : $related_query->the_post(); ?>
          <?php get_template_part('templates/parts/c-card'); ?>
      <?php endwhile; ?>
    </div>
  </section>

<?php
    // 投稿データをリセット
    wp_reset_postdata();
endif;
?>

<?php
get_footer();