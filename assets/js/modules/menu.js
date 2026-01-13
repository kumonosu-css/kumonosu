export const menu = () => {
  const navi      = document.querySelector('.js-navi');
  const naviBg    = document.querySelector('.js-navi-bg');
  const menuBtn   = document.querySelector('.js-menubtn');
  const overlay   = document.querySelector('.js-overlay');
  const searchBtn = document.querySelector('.js-searchbtn');

  if (!navi || !naviBg || !menuBtn) return;

  // ==========================
  // モード管理（中身の切替）
  // ==========================
  const setMode = (mode) => {
    navi.classList.remove('-mode-menu', '-mode-search');
    if (mode === 'menu') navi.classList.add('-mode-menu');
    if (mode === 'search') navi.classList.add('-mode-search');
  };

  const isOpen = () => navi.classList.contains('-open');
  const isOpenMenu = () => navi.classList.contains('-open-menu');
  const isOpenSearch = () => navi.classList.contains('-open-search');

  // ==========================
  // 開く処理（サイズ計測）
  // ==========================
  const openNav = (type /* 'menu' | 'search' */) => {
    // 計測モード
    navi.classList.add('-measuring');

    const contentWidth  = naviBg.offsetWidth;
    const contentHeight = naviBg.offsetHeight;

    navi.classList.remove('-measuring');

    // ゴールサイズをセット
    naviBg.style.width  = contentWidth + 'px';
    naviBg.style.height = contentHeight + 'px';

    void naviBg.offsetWidth; // reflow

    // 状態クラス（open を分離）
    navi.classList.remove('-closed', '-open-menu', '-open-search');
    navi.classList.add('-open');

    if (type === 'menu') navi.classList.add('-open-menu');
    if (type === 'search') navi.classList.add('-open-search');
  };

  const closeNav = () => {
    navi.classList.remove('-open', '-open-menu', '-open-search');
    navi.classList.add('-closed');

    naviBg.style.width  = '';
    naviBg.style.height = '';
  };

  // ==========================
  // メニューボタン
  // ==========================
  const handleMenuClick = () => {
    // 「メニューとして開いてる」ならトグルで閉じる
    if (isOpenMenu()) {
      closeNav();
      return;
    }

    // 閉じてる or 検索で開いてる → メニューで開く（再計測）
    setMode('menu');
    openNav('menu');
  };

  // ==========================
  // 検索ボタン
  // ==========================
  const handleSearchClick = () => {
    // 「検索として開いてる」ならトグルで閉じる
    if (isOpenSearch()) {
      closeNav();
      return;
    }

    // 閉じてる or メニューで開いてる → 検索で開く（再計測）
    setMode('search');
    openNav('search');
  };

  // ==========================
  // イベント登録
  // ==========================
  menuBtn.addEventListener('click', handleMenuClick);

  if (searchBtn) {
    searchBtn.addEventListener('click', handleSearchClick);
  }

  if (overlay) {
    overlay.addEventListener('click', closeNav);
  }

  // 初期状態
  setMode('menu');
};
