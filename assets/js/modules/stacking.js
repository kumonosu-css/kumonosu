import gsap from "https://esm.sh/gsap";
import ScrollTrigger from "https://esm.sh/gsap/ScrollTrigger";

export const stacking = () => {
  gsap.registerPlugin(ScrollTrigger);

  const section = document.querySelector(".l-section--reasons");
  const metrics = document.querySelector(".l-reasons-wrap");
  const cards = gsap.utils.toArray(".c-metric-card-wrapper");
  const targetMetricElement = document.querySelector(".c-section-metrics");

  if (!section || !metrics || cards.length === 0) return;
  if (section.dataset.stackingInit === "1") return;
  section.dataset.stackingInit = "1";

  // iOSのリサイズによるガタつきを防止する設定（normalizeScrollは使わない）
  ScrollTrigger.config({ 
    ignoreMobileResize: true 
  });

  const mm = gsap.matchMedia();

  mm.add({
    isMobile: "(max-width: 600px)",
    isDesktop: "(min-width: 601px)"
  }, (context) => {
    let { isMobile } = context.conditions;

    // 1. 高さの計算
    const setHeight = () => {
      let maxH = 0;
      cards.forEach(card => {
        gsap.set(card, { height: "auto" });
        maxH = Math.max(maxH, card.offsetHeight);
      });
      // カードの最大高さに基づいてコンテナを固定
      gsap.set(targetMetricElement, { height: maxH });
    };

    setHeight();
    
    // 2. 初期スタイル（!important 競合を避けるためGSAPで設定）
    gsap.set(cards, {
      position: "absolute",
      top: 0,
      left: 0,
      width: "100%",
      zIndex: (i) => i + 1,
      autoAlpha: (i) => (i === 0 ? 1 : 0),
      scale: (i) => (i === 0 ? 1 : 0.9),
      yPercent: (i) => (i === 0 ? 0 : 100),
      force3D: true
    });

    // 3. アニメーション作成
    const tl = gsap.timeline({
      scrollTrigger: {
        trigger: section,
        start: isMobile ? "50% top" : "center center", // 止まる場合はここを微調整
        end: () => `+=${cards.length * 100}%`, // スクロール量をカード枚数に比例させる
        pin: metrics,
        pinSpacing: true, // これをtrueにすることで、止まった後にページが続くようになります
        scrub: 1,
        invalidateOnRefresh: true,
        anticipatePin: 1, // Pin時のガタつきを軽減
      }
    });

    cards.forEach((card, i) => {
      if (i === 0) return;
      
      tl.to(cards[i - 1], {
        autoAlpha: 0,
        scale: 0.9,
        duration: 1,
      }, i - 1)
      .to(card, {
        autoAlpha: 1,
        scale: 1,
        yPercent: 0,
        duration: 1,
      }, i - 1);
    });

    return () => {
      // クリーンアップ
    };
  });
};