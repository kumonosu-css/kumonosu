<?php
/**
 * Preview専用テンプレ（最小HTML）
 * /css/{id}/?preview のときだけ single-css.php から include される想定
 */
if ( ! defined('ABSPATH') ) exit;

/**
 * 直include以外の経路になっても外部サイト埋め込みを防ぐ保険
 * - 同一オリジン（自サイト）だけ iframe 許可
 */
if ( ! headers_sent() ) {
  header("Content-Security-Policy: frame-ancestors 'self'");
  header('X-Frame-Options: SAMEORIGIN'); // 旧ブラウザ保険
}

// noindex（HTML側）
?>
<!doctype html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">

<?php
$kumonosu = get_kumonosu_field();

/**
 * 便利関数：複数行の「URL」or「<script ...>」をタグ化
 */
$kumonosu_build_ext_js = function(string $raw): string {
  $raw = trim($raw);
  if ($raw === '') return '';

  $lines = preg_split("/\R/u", $raw);
  $out = [];

  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '') continue;

    // <script ...> なら最低限だけ許可してそのまま
    if (stripos($line, '<script') === 0) {
      $allowed = [
        'script' => [
          'src' => true,
          'type' => true,
          'defer' => true,
          'async' => true,
          'crossorigin' => true,
          'referrerpolicy' => true,
        ],
      ];
      $out[] = wp_kses($line, $allowed);
      continue;
    }

    // URLだけなら script タグ化
    if (preg_match('~^https?://~i', $line)) {
      $src = esc_url($line);
      $out[] = '<script src="' . $src . '"></script>';
    }
  }

  return implode("\n", $out);
};

$kumonosu_build_ext_css = function(string $raw): string {
  $raw = trim($raw);
  if ($raw === '') return '';

  $lines = preg_split("/\R/u", $raw);
  $out = [];

  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '') continue;

    // <link ...> or <style ...> なら最低限だけ許可
    if (stripos($line, '<link') === 0 || stripos($line, '<style') === 0) {
      $allowed = [
        'link' => [
          'rel' => true,
          'href' => true,
          'as' => true,
          'type' => true,
          'media' => true,
          'crossorigin' => true,
          'referrerpolicy' => true,
        ],
        'style' => [
          'id' => true,
          'media' => true,
        ],
      ];
      $out[] = wp_kses($line, $allowed);
      continue;
    }

    // URLだけなら link タグ化
    if (preg_match('~^https?://~i', $line)) {
      $href = esc_url($line);
      $out[] = '<link rel="stylesheet" href="' . $href . '">';
    }
  }

  return implode("\n", $out);
};

/**
 * iframe素材
 */
$iframe_css       = ! empty($kumonosu['code_css'])  ? (string) $kumonosu['code_css']  : '';
$iframe_js        = ! empty($kumonosu['code_js'])   ? (string) $kumonosu['code_js']   : '';
$iframe_html_body = ! empty($kumonosu['code_html']) ? (string) $kumonosu['code_html'] : '';

$ext_css_raw = ! empty($kumonosu['ext_css']) ? (string) $kumonosu['ext_css'] : '';
$ext_js_raw  = ! empty($kumonosu['ext_js'])  ? (string) $kumonosu['ext_js']  : '';
$ext_js_pos  = ! empty($kumonosu['ext_js_pos']) ? (string) $kumonosu['ext_js_pos'] : 'body';

$is_module = (!empty($kumonosu['script_type']) && $kumonosu['script_type'] === 'module');

// 文字列崩壊対策
$iframe_html_body = str_replace('</script>', '<\/script>', $iframe_html_body);
$iframe_js        = str_replace('</script>', '<\/script>', $iframe_js);
$iframe_css       = str_replace('</style>',  '<\/style>',  $iframe_css);

// 外部CSS/JS
$ext_css = $kumonosu_build_ext_css($ext_css_raw);
$ext_js  = $kumonosu_build_ext_js($ext_js_raw);

$ext_js_in_head = ($ext_js_pos === 'head') ? $ext_js : '';
$ext_js_in_body = ($ext_js_pos === 'body') ? $ext_js : '';

/**
 * 高さ通知（親が iframe 高さを追従するため）
 */
$resize_js = <<<JS
(function(){
  function postHeight(){
    var docEl = document.documentElement;
    var body  = document.body;
    var h = Math.max(
      docEl ? docEl.scrollHeight : 0,
      docEl ? docEl.offsetHeight : 0,
      body  ? body.scrollHeight  : 0,
      body  ? body.offsetHeight  : 0
    ) + 4;

    parent.postMessage({ type: 'kumonosu:resize', height: h }, '*');
  }

  window.addEventListener('load', postHeight);

  if ('ResizeObserver' in window) {
    var ro = new ResizeObserver(postHeight);
    ro.observe(document.documentElement);
  } else {
    setInterval(postHeight, 300);
  }

  window.addEventListener('message', function(e){
    if (e && e.data && e.data.type === 'kumonosu:force-resize') postHeight();
  });
})();
JS;
?>

<?php echo $ext_css . "\n"; ?>
<?php echo $ext_js_in_head . "\n"; ?>

<style>
  html, body { margin: 0; padding: 0; background: transparent; }
</style>

<style><?php echo $iframe_css; ?></style>
</head>
<body>

<?php echo $iframe_html_body; ?>

<?php echo $ext_js_in_body . "\n"; ?>

<?php if ($is_module): ?>
<script type="module"><?php echo $iframe_js; ?></script>
<?php else: ?>
<script>
window.addEventListener("load", function(){
  (function(){ <?php echo $iframe_js; ?> })();
});
</script>
<?php endif; ?>

<script><?php echo $resize_js; ?></script>

</body>
</html>
