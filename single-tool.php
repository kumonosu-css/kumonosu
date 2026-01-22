<?php
/**
 * Single Tool Template
 * ヘッダー：非追従 / 左にタイトル / 右にABOUTボタン / 重なり回避
 * フッター：コピーライト追加
 */

if ( ! have_posts() ) {
    status_header(404);
    exit;
}
the_post();

/**
 * 1. WordPressの標準機能を徹底的にオフにする
 */
function kumonosu_super_clean_up() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'rest_output_link_wp_head');
    remove_action('wp_head', 'wp_shortlink_wp_head');

    add_action('wp_enqueue_scripts', function() {
        wp_dequeue_script('kumonosu-main');
        wp_dequeue_script('kumonosu-main-js');
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('global-styles');
        wp_dequeue_style('classic-theme-styles');
        wp_dequeue_style('dashicons');

        if ( ! is_admin_bar_showing() ) {
            wp_deregister_script('jquery');
        }
    }, 9999);
}
kumonosu_super_clean_up();

/**
 * 2. ツールHTMLの抽出と振り分け関数
 */
function kumonosu_extract_tool_parts( $full_html ) {
  $parts = [
    'head_css'   => '',
    'footer_js'  => '',
    'body_html'  => '',
    'body_attrs' => '',
  ];
  if ( empty( $full_html ) ) return $parts;

  $html = (string) $full_html;

  if ( preg_match('/<body\b([^>]*)>/i', $html, $m) ) {
    $parts['body_attrs'] = trim($m[1]);
  }

  $head_inner = '';
  if ( preg_match('/<head\b[^>]*>(.*?)<\/head>/is', $html, $m) ) {
    $head_inner = $m[1];
  }

  $body_inner = '';
  if ( preg_match('/<body\b[^>]*>(.*?)<\/body>/is', $html, $m) ) {
    $body_inner = $m[1];
  } else {
    $body_inner = $html;
  }

  if ( preg_match_all('/<style\b[^>]*>.*?<\/style>/is', $head_inner, $mm) ) {
    $parts['head_css'] .= implode("\n", $mm[0]) . "\n";
    $head_inner = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $head_inner);
  }

  if ( preg_match_all('/<link\b[^>]*rel=("|\')?stylesheet\1[^>]*>/is', $head_inner, $mm) ) {
    $parts['head_css'] .= implode("\n", $mm[0]) . "\n";
    $head_inner = preg_replace('/<link\b[^>]*rel=("|\')?stylesheet\1[^>]*>/is', '', $head_inner);
  }

  if ( preg_match_all('/<script\b[^>]*>.*?<\/script>/is', $head_inner, $mm) ) {
    $parts['footer_js'] .= implode("\n", $mm[0]) . "\n";
    $head_inner = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $head_inner);
  }

  if ( preg_match_all('/<script\b[^>]*>.*?<\/script>/is', $body_inner, $mm) ) {
    $parts['footer_js'] .= implode("\n", $mm[0]) . "\n";
    $body_inner = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $body_inner);
  }

  $parts['body_html'] = $body_inner;
  return $parts;
}

$full_html_meta = get_post_meta( get_the_ID(), '_kumonosu_tool_full_html', true );
$parts = kumonosu_extract_tool_parts( $full_html_meta );

if ( ! function_exists( 'get_kumonosu_description' ) ) {
    function get_kumonosu_description() { return get_the_excerpt(); }
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
  <meta charset="utf-8">
  <title><?php echo esc_html( get_the_title() ); ?>【KUMONOSU】</title>

  <?php
    $desc = get_kumonosu_description();
    if ( ! empty( $desc ) ) echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags($desc) ) . '">' . "\n";
  ?>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="canonical" href="<?php echo (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]; ?>" />

  <?php if ( locate_template('templates/parts/c-meta_ogp.php') ) get_template_part( 'templates/parts/c-meta_ogp' ); ?>
  <?php if ( locate_template('templates/parts/c-meta_schema.php') ) get_template_part( 'templates/parts/c-meta_schema' ); ?>

  <?php wp_head(); ?>

  <style>
    /* 1. 共通グローバルヘッダー */
    #global-tool-header {
        height: 40px;
        background: #0d0e14;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        position: relative;
        z-index: 1000;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .global-tool-title {
        font-size: 11px;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.5);
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    .about-btn {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: rgba(255, 255, 255, 0.7);
        font-size: 10px;
        font-weight: 700;
        padding: 4px 14px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        letter-spacing: 0.1em;
    }
    .about-btn:hover {
        background: rgba(59, 130, 246, 0.2);
        border-color: #3B82F6;
        color: white;
    }

    /* 2. 被り防止ロジック */
    #tool-main .fixed.top-0,
    #tool-main header.fixed,
    #tool-main nav.fixed {
        position: sticky !important;
        top: 0 !important;
        z-index: 999;
    }

    /* 3. ポップアップ */
    #about-popup {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1000000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    #about-popup .overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.9);
        backdrop-filter: blur(10px);
    }
    #about-popup .content {
        position: relative;
        background: #0F1117;
        border: 1px solid rgba(255, 255, 255, 0.1);
        width: 100%;
        max-width: 720px;
        max-height: 85vh;
        border-radius: 28px;
        overflow-y: auto;
        padding: 50px;
        box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.7);
    }
    .popup-close {
        position: absolute;
        top: 25px;
        right: 25px;
        color: rgba(255, 255, 255, 0.3);
        cursor: pointer;
        font-size: 24px;
    }
    .seo-text-area { line-height: 1.8; color: rgba(255, 255, 255, 0.7); }
    .seo-text-area h2 { font-size: 20px; color: #fff; margin: 2em 0 1em; border-left: 4px solid #3B82F6; padding-left: 15px; }

    /* 4. フッタースタイル */
    footer {
        padding: 5px 20px;
        background: #0d0e14;
        text-align: center;
    }
    .c-footer-copyright {
        font-size: 11px;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.3);
        letter-spacing: 0.1em;
        margin: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .admin-bar #global-tool-header { margin-top: 0; }
  </style>

  <?php echo $parts['head_css']; ?>
</head>

<body <?php echo $parts['body_attrs']; ?>>
  <?php wp_body_open(); ?>

  <!-- 1. 共通グローバルヘッダー -->
  <header id="global-tool-header">
      <h1 class="global-tool-title"><?php echo esc_html( get_the_title() ); ?></h1>
      <button class="about-btn" onclick="openAbout()">ABOUT</button>
  </header>

  <!-- 2. ポップアップ -->
  <div id="about-popup">
      <div class="overlay" onclick="closeAbout()"></div>
      <div class="content">
          <span class="popup-close material-icons-round" onclick="closeAbout()">close</span>
          <div class="seo-text-area">
              <div style="font-size: 11px; font-weight: bold; color: #3B82F6; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.2em;">Information</div>
              <?php the_content(); ?>
          </div>
      </div>
  </div>

  <main id="tool-main">
    <?php echo $parts['body_html']; ?>
  </main>

  <!-- 3. フッター -->
  <footer>
      <p class="c-footer-copyright">© KUMONOSU</p>
  </footer>

  <script>
    function openAbout() {
        document.getElementById('about-popup').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeAbout() {
        document.getElementById('about-popup').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAbout();
    });
  </script>

  <?php echo $parts['footer_js']; ?>
  <?php wp_footer(); ?>
</body>
</html>