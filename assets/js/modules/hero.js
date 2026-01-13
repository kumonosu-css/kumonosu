export const hero = () => {
    // 1. 要素の取得
    const liqCanvas = document.getElementById('liquidCanvas');
    const container = document.getElementById('canvas-container');

    if (!liqCanvas) return;

    const lctx = liqCanvas.getContext('2d');
    if (!lctx) return;

    // === [ORIGINAL CONFIGURATION] ===
    const BASE_PARTICLE_SIZE = 12.0;
    const SAMPLE_ATTEMPTS = 16500;
    const MAX_DEPTH_Z = 50;
    const MIN_DENSITY_INCLUSION = 0.143;
    const DENSITY_FALLOFF_POWER = 1.5;
    const CONTACT_AXIS_WIDTH = 50;
    const BOUNDARY_BOOST_FACTOR = 2;
    const BLOOM_STRENGTH = 1.5;
    const BLOOM_RADIUS = 1.2;
    const BLOOM_THRESHOLD = 0.15;
    const AURA_SIZE_MULTIPLIER = 2.5;
    const AURA_OPACITY_BASE = 0.04;
    const AURA_LIGHTNESS_SCALE = 0.08;
    const SVG_WIDTH = 90.54;
    const SVG_HEIGHT = 84.42;
    const MAX_RADIUS_SHAPE_GLOBAL = 300;
    const SHAPE_CENTERS = [
        { x: -154, y: 147 }, { x: 150, y: 147 },
        { x: -154, y: -137 }, { x: 150, y: -137 }
    ];
    const MAX_RADIUS_LOCAL = 200;

    const SVG_XML = `<svg id="_レイヤー_1" data-name="レイヤー 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${SVG_WIDTH} ${SVG_HEIGHT}">
<defs>
  <style>
    .cls-1 { fill: #2f4b9e; } /* Blue */
    .cls-2 { fill: #e8413d; } /* Red */
  </style>
</defs>
<path class="cls-1" d="M20.26,0h-2.09C12.5.36,6.97,5.55,4.3,10.28c-5.69,10.09-6.22,22.59,1.35,31.86l.22.05c6.63.27,12.94-2.83,18.52-6.08,7.21-4.2,12.33-10.36,16.48-17.52,1.51-2.6,2.65-5.39,4.31-7.9l.02-.23c-1.63-.99-3.11-2.2-4.72-3.22C34.35,3.38,27.53.54,20.26,0Z"/>
<path class="cls-2" d="M65.99,36.11c5.58,3.25,11.89,6.36,18.52,6.08l.22-.05c7.57-9.27,7.04-21.77,1.35-31.86-2.67-4.73-8.2-9.92-13.86-10.28h-2.09c-7.27.54-14.09,3.38-20.21,7.25-1.61,1.02-3.09,2.23-4.72,3.22l.02.23c1.66,2.51,2.8,5.3,4.31,7.9,4.14,7.16,9.27,13.31,16.48,17.52Z"/>
<path class="cls-2" d="M40.86,65.83c-4.14-7.16-9.27-13.31-16.48-17.52-5.58-3.25-11.89-6.36-18.52-6.08l-.22.05c-7.57,9.27-7.04,21.77-1.35,31.86,2.67,4.73,8.2,9.92,13.86,10.28h2.09c7.27-.54,14.09-3.38,20.21-7.25,1.61,1.02,3.09-2.23,4.72-3.22l-.02-.23c-1.66-2.51-2.8-5.3-4.31-7.9Z"/>
<path class="cls-1" d="M84.88,42.28l-.22-.05c-6.63-.27-12.94,2.83-18.52,6.08-7.21,4.2-12.33,10.36-16.48,17.52-1.51,2.6-2.65,5.39-4.31,7.9l-.02.23c1.63.99,3.11,2.2,4.72,3.22,6.12,3.87,12.94,6.71,20.21,7.25h2.09c5.67-.36,11.19-5.55,13.86-10.28,5.69-10.09,6.22-22.59-1.35-31.86Z"/>
</svg>`;

    // --- LIQUID BACKGROUND LOGIC ---
    let lWidth = 0, lHeight = 0, lParticles = [];
    const lColors = ['#3624ff', '#FF3C3D'];

    class LiqParticle {
        constructor(isInitial = true) {
            // 初期化時かリサイズ時かで位置の決め方を変える
            this.x = Math.random() * (lWidth || window.innerWidth);
            this.y = Math.random() * (lHeight || window.innerHeight);
            this.radiusFactor = (Math.random() * 0.01 + 0.07);
            const maxSide = Math.max(lWidth || window.innerWidth, lHeight || window.innerHeight);
            this.radius = this.radiusFactor * maxSide;
            this.vx = (Math.random() - 0.5) * 0.35;
            this.vy = (Math.random() - 0.5) * 0.35;
            this.color = lColors[Math.floor(Math.random() * lColors.length)];
        }
        rescale(wRatio, hRatio, newMaxSide) {
            this.x *= wRatio;
            this.y *= hRatio;
            this.radius = this.radiusFactor * newMaxSide;
        }
        update() {
            this.x += this.vx;
            this.y += this.vy;
            const margin = this.radius;
            // 画面端でのラップ処理
            if (this.x < -margin) this.x = lWidth + margin;
            if (this.x > lWidth + margin) this.x = -margin;
            if (this.y < -margin) this.y = lHeight + margin;
            if (this.y > lHeight + margin) this.y = -margin;
        }
        draw() {
            lctx.beginPath();
            const grad = lctx.createRadialGradient(this.x, this.y, 0, this.x, this.y, this.radius);
            const alpha = this.color === '#3624ff' ? '33' : '33';
            grad.addColorStop(0, this.color + alpha);
            grad.addColorStop(0.6, this.color + '11');
            grad.addColorStop(1, this.color + '00');
            lctx.fillStyle = grad;
            lctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            lctx.fill();
        }
    }

    function handleLiqResize() {
        const oldW = lWidth;
        const oldH = lHeight;

        // 新しいサイズを設定（0.5倍の解像度で負荷軽減）
        lWidth = liqCanvas.width = window.innerWidth * 0.5;
        lHeight = liqCanvas.height = window.innerHeight * 0.5;

        // すでに粒子が存在する場合のみ比率計算を行う
        if (oldW > 0 && oldH > 0 && lParticles.length > 0) {
            const widthRatio = lWidth / oldW;
            const heightRatio = lHeight / oldH;
            const newMaxSide = Math.max(lWidth, lHeight);

            lParticles.forEach(p => {
                p.rescale(widthRatio, heightRatio, newMaxSide);
            });
        } else {
            // 粒子がない、あるいは初回の場合は初期化
            initLiquid();
        }
    }

    function initLiquid() {
        lWidth = liqCanvas.width = window.innerWidth * 0.5;
        lHeight = liqCanvas.height = window.innerHeight * 0.5;
        lParticles = [];
        for (let i = 0; i < 80; i++) {
            lParticles.push(new LiqParticle());
        }
    }

    // --- THREE.JS PARTICLE LOGIC ---
    let scene, camera, renderer, composer, particles, auraParticles, geometry;
    let particlesEnabled = false;

    if (container && typeof THREE !== 'undefined') {
        particlesEnabled = true;

        function createGlowTexture() {
            const canvas = document.createElement('canvas');
            canvas.width = 32; canvas.height = 32;
            const context = canvas.getContext('2d');
            const gradient = context.createRadialGradient(16, 16, 0, 16, 16, 16);
            gradient.addColorStop(0, 'rgba(255, 255, 255, 1)');
            gradient.addColorStop(0.2, 'rgba(255, 255, 255, 0.8)');
            gradient.addColorStop(0.5, 'rgba(255, 255, 255, 0.2)');
            gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');
            context.fillStyle = gradient;
            context.fillRect(0, 0, 32, 32);
            return new THREE.CanvasTexture(canvas);
        }

        const glowTexture = createGlowTexture();
        scene = new THREE.Scene();
        scene.fog = new THREE.FogExp2(0x000000, 0.001);

        camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 1, 3000);
        camera.position.z = 600;

        renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        container.appendChild(renderer.domElement);

        if (typeof THREE.EffectComposer !== 'undefined') {
            composer = new THREE.EffectComposer(renderer);
            composer.addPass(new THREE.RenderPass(scene, camera));
            if (typeof THREE.UnrealBloomPass !== 'undefined') {
                const bloomPass = new THREE.UnrealBloomPass(new THREE.Vector2(window.innerWidth, window.innerHeight), BLOOM_STRENGTH, BLOOM_RADIUS, BLOOM_THRESHOLD);
                composer.addPass(bloomPass);
            }
        }

        const image = new Image();
        image.crossOrigin = "Anonymous";
        image.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const w = 300; const h = (SVG_HEIGHT / SVG_WIDTH) * w;
            canvas.width = w; canvas.height = h;
            ctx.drawImage(image, 0, 0, w, h);
            const imgData = ctx.getImageData(0, 0, w, h).data;
            const positions = [], targets = [], colors = [], sizes = [];

            for (let i = 0; i < SAMPLE_ATTEMPTS; i++) {
                const x = Math.floor(Math.random() * w);
                const y = Math.floor(Math.random() * h);
                const index = (x + y * w) * 4;
                const r = imgData[index], g = imgData[index + 1], b = imgData[index + 2], a = imgData[index + 3];

                if (a > 20 && (r > 10 || g > 10 || b > 10)) {
                    const originX = (x - w / 2) * 2;
                    const originY = -(y - h / 2) * 2;
                    const isVerticalContact = Math.abs(originX) < CONTACT_AXIS_WIDTH;
                    const isHorizontalContact = Math.abs(originY) < CONTACT_AXIS_WIDTH;
                    let iterations = (isVerticalContact || isHorizontalContact) ? BOUNDARY_BOOST_FACTOR : 1;

                    for (let j = 0; j < iterations; j++) {
                        let localCenter;
                        const isBlue = r < b && g < b && b > 100;
                        const isRed = r > b && r > g && r > 100;
                        if (isBlue) localCenter = (originY > 0) ? SHAPE_CENTERS[0] : SHAPE_CENTERS[3];
                        else if (isRed) localCenter = (originY > 0) ? SHAPE_CENTERS[1] : SHAPE_CENTERS[2];
                        else continue;

                        const dx = originX - localCenter.x, dy = originY - localCenter.y;
                        const localRadius = Math.sqrt(dx * dx + dy * dy);
                        const normalizedLocalRadius = Math.min(1.0, localRadius / MAX_RADIUS_LOCAL);
                        const inclusionProbability = MIN_DENSITY_INCLUSION + (1.0 - MIN_DENSITY_INCLUSION) * Math.pow(normalizedLocalRadius, DENSITY_FALLOFF_POWER);

                        if (Math.random() > inclusionProbability) continue;

                        const globalRadius = Math.sqrt(originX * originX + originY * originY);
                        const normalizedRadiusGlobal = Math.min(1.0, globalRadius / MAX_RADIUS_SHAPE_GLOBAL);
                        const originZ = MAX_DEPTH_Z * (1 - Math.pow(normalizedRadiusGlobal, 3));

                        positions.push(originX, originY, originZ);
                        targets.push({ x: (Math.random() - 0.5) * window.innerWidth * 1.5, y: (Math.random() - 0.5) * window.innerHeight * 1.5, z: (Math.random() - 0.5) * 1200 });

                        const color = new THREE.Color();
                        color.setRGB(r / 255.0, g / 255.0, b / 255.0);
                        const hsl = {}; color.getHSL(hsl);
                        color.setHSL(hsl.h, Math.min(1.0, hsl.s * 1.7), Math.min(0.60, hsl.l * 1.2));
                        colors.push(color.r, color.g, color.b);
                        sizes.push((Math.random() * 0.5 + 0.7) * BASE_PARTICLE_SIZE);
                    }
                }
            }
            geometry = new THREE.BufferGeometry();
            geometry.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
            geometry.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));
            geometry.setAttribute('size', new THREE.Float32BufferAttribute(sizes, 1));
            geometry.userData = { origins: new Float32Array(positions), targets, initialSizes: new Float32Array(sizes) };

            particles = new THREE.Points(geometry, new THREE.PointsMaterial({
                size: BASE_PARTICLE_SIZE, map: glowTexture, vertexColors: true, transparent: true, opacity: 1.0, depthWrite: false, blending: THREE.AdditiveBlending, sizeAttenuation: true
            }));
            scene.add(particles);

            const auraColors = new Float32Array(colors.length);
            const tempC = new THREE.Color();
            for (let i = 0; i < colors.length; i += 3) {
                tempC.setRGB(colors[i], colors[i + 1], colors[i + 2]);
                const h = {}; tempC.getHSL(h); tempC.setHSL(h.h, h.s, h.l * AURA_LIGHTNESS_SCALE);
                auraColors[i] = tempC.r; auraColors[i+1] = tempC.g; auraColors[i+2] = tempC.b;
            }
            auraParticles = new THREE.Points(geometry.clone(), new THREE.PointsMaterial({
                size: BASE_PARTICLE_SIZE * AURA_SIZE_MULTIPLIER, map: glowTexture, vertexColors: true, transparent: true, opacity: AURA_OPACITY_BASE, depthWrite: false, blending: THREE.AdditiveBlending, sizeAttenuation: true
            }));
            auraParticles.geometry.setAttribute('color', new THREE.Float32BufferAttribute(auraColors, 3));
            auraParticles.position.z = -5;
            scene.add(auraParticles);
        };
        const blob = new Blob([SVG_XML], {type: 'image/svg+xml'});
        image.src = URL.createObjectURL(blob);
    }

    // --- ANIMATION LOOP ---
    let currentScroll = 0, targetScroll = 0;

    if (particlesEnabled) {
    window.addEventListener('scroll', () => {
        targetScroll = Math.min(1, Math.max(0, window.scrollY / window.innerHeight));
    }, { passive: true });
    }

    function animate(time) {
        requestAnimationFrame(animate);

        // 背景描画（lWidth, lHeightが有効な場合のみ）
        if (lWidth > 0 && lHeight > 0) {
            lctx.globalCompositeOperation = 'source-over';
            lctx.fillStyle = '#000000';
            lctx.fillRect(0, 0, lWidth, lHeight);
            lctx.globalCompositeOperation = 'lighter';
            lParticles.forEach(p => {
                p.update();
                p.draw();
            });
        }

        // パーティクル更新 (containerがある場合のみ)
        if (particlesEnabled && particles && geometry) {
            currentScroll += (targetScroll - currentScroll) * 0.08;
            const posAttr = geometry.attributes.position;
            const sizeAttr = geometry.attributes.size;
            const { origins, targets, initialSizes } = geometry.userData;

            particles.rotation.y += 0.001 * (1 - currentScroll);
            particles.rotation.z += 0.0005 * (1 - currentScroll);
            if (auraParticles) auraParticles.rotation.copy(particles.rotation);

            const pulse = Math.sin(time * 0.0015) * 0.1 + 1.0;
            for (let i = 0; i < targets.length; i++) {
                const ix = i * 3;
                posAttr.array[ix] = origins[ix] + (targets[i].x - origins[ix]) * currentScroll;
                posAttr.array[ix+1] = origins[ix+1] + (targets[i].y - origins[ix+1]) * currentScroll;
                posAttr.array[ix+2] = origins[ix+2] + (targets[i].z - origins[ix+2]) * currentScroll;
                sizeAttr.array[i] = initialSizes[i] * pulse;
            }
            posAttr.needsUpdate = true; sizeAttr.needsUpdate = true;

            const fadeStart = window.innerHeight * 0.3;
            const fadeDistance = window.innerHeight * 0.7;
            let opacity = 1.0;
            if (window.scrollY > fadeStart) {
                opacity = 1.0 - (Math.min(1, (window.scrollY - fadeStart) / fadeDistance));
            }
            particles.material.opacity = opacity;
            if (auraParticles) auraParticles.material.opacity = opacity * AURA_OPACITY_BASE;

            if (composer) composer.render();
            else renderer.render(scene, camera);
        } else if (particlesEnabled && renderer) {
             renderer.render(scene, camera);
        }
    }

    // リサイズ処理
    window.addEventListener('resize', () => {
        handleLiqResize();

        if (particlesEnabled && renderer) {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
            if (composer) composer.setSize(window.innerWidth, window.innerHeight);
        }
    });

    // 初回実行
    initLiquid();
    animate(0);
};