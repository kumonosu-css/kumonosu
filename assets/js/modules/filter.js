/**
 * =========================================================
 * KUMONOSU CSS Filter (Keyword + Taxonomy)
 * =========================================================
 */
export const filter = () => {
  const form = document.querySelector('.js-css-filter-form');
  if (!form) return;

  const ajaxUrl = form.dataset.ajaxUrl;
  const nonce   = form.dataset.nonce;

  if (!ajaxUrl || !nonce) return;

  const countEl   = form.querySelector('.js-filter-count');
  const submitBtn = form.querySelector('.js-filter-submit');
  const clearBtn  = form.querySelector('.js-filter-clear');
  // キーワード入力欄
  const keywordInput = form.querySelector('.js-filter-keyword');

  const TAX_CATS = 'css-category';
  const TAX_TAGS = 'css-tag';
  const SEARCH_KEY = 'q';

  let timer   = null;
  let lastKey = '';

  /* -----------------------------------------
   * 補助関数（UI / 状態管理）
   * ----------------------------------------- */

  // ローディング表示の切り替え
  const setLoading = (isLoading) => {
    form.classList.toggle('is-loading', isLoading);
  };

  // 件数表示の更新
  const updateUI = (count) => {
    if (countEl) countEl.textContent = String(count);
    if (submitBtn) submitBtn.disabled = (count === 0);
  };

  // チェックボックスのラベルテキストを取得
  const getLabelByValue = (tax, value) => {
    const input = form.querySelector(
      `input[name="${tax}[]"][value="${CSS.escape(value)}"]`
    );
    if (!input) return value;
    const label = input.closest('label');
    if (!label) return value;
    // 余計な改行や空白を除去
    return label.textContent.replace(/\s+/g, ' ').trim();
  };

  /* -----------------------------------------
   * URLクエリ操作
   * ----------------------------------------- */
  
  // URLから配列データを取得（カテゴリ・タグ用）
  const readQueryValues = (key) => {
    const params = new URLSearchParams(window.location.search);
    const arr = params.getAll(`${key}[]`).filter(Boolean);
    const one = params.get(key);
    if (one) {
      one.split(',').map(s => s.trim()).filter(Boolean).forEach(v => arr.push(v));
    }
    return Array.from(new Set(arr));
  };

  // URLから単一の文字列を取得（キーワード検索用）
  const readQueryValue = (key) => {
    const params = new URLSearchParams(window.location.search);
    return params.get(key) || '';
  };

  // URLに値を反映させる
  const setQueryValues = (key, values) => {
    const params = new URLSearchParams(window.location.search);
    
    // 一旦既存の値を削除
    params.delete(key);
    params.delete(`${key}[]`);

    if (Array.isArray(values)) {
      // 配列の場合（カテゴリ・タグ）
      values.forEach(v => params.append(`${key}[]`, v));
    } else if (values) {
      // 文字列の場合（キーワード）
      params.set(key, values);
    }

    const qs = params.toString();
    const newUrl = `${window.location.pathname}${qs ? `?${qs}` : ''}`;
    history.replaceState(null, '', newUrl);
  };

  // URLの状態をフォームの入力欄に同期させる
  const syncInputsFromUrl = () => {
    const cats = readQueryValues(TAX_CATS);
    const tags = readQueryValues(TAX_TAGS);
    const s    = readQueryValue(SEARCH_KEY);

    form.querySelectorAll(`input[name="${TAX_CATS}[]"]`).forEach(el => {
      el.checked = cats.includes(el.value);
    });
    form.querySelectorAll(`input[name="${TAX_TAGS}[]"]`).forEach(el => {
      el.checked = tags.includes(el.value);
    });

    if (keywordInput) {
      keywordInput.value = s;
    }
    lastKey = '';
  };

  /* -----------------------------------------
   * チップ（選択中の条件表示）
   * ----------------------------------------- */
  const chipsWrap = document.querySelector('.js-active-filters');
  const chipsList = document.querySelector('.js-active-filters-list');
  const chipsClearBtn = document.querySelector('.js-active-filters-clear');

  const labelMap = {
    [TAX_CATS]: 'Category',
    [TAX_TAGS]: 'Parts',
    [SEARCH_KEY]: 'Keyword',
  };

  const renderChips = () => {
    if (!chipsWrap || !chipsList) return;

    const cats = readQueryValues(TAX_CATS);
    const tags = readQueryValues(TAX_TAGS);
    const s    = readQueryValue(SEARCH_KEY);

    const items = [
      ...cats.map(v => ({ tax: TAX_CATS, value: v })),
      ...tags.map(v => ({ tax: TAX_TAGS, value: v })),
    ];
    
    if (s) {
      items.push({ tax: SEARCH_KEY, value: s });
    }

    if (items.length === 0) {
      chipsWrap.hidden = true;
      chipsList.innerHTML = '';
      return;
    }

    chipsWrap.hidden = false;
    chipsList.innerHTML = items.map(({ tax, value }) => {
      const label = labelMap[tax] || tax;
      const name = (tax === SEARCH_KEY) ? `"${value}"` : getLabelByValue(tax, value);

      return `
        <li class="c-active-filters__item">
          <button type="button"
            class="c-active-filters__chip js-active-filter-chip"
            data-tax="${tax}"
            data-value="${value}">
            <span class="c-active-filters__chip-value">${name}</span>
            <span class="c-active-filters__chip-x" aria-hidden="true">×</span>
          </button>
        </li>
      `;
    }).join('');
  };

  /* -----------------------------------------
   * アクション（解除・取得）
   * ----------------------------------------- */

  // 個別解除
  const removeOneFilter = async (tax, value) => {
    if (tax === SEARCH_KEY) {
      setQueryValues(SEARCH_KEY, '');
      if (keywordInput) keywordInput.value = '';
    } else {
      const current = readQueryValues(tax);
      const next = current.filter(v => v !== value);
      setQueryValues(tax, next);
      const checkbox = form.querySelector(`input[name="${tax}[]"][value="${CSS.escape(value)}"]`);
      if (checkbox) checkbox.checked = false;
    }

    lastKey = '';
    renderChips();
    scheduleFetch();
    window.dispatchEvent(new CustomEvent('kumonosu:filters-changed'));
  };

  // 全解除
  const clearAllFilters = () => {
    setQueryValues(TAX_CATS, []);
    setQueryValues(TAX_TAGS, []);
    setQueryValues(SEARCH_KEY, '');

    form.querySelectorAll('input[type="checkbox"]').forEach(el => (el.checked = false));
    if (keywordInput) keywordInput.value = '';

    lastKey = '';
    renderChips();
    scheduleFetch();
    window.dispatchEvent(new CustomEvent('kumonosu:filters-changed'));
  };

  // AJAXで件数を取得
  const fetchCount = async () => {
    const cats = Array.from(form.querySelectorAll(`input[name="${TAX_CATS}[]"]:checked`)).map(el => el.value);
    const tags = Array.from(form.querySelectorAll(`input[name="${TAX_TAGS}[]"]:checked`)).map(el => el.value);
    const s    = keywordInput ? keywordInput.value : '';

    const key = JSON.stringify({ cats, tags, s });
    if (key === lastKey) return;
    lastKey = key;

    setLoading(true);

    const fd = new FormData();
    fd.append('action', 'kumonosu_css_count');
    fd.append('nonce', nonce);
    fd.append('q', s);
    cats.forEach(v => fd.append(`${TAX_CATS}[]`, v));
    tags.forEach(v => fd.append(`${TAX_TAGS}[]`, v));

    try {
      const res = await fetch(ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      });
      const json = await res.json();

      if (json?.success && typeof json.data?.count !== 'undefined') {
        updateUI(Number(json.data.count) || 0);
      } else {
        if (countEl) countEl.textContent = '-';
      }
    } catch (e) {
      console.error(e);
      if (countEl) countEl.textContent = '-';
    } finally {
      setLoading(false);
    }
  };

  const scheduleFetch = () => {
    clearTimeout(timer);
    timer = setTimeout(fetchCount, 250); // 入力時は少し待ってから実行
  };

  /* -----------------------------------------
   * イベントリスナー
   * ----------------------------------------- */
  
  // チェックボックス変更
  form.addEventListener('change', (e) => {
    if (e.target.matches('input[type="checkbox"]')) {
      renderChips();
      scheduleFetch();
    }
  });

  // キーワード入力
  if (keywordInput) {
    keywordInput.addEventListener('input', () => {
      setQueryValues(SEARCH_KEY, keywordInput.value);
      renderChips();
      scheduleFetch();

      window.dispatchEvent(new CustomEvent('kumonosu:filters-changed'));
    });
  }

  // チップのクリック（解除）
  if (chipsList) {
    chipsList.addEventListener('click', (e) => {
      const btn = e.target.closest('.js-active-filter-chip');
      if (!btn) return;
      removeOneFilter(btn.dataset.tax, btn.dataset.value);
    });
  }

  // クリアボタン
  if (chipsClearBtn) chipsClearBtn.addEventListener('click', clearAllFilters);
  if (clearBtn) {
    clearBtn.addEventListener('click', clearAllFilters);
  }

  // 初期化実行
  syncInputsFromUrl();
  renderChips();
  fetchCount();
};