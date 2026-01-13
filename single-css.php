<?php
/**
 * @package WordPress
 * @subpackage notebook Original Theme
 */

$kumonosu = get_kumonosu_field();
$container_id = 'js-kumonosu-preview';

/**
 * =========================================================
 * ?preview（値なし）のときだけプレビュー専用テンプレを返す
 *  - 直アクセス（document）→ 404
 *  - iframe埋め込み（iframe）→ 誰でもOK
 *  - no-store（キャッシュ禁止）
 * =========================================================
 */
$is_preview = array_key_exists('preview', $_GET) && $_GET['preview'] === '';

if ( $is_preview ) {

  // ---------------------------------------------------------
  // ① 「iframeとしての読み込み」だけ許可する（直アクセスは拒否）
  // ---------------------------------------------------------
  $sec_fetch_dest = isset($_SERVER['HTTP_SEC_FETCH_DEST'])
    ? strtolower((string) $_SERVER['HTTP_SEC_FETCH_DEST'])
    : '';

  $sec_fetch_site = isset($_SERVER['HTTP_SEC_FETCH_SITE'])
    ? strtolower((string) $_SERVER['HTTP_SEC_FETCH_SITE'])
    : '';

  $referer = isset($_SERVER['HTTP_REFERER'])
    ? (string) $_SERVER['HTTP_REFERER']
    : '';

  // iframe判定（Sec-Fetch-Dest が一番強い）
  $is_iframe_request = ($sec_fetch_dest === 'iframe');

  // 一部環境で Sec-Fetch-* が無い場合の保険として Referer を使う
  //（同一サイトからの遷移っぽい時だけ許可）
  $home = home_url('/');
  $is_same_site_referer = ($referer !== '' && strpos($referer, $home) === 0);

  // 他サイトに埋め込まれるのを防ぐ（同一オリジン/同一サイトのみ許可）
  $is_same_site_fetch = in_array($sec_fetch_site, ['same-origin', 'same-site'], true);

  // 許可条件：
  // - 通常は「iframe読み込み」ならOK
  // - Sec-Fetch が無い環境では「同一サイトReferer」ならOK
  // - かつ、他サイト埋め込みっぽい場合は弾く
  $allowed = false;

  if ( $is_iframe_request ) {
    // Sec-Fetch-Site が取れるなら同一サイトだけ許可
    $allowed = ($sec_fetch_site === '' || $is_same_site_fetch);
  } else {
    // 直アクセス（document等）は拒否。ただし保険条件のみ許可
    $allowed = ($sec_fetch_dest === '' && $is_same_site_referer);
  }

  if ( ! $allowed ) {
    status_header(404);
    exit;
  }

  // ---------------------------------------------------------
  // ② no-store（キャッシュ禁止）
  // ---------------------------------------------------------
  nocache_headers();
  if ( ! headers_sent() ) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
  }

  // ---------------------------------------------------------
  // ③ 他サイトへの埋め込みを明示的に禁止（同一オリジンのみ）
  // ---------------------------------------------------------
  if ( ! headers_sent() ) {
    header("Content-Security-Policy: frame-ancestors 'self'");
  }

  // プレビュー専用テンプレ（header/footer等なし）
  $preview_tpl = get_template_directory() . '/single-css-preview.php';
  if ( file_exists($preview_tpl) ) {
    include $preview_tpl;
    exit;
  }

  status_header(404);
  exit;
}

/**
 * =========================================================
 * iframe permissions（チェックボックス）→ allow / sandbox に変換
 * =========================================================
 */

// 1) まずはチェックボックス保存の permissions を優先
$iframe_permissions = [];
if ( ! empty($kumonosu['permissions']) && is_array($kumonosu['permissions']) ) {
  $iframe_permissions = $kumonosu['permissions'];
}

// 2) フォールバック：旧Config JSON に permissions が書いてある場合も拾う（任意）
if ( empty($iframe_permissions) && ! empty($kumonosu['code_config']) ) {
  $cfg = json_decode( (string) $kumonosu['code_config'], true );
  if ( is_array($cfg) && ! empty($cfg['permissions']) && is_array($cfg['permissions']) ) {
    $iframe_permissions = $cfg['permissions'];
  }
}

// 3) allow="..." 用ホワイトリスト
// ※ fullscreen は sandbox では制御しない（無効フラグなので入れない）
// ※ iframeは allowfullscreen を付けているので allow には常に fullscreen を入れる
$allow_map = [
  'camera'     => 'camera',
  'microphone' => 'microphone',
  'autoplay'   => 'autoplay',
  'clipboard'  => 'clipboard-write',
];

$allow_tokens = [];
foreach ($iframe_permissions as $perm) {
  $perm = strtolower(trim((string)$perm));
  if (isset($allow_map[$perm])) {
    $allow_tokens[] = $allow_map[$perm];
  }
}

// ✅ iframeは常にフルスクリーン可能にする（方針どおり）
$allow_tokens[] = 'fullscreen';

$allow_tokens = array_values(array_unique($allow_tokens));
$iframe_allow_value = implode('; ', $allow_tokens);

// 4) sandbox を「必要最小限」にする（fullscreen は sandbox では制御しない）
$needs_same_origin = in_array('camera', $iframe_permissions, true) || in_array('microphone', $iframe_permissions, true);

$sandbox_tokens = ['allow-scripts'];

// camera/microphone が必要な時だけ allow-same-origin
if ($needs_same_origin) {
  $sandbox_tokens[] = 'allow-same-origin';
}

// alert() 等（必要な時だけ）
if (in_array('modals', $iframe_permissions, true)) {
  $sandbox_tokens[] = 'allow-modals';
}

$sandbox_tokens = array_values(array_unique($sandbox_tokens));

get_header();
?>

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
        $terms = get_the_terms(get_the_ID(), 'css-category');

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
      $terms = get_the_terms(get_the_ID(), 'css-tag');
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

    <!-- Intro -->
    <section class="l-section l-section--intro">
      <?php if ( ! empty( $kumonosu['intro'] ) ) : ?>
      <p class="c-intro-content"><?php echo nl2br( esc_html( $kumonosu['intro'] ) ); ?></p>
      <?php endif; ?>
    </section>

    <!-- Toc -->
    <section class="l-section l-section--toc">
      <div class="c-toc-inner">
        <h3 class="c-toc-title">目次</h3>
        <?php echo '[PAGE_TOC]'; ?>
      </div>
    </section>

    <!-- Preview -->
    <section class="l-section l-section--preview">
      <h2 class="c-section-title">
        <span class="c-section-title-main" data-text="Preview">Preview</span>
        <span class="c-section-title-sub" data-text="プレビュー">プレビュー</span>
      </h2>

      <?php
      // /css/8/?preview （値なし）を作る（パーマリンクを崩さない）
      $preview_url = add_query_arg('preview', '', get_permalink(get_the_ID()));
      ?>

      <div class="c-preview-frame" id="js-kumonosu-preview-frame">
        <button
          type="button"
          class="c-preview-fs"
          id="js-kumonosu-preview-fullscreen"
          aria-label="Toggle fullscreen"
        >
          <!-- 全画面アイコン -->
          <svg class="icon-enter" height="24" viewBox="0 0 24 24" width="24" aria-hidden="true">
            <path d="M10 3H3V10C3 10.26 3.10 10.51 3.29 10.70C3.48 10.89 3.73 11 4 11C4.26 11 4.51 10.89 4.70 10.70C4.89 10.51 5 10.26 5 10V6.41L9.29 10.70L9.36 10.77C9.56 10.92 9.80 11.00 10.04 10.99C10.29 10.98 10.52 10.87 10.70 10.70C10.87 10.52 10.98 10.29 10.99 10.04C11.00 9.80 10.92 9.56 10.77 9.36L10.70 9.29L6.41 5H10C10.26 5 10.51 4.89 10.70 4.70C10.89 4.51 11 4.26 11 4C11 3.73 10.89 3.48 10.70 3.29C10.51 3.10 10.26 3 10 3ZM20 13C19.73 13 19.48 13.10 19.29 13.29C19.10 13.48 19 13.73 19 14V17.58L14.70 13.29L14.63 13.22C14.43 13.07 14.19 12.99 13.95 13.00C13.70 13.01 13.47 13.12 13.29 13.29C13.12 13.47 13.01 13.70 13.00 13.95C12.99 14.19 13.07 14.43 13.22 14.63L13.29 14.70L17.58 19H14C13.73 19 13.48 19.10 13.29 19.29C13.10 19.48 13 19.73 13 20C13 20.26 13.10 20.51 13.29 20.70C13.48 20.89 13.73 21 14 21H21V14C21 13.73 20.89 13.48 20.70 13.29C20.51 13.10 20.26 13 20 13Z"
              fill="white"></path>
          </svg>

          <!-- 縮小アイコン -->
          <svg class="icon-exit" height="24" viewBox="0 0 24 24" width="24" aria-hidden="true">
            <path d="M3.29 3.29C3.11 3.46 3.01 3.70 3.00 3.94C2.98 4.19 3.06 4.43 3.22 4.63L3.29 4.70L7.58 8.99H5C4.73 8.99 4.48 9.10 4.29 9.29C4.10 9.47 4 9.73 4 9.99C4 10.26 4.10 10.51 4.29 10.70C4.48 10.89 4.73 10.99 5 10.99H11V4.99C11 4.73 10.89 4.47 10.70 4.29C10.51 4.10 10.26 3.99 10 3.99C9.73 3.99 9.48 4.10 9.29 4.29C9.10 4.47 9 4.73 9 4.99V7.58L4.70 3.29L4.63 3.22C4.43 3.06 4.19 2.98 3.94 3.00C3.70 3.01 3.46 3.11 3.29 3.29ZM19 13H13V19C13 19.26 13.10 19.51 13.29 19.70C13.48 19.89 13.73 20 14 20C14.26 20 14.51 19.89 14.70 19.70C14.89 19.51 15 19.26 15 19V16.41L19.29 20.70L19.36 20.77C19.56 20.92 19.80 21.00 20.04 20.99C20.29 20.98 20.52 20.87 20.70 20.70C20.87 20.52 20.98 20.29 20.99 20.04C21.00 19.80 20.92 19.56 20.77 19.36L20.70 19.29L16.41 15H19C19.26 15 19.51 14.89 19.70 14.70C19.89 14.51 20 14.26 20 14C20 13.73 19.89 13.48 19.70 13.29C19.51 13.10 19.26 13 19 13Z"
              fill="white"></path>
          </svg>
        </button>

        <iframe
          id="js-kumonosu-preview-iframe"
          class="c-preview-iframe"
          sandbox="<?php echo esc_attr(implode(' ', $sandbox_tokens)); ?>"
          allow="<?php echo esc_attr($iframe_allow_value); ?>"
          allowfullscreen
          src="<?php echo esc_url($preview_url); ?>"
          loading="lazy"
          referrerpolicy="same-origin"
          title="<?php echo esc_attr( get_the_title() . ' preview' ); ?>"
        ></iframe>
      </div>
    </section>

    <script>
    (function(){
      const frame = document.getElementById('js-kumonosu-preview-frame');
      const btn   = document.getElementById('js-kumonosu-preview-fullscreen');

      if (!frame || !btn) return;

      btn.addEventListener('click', async () => {
        try {
          if (document.fullscreenElement) {
            await document.exitFullscreen();
          } else {
            await frame.requestFullscreen();
          }
        } catch (e) {
          console.warn('Fullscreen toggle failed:', e);
        }
      });
    })();
    </script>

    <!-- Code -->
    <section class="l-section l-section--code">
      <h2 class="c-section-title">
        <span class="c-section-title-main" data-text="Code">Code</span>
        <span class="c-section-title-sub" data-text="コード">コード</span>
      </h2>
      <div class="c-code">
        <div class="c-preview-tab">
          <!-- HTML -->
          <?php if ( ! empty( $kumonosu['code_html'] ) ) : ?>
          <label>
            <input type="radio" name="preview-tab" checked>
            HTML
          </label>
          <div class="c-code-panel" id="panel-html">
            <button type="button" class="c-code-copy" data-copy-target="#code-html" aria-label="HTMLをコピー">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_copy.svg" alt="HTMLをコピー">
            </button>
            <pre class="c-code-code" id="code-html"><code class="language-html"><?php echo esc_html( $kumonosu['code_html'] ); ?></code></pre>
          </div>
          <?php endif; ?>

          <!-- CSS -->
          <?php if ( ! empty( $kumonosu['code_css'] ) ) : ?>
          <label>
              <input type="radio" name="preview-tab">
              CSS
          </label>
          <div class="c-code-panel" id="panel-css">
            <button type="button" class="c-code-copy" data-copy-target="#code-css" aria-label="CSSをコピー">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_copy.svg" alt="CSSをコピー">
            </button>
            <pre class="c-code-code" id="code-css"><code class="language-css"><?php echo esc_html( $kumonosu['code_css'] ); ?></code></pre>
          </div>
          <?php endif; ?>

          <!-- JavaScript -->
          <?php if ( ! empty( $kumonosu['code_js'] ) ) : ?>
          <label>
              <input type="radio" name="preview-tab">
              JavaScript
          </label>
          <div class="c-code-panel" id="panel-js">
            <button type="button" class="c-code-copy" data-copy-target="#code-js" aria-label="CSSをコピー">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_copy.svg" alt="CSSをコピー">
            </button>
            <div class="c-code-box">
              <pre class="c-code-code" id="code-js"><code class="language-javascript"><?php echo esc_html( $kumonosu['code_js'] ); ?></code></pre>
            </div>
          </div>
          <?php endif; ?>

          <!-- Config (JSON) -->
          <?php if ( ! empty( $kumonosu['code_config'] ) ) : ?>
          <label>
              <input type="radio" name="preview-tab">
              Config
          </label>
          <div class="c-code-panel" id="panel-config">
            <button type="button" class="c-code-copy" data-copy-target="#code-config" aria-label="CSSをコピー">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_copy.svg" alt="CSSをコピー">
            </button>
            <pre class="c-code-code" id="code-config"><code class="language-json"><?php echo esc_html( $kumonosu['code_config'] ); ?></code></pre>
          </div>
          <?php endif; ?>

          <!-- 外部CSS -->
          <?php if ( ! empty( $kumonosu['ext_css'] ) ) : ?>
          <label>
              <input type="radio" name="preview-tab">
              外部CSS
          </label>
          <div class="c-code-panel" id="panel-extcss">
            <button type="button" class="c-code-copy" data-copy-target="#code-extcss" aria-label="CSSをコピー">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_copy.svg" alt="CSSをコピー">
            </button>
            <pre class="c-code-code" id="code-extcss"><code class="language-json"><?php echo esc_html( $kumonosu['ext_css'] ); ?></code></pre>
          </div>
          <?php endif; ?>

          <!-- 外部CSS -->
          <?php if ( ! empty( $kumonosu['ext_js'] ) ) : ?>
          <label>
              <input type="radio" name="preview-tab">
              外部JS
          </label>
          <div class="c-code-panel" id="panel-extjs">
            <button type="button" class="c-code-copy" data-copy-target="#code-extjs" aria-label="CSSをコピー">
              <img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_copy.svg" alt="CSSをコピー">
            </button>
            <pre class="c-code-code" id="code-extjs"><code class="language-json"><?php echo esc_html( $kumonosu['ext_js'] ); ?></code></pre>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Explanation -->
    <section class="l-section l-section--explanation">
      <h2 class="c-section-title">
        <span class="c-section-title-main" data-text="Explanation">Explanation</span>
        <span class="c-section-title-sub" data-text="詳しい説明">詳しい説明</span>
      </h2>

      <div class="c-explanation-content">
        <?php the_content(); ?>
      </div>
    </section>

    <!-- Freetext -->
    <?php
    // データの取得（すでに取得済みの場合は不要です）
    $kumo_data = get_kumonosu_page_data();

    // フリーテキストがある場合のみループ処理
    if ( ! empty( $kumo_data['free_texts'] ) ) :
        foreach ( $kumo_data['free_texts'] as $item ) :
            // 変数に代入（存在しない場合の空文字対策も含む）
            $title_en = isset( $item['title_en'] ) ? $item['title_en'] : '';
            $title_jp = isset( $item['title_jp'] ) ? $item['title_jp'] : '';
            $body     = isset( $item['body'] )     ? $item['body']     : '';
            ?>

            <section class="l-section l-section--explanation">
                <h2 class="c-section-title">
                    <!-- data-text属性にも英語タイトルを入れる必要があります -->
                    <span class="c-section-title-main" data-text="<?php echo esc_attr( $title_en ); ?>">
                      <?php echo esc_html( $title_en ); ?>
                    </span>
                    <span class="c-section-title-sub" data-text="<?php echo esc_html( $title_jp ); ?>">
                      <?php echo esc_html( $title_jp ); ?>
                    </span>
                </h2>

                <div class="c-explanation-content">
                    <?php
                    // 本文の改行コードを <br> タグに変換して表示
                    echo nl2br( esc_html( $body ) );
                    ?>
                </div>
            </section>

        <?php
        endforeach;
    endif;
    ?>

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
        <h2 class="c-section-title">
          <span class="c-section-title-main" data-text="Share">Share</span>
        </h2>
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
$taxonomy_slug   = 'css-category'; // タクソノミー設定
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
  <section class="l-section l-section--related js-scroll-trigger">
    <h2 class="c-section-title">
      <span class="c-section-title-main" data-text="Related CSS">Related</span>
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