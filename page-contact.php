<?php
get_header();
?>

<?php
$queried = get_queried_object();
$slug = is_a($queried, 'WP_Post') ? $queried->post_name : '';
$templates = array();
if ($slug) {
  $templates[] = 'components/pages/page-' . $slug . '.php';
}
$templates[] = 'components/pages/page.php';
$template_path = locate_template($templates, false, false);
if ($template_path) {
  include $template_path;
} else {
  get_template_part('components/pages/page');
}
?>

<section class="l-section l-section--about">
  <h1 class="c-section-title">
    <span class="c-section-title-main" data-text="<?php
      global $post;
      $slug = $post->post_name;
      echo ucfirst( $slug );
    ?>">
    <?php
      echo ucfirst( $slug );
    ?>
    </span>
    <span class="c-section-title-sub" data-text="<?php the_title(); ?>"><?php the_title(); ?></span>
  </h1>
  <div class="c-section-content">
    <?php
    // get_header(); 削除（上部で読み込み済みのための重複削除）
    $err = isset($_GET['err']) ? sanitize_text_field(wp_unslash($_GET['err'])) : '';
    ?>
    <?php if ($err): ?>
      <p class="c-form-error" role="alert"><?php echo esc_html($err); ?></p>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
      <input type="hidden" name="action" value="kumonosu_contact_submit">
      <?php wp_nonce_field('kumonosu_contact', 'kumonosu_nonce'); ?>
      <input type="hidden" name="form_started" value="<?php echo esc_attr(time()); ?>">
      <input type="hidden" name="form_token" value="<?php echo esc_attr(wp_generate_uuid4()); ?>">

      <p>
        <label>お名前 <span class="red">*</span></label>
        <input type="text" name="name" id="field-name" placeholder="例）山田太郎" required>
      </p>

      <p>
        <label>メール <span class="red">*</span></label>
        <input type="email" name="email" id="field-email" placeholder="例）xxx@kumonosu-css.com" required>
      </p>

      <p>
        <label>お問い合わせ内容 <span class="red">*</span></label>
        <textarea name="message" rows="6" id="field-message" placeholder="お問い合わせ内容をご記入ください。" required></textarea>
      </p>

      <!-- honeypot -->
      <div style="position:absolute; left:-9999px; width:1px; height:1px; overflow:hidden;" aria-hidden="true">
        <label>Company</label>
        <input name="company" type="text" tabindex="-1" autocomplete="off">
      </div>

      <!-- サイトキーをデータベースから取得 -->
      <?php $site_key = get_option('kumonosu_recaptcha_site_key'); ?>

      <?php if ($site_key): ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <!-- reCAPTCHAタグ：callbackを指定 -->
        <div class="g-recaptcha"
            data-sitekey="<?php echo esc_attr($site_key); ?>"
            data-callback="onRecaptchaSuccess"
            data-expired-callback="onRecaptchaExpired"
            style="width: fit-content;margin: 0 auto 30px;">
        </div>
      <?php endif; ?>

      <button type="submit" class="gradient-btn" id="submit-btn" disabled>
        <span class="gradient-btn__text">送信</span>
        <span class="gradient-btn__icon">»</span>
      </button>
    </form>
  </div>
</section>

<script>
/**
 * バリデーション管理
 */
(function() {
  const submitBtn = document.getElementById('submit-btn');
  const fieldName = document.getElementById('field-name');
  const fieldEmail = document.getElementById('field-email');
  const fieldMessage = document.getElementById('field-message');

  let isRecaptchaChecked = false; // reCAPTCHAの状態

  // 判定ロジック
  function validateForm() {
    const nameValue = fieldName.value.trim();
    const emailValue = fieldEmail.value.trim();
    const messageValue = fieldMessage.value.trim();

    // 1. 必須項目のチェック
    const isAllFilled = nameValue !== "" && emailValue !== "" && messageValue !== "";

    // 2. 日本語が含まれているかチェック
    const hasJapanese = /[ぁ-んァ-ヶー一-龠]/.test(messageValue);

    // 3. 全ての条件（必須＋日本語＋reCAPTCHA）を満たしているか
    // ※reCAPTCHA設定がない場合はチェックをスキップ
    const recaptchaCondition = <?php echo $site_key ? 'isRecaptchaChecked' : 'true'; ?>;

    if (isAllFilled && hasJapanese && recaptchaCondition) {
      submitBtn.disabled = false;
    } else {
      submitBtn.disabled = true;
    }
  }

  // 入力イベントの監視
  [fieldName, fieldEmail, fieldMessage].forEach(el => {
    el.addEventListener('input', validateForm);
  });

  // reCAPTCHA用コールバック関数（グローバルに公開）
  window.onRecaptchaSuccess = function() {
    isRecaptchaChecked = true;
    validateForm();
  };

  window.onRecaptchaExpired = function() {
    isRecaptchaChecked = false;
    validateForm();
  };
})();
</script>

<?php
get_footer();
?>