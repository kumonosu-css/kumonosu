<?php
/**
 * Embed content template (KUMONOSU)
 * - サムネ + タイトルのみ
 * - 抜粋・サイト名なし
 */
if ( ! defined('ABSPATH') ) exit;

$thumb = has_post_thumbnail() ? get_the_post_thumbnail_url(null, 'medium_large') : '';
?>
<div class="k-embedCard-wrap">
  <a class="k-embedCard" href="<?php the_permalink(); ?>" target="_top" rel="noopener">
    <?php if ( $thumb ) : ?>
      <span class="k-embedCard__thumb" aria-hidden="true">
        <img src="<?php echo esc_url($thumb); ?>" alt="" loading="lazy" decoding="async">
      </span>
    <?php endif; ?>

    <span class="k-embedCard__body">
      <span class="k-embedCard__title"><?php the_title(); ?></span>
    </span>
  </a>
</div>

<style>
  .k-embedCard-wrap {
    container-type: inline-size;
  }
  .k-embedCard {
    display:flex;
    gap:16px;
    align-items:flex-start;
    text-decoration:none;
    color:inherit;

    background:#000;
    border:1px solid #2d2d2d;
    border-radius:16px;
    padding:25px;
    box-sizing: border-box;
    transition: border-color 0.3s ease;
  }
  .k-embedCard:hover {
    border:1px solid #9c9c9cff;
  }
  /* 左サムネ */
  .k-embedCard__thumb{
    flex:0 0 180px;
  }
  .k-embedCard__thumb img{
    width:100%;
    border-radius:10px;
  }

  /* 右タイトル */
  .k-embedCard__body{
    min-width:0;
    flex:1;
  }

  .k-embedCard__title{
    font-weight:bold;
    font-size:16px;
    color: #fff;
  }
  @media (max-width: 480px) {
    .k-embedCard{
      flex-flow: column;
      margin-bottom: 30px;
    }
    .k-embedCard__thumb {
      flex: 0;
    }
    .k-embedCard__title {
      font-size:15px
    }
  }
</style>
