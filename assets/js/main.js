import { menu } from "./modules/menu.js";              /* メニュー */
import { hero } from "./modules/hero.js";              /* 背景ロゴ */
import { stacking } from "./modules/stacking.js";      /* 理由 */
import { loading } from "./modules/loading.js";        /* ローディング */
import { copytoclipboard } from "./modules/copytoclipboard.js"; /* コードコピー */
import { pv } from "./modules/pv.js";                  /* PV計測 */
import { filter } from "./modules/filter.js";          /* 絞り込み検索件数表示 */
import { list } from "./modules/list.js";              /* 絞り込み検索後の並び替え */
import { blogslider } from "./modules/blogslider.js";  /* BLOGのスライダー */

document.addEventListener("DOMContentLoaded", () => {
  const page = document.body.dataset.page;

  /* =========================
   * 全ページ共通
   * ========================= */
  menu();
  hero();
  loading();
  pv();
  filter();

  /* =========================
   * front-page.php のみ
   * ========================= */
  if (page === "front") {
    stacking();
  }

  /* =========================
   * single-css.php のみ
   * ========================= */
  if (page === "single-css" || page === "single-blog") {
    copytoclipboard();
  }

  /* =========================
   * archive-css.php のみ
   * ========================= */
  if (page === "archive-css") {
    list();
  }

  /* =========================
   * archive-blog.php のみ
   * ========================= */
  if (page === "archive-blog") {
    blogslider();
  }
});
