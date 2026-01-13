export const copytoclipboard = () => {
  // 対象セレクタ（URL/コード共通）
  const SELECTOR = '[data-copy-target], .js-copy-url, .c-code-copy';

  // 1個も無いなら何もしない（早期 return）
  if (!document.querySelector(SELECTOR)) return;

  // トースト（無ければ生成）
  let toast = document.querySelector('.c-copy-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.className = 'c-copy-toast';
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    toast.textContent = 'クリップボードにコピーしました';
    document.body.appendChild(toast);
  }

  let timer = null;

  const showToast = () => {
    toast.classList.add('is-visible');
    clearTimeout(timer);
    timer = setTimeout(() => toast.classList.remove('is-visible'), 1600);
  };

  const writeClipboard = async (text) => {
    if (!text || !String(text).trim()) return;

    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
      } else {
        const temp = document.createElement('textarea');
        temp.value = text;
        temp.setAttribute('readonly', '');
        temp.style.position = 'fixed';
        temp.style.left = '-9999px';
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
      }
      showToast();
    } catch (err) {
      console.error('Copy failed', err);
    }
  };

  // ✅ イベント委譲（1回だけ）
  document.addEventListener('click', (e) => {
    const el = e.target?.closest?.(SELECTOR);
    if (!el) return;

    // a / button の遷移を止める
    if (el.matches('a, button')) e.preventDefault();

    // A) URLコピー
    if (el.classList.contains('js-copy-url')) {
      writeClipboard(location.href);
      return;
    }

    // B) data-copy-target
    const selector = el.getAttribute('data-copy-target');
    if (selector) {
      const targetEl = document.querySelector(selector);
      if (!targetEl) return;

      const text = targetEl.value ?? targetEl.textContent ?? '';
      writeClipboard(text);
      return;
    }

    // C) .c-code-copy（互換）
    if (el.classList.contains('c-code-copy')) {
      const panel = el.closest('.c-code-panel');
      if (!panel) return;

      const codeEl = panel.querySelector('.c-code-code');
      if (!codeEl) return;

      const text = codeEl.textContent ?? '';
      writeClipboard(text);
    }
  }, { passive: false });
};
