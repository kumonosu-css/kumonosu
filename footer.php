</main><!--l-main-->

<footer class="l-footer footer">
    <?php if ( is_front_page() || is_home() ) : ?>
    <?php else : ?>
    <!-- ▼ 下層ページの場合：パンくずリストを表示 ▼ -->
    <div class="l-navi-breadcrumb">
        <?php
            // functions.phpで作った関数、またはプラグインのコード
            if ( function_exists('my_custom_breadcrumb') ) {
                my_custom_breadcrumb();
            }
        ?>
    </div>
    <?php endif; ?>

     <div class="l-footer-inner">

        <!-- 左：プロフィール -->
        <div class="l-footer-profile">

        <div class="c-footer-logo">
            <a class="c-logo" href="<?php echo esc_url(home_url('/')); ?>">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/logo.svg" alt="<?php bloginfo(); ?>">
            </a>
        </div>

        <div class="c-footer-profile">
            <p class="c-footer-profile-name">ジマ</p>
            <p class="c-footer-profile-job">WEBデザイナー・ディレクター</p>
            <p class="c-footer-profile-desc">
            趣味で CSS / JS を使った表現をまとめるサイト「KUMONOSU」を運営しています。<br>
            日々気になったアニメーションやインタラクションを試しながら、見て楽しく、コピペしてすぐ使える形でまとめています。<br>
            デザインから実装、更新まで一人で手掛けながら、誰かの「こんな表現を作ってみたい」に繋がるサイトを目指しています。<br>
            スパイダーマンが好きで、その世界観や色使いからもよくインスピレーションをもらっています。
            </p>
        </div>

        <ul class="c-footer-social">
            <li><a href="https://youtube.com/@kumonosu-css" target="_blank" aria-label="YouTube"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_youtube.svg" alt="YouTube"></a></li>
            <li><a href="https://www.instagram.com/kumonosu.css" target="_blank" aria-label="Instagram"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_instagram.svg" alt="Instagram"></a></li>
            <li><a href="https://www.tiktok.com/@kumonosu25" target="_blank" aria-label="TikTok"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_tiktok.svg" alt="TikTok"></a></li>
            <li><a href="https://x.com/kumonosucss" target="_blank" aria-label="X"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/common/icon_x.svg" alt="X"></a></li>
        </ul>

        </div>

        <!-- 右：リンク -->
        <div class="l-footer-nav">
            <nav class="c-footer-subnav">
                <ul>
                    <li><a href="/">HOME</a></li>
                    <li><a href="/css/">CSS</a></li>
                    <li><a href="/blog/">BLOG</a></li>
                    <li><a href="/contact/">CONTACT</a></li>
                </ul>
            </nav>
            <div class="c-footer-links">
                <ul>
                    <li><a href="/about/">ABOUT</a></li>
                    <li><a href="/privacy-policy/">PRIVACY POLICY</a></li>
                    <li><a href="/sitemap/">SITEMAP</a></li>
                </ul>
            </div>
        </div>
    </div>
  <p class="c-footer-copyright">
    このホームページに記載されている一切の文言・図版・写真を、手段や形態を問わず、複製、転載することを禁じます。<br>
    © KUMONOSU
  </p>


</footer>
</div>
<?php if ( is_home() || is_front_page() ) : ?>
<!-- Load Three.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<!-- Load Three.js Post-Processing Extensions for Bloom -->
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/postprocessing/EffectComposer.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/postprocessing/RenderPass.js"></script>
<!-- Dependencies for Unreal Bloom Pass -->
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/shaders/LuminosityShader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/shaders/LuminosityHighPassShader.js"></script>
<!-- Core Bloom Components -->
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/shaders/CopyShader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/postprocessing/ShaderPass.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/postprocessing/UnrealBloomPass.js"></script>
<?php endif; ?>
<?php if ( !is_home() || !is_front_page() ) : ?>
<!-- Load Three.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<?php endif; ?>
<?php if( is_404() ): ?>
<!-- Three.js 関連ライブラリ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/postprocessing/EffectComposer.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/postprocessing/RenderPass.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/postprocessing/ShaderPass.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/shaders/CopyShader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/shaders/LuminosityHighPassShader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/postprocessing/UnrealBloomPass.js"></script>

<script>
    (function() {
        const PARTICLE_COUNT = 7500;
        const TEXT_TO_SHOW = "404";
        const FONT_SIZE = 120; // 文字を少し大きく

        let scene, camera, renderer, composer;
        let particles, geometry;
        let positions = [], targets = [], colors = [], baseColors = [];

        let glitchIntensity = 1;
        let nextGlitchTime = 0;

        function createTextPoints() {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            // 文字のベース解像度を定義
            canvas.width = 600;
            canvas.height = 300; 
            ctx.fillStyle = "#fff";
            ctx.font = `bold ${FONT_SIZE}px 'Share Tech Mono'`;
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText(TEXT_TO_SHOW, canvas.width / 2, canvas.height / 2);

            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
            const points = [];
            for (let y = 0; y < canvas.height; y += 2) {
                for (let x = 0; x < canvas.width; x += 2) {
                    if (imageData[(x + y * canvas.width) * 4 + 3] > 128) {
                        points.push({
                            x: (x - canvas.width / 2) * 2.5,
                            y: -(y - canvas.height / 2) * 2.5,
                            z: 0
                        });
                    }
                }
            }
            return points;
        }

        function init() {
            const container = document.getElementById('kumonosu404-canvas-container');
            if (!container) return;

            // コンテナの現在のサイズを取得
            const width = container.clientWidth;
            const height = container.clientHeight;

            scene = new THREE.Scene();
            
            // カメラ設定：高さを基準に視野角を調整
            camera = new THREE.PerspectiveCamera(75, width / height, 1, 3000);
            // 404の高さ(300*2.5=750)が画面に収まるようにカメラ距離を自動調整
            camera.position.z = 550; 

            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setSize(width, height);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            renderer.setClearColor(0x000000, 0); // 背景を透明に
            container.appendChild(renderer.domElement);

            composer = new THREE.EffectComposer(renderer);
            // 透明度を維持するためのRGBA設定
            const renderPass = new THREE.RenderPass(scene, camera);
            composer.addPass(renderPass);
            
            const bloomPass = new THREE.UnrealBloomPass(new THREE.Vector2(width, height), 1.0, 0.5, 0.1);
            composer.addPass(bloomPass);

            const points = createTextPoints();
            geometry = new THREE.BufferGeometry();

            for (let i = 0; i < PARTICLE_COUNT; i++) {
                const pt = points[i % points.length];
                positions.push(pt.x, pt.y, pt.z);
                targets.push(pt.x, pt.y, pt.z);

                const rand = Math.random();
                let r, g, b;
                if (rand > 0.9) { r = 1; g = 0.1; b = 0.4; } 
                else if (rand > 0.8) { r = 0.1; g = 0.9; b = 1; } 
                else { r = 0.95; g = 0.95; b = 1; }

                colors.push(r, g, b);
                baseColors.push(r, g, b);
            }

            geometry.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
            geometry.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));

            const material = new THREE.PointsMaterial({
                size: 2.5,
                vertexColors: true,
                transparent: true,
                opacity: 0.9,
                blending: THREE.NormalBlending // 背景色が何色でも見えるようにNormalBlending
            });

            particles = new THREE.Points(geometry, material);
            scene.add(particles);

            animate();
        }

        function animate() {
            requestAnimationFrame(animate);
            const time = performance.now();
            const posAttr = geometry.attributes.position;
            const colAttr = geometry.attributes.color;

            if (time > nextGlitchTime) {
                glitchIntensity = Math.random() > 0.8 ? 15 + Math.random() * 20 : 0.5 + Math.random() * 3;
                nextGlitchTime = time + (Math.random() * 400 + 50);
            }

            const isSliceActive = Math.random() > 0.96;
            const sliceY = (Math.random() - 0.5) * 200;
            const sliceHeight = Math.random() * 25;
            const sliceOffset = (Math.random() - 0.5) * 100;

            for (let i = 0; i < PARTICLE_COUNT; i++) {
                const ix = i * 3;
                const iy = i * 3 + 1;
                const tx = targets[ix];
                const ty = targets[iy];

                let ox = (Math.random() - 0.5) * glitchIntensity;
                let oy = (Math.random() - 0.5) * glitchIntensity;

                if (isSliceActive && ty > sliceY && ty < sliceY + sliceHeight) {
                    ox += sliceOffset;
                }

                if (Math.random() > 0.9998) {
                    ox += (Math.random() - 0.5) * 400;
                    oy += (Math.random() - 0.5) * 100;
                }

                posAttr.array[ix] = tx + ox;
                posAttr.array[iy] = ty + oy;

                if (Math.random() > 0.992) {
                    colAttr.array[ix] = Math.random();
                    colAttr.array[ix+1] = Math.random();
                    colAttr.array[ix+2] = 1;
                } else {
                    colAttr.array[ix] = baseColors[ix];
                    colAttr.array[ix+1] = baseColors[ix+1];
                    colAttr.array[ix+2] = baseColors[ix+2];
                }
            }

            posAttr.needsUpdate = true;
            colAttr.needsUpdate = true;
            composer.render();
        }

        // リサイズ処理もコンテナ基準に変更
        window.addEventListener('resize', () => {
            const container = document.getElementById('kumonosu404-canvas-container');
            if (!container || !camera || !renderer || !composer) return;
            
            const width = container.clientWidth;
            const height = container.clientHeight;
            
            camera.aspect = width / height;
            camera.updateProjectionMatrix();
            renderer.setSize(width, height);
            composer.setSize(width, height);
        });

        document.fonts.ready.then(init);
    })();
</script>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>