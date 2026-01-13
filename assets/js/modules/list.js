/**
 * =========================================================
 * KUMONOSU CSS List (conflict-safe)
 * - 並び替え（new / popular / old）
 * - MORE（無限ロード）
 * - URLの css-category / css-tag を常に維持
 * - 旧JSのイベント競合を強制的に無効化（cloneでリスナー全消し）
 * =========================================================
 */

export const list = () => {
  const root = document.querySelector('[data-kumonosu-list]');
  if (!root) return;

  // 二重初期化防止（イベント増殖防止）
  if (root.dataset.listInit === '1') return;
  root.dataset.listInit = '1';

  const grid = root.querySelector('[data-kumonosu-grid]');
  if (!grid) return;

  const ajaxUrl = root.dataset.ajaxUrl;
  const nonce = root.dataset.nonce;

  if (!ajaxUrl || !nonce) {
    console.warn('[kumonosu] ajaxUrl / nonce is missing');
    return;
  }

  const TAX_CATS = 'css-category';
  const TAX_TAGS = 'css-tag';

  const state = {
    sort: root.dataset.sort || 'new',
    paged: Number(root.dataset.paged || '1'),
    ppp: Number(root.dataset.ppp || '24'),
    max: Number(root.dataset.max || '1'),
    loading: false,
  };

  /* -----------------------------------------
   * URLから filters を読む（完全同期）
   * ----------------------------------------- */
  const readQueryValues = (key) => {
    const params = new URLSearchParams(window.location.search);
    const values = [];

    params.getAll(`${key}[]`).forEach(v => {
      if (v) values.push(v);
    });

    const one = params.get(key);
    if (one) {
      one.split(',').forEach(v => {
        const s = v.trim();
        if (s) values.push(s);
      });
    }

    return Array.from(new Set(values));
  };

  const getFilters = () => {
    const params = new URLSearchParams(window.location.search);
    return {
      cats: readQueryValues(TAX_CATS),
      tags: readQueryValues(TAX_TAGS),
      q: params.get('q') || '',
    };
  };

  /* -----------------------------------------
   * 競合対策：旧リスナーを完全に殺す
   * - 同じDOMに複数の addEventListener が付いてると
   *   片方が全件で上書きすることがある
   * - cloneNode で “イベントだけ” 全消しできる
   * ----------------------------------------- */
  const resetElementEvents = (el) => {
    if (!el) return null;
    const clone = el.cloneNode(true);
    el.parentNode.replaceChild(clone, el);
    return clone;
  };

  let select = root.querySelector('[data-kumonosu-sort-select]');
  let moreBtn = root.querySelector('[data-kumonosu-more]');

  // ★ここで旧イベントを全削除
  select = resetElementEvents(select);
  moreBtn = resetElementEvents(moreBtn);

  /* -----------------------------------------
   * UI helpers
   * ----------------------------------------- */
  const setLoading = (on) => {
    state.loading = on;
    root.classList.toggle('is-loading', on);
    if (select) select.disabled = on;
    if (moreBtn) moreBtn.disabled = on || state.paged >= state.max;
  };

  const updateMoreVisibility = () => {
    if (!moreBtn) return;
    const canMore = state.paged < state.max;
    moreBtn.style.display = canMore ? '' : 'none';
  };

  /* -----------------------------------------
   * AJAX request
   * ----------------------------------------- */
  const request = async (paged, sort) => {
    const { cats, tags, q } = getFilters();

    console.log('[kumonosu] request filters', { cats, tags, paged, sort });

    const fd = new FormData();
    fd.append('action', 'kumonosu_css_list_v2');
    fd.append('nonce', nonce);
    fd.append('paged', String(paged));
    fd.append('ppp', String(state.ppp));
    fd.append('sort', sort);
    fd.append('q', q);

    cats.forEach(v => fd.append(`${TAX_CATS}[]`, v));
    tags.forEach(v => fd.append(`${TAX_TAGS}[]`, v));

    const res = await fetch(ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: fd,
    });

    const json = await res.json();

    // ★レスポンス確認（received を見れば “上書き犯” が分かる）
    console.log('[kumonosu] response received', json?.data?.received);

    if (!json || !json.success) return null;
    return json.data;
  };

  /* -----------------------------------------
   * actions
   * ----------------------------------------- */
  const doSort = async (nextSort) => {
    if (state.loading) return;

    setLoading(true);
    try {
      state.sort = nextSort;
      state.paged = 1;

      const data = await request(1, state.sort);
      if (!data) return;

      grid.innerHTML = data.html || '';

      state.max = Number(data.max_pages || 1);

      root.dataset.sort = state.sort;
      root.dataset.paged = '1';
      root.dataset.max = String(state.max);

      updateMoreVisibility();
    } finally {
      setLoading(false);
    }
  };

  const doMore = async () => {
    if (state.loading) return;
    if (state.paged >= state.max) return;

    setLoading(true);
    try {
      const nextPage = state.paged + 1;

      const data = await request(nextPage, state.sort);
      if (!data) return;

      if (data.html) {
        grid.insertAdjacentHTML('beforeend', data.html);
      }

      state.paged = nextPage;
      state.max = Number(data.max_pages || state.max);

      root.dataset.paged = String(state.paged);
      root.dataset.max = String(state.max);

      updateMoreVisibility();
    } finally {
      setLoading(false);
    }
  };

  /* -----------------------------------------
   * events（ここからは “新しいDOM” に付けるので競合しない）
   * ----------------------------------------- */
  if (select) {
    select.value = state.sort;
    select.addEventListener('change', (e) => {
      e.stopImmediatePropagation();
      doSort(select.value);
    }, { capture: true });
  }

  if (moreBtn) {
    moreBtn.addEventListener('click', (e) => {
      e.stopImmediatePropagation();
      doMore();
    }, { capture: true });
  }

  updateMoreVisibility();

  // filters-changed（増殖防止つき）
  const onFiltersChanged = async () => {
    const nextSort = (select && select.value) ? select.value : state.sort;
    await doSort(nextSort);
  };

  // 既存があれば外してから付ける（保険）
  window.removeEventListener('kumonosu:filters-changed', root.__onFiltersChanged);
  root.__onFiltersChanged = onFiltersChanged;
  window.addEventListener('kumonosu:filters-changed', onFiltersChanged);

}; // list関数の終わりはここになります