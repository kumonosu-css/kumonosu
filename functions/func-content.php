<?php

// iframeのレスポンシブ対応
function wrap_iframe_in_div($the_content) {
  if ( is_singular() ) {
    // YouTube
    $the_content = preg_replace('/<iframe[^>]+?youtube\.com[^<]+?<\/iframe>/is', '<div class="video-container"><div class="video">${0}</div></div>', $the_content);
    // Instagram
    $the_content = preg_replace('/<iframe[^>]+?instagram\.com[^<]+?<\/iframe>/is', '<div class="instagram-container"><div class="instagram">${0}</div></div>', $the_content);
  }
  return $the_content;
}
add_filter('the_content','wrap_iframe_in_div');

/* =========================================================
  目次
========================================================= */
/**
 * css / blog のみ：出力HTMLをバッファして [PAGE_TOC] を置換
 * - .c-section-title-main をTOC対象（Previewだけ）
 * - 本文内の h2 もTOC対象
 * - ただし <h2 class="c-section-title"> は除外（Preview プレビュー問題対策）
 */

$GLOBALS['kumonosu_toc_buffer_started'] = false;

add_action('wp', function () {
  if ( is_admin() || is_feed() || is_preview() ) return;

  // 目次を使う投稿タイプだけ
  if ( ! is_singular( array('css', 'blog') ) ) return;

  // 二重起動防止
  if ( ! empty($GLOBALS['kumonosu_toc_buffer_started']) ) return;

  $GLOBALS['kumonosu_toc_buffer_started'] = true;
  ob_start('kumonosu_toc_process_html');
}, 0);

// 念のため：最後にバッファを確実に吐く
add_action('shutdown', function () {
  if ( empty($GLOBALS['kumonosu_toc_buffer_started']) ) return;

  while ( ob_get_level() > 0 ) {
    @ob_end_flush();
  }
}, 0);


function kumonosu_toc_process_html( $html ) {
  $placeholder = '[PAGE_TOC]';

  // 置き場所が無いなら何もしない
  if ( strpos($html, $placeholder) === false ) return $html;

  $toc_items = [];
  $i = 1;

  // span.c-section-title-main と、h2（ただし h2.c-section-title は除外）
  $pattern = '/
    (?:
      <span\b(?P<span_attrs>[^>]*\bclass=(?:"|\')[^"\']*\bc-section-title-sub\b[^"\']*(?:"|\')[^>]*)>
        (?P<span_inner>.*?)
      <\/span>
    )
    |
    (?:
      <h2\b
        (?P<h2_attrs>(?![^>]*\bclass=(?:"|\')[^"\']*\bc-section-title\b)[^>]*)
      >
        (?P<h2_inner>.*?)
      <\/h2>
    )
  /isx';

  $html = preg_replace_callback($pattern, function($m) use (&$i, &$toc_items) {

    // --- span.c-section-title-main（Previewだけ拾う） ---
    if ( isset($m['span_inner']) && $m['span_inner'] !== '' ) {
      $attrs = $m['span_attrs'];
      $inner = $m['span_inner'];
      $text  = trim( wp_strip_all_tags($inner) );
      if ( $text === '' ) return $m[0];

      $id = 'heading-' . $i;

      // id が無ければ付与
      if ( ! preg_match('/\bid=("|\')[^"\']+\1/i', $attrs) ) {
        $out = '<span id="' . esc_attr($id) . '"' . $attrs . '>' . $inner . '</span>';
      } else {
        $out = $m[0];
      }

      $toc_items[] = ['id' => $id, 'text' => $text];
      $i++;
      return $out;
    }

    // --- 本文の h2（h2.c-section-title は除外済み） ---
    if ( isset($m['h2_inner']) && $m['h2_inner'] !== '' ) {
      $attrs = $m['h2_attrs'];
      $inner = $m['h2_inner'];
      $text  = trim( wp_strip_all_tags($inner) );
      if ( $text === '' ) return $m[0];

      $id = 'heading-' . $i;

      if ( ! preg_match('/\bid=("|\')[^"\']+\1/i', $attrs) ) {
        $out = '<h2 id="' . esc_attr($id) . '"' . $attrs . '>' . $inner . '</h2>';
      } else {
        $out = $m[0];
      }

      $toc_items[] = ['id' => $id, 'text' => $text];
      $i++;
      return $out;
    }

    return $m[0];
  }, $html);

  // TOCが空ならプレースホルダーだけ消す
  if ( empty($toc_items) ) {
    return str_replace($placeholder, '', $html);
  }

  // TOC生成
  $toc = '<ol class="c-toc-list">';
  foreach ( $toc_items as $item ) {
    $toc .= '<li><a href="#' . esc_attr($item['id']) . '">' . esc_html($item['text']) . '</a></li>';
  }
  $toc .= '</ol>';

  // 差し込み
  return str_replace($placeholder, $toc, $html);
}
add_action('wp_footer', function() {
  if ( ! is_singular( array('css', 'blog') ) ) return;
  ?>
  <script>
  (function(){
    document.addEventListener('DOMContentLoaded', function() {
      // 監視対象のリンクを取得
      const tocLinks = document.querySelectorAll('.c-aside-list a');
      if (tocLinks.length === 0) return;

      const targets = [];
      tocLinks.forEach(link => {
        const id = link.getAttribute('href').slice(1);
        const target = document.getElementById(id);
        if (target) targets.push(target);
      });

      const observerOptions = {
        // 画面上部20%〜70%の位置を見ていると判定
        rootMargin: '-20% 0px -70% 0px',
        threshold: 0
      };

      const observerCallback = (entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            // ★変更点1：すべての「親li」からクラスを外す
            tocLinks.forEach(l => l.parentElement.classList.remove('is-active'));
            
            const activeId = entry.target.id;
            const activeLink = document.querySelector(`.c-aside-list a[href="#${activeId}"]`);
            
            if (activeLink) {
              // ★変更点2：対象リンクの「親li」にクラスを付ける
              activeLink.parentElement.classList.add('is-active');
            }
          }
        });
      };

      const observer = new IntersectionObserver(observerCallback, observerOptions);
      targets.forEach(target => observer.observe(target));
    });
  })();
  </script>
  <?php
}, 99);


/* =========================================================
  お問い合わせ
========================================================= */
/* =========================================================
  2) admin-post で受信して保存（フォーム側 action=kumonosu_contact_submit）
========================================================= */
add_action('admin_post_nopriv_kumonosu_contact_submit', 'kumonosu_handle_contact_submit');
add_action('admin_post_kumonosu_contact_submit', 'kumonosu_handle_contact_submit');

function kumonosu_handle_contact_submit() {
  // nonce
  if (!isset($_POST['kumonosu_nonce']) || !wp_verify_nonce($_POST['kumonosu_nonce'], 'kumonosu_contact')) {
    wp_die('Invalid request.');
  }

  // honeypot
  if (!empty($_POST['company'])) {
    wp_die('Spam detected.');
  }

  $ip = kumonosu_get_ip_address();
  $ua = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';

  // レート制限（同IP：10分に3回）
  kumonosu_rate_limit_or_die($ip, 3, 10 * MINUTE_IN_SECONDS);

  // time trap（3秒未満送信は弾く）
  $started = isset($_POST['form_started']) ? (int) wp_unslash($_POST['form_started']) : 0;
  if ($started <= 0 || (time() - $started) < 3) {
    wp_die('Invalid request.');
  }

  // one-time token（二重送信/リプレイ防止）
  $token = isset($_POST['form_token']) ? sanitize_text_field(wp_unslash($_POST['form_token'])) : '';
  if ($token === '' || !preg_match('/^[a-f0-9-]{20,}$/i', $token)) {
    wp_die('Invalid request.');
  }
  kumonosu_one_time_token_or_die($token, 30 * MINUTE_IN_SECONDS);

  // 入力値
  $name    = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
  $email   = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
  $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';

  // reCAPTCHAの検証処理
  $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

  if (empty($recaptcha_response)) {
    // チェックが入っていない場合
    kumonosu_contact_back('「私はロボットではありません」にチェックを入れてください。');
  }

  if (!kumonosu_verify_recaptcha_v2($recaptcha_response)) {
    // 認証に失敗した場合（不正なリクエストなど）
    wp_die('reCAPTCHA認証に失敗しました。もう一度お試しください。');
  }

  // 必須
  if ($name === '' || $email === '' || $message === '') {
    kumonosu_contact_back('必須項目が未入力です。');
  }
  if (!is_email($email)) {
    kumonosu_contact_back('メールアドレスの形式が正しくありません。');
  }

  // 簡易スパム判定（URL過多/長文など）
  kumonosu_spam_guard_or_die($message);

  // 保存：publish（状態ラベルを出さないため）
  $post_id = wp_insert_post([
    'post_type'   => 'contact',
    'post_status' => 'publish',
    'post_title'  => $name, // 件名の代わりに名前
  ], true);

  if (is_wp_error($post_id)) {
    kumonosu_contact_back('保存に失敗しました。時間をおいて再度お試しください。');
  }

  // メタ保存
  update_post_meta($post_id, 'name', $name);
  update_post_meta($post_id, 'email', $email);
  update_post_meta($post_id, 'message', $message);
  update_post_meta($post_id, 'ip', $ip);
  update_post_meta($post_id, 'ua', $ua);

  // 未読＆対応ステータス（初期）
  update_post_meta($post_id, 'is_read', 0);                // 未読
  update_post_meta($post_id, 'contact_status', 'pending'); // 未対応

  // 完了ページへ
  wp_safe_redirect(home_url('/contact/thanks/'));
  exit;
}

function kumonosu_contact_back($err) {
  $url = add_query_arg(['err' => rawurlencode($err)], wp_get_referer() ? wp_get_referer() : home_url('/contact/'));
  wp_safe_redirect($url);
  exit;
}

function kumonosu_get_ip_address(): string {
  $keys = ['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'];
  foreach ($keys as $k) {
    if (!empty($_SERVER[$k])) {
      $ip = sanitize_text_field(wp_unslash($_SERVER[$k]));
      $ip = explode(',', $ip)[0];
      return trim($ip);
    }
  }
  return '';
}

function kumonosu_rate_limit_or_die(string $ip, int $limit, int $seconds): void {
  if ($ip === '') return;
  $key = 'kumonosu_contact_rl_' . md5($ip);
  $count = (int) get_transient($key);
  if ($count >= $limit) {
    wp_die('送信回数が多すぎます。時間をおいてお試しください。');
  }
  set_transient($key, $count + 1, $seconds);
}

function kumonosu_one_time_token_or_die(string $token, int $seconds): void {
  $key = 'kumonosu_contact_tok_' . md5($token);
  if (get_transient($key)) {
    wp_die('この送信は既に受け付けました。');
  }
  set_transient($key, 1, $seconds);
}

function kumonosu_spam_guard_or_die(string $message): void {
  if (mb_strlen($message) > 3000) {
    wp_die('内容が長すぎます。');
  }
  $url_hits = preg_match_all('/https?:\/\/|www\./i', $message, $m);
  if ($url_hits > 2) {
    wp_die('不正な送信です。');
  }
  // ★追加：日本語（ひらがな・カタカナ・漢字）が含まれているかチェック
  // 日本語が1文字も含まれていない（英数字や記号のみ）場合はエラーにする
  if (!preg_match('/[ぁ-んァ-ヶー一-龠]/u', $message)) {
    wp_die('エラー：お問い合わせ内容は日本語で入力してください（英数字のみの送信はスパム対策のため禁止しています）。');
  }
}
/**
 * Google reCAPTCHA v2 の検証（管理画面の保存値を使用）
 */
function kumonosu_verify_recaptcha_v2($token) {
  // 管理画面で保存したシークレットキーを取得
  $secret_key = get_option('kumonosu_recaptcha_secret_key');

  if (empty($secret_key)) {
    return true; // キーが未設定の場合は、検証をスルー（テスト用）
  }

  $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
    'body' => [
      'secret'   => $secret_key,
      'response' => $token,
      'remoteip' => kumonosu_get_ip_address(),
    ],
  ]);

  if (is_wp_error($response)) return false;

  $json = json_decode(wp_remote_retrieve_body($response));
  return (isset($json->success) && $json->success === true);
}

/* =========================================================
  4) 管理画面メニューに未読バッジ
========================================================= */
function contact_unread_count(): int {
  $q = new WP_Query([
    'post_type'      => 'contact',
    'post_status'    => 'publish',
    'posts_per_page' => 1,
    'fields'         => 'ids',
    'meta_query'     => [
      [
        'key'     => 'is_read',
        'value'   => 0,
        'compare' => '=',
        'type'    => 'NUMERIC',
      ],
    ],
  ]);
  return (int) $q->found_posts;
}

add_action('admin_menu', function () {
  $count = contact_unread_count();
  if ($count <= 0) return;

  global $menu;
  $slug = 'edit.php?post_type=contact';

  foreach ($menu as $i => $item) {
    if (!isset($item[2]) || $item[2] !== $slug) continue;
    $menu[$i][0] .= ' <span class="awaiting-mod">' . number_format_i18n($count) . '</span>';
    break;
  }
}, 999);

/* =========================================================
  5) 対応ステータス（未対応/対応済/対応不可）
========================================================= */
function contact_status_options(): array {
  return [
    'pending'  => '未対応',
    'done'     => '対応済',
    'rejected' => '対応不可',
  ];
}

/* 詳細画面：プルダウン（サイド） */
add_action('add_meta_boxes', function () {
  add_meta_box(
    'contact_status_box',
    '対応ステータス',
    'contact_status_metabox',
    'contact',
    'side',
    'high'
  );
});

function contact_status_metabox($post) {
  $opts = contact_status_options();
  $current = get_post_meta($post->ID, 'contact_status', true);
  if (!$current) $current = 'pending';

  wp_nonce_field('contact_status_save', 'contact_status_nonce');

  echo '<p style="margin-top:0;">対応状況を選択</p>';
  echo '<select name="contact_status" style="width:100%;">';
  foreach ($opts as $val => $label) {
    printf(
      '<option value="%s" %s>%s</option>',
      esc_attr($val),
      selected($current, $val, false),
      esc_html($label)
    );
  }
  echo '</select>';
}

/* 保存 */
add_action('save_post_contact', function ($post_id) {
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $post_id)) return;

  if (!isset($_POST['contact_status_nonce']) || !wp_verify_nonce($_POST['contact_status_nonce'], 'contact_status_save')) {
    return;
  }

  $allowed = array_keys(contact_status_options());
  $new = isset($_POST['contact_status']) ? sanitize_text_field(wp_unslash($_POST['contact_status'])) : 'pending';
  if (!in_array($new, $allowed, true)) $new = 'pending';

  update_post_meta($post_id, 'contact_status', $new);
});

/* 一覧カラム：状態（未読/既読）＆ 対応 */
add_filter('manage_contact_posts_columns', function ($cols) {
  $new = [];
  foreach ($cols as $k => $v) {
    $new[$k] = $v;
    if ($k === 'title') {
      $new['read_state'] = '未読';
      $new['contact_status'] = '対応';
    }
  }
  return $new;
});

add_action('manage_contact_posts_custom_column', function ($col, $post_id) {
  if ($col === 'read_state') {
    echo ((int) get_post_meta($post_id, 'is_read', true) === 0) ? '未読' : '既読';
    return;
  }

  if ($col === 'contact_status') {
    $opts = contact_status_options();
    $val = get_post_meta($post_id, 'contact_status', true);
    if (!$val) $val = 'pending';
    echo esc_html($opts[$val] ?? '未対応');
    return;
  }
}, 10, 2);

/* 一覧で絞り込み（対応：すべて/未対応/対応済/対応不可） */
add_action('restrict_manage_posts', function () {
  global $typenow;
  if ($typenow !== 'contact') return;

  $opts = contact_status_options();
  $current = isset($_GET['contact_status']) ? sanitize_text_field(wp_unslash($_GET['contact_status'])) : '';

  echo '<select name="contact_status">';
  echo '<option value="">すべての対応</option>';
  foreach ($opts as $val => $label) {
    printf(
      '<option value="%s" %s>%s</option>',
      esc_attr($val),
      selected($current, $val, false),
      esc_html($label)
    );
  }
  echo '</select>';
});

add_action('pre_get_posts', function ($q) {
  if (!is_admin() || !$q->is_main_query()) return;

  $pt = $q->get('post_type');
  if ($pt !== 'contact') return;

  if (!empty($_GET['contact_status'])) {
    $status = sanitize_text_field(wp_unslash($_GET['contact_status']));
    $allowed = array_keys(contact_status_options());
    if (!in_array($status, $allowed, true)) return;

    $meta = (array) $q->get('meta_query');

    // ★ここがポイント：未対応(pending)は「メタ未設定」も含める
    if ($status === 'pending') {
      $meta[] = [
        'relation' => 'OR',
        [
          'key'   => 'contact_status',
          'value' => 'pending',
        ],
        [
          'key'     => 'contact_status',
          'compare' => 'NOT EXISTS',
        ],
      ];
    } else {
      $meta[] = [
        'key'   => 'contact_status',
        'value' => $status,
      ];
    }

    $q->set('meta_query', $meta);
  }
});

/* =========================================================
  6) 「公開/非公開」などの状態ラベルを消す（一覧のタイトル横）
========================================================= */
add_filter('display_post_states', function ($states, $post) {
  if (is_admin() && $post->post_type === 'contact') {
    return []; // 状態ラベル非表示
  }
  return $states;
}, 10, 2);

/* 行アクション「表示」も不要なら消す */
add_filter('post_row_actions', function ($actions, $post) {
  if ($post->post_type === 'contact') {
    unset($actions['view']);
    // クイック編集も不要なら↓
    // unset($actions['inline hide-if-no-js']);
  }
  return $actions;
}, 10, 2);

add_action('admin_init', function () {
  if (!current_user_can('manage_options')) return;
  if (get_option('contact_status_backfilled')) return;

  $q = new WP_Query([
    'post_type'      => 'contact',
    'post_status'    => 'any',
    'posts_per_page' => -1,
    'fields'         => 'ids',
    'meta_query'     => [
      [
        'key'     => 'contact_status',
        'compare' => 'NOT EXISTS',
      ],
    ],
  ]);

  foreach ($q->posts as $id) {
    update_post_meta($id, 'contact_status', 'pending');
  }

  update_option('contact_status_backfilled', 1);
});
/**
 * reCAPTCHA設定メニューの追加
 */
add_action('admin_menu', function () {
  add_submenu_page(
    'edit.php?post_type=contact', // お問い合わせメニューの中に作成
    'reCAPTCHA設定',
    'reCAPTCHA設定',
    'manage_options',
    'kumonosu_recaptcha_settings',
    'kumonosu_recaptcha_settings_page'
  );
});

/**
 * 設定ページのHTML
 */
function kumonosu_recaptcha_settings_page() {
  ?>
  <div class="wrap">
    <h1>reCAPTCHA v2 設定</h1>
    <form method="post" action="options.php">
      <?php
      settings_fields('kumonosu_recaptcha_group');
      do_settings_sections('kumonosu_recaptcha_group');
      ?>
      <table class="form-table">
        <tr>
          <th scope="row"><label for="kumonosu_recaptcha_site_key">サイトキー</label></th>
          <td>
            <input name="kumonosu_recaptcha_site_key" type="text" id="kumonosu_recaptcha_site_key" value="<?php echo esc_attr(get_option('kumonosu_recaptcha_site_key')); ?>" class="regular-text">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="kumonosu_recaptcha_secret_key">シークレットキー</label></th>
          <td>
            <input name="kumonosu_recaptcha_secret_key" type="text" id="kumonosu_recaptcha_secret_key" value="<?php echo esc_attr(get_option('kumonosu_recaptcha_secret_key')); ?>" class="regular-text">
          </td>
        </tr>
      </table>
      <?php submit_button(); ?>
    </form>
  </div>
  <?php
}

/**
 * 設定値の登録
 */
add_action('admin_init', function () {
  register_setting('kumonosu_recaptcha_group', 'kumonosu_recaptcha_site_key');
  register_setting('kumonosu_recaptcha_group', 'kumonosu_recaptcha_secret_key');
});


/**
 * =========================================================
 * KUMONOSU PV (Daily + Total) / Sort / LoadMore (AJAX)
 *  - Daily PV をDBテーブルに保存（投稿別 + サイト合計）
 *  - Total PV は postmeta にも保存（人気順ソート高速化）
 *  - サイト合計（今日/累計）も日次で保存（全ページ対象）
 *  - 本日PV上位（css固定）を取得（リロードで即反映）
 *  - 並び替え・もっと見る（css/blog一覧）をAJAXで返す
 *  - 管理者ログイン時（manage_options）はPV除外
 * =========================================================
 */
function kumonosu_ensure_pv_table(): void {
  global $wpdb;

  $table = $wpdb->prefix . 'kumonosu_pv_daily';

  // すでにあれば何もしない
  $exists = $wpdb->get_var( $wpdb->prepare("SHOW TABLES LIKE %s", $table) );
  if ($exists === $table) return;

  // なければ作成
  $charset = $wpdb->get_charset_collate();
  require_once ABSPATH . 'wp-admin/includes/upgrade.php';

  $sql = "CREATE TABLE {$table} (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    ymd DATE NOT NULL,
    post_id BIGINT(20) UNSIGNED NOT NULL,
    views BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY ymd_post (ymd, post_id),
    KEY post_id (post_id),
    KEY ymd (ymd)
  ) {$charset};";

  dbDelta($sql);
}


/** ---------------------------------------
 * 0-1) PV用テーブル作成（テーマ切替時）
 * --------------------------------------*/
add_action('after_switch_theme', function () {
  global $wpdb;

  $table   = $wpdb->prefix . 'kumonosu_pv_daily';
  $charset = $wpdb->get_charset_collate();

  require_once ABSPATH . 'wp-admin/includes/upgrade.php';

  $sql = "CREATE TABLE {$table} (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    ymd DATE NOT NULL,
    post_id BIGINT(20) UNSIGNED NOT NULL,
    views BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY ymd_post (ymd, post_id),
    KEY post_id (post_id),
    KEY ymd (ymd)
  ) {$charset};";

  dbDelta($sql);
});


/** ---------------------------------------
 * 0-2) 共通：日付（WPタイムゾーン）
 * --------------------------------------*/
function kumonosu_today_ymd(): string {
  return wp_date('Y-m-d'); // WP設定のタイムゾーンに従う
}


/** ---------------------------------------
 * 0-3) サイト全体PV加算（1hit）
 *  - 日次テーブル(サイト合計 post_id=0) を +1
 * --------------------------------------*/
function kumonosu_site_pv_increment(): void {
  kumonosu_ensure_pv_table(); // ← 追加

  global $wpdb;
  $table = $wpdb->prefix . 'kumonosu_pv_daily';
  $ymd   = kumonosu_today_ymd();

  $wpdb->query(
    $wpdb->prepare(
      "INSERT INTO {$table} (ymd, post_id, views)
       VALUES (%s, 0, 1)
       ON DUPLICATE KEY UPDATE views = views + 1",
      $ymd
    )
  );
}


/** ---------------------------------------
 * 0-3) 記事PV加算（1hit）
 *  - 日次テーブル(投稿) + サイト合計
 *  - postmeta: _pv_total を加算（人気順用）
 * --------------------------------------*/
function kumonosu_pv_increment(int $post_id): void {
  if ($post_id <= 0) return;

  kumonosu_ensure_pv_table();

  global $wpdb;
  $table = $wpdb->prefix . 'kumonosu_pv_daily';
  $ymd   = kumonosu_today_ymd();

  // 投稿PV（日次）
  $wpdb->query(
    $wpdb->prepare(
      "INSERT INTO {$table} (ymd, post_id, views)
       VALUES (%s, %d, 1)
       ON DUPLICATE KEY UPDATE views = views + 1",
      $ymd, $post_id
    )
  );

  // サイト合計（日次）
  kumonosu_site_pv_increment();

  // 人気順のため total を postmeta でも加算
  $current = (int) get_post_meta($post_id, '_pv_total', true);
  update_post_meta($post_id, '_pv_total', $current + 1);
}


/** ---------------------------------------
 * 0-4) 今日のサイトPV / 累計サイトPV
 * --------------------------------------*/
function kumonosu_get_site_pv_today(): int {
  global $wpdb;
  $table = $wpdb->prefix . 'kumonosu_pv_daily';
  $ymd   = kumonosu_today_ymd();

  $v = $wpdb->get_var(
    $wpdb->prepare(
      "SELECT views FROM {$table} WHERE ymd=%s AND post_id=0 LIMIT 1",
      $ymd
    )
  );

  return (int) ($v ?? 0);
}

function kumonosu_get_site_pv_total(): int {
  global $wpdb;
  $table = $wpdb->prefix . 'kumonosu_pv_daily';

  $v = $wpdb->get_var("SELECT COALESCE(SUM(views),0) FROM {$table} WHERE post_id=0");
  return (int) ($v ?? 0);
}


/** ---------------------------------------
 * 0-5) 本日のPV上位N件（css固定）
 * --------------------------------------*/
function kumonosu_get_top_css_today(int $limit = 2): array {
  global $wpdb;
  $table = $wpdb->prefix . 'kumonosu_pv_daily';
  $ymd   = kumonosu_today_ymd();

  $rows = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT d.post_id, d.views
       FROM {$table} d
       INNER JOIN {$wpdb->posts} p ON p.ID = d.post_id
       WHERE d.ymd=%s
         AND p.post_type='css'
         AND p.post_status='publish'
       ORDER BY d.views DESC
       LIMIT %d",
      $ymd, $limit
    )
  );

  $ids = array_map(fn($r) => (int) $r->post_id, $rows ?: []);
  if (!$ids) return [];

  return get_posts([
    'post_type'      => 'css',
    'post__in'       => $ids,
    'orderby'        => 'post__in',
    'posts_per_page' => $limit,
  ]);
}


/** ---------------------------------------
 * 0-6) PV計測AJAX（キャッシュ対策）
 *  - post_id > 0 : 記事PV + サイト全体PV
 *  - post_id = 0 : サイト全体PVのみ（トップ/アーカイブ/固定ページ用）
 *  - 管理者ログイン時（manage_options）は除外
 * --------------------------------------*/
add_action('wp_ajax_kumonosu_pv', 'kumonosu_ajax_pv');
add_action('wp_ajax_nopriv_kumonosu_pv', 'kumonosu_ajax_pv');

function kumonosu_ajax_pv() {
  $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
  if (!wp_verify_nonce($nonce, 'kumonosu_pv_nonce')) {
    wp_send_json_error(['message' => 'bad nonce'], 403);
  }

  // 管理者（権限: manage_options）を除外
  if (is_user_logged_in() && current_user_can('manage_options')) {
    wp_send_json_success(['ok' => true, 'skipped' => 'admin']);
  }

  // post_id が無いページ（トップ等）もあるので 0 許可
  $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;

  if ($post_id > 0) {
    kumonosu_pv_increment($post_id);
  } else {
    kumonosu_site_pv_increment();
  }

  wp_send_json_success(['ok' => true]);
}


/** ---------------------------------------
 * 0-7) JSに渡す（ajaxurl / nonce）
 *  - enqueueハンドルは "kumonosu-main" に固定
 * --------------------------------------*/
add_action('wp_enqueue_scripts', function () {
  // あなたの enqueue と一致している必要がある
  $handle = 'kumonosu-main';

  wp_localize_script($handle, 'KUMONOSU_PV', [
    'ajaxurl' => admin_url('admin-ajax.php'),
    'nonce'   => wp_create_nonce('kumonosu_pv_nonce'),
  ]);
}, 20);


/** ---------------------------------------
 * 0-8) 一覧：ソート用WP_Query args
 * --------------------------------------*/
function kumonosu_build_sort_args(string $sort): array {
  $sort = $sort ?: 'new';

  if ($sort === 'popular') {
    return [
      'orderby'  => 'meta_value_num',
      'meta_key' => '_pv_total',
      'order'    => 'DESC',
    ];
  }

  if ($sort === 'old') {
    return [
      'orderby' => 'date',
      'order'   => 'ASC',
    ];
  }

  return [
    'orderby' => 'date',
    'order'   => 'DESC',
  ];
}
// 公開時に _pv_total が無ければ 0 を入れる（0PVでも人気順に出すため）
add_action('save_post', function ($post_id) {
  // 自動保存・リビジョン除外
  if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;

  $post_type = get_post_type($post_id);
  if (!in_array($post_type, ['css', 'blog'], true)) return;

  // まだ無ければ 0 を作る
  if (get_post_meta($post_id, '_pv_total', true) === '') {
    add_post_meta($post_id, '_pv_total', 0, true);
  }
}, 10);

/** ---------------------------------------
 * 0-9) LoadMore / Sort AJAX
 *  - post_type: css or blog
 *  - sort: new|popular|old
 *  - paged: 1,2,3...
 * --------------------------------------*/
add_action('wp_ajax_kumonosu_load', 'kumonosu_ajax_load');
add_action('wp_ajax_nopriv_kumonosu_load', 'kumonosu_ajax_load');

function kumonosu_ajax_load() {
  $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
  if (!wp_verify_nonce($nonce, 'kumonosu_pv_nonce')) {
    wp_send_json_error(['message' => 'bad nonce'], 403);
  }

  $post_type = isset($_POST['post_type']) ? sanitize_key(wp_unslash($_POST['post_type'])) : '';
  if (!in_array($post_type, ['css', 'blog'], true)) {
    wp_send_json_error(['message' => 'bad post_type'], 400);
  }

  $sort  = isset($_POST['sort']) ? sanitize_key(wp_unslash($_POST['sort'])) : 'new';
  $paged = isset($_POST['paged']) ? max(1, (int) $_POST['paged']) : 1;
  $ppp   = isset($_POST['ppp']) ? max(1, (int) $_POST['ppp']) : 12;

  $sort_args = kumonosu_build_sort_args($sort);

  $q = new WP_Query(array_merge([
    'post_type'      => $post_type,
    'post_status'    => 'publish',
    'paged'          => $paged,
    'posts_per_page' => $ppp,
    'no_found_rows'  => false,
  ], $sort_args));

  ob_start();

  if ($q->have_posts()) {
    while ($q->have_posts()) {
      $q->the_post();

      // 初期表示と同じカードテンプレを返す
      if ($post_type === 'css') {
        get_template_part('templates/parts/c-card', 'css');
      } else {
        get_template_part('templates/parts/c-card', 'css'); // blog用がある場合
        // blog用が無いなら ↓
        // get_template_part('templates/parts/c-card');
      }
    }
  }

  wp_reset_postdata();

  $html = ob_get_clean();

  wp_send_json_success([
    'html'  => $html,
    'max'   => (int) $q->max_num_pages,
    'found' => (int) $q->found_posts,
  ]);
}

/**
 * =========================================================
 * CSSアーカイブ：複数選択のURLクエリで tax 絞り込み（AND）
 * 例）/css/?css-category[]=hover&css-tag[]=button&css-tag[]=card
 *  - taxonomy内はIN（OR）
 *  - taxonomy間はAND
 * =========================================================
 */
add_action('pre_get_posts', function ($q) {
  if ( is_admin() || ! $q->is_main_query() ) return;

  if ( $q->is_post_type_archive('css') ) {

    // 1. キーワード検索の反映 (q -> s)
    if ( isset($_GET['q']) && $_GET['q'] !== '' ) {
        $q->set('s', sanitize_text_field($_GET['q']));
    }

    $tax_category = 'css-category';
    $tax_tag      = 'css-tag';

    // 2. タクソノミー絞り込みの反映
    $cats = isset($_GET[$tax_category]) ? (array) $_GET[$tax_category] : [];
    $tags = isset($_GET[$tax_tag])      ? (array) $_GET[$tax_tag]      : [];

    $cats = array_values(array_filter(array_map('sanitize_text_field', $cats)));
    $tags = array_values(array_filter(array_map('sanitize_text_field', $tags)));

    $tax_query = ['relation' => 'OR'];
    if (!empty($cats)) {
      $tax_query[] = ['taxonomy' => $tax_category, 'field' => 'slug', 'terms' => $cats, 'operator' => 'IN'];
    }
    if (!empty($tags)) {
      $tax_query[] = ['taxonomy' => $tax_tag, 'field' => 'slug', 'terms' => $tags, 'operator' => 'IN'];
    }

    if (count($tax_query) > 1) {
      $q->set('tax_query', $tax_query);
    }
  }
});


/**
 * =========================================================
 * AJAX：該当件数を返す（リアルタイム更新用）
 * =========================================================
 */
add_action('wp_ajax_kumonosu_css_count', 'kumonosu_ajax_css_count');
add_action('wp_ajax_nopriv_kumonosu_css_count', 'kumonosu_ajax_css_count');

function kumonosu_ajax_css_count() {
  $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
  if (!wp_verify_nonce($nonce, 'kumonosu_css_filter_nonce')) {
    wp_send_json_error(['message' => 'invalid nonce'], 403);
  }

  $tax_category = 'css-category';
  $tax_tag      = 'css-tag';

  $cats = isset($_POST[$tax_category]) ? (array) $_POST[$tax_category] : [];
  $tags = isset($_POST[$tax_tag])      ? (array) $_POST[$tax_tag]      : [];
  $keyword = isset($_POST['q']) ? sanitize_text_field($_POST['q']) : '';

  $cats = array_values(array_filter(array_map('sanitize_text_field', $cats)));
  $tags = array_values(array_filter(array_map('sanitize_text_field', $tags)));

  // 並び順が違っても同じ条件なら同じキーにする
  sort($cats);
  sort($tags);

  /**
   * ---------------------------------------------------------
   * トランジェントキー（条件別）
   * - 長くなりすぎないように md5
   * - 例: kumonosu_css_count_5f2a...
   * ---------------------------------------------------------
   */
  $hash = md5('cats:' . implode(',', $cats) . '|tags:' . implode(',', $tags) . '|q:' . $keyword);
  $tkey = 'kumonosu_css_count_' . $hash;

  // キャッシュがあれば即返す
  $cached = get_transient($tkey);
  if ($cached !== false) {
    wp_send_json_success(['count' => (int) $cached, 'cached' => 1]);
  }

  // tax_query 構築（OR仕様）
  $tax_query = [];

  if (!empty($cats) && !empty($tags)) {
    // カテゴリ or パーツ のどちらかに該当すればOK
    $tax_query = [
      'relation' => 'OR',
      [
        'taxonomy' => $tax_category,
        'field'    => 'slug',
        'terms'    => $cats,
        'operator' => 'IN',
      ],
      [
        'taxonomy' => $tax_tag,
        'field'    => 'slug',
        'terms'    => $tags,
        'operator' => 'IN',
      ],
    ];
  } elseif (!empty($cats)) {
    $tax_query = [[
      'taxonomy' => $tax_category,
      'field'    => 'slug',
      'terms'    => $cats,
      'operator' => 'IN',
    ]];
  } elseif (!empty($tags)) {
    $tax_query = [[
      'taxonomy' => $tax_tag,
      'field'    => 'slug',
      'terms'    => $tags,
      'operator' => 'IN',
    ]];
  }

  $args = [
    'post_type'      => 'css',
    'post_status'    => 'publish',
    's'              => $keyword, // 追加：検索ワードを指定
    'posts_per_page' => 1,
    'fields'         => 'ids',
    'no_found_rows'  => false, // found_posts を使うので false
  ];

  if (!empty($tax_query)) {
    $args['tax_query'] = $tax_query;
  }

  $q = new WP_Query($args);
  $count = (int) $q->found_posts;
  wp_reset_postdata();

  // ✅ 例えば 10分キャッシュ（必要なら調整）
  set_transient($tkey, $count, 10 * MINUTE_IN_SECONDS);

  wp_send_json_success(['count' => $count, 'cached' => 0]);
}
/**
 * =========================================================
 * CSS 一覧（MORE / sort）AJAX：フィルター追従 完全版
 * action: kumonosu_css_list
 * POST:
 *  - sort: new / popular / old
 *  - paged, ppp
 *  - css-category[] / css-tag[]（複数）
 * =========================================================
 */
add_action('wp_ajax_kumonosu_css_list', 'kumonosu_ajax_css_list');
add_action('wp_ajax_nopriv_kumonosu_css_list', 'kumonosu_ajax_css_list');

function kumonosu_ajax_css_list() {

  // nonce
  $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
  if (!wp_verify_nonce($nonce, 'kumonosu_css_filter_nonce')) {
    wp_send_json_error(['message' => 'invalid nonce'], 403);
  }

  $post_type    = 'css';
  $tax_category = 'css-category';
  $tax_tag      = 'css-tag';

  // sort / paged / ppp
  $sort  = isset($_POST['sort'])  ? sanitize_text_field($_POST['sort'])  : 'new';
  $paged = isset($_POST['paged']) ? max(1, (int) $_POST['paged']) : 1;
  $ppp   = isset($_POST['ppp'])   ? max(1, (int) $_POST['ppp'])   : 24;
  $keyword = isset($_POST['q']) ? sanitize_text_field($_POST['q']) : '';

  /**
   * ---------------------------------------------------------
   * filters取得：取りこぼしゼロ版
   * - FormDataで 'css-category[]' を送ると PHPでは $_POST['css-category'] に入るのが通常
   * - 念のため 'css-category[]' キーでも拾う
   * ---------------------------------------------------------
   */
  $cats_raw = $_POST[$tax_category] ?? ($_POST[$tax_category . '[]'] ?? []);
  $tags_raw = $_POST[$tax_tag]      ?? ($_POST[$tax_tag . '[]']      ?? []);

  $cats = (array) $cats_raw;
  $tags = (array) $tags_raw;

  // 文字列で来た場合（カンマ区切り等）も吸収
  if (count($cats) === 1 && is_string($cats[0]) && str_contains($cats[0], ',')) {
    $cats = array_map('trim', explode(',', $cats[0]));
  }
  if (count($tags) === 1 && is_string($tags[0]) && str_contains($tags[0], ',')) {
    $tags = array_map('trim', explode(',', $tags[0]));
  }

  $cats = array_values(array_filter(array_map('sanitize_text_field', $cats)));
  $tags = array_values(array_filter(array_map('sanitize_text_field', $tags)));

  // ★デバッグ：WP_DEBUG のときだけログ出し（必要なら一時的にON）
  if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('[kumonosu_css_list] sort=' . $sort . ' paged=' . $paged . ' ppp=' . $ppp);
    error_log('[kumonosu_css_list] cats=' . print_r($cats, true));
    error_log('[kumonosu_css_list] tags=' . print_r($tags, true));
  }

  // tax_query
  $tax_query = ['relation' => 'OR'];

  if (!empty($cats)) {
    $tax_query[] = [
      'taxonomy' => $tax_category,
      'field'    => 'slug',
      'terms'    => $cats,
      'operator' => 'IN',
    ];
  }
  if (!empty($tags)) {
    $tax_query[] = [
      'taxonomy' => $tax_tag,
      'field'    => 'slug',
      'terms'    => $tags,
      'operator' => 'IN',
    ];
  }

  // sort
  $orderby  = 'date';
  $order    = 'DESC';
  $meta_key = '';

  if ($sort === 'old') {
    $orderby = 'date';
    $order   = 'ASC';
  } elseif ($sort === 'popular') {
    // ※あなたのPVメタキーに合わせて必要なら変更
    $meta_key = '_pv_total';
    $orderby  = 'meta_value_num';
    $order    = 'DESC';
  }

  $args = [
    'post_type'      => $post_type,
    'post_status'    => 'publish',
    'posts_per_page' => $ppp,
    'paged'          => $paged,
    'orderby'        => $orderby,
    'order'          => $order,
  ];

  if ($meta_key) {
    $args['meta_key'] = $meta_key;
  }
  if (count($tax_query) > 1) {
    $args['tax_query'] = $tax_query;
  }

  // ★デバッグ：最終クエリvars
  if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('[kumonosu_css_list] query_args=' . print_r($args, true));
  }

  $q = new WP_Query($args);

  ob_start();
  if ($q->have_posts()) {
    while ($q->have_posts()) {
      $q->the_post();
      get_template_part('templates/parts/c-card', 'css');
    }
  }
  wp_reset_postdata();
  $html = ob_get_clean();

  wp_send_json_success([
    'html'      => $html,
    'max_pages' => (int) ($q->max_num_pages ?? 1),
    'found'     => (int) ($q->found_posts ?? 0),
  ]);
}
add_action('wp_ajax_kumonosu_css_list_v2', 'kumonosu_ajax_css_list_v2');
add_action('wp_ajax_nopriv_kumonosu_css_list_v2', 'kumonosu_ajax_css_list_v2');

function kumonosu_ajax_css_list_v2() {
  // ここが動いているか確認するための保険（あとで消してOK）
  // wp_send_json_success(['ok' => 1]); // ←一旦これを有効化して動作確認してもOK

  $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
  if (!wp_verify_nonce($nonce, 'kumonosu_css_filter_nonce')) {
    wp_send_json_error(['message' => 'invalid nonce'], 403);
  }

  $post_type    = 'css';
  $tax_category = 'css-category';
  $tax_tag      = 'css-tag';

  $sort  = isset($_POST['sort'])  ? sanitize_text_field($_POST['sort'])  : 'new';
  $paged = isset($_POST['paged']) ? max(1, (int) $_POST['paged']) : 1;
  $ppp   = isset($_POST['ppp'])   ? max(1, (int) $_POST['ppp'])   : 24;
  $keyword = isset($_POST['q']) ? sanitize_text_field($_POST['q']) : '';

  $cats = isset($_POST[$tax_category]) ? (array) $_POST[$tax_category] : [];
  $tags = isset($_POST[$tax_tag])      ? (array) $_POST[$tax_tag]      : [];

  $cats = array_values(array_filter(array_map('sanitize_text_field', $cats)));
  $tags = array_values(array_filter(array_map('sanitize_text_field', $tags)));

  // tax_query（OR仕様）
  $tax_query = [];

  if (!empty($cats) && !empty($tags)) {
    $tax_query = [
      'relation' => 'OR',
      [
        'taxonomy' => $tax_category,
        'field'    => 'slug',
        'terms'    => $cats,
        'operator' => 'IN',
      ],
      [
        'taxonomy' => $tax_tag,
        'field'    => 'slug',
        'terms'    => $tags,
        'operator' => 'IN',
      ],
    ];
  } elseif (!empty($cats)) {
    $tax_query = [[
      'taxonomy' => $tax_category,
      'field'    => 'slug',
      'terms'    => $cats,
      'operator' => 'IN',
    ]];
  } elseif (!empty($tags)) {
    $tax_query = [[
      'taxonomy' => $tax_tag,
      'field'    => 'slug',
      'terms'    => $tags,
      'operator' => 'IN',
    ]];
  }

  $orderby  = 'date';
  $order    = 'DESC';
  $meta_key = '';

  if ($sort === 'old') {
    $orderby = 'date';
    $order   = 'ASC';
  } elseif ($sort === 'popular') {
    $meta_key = '_pv_total'; // ←あなたのPVキーに合わせて
    $orderby  = 'meta_value_num';
    $order    = 'DESC';
  }

  $args = [
    'post_type'      => $post_type,
    'post_status'    => 'publish',
    's'              => $keyword,
    'posts_per_page' => $ppp,
    'paged'          => $paged,
    'orderby'        => $orderby,
    'order'          => $order,
  ];

  if ($meta_key) $args['meta_key'] = $meta_key;
  if (count($tax_query) > 1) $args['tax_query'] = $tax_query;

  $q = new WP_Query($args);

  ob_start();
  if ($q->have_posts()) {
    while ($q->have_posts()) {
      $q->the_post();
      get_template_part('templates/parts/c-card', 'css');
    }
  }
  wp_reset_postdata();
  $html = ob_get_clean();

  wp_send_json_success([
    'html'      => $html,
    'max_pages' => (int) ($q->max_num_pages ?? 1),
    'found'     => (int) ($q->found_posts ?? 0),
    'received'  => ['cats' => $cats, 'tags' => $tags, 'sort' => $sort],
  ]);
}
add_action('wp_ajax_kumonosu_css_list_json', 'kumonosu_ajax_css_list_json');
add_action('wp_ajax_nopriv_kumonosu_css_list_json', 'kumonosu_ajax_css_list_json');

function kumonosu_ajax_css_list_json() {

  $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
  if (!wp_verify_nonce($nonce, 'kumonosu_css_filter_nonce')) {
    wp_send_json_error(['message' => 'invalid nonce'], 403);
  }

  $post_type    = 'css';
  $tax_category = 'css-category';
  $tax_tag      = 'css-tag';

  $sort  = isset($_POST['sort'])  ? sanitize_text_field($_POST['sort'])  : 'new';
  $paged = isset($_POST['paged']) ? max(1, (int) $_POST['paged']) : 1;
  $ppp   = isset($_POST['ppp'])   ? max(1, (int) $_POST['ppp'])   : 24;

  // ✅ JSONで受け取る（ここが肝）
  $cats = [];
  $tags = [];

  if (!empty($_POST['cats_json'])) {
    $decoded = json_decode(wp_unslash($_POST['cats_json']), true);
    if (is_array($decoded)) $cats = $decoded;
  }
  if (!empty($_POST['tags_json'])) {
    $decoded = json_decode(wp_unslash($_POST['tags_json']), true);
    if (is_array($decoded)) $tags = $decoded;
  }

  $cats = array_values(array_filter(array_map('sanitize_text_field', (array)$cats)));
  $tags = array_values(array_filter(array_map('sanitize_text_field', (array)$tags)));

  // tax_query
  $tax_query = ['relation' => 'OR'];

  if (!empty($cats)) {
    $tax_query[] = [
      'taxonomy' => $tax_category,
      'field'    => 'slug',
      'terms'    => $cats,
      'operator' => 'IN',
    ];
  }
  if (!empty($tags)) {
    $tax_query[] = [
      'taxonomy' => $tax_tag,
      'field'    => 'slug',
      'terms'    => $tags,
      'operator' => 'IN',
    ];
  }

  // sort
  $orderby  = 'date';
  $order    = 'DESC';
  $meta_key = '';

  if ($sort === 'old') {
    $orderby = 'date';
    $order   = 'ASC';
  } elseif ($sort === 'popular') {
    // ※あなたのPVメタキーに合わせて必要なら変更
    $meta_key = '_pv_total';
    $orderby  = 'meta_value_num';
    $order    = 'DESC';
  }

  $args = [
    'post_type'      => $post_type,
    'post_status'    => 'publish',
    'posts_per_page' => $ppp,
    'paged'          => $paged,
    'orderby'        => $orderby,
    'order'          => $order,
  ];

  if ($meta_key) $args['meta_key'] = $meta_key;
  if (count($tax_query) > 1) $args['tax_query'] = $tax_query;

  $q = new WP_Query($args);

  ob_start();
  if ($q->have_posts()) {
    while ($q->have_posts()) {
      $q->the_post();
      get_template_part('templates/parts/c-card', 'css');
    }
  }
  wp_reset_postdata();
  $html = ob_get_clean();

  wp_send_json_success([
    'html'      => $html,
    'max_pages' => (int) ($q->max_num_pages ?? 1),
    'found'     => (int) ($q->found_posts ?? 0),
    // デバッグ用（確認できたら消してOK）
    'received'  => ['cats' => $cats, 'tags' => $tags, 'sort' => $sort],
  ]);
}
