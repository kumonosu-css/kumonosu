export const pv = () => {
  const PV = window.KUMONOSU_PV;
  if (!PV?.ajaxurl || !PV?.nonce) return;

  // =========================================================
  // A) PV計測（全ページ / リロードごと）
  // =========================================================
  {
    const el = document.querySelector('[data-kumonosu-post-id]');
    const postId = el ? Number(el.getAttribute('data-kumonosu-post-id')) : 0;

    const fd = new FormData();
    fd.append('action', 'kumonosu_pv');
    fd.append('nonce', PV.nonce);
    fd.append('post_id', String(Number.isFinite(postId) ? postId : 0));

    fetch(PV.ajaxurl, {
      method: 'POST',
      credentials: 'same-origin',
      body: fd,
      keepalive: true, // ページ離脱時も送信されやすい（対応ブラウザのみ）
    }).catch(() => {});
  }

  // =========================================================
  // B) 一覧：並び替え(select) & もっと見る（通常一覧用）
  //  - DOMがあるページだけ動く
  //  - list.js（絞り込み用）とは別物なので共存OK
  // =========================================================
  const listRoot = document.querySelector('[data-kumonosu-list]');
  if (!listRoot) return;

  // 二重初期化防止（イベント増殖防止）
  if (listRoot.dataset.pvListInit === '1') return;
  listRoot.dataset.pvListInit = '1';

  const postType = listRoot.getAttribute('data-post-type'); // css or blog
  const grid = listRoot.querySelector('[data-kumonosu-grid]');
  const btnMore = listRoot.querySelector('[data-kumonosu-more]');
  const sortSelect = listRoot.querySelector('[data-kumonosu-sort-select]');

  if (!grid || !postType) return;

  let sort = listRoot.getAttribute('data-sort') || 'new';
  let paged = Number(listRoot.getAttribute('data-paged') || '1');
  const ppp = Number(listRoot.getAttribute('data-ppp') || '12');

  // max は「取得できたら使う」方針（未取得ならnull）
  let max = Number(listRoot.getAttribute('data-max'));
  if (!Number.isFinite(max) || max <= 0) max = null;

  let isLoading = false;

  // select初期値をdata-sortに合わせる（HTML側がズレても補正）
  if (sortSelect) sortSelect.value = sort;

  const toggleMore = () => {
    if (!btnMore) return;

    // max が未取得なら、とりあえず表示（消しすぎるより安全）
    if (max == null) {
      btnMore.style.display = '';
      btnMore.disabled = false;
      return;
    }

    const canMore = paged < max;
    btnMore.style.display = canMore ? '' : 'none';
    btnMore.disabled = !canMore || isLoading;
  };

  const request = async ({ reset = false } = {}) => {
    if (isLoading) return;
    isLoading = true;
    toggleMore();

    const fd = new FormData();
    fd.append('action', 'kumonosu_load');
    fd.append('nonce', PV.nonce);
    fd.append('post_type', postType);
    fd.append('sort', sort);
    fd.append('paged', String(paged));
    fd.append('ppp', String(ppp));

    try {
      const res = await fetch(PV.ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd,
      });

      // JSONとして読めない時も落とさない
      let json = null;
      try {
        json = await res.json();
      } catch (e) {
        return;
      }

      if (!json?.success) return;

      const nextMax = Number(json.data?.max);
      if (Number.isFinite(nextMax) && nextMax > 0) {
        max = nextMax;
        listRoot.setAttribute('data-max', String(max));
      }

      if (reset) grid.innerHTML = '';
      grid.insertAdjacentHTML('beforeend', json.data?.html || '');

    } finally {
      isLoading = false;
      toggleMore();
    }
  };

  // -----------------------------
  // sort（select）
  // -----------------------------
  if (sortSelect) {
    sortSelect.addEventListener('change', async () => {
      const next = String(sortSelect.value || 'new');
      if (next === sort) return;

      sort = next;
      paged = 1;

      listRoot.setAttribute('data-sort', sort);
      listRoot.setAttribute('data-paged', '1');

      // sort変更直後は max 未確定になりがちなので一旦 null に戻す（あれば上書きされる）
      max = null;
      listRoot.removeAttribute('data-max');

      await request({ reset: true });
    });
  }

  // -----------------------------
  // more
  // -----------------------------
  if (btnMore) {
    btnMore.addEventListener('click', async () => {
      if (isLoading) return;

      // max が分かっているなら止める
      if (max != null && paged >= max) return;

      paged += 1;
      listRoot.setAttribute('data-paged', String(paged));
      await request();
    });
  }

  // init
  toggleMore();
};
