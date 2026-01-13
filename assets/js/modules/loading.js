export const loading = () => {
    /**
     * PIXIV TECH FES CYBER SHUTDOWN LOADER
     * - Exit sequence is a multi-phase glitch shutdown, not a fade.
     * - Sequence: Logo/BG Cut (0.15s) -> Glitch Breakdown (0.10s) -> Full Clear.
     */

    const canvas = document.getElementById('c');
    // Canvasがない場合は処理しない（エラー防止）
    if (!canvas) return;

    // Use { alpha: true } to allow the canvas to become transparent for the residue phase
    const ctx = canvas.getContext('2d', { alpha: true });
    const loaderContainer = document.getElementById('loader-container');
    const jsLoading = document.getElementById('js-loading');

    // 必要な要素が無いページでは何もしない（全ページ読み込み対策）
    if (!loaderContainer || !jsLoading) return;
    if (!ctx) return;

    // 二重初期化防止（イベント増殖防止）
    if (jsLoading.dataset.loadingInit === '1') return;
    jsLoading.dataset.loadingInit = '1';

    let width, height;
    const pixelRatio = window.devicePixelRatio || 1;
    let animationFrameId;

    // --- CONFIGURATION ---
    const ANIMATION_DURATION_WITH_LOGO = 1.0;
    const ANIMATION_DURATION_NO_LOGO = 0.3;
    const LOGO_CUT_DURATION = 0.5;
    const RESIDUE_DURATION = 0.1;
    const SHUTDOWN_DURATION = LOGO_CUT_DURATION + RESIDUE_DURATION;
    const BASE_SCALE_PERCENTAGE = 0.15;
    const MIN_LOGO_DISPLAY_TIME = 1.5;

    let logoDisplayStartTime = Date.now();

    // --- SVG DATA (Path2D sources) ---
    const LOGO_PARTS = [{
            color: '#2f4b9e',
            d: "M20.26,0h-2.09C12.5.36,6.97,5.55,4.3,10.28c-5.69,10.09-6.22,22.59,1.35,31.86l.22.05c6.63.27,12.94-2.83,18.52-6.08,7.21-4.2,12.33-10.36,16.48-17.52,1.51-2.6,2.65-5.39,4.31-7.9l.02-.23c-1.63-.99-3.11-2.2-4.72-3.22C34.35,3.38,27.53.54,20.26,0Z"
        },
        {
            color: '#e8413d',
            d: "M65.99,36.11c5.58,3.25,11.89,6.36,18.52,6.08l.22-.05c7.57-9.27,7.04-21.77,1.35-31.86C83.42,5.55,77.89.36,72.23,0h-2.09c-7.27.54-14.09,3.38-20.21,7.25-1.61,1.02-3.09,2.23-4.72,3.22l.02.23c1.66,2.51,2.8,5.3,4.31,7.9,4.14,7.16,9.27,13.31,16.48,17.52h-.02Z"
        },
        {
            color: '#e8413d',
            d: "M40.87,65.83c-4.14-7.16-9.27-13.31-16.48-17.52-5.58-3.25-11.89-6.36-18.52-6.08l-.22.05c-7.57,9.27-7.04,21.77-1.35,31.86,2.67,4.73,8.2,9.92,13.86,10.28h2.09c7.27-.54,14.09-3.38,20.21-7.25,1.61,1.02,3.09,2.23,4.72,3.22l-.02-.23c-1.66-2.51-2.8-5.3-4.31-7.9h.02Z"
        },
        {
            color: '#2f4b9e',
            d: "M84.89,42.28l-.22-.05c-6.63-.27-12.94,2.83-18.52,6.08-7.21,4.2-12.33,10.36-16.48,17.52-1.51,2.6-2.65,5.39-4.31,7.9l-.02.23c1.63.99,3.11,2.2,4.72,3.22,6.12,3.87,12.94,6.71,20.21,7.25h2.09c5.67-.36,11.19-5.55,13.86-10.28,5.69-10.09,6.22-22.59-1.35-31.86h.02Z"
        }
    ];
    const TEXT_PATHS_D = [
        "M104.06,1.17v82.08h-3.52V1.17h3.52ZM152.25,1.17l-39.4,39.16,43.15,42.91h-5.04l-43.15-42.91L147.21,1.17h5.04ZM164.71,1.17h44.91v46.78c0,12.51-2.15,21.73-6.45,27.67-4.3,5.86-11.26,8.79-20.87,8.79s-16.57-2.93-20.87-8.79c-4.3-5.94-6.45-15.16-6.45-27.67V1.17h3.52v46.78c0,6.49.55,11.96,1.64,16.42,1.09,4.38,2.74,7.74,4.92,10.08s4.65,4.03,7.39,5.04c2.74.94,6.02,1.41,9.85,1.41s7.11-.47,9.85-1.41c2.74-1.02,5.2-2.7,7.39-5.04,2.19-2.35,3.83-5.71,4.92-10.08,1.09-4.46,1.64-9.93,1.64-16.42V4.69h-41.39V1.17h0ZM230.19,79.73h46.2V9.5l-26.38,56.52-26.38-56.63v73.87l-3.52-.12V1.06h3.52l26.38,56.63L276.39,1.17l3.52-.12v82.19l-49.71-.12v-3.4h-.01ZM337.01,75.63c-4.3,5.86-11.26,8.79-20.87,8.79s-16.57-2.93-20.87-8.79c-4.3-5.94-6.45-15.16-6.45-27.67v-11.49c0-12.51,2.15-21.69,6.45-27.55,4.3-5.95,11.26-8.92,20.87-8.92s16.57,2.97,20.87,8.91c4.3,5.86,6.45,15.05,6.45,27.55v11.49c0,12.51-2.15,21.73-6.45,27.67h0ZM325.99,5.04c-2.74-1.02-6.02-1.52-9.85-1.52s-7.11.51-9.85,1.52c-2.74.94-5.2,2.58-7.39,4.92-2.19,2.35-3.83,5.75-4.92,10.2-1.09,4.38-1.64,9.81-1.64,16.3v11.49c0,6.49.55,11.96,1.64,16.42,1.09,4.38,2.74,7.74,4.92,10.08,2.19,2.35,4.65,4.03,7.39,5.04,2.74.94,6.02,1.41,9.85,1.41s7.11-.47,9.85-1.41c2.74-1.02,5.2-2.7,7.39-5.04,2.19-2.35,3.83-5.71,4.93-10.08,1.09-4.46,1.64-9.93,1.64-16.42v-11.49c0-6.49-.55-11.92-1.64-16.3-1.1-4.46-2.74-7.86-4.93-10.2-2.19-2.35-4.65-3.99-7.39-4.92ZM361.53,79.73h36.35L355.9,7.15v76.1h-3.52V1.17h4.1l43.85,75.86V1.17h3.52v82.08h-42.33v-3.52h0ZM460.93,75.63c-4.3,5.86-11.26,8.79-20.87,8.79s-16.57-2.93-20.87-8.79c-4.3-5.94-6.45-15.16-6.45-27.67v-11.49c0-12.51,2.15-21.69,6.45-27.55,4.3-5.95,11.25-8.92,20.87-8.92s16.57,2.97,20.87,8.91c4.3,5.86,6.45,15.05,6.45,27.55v11.49c0,12.51-2.15,21.73-6.45,27.67h0ZM449.91,5.04c-2.73-1.02-6.02-1.52-9.85-1.52s-7.11.51-9.85,1.52c-2.73.94-5.2,2.58-7.39,4.92-2.19,2.35-3.83,5.75-4.92,10.2-1.09,4.38-1.64,9.81-1.64,16.3v11.49c0,6.49.55,11.96,1.64,16.42,1.1,4.38,2.74,7.74,4.92,10.08,2.19,2.35,4.65,4.03,7.39,5.04,2.74.94,6.02,1.41,9.85,1.41s7.11-.47,9.85-1.41c2.74-1.02,5.2-2.7,7.39-5.04,2.19-2.35,3.83-5.71,4.92-10.08,1.1-4.46,1.64-9.93,1.64-16.42v-11.49c0-6.49-.55-11.92-1.64-16.3-1.09-4.46-2.73-7.86-4.92-10.2-2.19-2.35-4.65-3.99-7.39-4.92ZM473.84,21.46c0-7.58,1.92-13.05,5.75-16.42,3.83-3.36,10.04-5.04,18.64-5.04,6.02,0,10.87,1.56,14.54,4.69,3.67,3.13,6.33,7.89,7.97,14.3l-10.9,8.21h-5.75l12.66-9.5c-1.64-5.24-4.02-8.91-7.15-11.02-3.05-2.11-6.84-3.17-11.37-3.17-3.13,0-5.78.2-7.97.59-2.11.39-4.26,1.17-6.45,2.35-2.11,1.09-3.71,2.89-4.81,5.39-1.09,2.5-1.64,5.71-1.64,9.61,0,3.44,1.56,6.49,4.69,9.15,3.21,2.66,7.07,4.92,11.61,6.8,4.54,1.88,9.07,3.79,13.6,5.75,4.61,1.95,8.48,4.46,11.61,7.5,3.21,3.05,4.81,6.57,4.81,10.55,0,7.82-1.95,13.64-5.86,17.47-3.83,3.83-10.44,5.75-19.82,5.75-7.66,0-13.68-1.52-18.06-4.57-4.38-3.13-7.39-7.93-9.03-14.42l3.28-1.41c1.56,6.33,4.22,10.75,7.97,13.25,3.83,2.42,9.11,3.63,15.83,3.63,7.5,0,13.05-1.45,16.65-4.34,3.67-2.89,5.51-8.01,5.51-15.36,0-3.44-1.6-6.49-4.81-9.15-3.13-2.66-7-4.92-11.61-6.8-4.53-1.88-9.07-3.79-13.6-5.75-4.53-1.95-8.4-4.46-11.61-7.5-3.13-3.05-4.69-6.57-4.69-10.55h0ZM538.98,1.17h44.91v46.78c0,12.51-2.15,21.73-6.45,27.67-4.3,5.86-11.26,8.79-20.87,8.79s-16.57-2.93-20.87-8.79c-4.3-5.94-6.45-15.16-6.45-27.67V1.17h3.52v46.78c0,6.49.55,11.96,1.64,16.42,1.09,4.38,2.73,7.74,4.92,10.08,2.19,2.35,4.65,4.03,7.39,5.04,2.73.94,6.02,1.41,9.85,1.41s7.11-.47,9.85-1.41c2.73-1.02,5.2-2.7,7.39-5.04,2.19-2.35,3.83-5.71,4.92-10.08,1.09-4.46,1.64-9.93,1.64-16.42V4.69h-41.39V1.17Z"
    ];

    const symbolPaths = LOGO_PARTS.map(p => ({
        color: p.color,
        path: new Path2D(p.d)
    }));
    const textPaths = TEXT_PATHS_D.map(d => new Path2D(d));

    const SVG_W = 583.89;
    const SVG_H = 84.43;

    let startTime = Date.now();
    let isAnimationComplete = false;
    let isLoadEventFired = false;
    let shutdownStartTime = 0;
    let isFullLoad = true;

    function easeOutExpo(x) {
        return x === 1 ? 1 : 1 - Math.pow(2, -10 * x);
    }

    function rand(min, max) {
        return Math.random() * (max - min) + min;
    }

    function hideLoader() {
        const elapsed = (Date.now() - logoDisplayStartTime) / 1000;
        if (
            shutdownStartTime > 0 ||
            !isAnimationComplete ||
            !isLoadEventFired ||
            (isFullLoad && elapsed < MIN_LOGO_DISPLAY_TIME)
        ) return;

        shutdownStartTime = Date.now();
        jsLoading.classList.add('loaded');
        jsLoading.classList.add('glitch-effect');
        setTimeout(() => {
            jsLoading.classList.remove('glitch-effect');
        }, 300);

        setTimeout(() => {
            if (animationFrameId) cancelAnimationFrame(animationFrameId);
            loaderContainer.classList.add('hidden');
        }, SHUTDOWN_DURATION * 1000);
    }

    function resize() {
        const viewportW = window.innerWidth;
        const viewportH = window.innerHeight;
        width = viewportW * pixelRatio;
        height = viewportH * pixelRatio;
        canvas.width = width;
        canvas.height = height;
        canvas.style.width = viewportW + 'px';
        canvas.style.height = viewportH + 'px';
    }

    function draw() {
        const now = (Date.now() - startTime) / 1000;

        if (shutdownStartTime === 0 && isAnimationComplete && isLoadEventFired) {
            hideLoader();
        }

        let t = now;
        let logoAlpha = 1;
        let effectAlpha = 1;
        let scale = 1;
        let glitchIntensity = 0;

        if (shutdownStartTime > 0) {
            const shutdownTime = (Date.now() - shutdownStartTime) / 1000;

            if (Math.random() < 0.6) {
                loaderContainer.style.filter = `hue-rotate(${rand(-5, 5)}deg) saturate(${rand(1.0, 2.0)}) contrast(${rand(1.0, 1.2)})`;
            } else {
                loaderContainer.style.filter = 'none';
            }

            if (shutdownTime < LOGO_CUT_DURATION) {
                const cutProg = shutdownTime / LOGO_CUT_DURATION;
                logoAlpha = Math.max(0, 1 - Math.pow(cutProg, 3));
                effectAlpha = 1;
                loaderContainer.style.opacity = Math.max(0, 1 - Math.pow(cutProg, 3));
            } else if (shutdownTime < SHUTDOWN_DURATION) {
                logoAlpha = 0;
                const residueTime = shutdownTime - LOGO_CUT_DURATION;
                const residueProg = residueTime / RESIDUE_DURATION;
                effectAlpha = Math.max(0, 1 - Math.pow(residueProg, 2));

                ctx.globalCompositeOperation = 'source-over';
                ctx.globalAlpha = effectAlpha * 0.9;
                ctx.font = `${18 * pixelRatio}px 'Inter', monospace`;
                ctx.fillStyle = `rgba(47, 75, 158, ${effectAlpha * 0.8})`;

                const shutdownMessage = "PROTOCOL D-87 ACTIVATED... CORE SYSTEM DISENGAGED";
                const scrollSpeed = 200 * pixelRatio;
                const textX = (width * 0.05) - (residueTime * scrollSpeed);
                const textY = height - (height * 0.1);
                ctx.fillText(shutdownMessage, textX, textY);
                loaderContainer.style.opacity = 0;
            } else {
                loaderContainer.style.opacity = 0;
                loaderContainer.style.filter = 'none';
                return;
            }

            const waitingTime = t - ANIMATION_DURATION;
            scale = 1.0 + Math.sin(waitingTime * 3) * 0.01;
            if (Math.random() < 0.2) glitchIntensity = rand(0.1, 0.4);
            else glitchIntensity = 0.02;

        } else if (t < ANIMATION_DURATION) {
            let prog = t / ANIMATION_DURATION;
            if (prog < 0.2) {
                scale = 0.5 + easeOutExpo(prog * 5) * 0.5;
                glitchIntensity = 1.0;
            } else if (prog < 0.8) {
                let midProg = (prog - 0.2) / 0.6;
                scale = 1.0 + Math.sin(midProg * Math.PI * 4) * 0.05 * (1 - midProg);
                glitchIntensity = 0.5 * (1 - midProg) + 0.02;
            } else {
                let endProg = (prog - 0.8) / 0.2;
                scale = 1.0 + Math.pow(1 - endProg, 2) * 0.02;
                glitchIntensity = 0.05;
            }
            if (t >= ANIMATION_DURATION) isAnimationComplete = true;
        } else {
            isAnimationComplete = true;
            loaderContainer.style.filter = 'none';
            const waitingTime = t - ANIMATION_DURATION;
            scale = 1.0 + Math.sin(waitingTime * 3) * 0.01;
            if (Math.random() < 0.05) glitchIntensity = rand(0.1, 0.4);
            else glitchIntensity = 0.02;
        }

        ctx.setTransform(1, 0, 0, 1, 0, 0);

        if (logoAlpha > 0) {
            ctx.fillStyle = '#050505';
            ctx.fillRect(0, 0, width, height);
        } else {
            ctx.clearRect(0, 0, width, height);
        }

        const containerW = width * BASE_SCALE_PERCENTAGE;
        const svgScale = containerW / SVG_W;
        const drawScale = svgScale * scale * pixelRatio;
        const finalLogoW = SVG_W * drawScale;
        const finalLogoH = SVG_H * drawScale;
        const offsetX = (width - finalLogoW) / 2;
        const offsetY = (height - finalLogoH) / 2;

        const shakeX = (Math.random() - 0.5) * 20 * glitchIntensity * pixelRatio;
        const shakeY = (Math.random() - 0.5) * 5 * glitchIntensity * pixelRatio;
        const sep = (2 + glitchIntensity * 30) * pixelRatio * (BASE_SCALE_PERCENTAGE / 0.8);

        const drawLogo = (dx, dy, colorMode) => {
            ctx.save();
            ctx.translate(offsetX + dx, offsetY + dy);
            ctx.scale(drawScale, drawScale);
            const fillStyle = colorMode === 'red' ? '#ff0000' : colorMode === 'blue' ? '#00ffff' : colorMode === 'white' ? '#ffffff' : null;
            symbolPaths.forEach(item => {
                ctx.fillStyle = fillStyle || item.color;
                ctx.fill(item.path);
            });
            textPaths.forEach(path => {
                ctx.fillStyle = fillStyle || '#ffffff';
                ctx.fill(path);
            });
            ctx.restore();
        };

        if (logoAlpha > 0 && isFullLoad) {
            ctx.globalCompositeOperation = 'lighter';
            ctx.globalAlpha = logoAlpha * 0.8;
            drawLogo(shakeX - sep, shakeY - sep * 0.5, 'red');
            drawLogo(shakeX + sep, shakeY + sep * 0.5, 'blue');
            ctx.globalCompositeOperation = 'lighter';
            ctx.globalAlpha = logoAlpha;
            drawLogo(shakeX, shakeY, 'white');
        }

        if (glitchIntensity > 0.01 || effectAlpha > 0) {
            const slices = Math.floor(glitchIntensity * 25);
            for (let i = 0; i < slices; i++) {
                if (shutdownStartTime > 0 && Math.random() > effectAlpha * 0.8) continue;
                const sliceY = rand(0, height);
                const sliceH = rand(2 * pixelRatio, 25 * pixelRatio);
                const sliceOffset = (Math.random() - 0.5) * 150 * glitchIntensity * pixelRatio;
                ctx.save();
                ctx.beginPath();
                ctx.rect(0, sliceY, width, sliceH);
                ctx.clip();
                if (logoAlpha > 0) {
                    ctx.globalCompositeOperation = 'source-over';
                    ctx.fillStyle = '#050505';
                    ctx.fillRect(0, sliceY, width, sliceH);
                }
                if (logoAlpha > 0 && isFullLoad) {
                    drawLogo(shakeX + sliceOffset, shakeY, 'white');
                }
                ctx.globalCompositeOperation = 'lighter';
                ctx.globalAlpha = effectAlpha * 0.17;
                ctx.fillStyle = Math.random() > 0.5 ? 'rgba(0,255,255,0.2)' : 'rgba(255,0,100,0.2)';
                ctx.fillRect(0, sliceY, width, sliceH);
                ctx.restore();
            }
        }

        ctx.globalCompositeOperation = 'overlay';
        ctx.globalAlpha = effectAlpha * 0.3;
        ctx.fillStyle = 'rgba(0,0,0,0.5)';
        for (let i = 0; i < height; i += 4 * pixelRatio) {
            ctx.fillRect(0, i, width, 1 * pixelRatio);
        }

        const grad = ctx.createRadialGradient(width / 2, height / 2, width / 3, width / 2, height / 2, width);
        grad.addColorStop(0, 'rgba(0,0,0,0)');
        grad.addColorStop(1, 'rgba(0,0,0,0.8)');
        ctx.globalCompositeOperation = 'multiply';
        ctx.globalAlpha = effectAlpha * 1;
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, width, height);

        animationFrameId = requestAnimationFrame(draw);
    }

    // --- INITIALIZATION ---
    const navigationEntry = performance.getEntriesByType('navigation')[0];
    const navType = navigationEntry ? navigationEntry.type : 'navigate';

    isFullLoad = false;
    if (navType === 'reload' || navType === 'back_forward') {
        isFullLoad = false;
    } else {
        const ref = document.referrer;
        const currentOrigin = window.location.origin;
        let refOrigin = '';
        try { refOrigin = ref ? new URL(ref).origin : ''; } catch (e) { refOrigin = ''; }
        isFullLoad = !refOrigin || refOrigin !== currentOrigin;
    }

    const ANIMATION_DURATION = isFullLoad ? ANIMATION_DURATION_WITH_LOGO : ANIMATION_DURATION_NO_LOGO;

    // イベントリスナーのセット（完了判定用）
    window.addEventListener('load', () => {
        isLoadEventFired = true;
        if (isAnimationComplete) hideLoader();
    });

    window.addEventListener('resize', () => {
        if (shutdownStartTime === 0) {
            cancelAnimationFrame(animationFrameId);
            resize();
            draw();
        }
    });

    // ★重要: window.onloadを待たずに即時実行する
    // これにより、JSが読み込まれた瞬間にCanvasを黒く塗りつぶせます
    resize();
    if (isFullLoad) {
        logoDisplayStartTime = Date.now();
    }
    draw();
};