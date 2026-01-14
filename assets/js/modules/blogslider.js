export const blogslider = () => {
  const root = document.querySelector('.post-type-archive-blog .l-section--featured');
  if (!root) return;

  const track = root.querySelector('.l-card-grid');
  if (!track) return;

  const prevBtn = root.querySelector('.c-blog-slider-prev');
  const nextBtn = root.querySelector('.c-blog-slider-next');
  const dotsWrap = root.querySelector('.c-blog-slider-dots');
  const nav = root.querySelector('.c-blog-slider-nav');

  const prefersReduce = window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches;
  const autoplayEnabled = !prefersReduce;
  const interval = 3500;

  let timer = null;
  let isAnimating = false;

  // 状態（rebuildで更新）
  let perView = 4;
  let originals = [];
  let originalsCount = 0;

  // index は track内（クローン含む）の左端
  let index = 0;
  let firstReal = 0;
  let lastReal = 0;
  let maxDotIndex = 0;

  // dots
  let dots = [];

  // drag
  let dragging = false;
  let startClientX = 0;
  let startClientY = 0;
  let startTranslate = 0;
  let moved = false;
  let locked = false;

  // pointer capture を「必要になったら」取るための管理
  let activePointerId = null;
  let hasCapture = false;

  // 「クリック」と「ドラッグ」を分ける閾値
  const MOVE_LOCK_PX = 8;     // 方向ロック開始
  const DRAG_START_PX = 12;   // これを超えたらドラッグ扱い

  /* =========================
     helpers
  ========================= */
  const getPerView = () => {
    const w = window.innerWidth;
    if (w <= 599) return 1;
    if (w <= 999) return 2;
    if (w <= 1299) return 3;
    return 4;
  };

  const getGap = () => {
    const cs = getComputedStyle(track);
    const g = cs.gap || cs.columnGap || '0px';
    const n = parseFloat(g);
    const fromCss = Number.isFinite(n) ? n : 0;

    if (window.innerWidth <= 599 && fromCss === 0) return 20;
    return fromCss;
  };

  const step = () => {
    const first = track.children[0];
    if (!first) return 0;
    const w = first.getBoundingClientRect().width || 0;
    return w + getGap();
  };

  const setTransition = (on) => {
    track.style.transition = on ? 'transform .5s ease' : 'none';
  };

  const setX = (x) => {
    track.style.transform = `translate3d(${x}px,0,0)`;
  };

  const currentX = () => {
    const st = getComputedStyle(track).transform;
    if (!st || st === 'none') return 0;
    const m = new DOMMatrixReadOnly(st);
    return m.m41;
  };

  const padLeft = () => {
    const cs = getComputedStyle(track);
    const v = parseFloat(cs.paddingLeft || '0');
    return Number.isFinite(v) ? v : 0;
  };

  const stop = () => {
    if (!timer) return;
    clearInterval(timer);
    timer = null;
  };

  const start = () => {
    if (!autoplayEnabled || timer) return;
    timer = setInterval(() => next(false), interval);
  };

  const clearClones = () => {
    track.querySelectorAll('.is-clone').forEach((el) => el.remove());
  };

  const updateNavVisibility = () => {
    if (!nav) return;
    nav.style.display = originalsCount <= perView ? 'none' : '';
  };

  /* =========================
     dots
  ========================= */
  const buildDots = () => {
    dots = [];
    if (!dotsWrap) return;

    dotsWrap.innerHTML = '';

    const dotCount = Math.max(0, originalsCount - perView + 1);
    if (dotCount <= 1) return;

    for (let i = 0; i < dotCount; i++) {
      const b = document.createElement('button');
      b.type = 'button';
      b.setAttribute('aria-label', `${i + 1}番目の位置へ`);
      b.addEventListener('click', () => {
        stop();
        moveToIndex(firstReal + i, true);
        start();
      });
      dotsWrap.appendChild(b);
      dots.push(b);
    }
  };

  const toDotIndex = () => {
    const rel = index - firstReal;
    return Math.max(0, Math.min(maxDotIndex, rel));
  };

  const updateDots = () => {
    if (!dots.length) return;
    const active = toDotIndex();
    dots.forEach((d, i) => d.classList.toggle('is-active', i === active));
  };

  /* =========================
     move / normalize
  ========================= */
  const moveToIndex = (nextIndex, animate = true) => {
    const s = step();
    if (!s) return;

    index = nextIndex;
    setTransition(animate);
    setX(padLeft() - index * s);
    updateDots();
  };

  const normalizeIfNeeded = () => {
    if (index < firstReal) {
      index = firstReal + originalsCount - (firstReal - index);
      moveToIndex(index, false);
    } else if (index > lastReal) {
      index = firstReal + (index - lastReal - 1);
      moveToIndex(index, false);
    }
  };

  const next = (user = false) => {
    if (isAnimating) return;
    isAnimating = true;
    if (user) stop();

    moveToIndex(index + 1, true);

    const onEnd = () => {
      track.removeEventListener('transitionend', onEnd);
      normalizeIfNeeded();
      isAnimating = false;
      if (user) start();
    };
    track.addEventListener('transitionend', onEnd);
  };

  const prev = (user = false) => {
    if (isAnimating) return;
    isAnimating = true;
    if (user) stop();

    moveToIndex(index - 1, true);

    const onEnd = () => {
      track.removeEventListener('transitionend', onEnd);
      normalizeIfNeeded();
      isAnimating = false;
      if (user) start();
    };
    track.addEventListener('transitionend', onEnd);
  };

  /* =========================
     drag
  ========================= */
  const beginDrag = (cx, cy, pointerId) => {
    stop();
    dragging = true;
    moved = false;
    locked = false;
    startClientX = cx;
    startClientY = cy;
    startTranslate = currentX();
    setTransition(false);

    activePointerId = pointerId ?? null;
    hasCapture = false;
  };

  const ensureCapture = () => {
    if (hasCapture) return;
    if (activePointerId == null) return;
    if (typeof track.setPointerCapture !== 'function') return;

    try {
      track.setPointerCapture(activePointerId);
      hasCapture = true;
    } catch (_) {
      // 取れなくても致命的ではないので無視
    }
  };

  const releaseCapture = () => {
    if (!hasCapture) return;
    if (activePointerId == null) return;
    if (typeof track.releasePointerCapture !== 'function') return;

    try {
      track.releasePointerCapture(activePointerId);
    } catch (_) {}
    hasCapture = false;
    activePointerId = null;
  };

  const dragMove = (cx, cy, ev) => {
    if (!dragging) return;

    const dx = cx - startClientX;
    const dy = cy - startClientY;

    // 方向ロック
    if (!locked) {
      if (Math.abs(dx) < MOVE_LOCK_PX && Math.abs(dy) < MOVE_LOCK_PX) return;
      locked = Math.abs(dx) > Math.abs(dy);

      // 横ドラッグだと確定したらここで capture を取る（←重要）
      if (locked) ensureCapture();
    }

    // 横ドラッグ中だけスクロール抑止
    if (locked && ev?.cancelable) ev.preventDefault();

    // 本当にドラッグした時だけ moved=true
    if (locked && Math.abs(dx) > DRAG_START_PX) moved = true;

    const s = step();
    if (!s) return;

    const maxX = padLeft();
    const minX = padLeft() - (track.children.length - 1) * s;

    let nextX = startTranslate + dx;

    // 抵抗
    if (nextX > maxX) nextX = maxX + (nextX - maxX) * 0.25;
    if (nextX < minX) nextX = minX + (nextX - minX) * 0.25;

    setX(nextX);
  };

  const endDrag = () => {
    if (!dragging) return;
    dragging = false;

    const s = step();
    if (!s) { releaseCapture(); start(); return; }

    const baseX = padLeft() - index * s;
    const dx = currentX() - baseX;
    const threshold = s * 0.2;

    if (dx > threshold) index -= 1;
    else if (dx < -threshold) index += 1;

    moveToIndex(index, true);

    const onEnd = () => {
      track.removeEventListener('transitionend', onEnd);
      normalizeIfNeeded();
      releaseCapture();
      start();
    };
    track.addEventListener('transitionend', onEnd);

    // ドラッグした時だけ、次のclickを1回だけ潰す（リンク誤爆防止）
    if (moved) {
      root.addEventListener(
        'click',
        (e) => { e.preventDefault(); e.stopPropagation(); },
        { capture: true, once: true }
      );
    }
  };

  /* =========================
     rebuild
  ========================= */
  const rebuild = () => {
    stop();
    isAnimating = false;

    clearClones();
    originals = Array.from(track.children);
    originalsCount = originals.length;

    perView = getPerView();
    updateNavVisibility();

    if (originalsCount <= perView) {
      root.classList.remove('is-blog-slider');
      setTransition(false);
      setX(0);
      if (dotsWrap) dotsWrap.innerHTML = '';
      return;
    }

    root.classList.add('is-blog-slider');

    const keepDot = Math.max(0, Math.min(originalsCount - perView, toDotIndex() || 0));

    const headClones = originals.slice(0, perView).map((el) => {
      const c = el.cloneNode(true);
      c.classList.add('is-clone');
      c.setAttribute('aria-hidden', 'true');
      return c;
    });

    const tailClones = originals.slice(-perView).map((el) => {
      const c = el.cloneNode(true);
      c.classList.add('is-clone');
      c.setAttribute('aria-hidden', 'true');
      return c;
    });

    headClones.forEach((c) => track.appendChild(c));
    tailClones.slice().reverse().forEach((c) => track.insertBefore(c, track.firstChild));

    firstReal = perView;
    lastReal = perView + originalsCount - 1;
    maxDotIndex = originalsCount - perView;

    index = firstReal + keepDot;

    buildDots();

    const init = () => {
      const s = step();
      if (!s) {
        requestAnimationFrame(init);
        return;
      }
      moveToIndex(index, false);
      start();
    };
    requestAnimationFrame(init);
  };

  /* =========================
     events
  ========================= */
  prevBtn?.addEventListener('click', () => prev(true));
  nextBtn?.addEventListener('click', () => next(true));

  // pointerで統一
  track.addEventListener('pointerdown', (e) => {
    if (e.pointerType === 'mouse' && e.button !== 0) return;
    // ★ ここでは capture を取らない（リンククリックを殺さない）
    beginDrag(e.clientX, e.clientY, e.pointerId);
  }, { passive: true });

  track.addEventListener('pointermove', (e) => dragMove(e.clientX, e.clientY, e), { passive: false });
  track.addEventListener('pointerup', endDrag, { passive: true });
  track.addEventListener('pointercancel', endDrag, { passive: true });

  root.addEventListener('mouseenter', stop);
  root.addEventListener('mouseleave', start);

  let raf = 0;
  window.addEventListener('resize', () => {
    cancelAnimationFrame(raf);
    raf = requestAnimationFrame(rebuild);
  }, { passive: true });

  window.addEventListener('load', rebuild, { once: true });

  rebuild();
};
