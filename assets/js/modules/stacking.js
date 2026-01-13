import gsap from "https://esm.sh/gsap";
import ScrollTrigger from "https://esm.sh/gsap/ScrollTrigger";

export const stacking = () => {
  gsap.registerPlugin(ScrollTrigger);

  const targetMetricElement = document.querySelector(".c-section-metrics");
  const section = document.querySelector(".l-section--reasons");
  const metrics = document.querySelector(".l-reasons-wrap");
  const cards = Array.from(document.querySelectorAll(".c-metric-card-wrapper"));

  // 必要要素チェック
  if (!section || !metrics || cards.length === 0) return;

  // 複数回呼ばれても増殖しないように（安全策）
  // section単位で管理すると同一ページ内の別セクションにも対応しやすい
  if (section.dataset.stackingInit === "1") return;
  section.dataset.stackingInit = "1";

  // -------------------------------
  // 1) 親高さを「最大カード高」に合わせる
  // -------------------------------
  let ro = null;
  let rafId = 0;

  const applyContainerHeight = () => {
    if (!targetMetricElement) return;

    // 一番背の高いカードに揃える
    let maxH = 0;
    for (const card of cards) {
      const h = card.offsetHeight;
      if (h > maxH) maxH = h;
    }

    // 変化がないなら何もしない（無駄refresh削減）
    const next = `${maxH}px`;
    if (targetMetricElement.style.height !== next) {
      targetMetricElement.style.height = next;
    }
  };

  const scheduleRefresh = () => {
    if (rafId) return; // すでに予約済みなら何もしない
    rafId = requestAnimationFrame(() => {
      rafId = 0;
      applyContainerHeight();
      ScrollTrigger.refresh();
    });
  };

  // 初回
  applyContainerHeight();

  // ResizeObserver（カードの改行/折返しで高さが変わるので監視）
  if ("ResizeObserver" in window) {
    ro = new ResizeObserver(() => {
      scheduleRefresh();
    });
    cards.forEach((c) => ro.observe(c));
  } else {
    // フォールバック：resize時のみ
    window.addEventListener("resize", scheduleRefresh, { passive: true });
  }

  // -------------------------------
  // 2) 初期配置
  // -------------------------------
  cards.forEach((card, index) => {
    card.style.zIndex = String(index + 1);
  });

  // 2枚未満だと step 計算が壊れるので分岐
  if (cards.length === 1) {
    gsap.set(cards[0], { yPercent: 0, opacity: 1, scale: 1, filter: "brightness(100%)" });
    return;
  }

  // 初期状態
  gsap.set(cards[0], { yPercent: 0, opacity: 1, scale: 1, filter: "brightness(100%)" });
  for (let i = 1; i < cards.length; i++) {
    gsap.set(cards[i], { yPercent: 110, opacity: 0, scale: 0.9 });
  }

  // -------------------------------
  // 3) onUpdate最適化：quickSetter化
  // -------------------------------
  const setters = cards.map((card) => ({
    yPercent: gsap.quickSetter(card, "yPercent"),
    opacity: gsap.quickSetter(card, "opacity"),
    scale: gsap.quickSetter(card, "scale"),
    // filterは文字列なのでquickSetterに向かない → 最低限に抑える
    el: card,
  }));

  let trigger = null;

  const createStackTrigger = (startValue) => {
    // 二重生成防止
    if (trigger) trigger.kill();

    trigger = ScrollTrigger.create({
      trigger: section,
      start: startValue,
      end: `+=${cards.length * 800}`,
      pin: metrics,
      scrub: 1,
      anticipatePin: 1,
      invalidateOnRefresh: true,

      onUpdate: (self) => {
        const progress = self.progress;
        const total = cards.length;
        const step = 1 / (total - 1);

        for (let i = 1; i < total; i++) {
          const s = (i - 1) * step;
          const e = i * step;
          const p = gsap.utils.clamp(0, 1, (progress - s) / (e - s));

          // 現カード（i）
          setters[i].yPercent(gsap.utils.interpolate(110, 0, p));
          setters[i].opacity(gsap.utils.interpolate(0, 1, p));
          setters[i].scale(gsap.utils.interpolate(0.9, 1, p));

          // 前カード（i-1）
          if (p > 0) {
            const prev = setters[i - 1];
            prev.scale(gsap.utils.interpolate(1, 0.95, p));
            prev.opacity(gsap.utils.interpolate(1, 0, p));

            // filterは最小限：値が変わる時だけ更新
            const b = Math.round(gsap.utils.interpolate(100, 70, p));
            const nextFilter = `brightness(${b}%)`;
            if (prev.el.style.filter !== nextFilter) prev.el.style.filter = nextFilter;
          } else {
            const prev = setters[i - 1];
            prev.scale(1);
            prev.opacity(1);
            if (prev.el.style.filter !== "brightness(100%)") prev.el.style.filter = "brightness(100%)";
          }
        }
      },
    });

    return trigger;
  };

  // -------------------------------
  // 4) matchMedia（revertで掃除）
  // -------------------------------
  const mm = ScrollTrigger.matchMedia({
    "(max-width: 600px)": () => createStackTrigger("center 5%"),
    "(min-width: 601px)": () => createStackTrigger("center 30%"),
  });

  // -------------------------------
  // 5) 後始末（SPA/再初期化対策）
  // -------------------------------
  const cleanup = () => {
    if (rafId) cancelAnimationFrame(rafId);
    rafId = 0;

    if (ro) ro.disconnect();
    ro = null;

    if (trigger) trigger.kill();
    trigger = null;

    if (mm) mm.revert();

    delete section.dataset.stackingInit;
  };

  // 必要なら外から呼べるようにしたい場合は return cleanup でもOK
  // ここではページ離脱などで勝手に掃除される想定なので、フックは任意です
};
