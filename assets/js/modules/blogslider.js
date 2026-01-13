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

  /* =========================
     helpers
  ========================= */
  const getPerView = () => {
    // CSSに合わせる：4 / 3 / 2 / 1
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

    // ★ 599以下は gap:20px 想定（CSSから取れない時の保険）
    if (window.innerWidth <= 599 && fromCss === 0) return 20;

    return fromCss;
  };

  const step = () => {
    // 1ステップ = カード幅 + gap（見切れや120px控除でもズレない）
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
     dots（1ステップごと）
     dotCount = originalsCount - perView + 1
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
      b.className = '';
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
     move / normalize (infinite)
  ========================= */
  const moveToIndex = (nextIndex, animate = true) => {
    const s = step();
    if (!s) return;

    index = nextIndex;
    setTransition(animate);
    setX(-index * s);
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
  const beginDrag = (cx, cy) => {
    stop();
    dragging = true;
    moved = false;
    locked = false;
    startClientX = cx;
    startClientY = cy;
    startTranslate = currentX();
    setTransition(false);
  };

  const dragMove = (cx, cy, ev) => {
    if (!dragging) return;

    const dx = cx - startClientX;
    const dy = cy - startClientY;

    // 縦スクロール優先
    if (!locked) {
      if (Math.abs(dx) < 6 && Math.abs(dy) < 6) return;
      locked = Math.abs(dx) > Math.abs(dy);
    }
    if (locked && ev?.cancelable) ev.preventDefault();
    if (Math.abs(dx) > 3) moved = true;

    const s = step();
    if (!s) return;

    const minX = -(track.children.length - 1) * s;
    const maxX = 0;

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
    if (!s) { start(); return; }

    const baseX = -index * s;
    const dx = currentX() - baseX;
    const threshold = s * 0.2;

    if (dx > threshold) index -= 1;
    else if (dx < -threshold) index += 1;

    moveToIndex(index, true);

    const onEnd = () => {
      track.removeEventListener('transitionend', onEnd);
      normalizeIfNeeded();
      start();
    };
    track.addEventListener('transitionend', onEnd);

    // ドラッグした時だけリンククリック抑止
    if (moved) {
      const cancelClick = (e) => { e.preventDefault(); e.stopPropagation(); };
      root.addEventListener('click', cancelClick, true);
      setTimeout(() => root.removeEventListener('click', cancelClick, true), 0);
    }
  };

  /* =========================
     rebuild (レスポンシブ追従)
  ========================= */
  const rebuild = () => {
    stop();
    isAnimating = false;

    // まずクローン除去→本物だけに
    clearClones();
    originals = Array.from(track.children);
    originalsCount = originals.length;

    perView = getPerView();
    updateNavVisibility();

    // スライド不要
    if (originalsCount <= perView) {
      setTransition(false);
      setX(0);
      if (dotsWrap) dotsWrap.innerHTML = '';
      return;
    }

    // 現在の表示位置を維持（dot相当）
    const keepDot = Math.max(0, Math.min(originalsCount - perView, toDotIndex() || 0));

    // 前後に perView 枚ずつクローン
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

    // 幅が取れてから開始（最初に触らないと動かない問題を潰す）
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

  // pointer
  track.addEventListener('pointerdown', (e) => {
    if (e.pointerType === 'mouse' && e.button !== 0) return;
    if (typeof track.setPointerCapture === 'function') track.setPointerCapture(e.pointerId);
    beginDrag(e.clientX, e.clientY);
  }, { passive: true });

  track.addEventListener('pointermove', (e) => dragMove(e.clientX, e.clientY, e), { passive: false });
  track.addEventListener('pointerup', endDrag, { passive: true });
  track.addEventListener('pointercancel', endDrag, { passive: true });

  // touch fallback
  track.addEventListener('touchstart', (e) => {
    const t = e.touches?.[0];
    if (!t) return;
    beginDrag(t.clientX, t.clientY);
  }, { passive: true });

  track.addEventListener('touchmove', (e) => {
    const t = e.touches?.[0];
    if (!t) return;
    dragMove(t.clientX, t.clientY, e);
  }, { passive: false });

  track.addEventListener('touchend', endDrag, { passive: true });
  track.addEventListener('touchcancel', endDrag, { passive: true });

  // hover停止
  root.addEventListener('mouseenter', stop);
  root.addEventListener('mouseleave', start);

  // resize追従（perViewが変わるのでrebuild）
  let raf = 0;
  window.addEventListener('resize', () => {
    cancelAnimationFrame(raf);
    raf = requestAnimationFrame(rebuild);
  }, { passive: true });

  window.addEventListener('load', rebuild, { once: true });

  // init
  rebuild();
};
