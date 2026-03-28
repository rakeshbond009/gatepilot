<?php if ($page == 'login'):
    $system_reactivated = false;
    if (isset($_GET['msg']) && $_GET['msg'] == 'system_inactive' && isset($_GET['tslug'])) {
        $m_conn = getMasterDatabaseConnection();
        if ($m_conn) {
            $slug_safe = mysqli_real_escape_string($m_conn, $_GET['tslug']);
            $res = mysqli_query($m_conn, "SELECT is_active FROM tenants WHERE slug = '$slug_safe'");
            if ($row = mysqli_fetch_assoc($res)) {
                if (($row['is_active'] ?? 1) == 1) {
                    $system_reactivated = true;
                }
            }
        }
    }
    ?>
    <div class="landing-page">
        <?php
        $m_conn = getMasterDatabaseConnection();
        $company_logo = getSetting($m_conn, 'company_logo');
        $logo_src = 'uploads/logo/gatepilot_logo.png'; // Fallback
        if ($company_logo) {
            $logo_src = preg_match('#^https?://#', $company_logo)
                ? $company_logo
                : rtrim(BASE_URL, '/') . '/' . ltrim($company_logo, '/');
        }
        ?>
        <header class="landing-header">
            <a href="https://codepilotx.com/" target="_blank" class="landing-logo">
                <img src="<?php echo htmlspecialchars($logo_src); ?>" alt="Gatepilot Logo"
                    onerror="this.src='https://cdn-icons-png.flaticon.com/512/2800/2800516.png'">
                Gatepilot
            </a>
            <div class="header-actions">
                <button onclick="toggleLoginModal(true)" class="btn btn-primary portal-access-btn"
                    style="padding: 18px 45px; border-radius: 20px; font-weight: 800; font-size: 1.2rem; box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4);">Login</button>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="hero-section">
            <!-- <div class="hero-content"> -->
            <h2 class="hero-title">High-Density Gate Management</h2>
            <p class="hero-subtitle">The enterprise benchmark for automated logistics, high-precision security
                tracking, and industrial facility oversight.</p>

            <div class="video-container"
                style="max-width: 1300px; aspect-ratio: 21/9; border: none; box-shadow: 0 40px 120px -20px rgba(0,0,0,0.7); overflow: hidden;">
                <img src="App_images/gatepilot_hero.png" alt="High-Density Gate Management Hero"
                    style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block;">
            </div>
    </div>
    </section>

    <!-- Mega Feature Blocks -->
    <div class="mega-showcase">

        <!-- 1. Command Center -->
        <div class="showcase-header">
            <h2>Mission Control Dashboard</h2>
            <p>Harness the power of real-time data with our high-density dashboards. Track every metric from vehicle
                occupancy to weekly throughput in one glance.</p>
        </div>
        <div class="mega-image-frame" style="margin-bottom: 80px;">
            <div
                style="background: #fff; padding: 20px; border-radius: 40px; box-shadow: 0 60px 100px -20px rgba(0,0,0,0.15); border: 1px solid #e2e8f0; overflow: hidden;">
                <img src="App_images/Screenshot 2026-03-10 135056.png" alt="Mission Control"
                    style="width: 100%; border-radius: 20px; display: block;">
            </div>
        </div>

        <!-- 2. Smart Logistics -->
        <div class="showcase-header">
            <h2>Next-Gen Inward Logistics</h2>
            <p>Eliminate logistics bottlenecks with high-precision entry forms. Capture document photos, vehicle
                weights, and driver identities with automated field verification.</p>
        </div>
        <div class="side-by-side-grid"
            style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; padding: 0 2%; margin-bottom: 80px;">
            <div
                style="background: #fff; padding: 15px; border-radius: 30px; box-shadow: 0 30px 60px -10px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden;">
                <img src="App_images/Inward Form1.jpg" alt="Inward 1"
                    style="width: 100%; border-radius: 15px; display: block;">
            </div>
            <div
                style="background: #fff; padding: 15px; border-radius: 30px; box-shadow: 0 30px 60px -10px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden;">
                <img src="App_images/Inward Form2.jpg" alt="Inward 2"
                    style="width: 100%; border-radius: 15px; display: block;">
            </div>
        </div>

        <!-- 3. Cargo & Compliance -->
        <div class="showcase-header">
            <h2>Precision Cargo & Logistics</h2>
            <p>Comprehensive workflows for Inward, Outward, Scrap, and Sample movements. Digital checklists ensure 100%
                compliance.</p>
        </div>
        <div class="side-by-side-grid"
            style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; padding: 0 2%; margin-bottom: 80px;">
            <div
                style="background: #fff; padding: 15px; border-radius: 30px; box-shadow: 0 30px 60px -10px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden;">
                <img src="App_images/Vehicle Loading1.jpg" alt="Loading 1"
                    style="width: 100%; border-radius: 15px; display: block;">
            </div>
            <div
                style="background: #fff; padding: 15px; border-radius: 30px; box-shadow: 0 30px 60px -10px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden;">
                <img src="App_images/Vehicle Unloading1.jpg" alt="Unloading 1"
                    style="width: 100%; border-radius: 15px; display: block;">
            </div>
        </div>

        <!-- 4. Digital Infrastructure & Security -->
        <div class="showcase-header">
            <h2>Digital Infrastructure & Security</h2>
            <p>Transition to a zero-paper facility with mobile-first security patrol tracking and GPS-verified logs.</p>
        </div>
        <div class="side-by-side-grid"
            style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; padding: 0 5%; margin-bottom: 80px;">
            <div
                style="background: #fff; padding: 15px; border-radius: 25px; box-shadow: 0 30px 60px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
                <p style="font-weight: 800; margin-bottom: 10px; color: #1e293b; text-align: center;">Digitized Guard
                    Patrols</p>
                <img src="App_images/Screenshot 2026-03-10 135021.png" alt="Guard Patrol Hardware"
                    style="width: 100%; border-radius: 15px;">
            </div>
            <div
                style="background: #fff; padding: 15px; border-radius: 25px; box-shadow: 0 30px 60px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
                <p style="font-weight: 800; margin-bottom: 10px; color: #1e293b; text-align: center;">Workforce Access
                    Control</p>
                <img src="App_images/Screenshot 2026-03-10 135039.png" alt="Workforce Access"
                    style="width: 100%; border-radius: 15px;">
            </div>
        </div>

        <!-- 5. Reporting & Intelligence -->
        <div class="showcase-header">
            <h2>Enterprise Reporting & Intelligence</h2>
            <p>Instantly export high-density PDF and Excel reports for stakeholders.</p>
        </div>
        <div class="side-by-side-grid"
            style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; padding: 0 2%; margin-bottom: 80px;">
            <div
                style="background: #fff; padding: 15px; border-radius: 30px; box-shadow: 0 30px 60px -10px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden;">
                <img src="App_images/Reports1.jpg" alt="Report 1" style="width: 100%; border-radius: 15px; display: block;">
            </div>
            <div
                style="background: #fff; padding: 15px; border-radius: 30px; box-shadow: 0 30px 60px -10px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden;">
                <img src="App_images/Reports2.jpg" alt="Report 2" style="width: 100%; border-radius: 15px; display: block;">
            </div>
        </div>

        <!-- 6. Proactive Risk Management (New) -->
        <div class="showcase-header">
            <h2>Proactive Risk Management</h2>
            <p>Stay ahead of compliance risks. Automatically track expiring RC, Fitness, Insurance, and Pollutions
                certificates.</p>
        </div>
        <div class="mega-image-frame" style="margin-bottom: 80px;">
            <div
                style="background: #fff; padding: 20px; border-radius: 40px; box-shadow: 0 60px 100px -20px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden;">
                <img src="App_images/Screenshot 2026-03-10 134959.png" alt="Risk Management"
                    style="width: 100%; border-radius: 20px; display: block;">
            </div>
        </div>
    </div>


    <!-- Technical Specifications -->
    <div class="technical-specs"
        style="padding: 100px 5%; background: #fff; border-radius: 40px; margin-bottom: 80px; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.05);">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 style="font-size: 3rem; font-weight: 950; color: #1e293b; letter-spacing: -2px;">Technical
                Infrastructure</h2>
            <p style="font-size: 1.2rem; color: #64748b; max-width: 700px; margin: 20px auto;">Built for scale,
                security, and enterprise-grade reliability.</p>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 40px;">
            <div style="padding: 30px; border-radius: 20px; background: #f8fafc; border: 1px solid #e2e8f0;">
                <h4 style="color: #4f46e5; margin-bottom: 15px; font-weight: 800;">Core Stack</h4>
                <p style="font-size: 0.95rem; line-height: 1.6; color: #475569;">Enterprise <strong>PHP 7.4</strong>
                    engine with a high-integrity <strong>MySQL</strong> relational database, ensuring zero data loss for
                    millions of records.</p>
            </div>
            <div style="padding: 30px; border-radius: 20px; background: #f8fafc; border: 1px solid #e2e8f0;">
                <h4 style="color: #4f46e5; margin-bottom: 15px; font-weight: 800;">Mobile Framework</h4>
                <p style="font-size: 0.95rem; line-height: 1.6; color: #475569;"><strong>Flutter-powered Hybrid
                        Container</strong> with native binary-level access to Camera and Location hardware for seamless
                    industrial scanning.</p>
            </div>
            <div style="padding: 30px; border-radius: 20px; background: #f8fafc; border: 1px solid #e2e8f0;">
                <h4 style="color: #4f46e5; margin-bottom: 15px; font-weight: 800;">QR Logic</h4>
                <p style="font-size: 0.95rem; line-height: 1.6; color: #475569;">Deep-integrated <strong>ZXing
                        Logic</strong> capable of parsing complex <strong>E-Invoice QR</strong> data into automated
                    vehicle entry records in < 500ms.</p>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="faq-section" style="padding: 50px 5% 100px; max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 2.5rem; font-weight: 900; text-align: center; margin-bottom: 50px; color: #1e293b;">
            Security & Integration FAQ</h2>
        <div class="faq-item" style="margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px;">
            <h4 style="font-weight: 800; cursor: pointer; color: #1e293b;"
                onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'">
                How does the automated Inward work?</h4>
            <p style="display: none; padding-top: 15px; color: #64748b; line-height: 1.7;">The app scans the
                <strong>Government E-Invoice QR</strong> or Vehicle Number Plate. It instantly fetches historical trips,
                driver licenses, and registration validity, allowing security to complete a 10-field form in one click.
            </p>
        </div>
        <div class="faq-item" style="margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px;">
            <h4 style="font-weight: 800; cursor: pointer; color: #1e293b;"
                onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'">
                Can we track the loading/unloading status?</h4>
            <p style="display: none; padding-top: 15px; color: #64748b; line-height: 1.7;">Yes. Gatepilot forces a
                <strong>Compliance Checklist</strong> for every vehicle. Loading and unloading teams must verify
                physical parameters and capture photos, which are then synced with the master Inward entry for full
                audit trails.
            </p>
        </div>
        <div class="faq-item" style="margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px;">
            <h4 style="font-weight: 800; cursor: pointer; color: #1e293b;"
                onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'">
                Is the Guard Patrol live?</h4>
            <p style="display: none; padding-top: 15px; color: #64748b; line-height: 1.7;">Yes. Security guards scan
                <strong>Physical Checkpoints</strong> throughout the facility. If a checkpoint is missed or a ticket is
                raised (e.g., "Fire hazard found"), the management dashboard alerts administrators in real-time.
            </p>
        </div>
    </div>
    </div>

    <footer style="padding: 100px 5% 50px; background: #f9fafb; color: #1e293b; border-top: 1px solid #e2e8f0;">
        <div class="footer-grid"
            style="max-width:1300px; margin:0 auto; display:grid; grid-template-columns: 2.5fr 1fr 1fr 1.5fr; gap:60px; text-align:left;">
            <div>
                <a href="https://codepilotx.com/" target="_blank" style="text-decoration: none;">
                    <img src="<?php echo htmlspecialchars($logo_src); ?>" alt="Logo"
                        style="height: 120px; margin-bottom: 30px; object-fit: contain;"
                        onerror="this.src='https://cdn-icons-png.flaticon.com/512/2800/2800516.png'">
                </a>
                <p style="color: #64748b; line-height: 1.7; font-size: 1.05rem; font-weight: 500;">Gatepilot by <a
                        href="https://codepilotx.com/" target="_blank"
                        style="color: #4f46e5; text-decoration: none; font-weight: 700;">CodePilotX</a> is a premium
                    enterprise
                    suite designed to streamline industrial gate operations. We provide high-security oversight and
                    real-time logistics intelligence for modern factories.</p>
            </div>
            <div>
                <h4
                    style="color:#1e293b; margin-bottom:25px; font-size:1.1rem; text-transform:uppercase; letter-spacing:1.5px; font-weight: 800;">
                    Platform</h4>
                <ul style="list-style:none; padding:0; color:#64748b; font-size:1rem; font-weight: 600;">
                    <li style="margin-bottom:12px;"><a href="#" style="color:inherit; text-decoration:none;">Dashboard</a>
                    </li>
                    <li style="margin-bottom:12px;"><a href="#" style="color:inherit; text-decoration:none;">Security
                            Patrol</a></li>
                    <li style="margin-bottom:12px;"><a href="#"
                            style="color:inherit; text-decoration:none;">Inward/Outward</a></li>
                </ul>
            </div>
            <div>
                <h4
                    style="color:#1e293b; margin-bottom:25px; font-size:1.1rem; text-transform:uppercase; letter-spacing:1.5px; font-weight: 800;">
                    Resources</h4>
                <ul style="list-style:none; padding:0; color:#64748b; font-size:1rem; font-weight: 600;">
                    <li style="margin-bottom:12px;"><a href="#" style="color:inherit; text-decoration:none;">Mobile
                            App</a></li>
                    <li style="margin-bottom:12px;"><a href="?page=privacy"
                            style="color:inherit; text-decoration:none;">Privacy Policy</a></li>
                    <li style="margin-bottom:12px;"><a href="?page=terms" style="color:inherit; text-decoration:none;">Terms
                            of Use</a></li>
                </ul>
            </div>
            <div>
                <h4
                    style="color:#1e293b; margin-bottom:25px; font-size:1.1rem; text-transform:uppercase; letter-spacing:1.5px; font-weight: 800;">
                    Status</h4>
                <div
                    style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 15px 25px; border-radius: 12px; display: inline-flex; align-items: center; gap: 10px; font-weight: 800; font-size:0.95rem; border: 1px solid rgba(16, 185, 129, 0.2);">
                    <span
                        style="width: 10px; height: 10px; background: #10b981; border-radius: 50%; box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);"></span>
                    Operational
                </div>
            </div>
        </div>
        <div
            style="max-width:1300px; margin:60px auto 0; border-top: 2px solid #f1f5f9; padding-top: 30px; text-align: center;">
            <p style="font-size: 0.95rem; color: #94a3b8; font-weight: 600;">&copy; <?php echo date('Y'); ?> <a
                    href="https://codepilotx.com/" target="_blank" style="color: inherit; text-decoration: none;">Gatepilot
                    · A CodePilotx Architecture. All Rights
                    Reserved.</a></p>
        </div>
    </footer>

    <!-- LOGIN MODAL -->
    <div id="loginModal" class="login-overlay <?php echo isset($error) ? 'active' : ''; ?>">
        <canvas id="login-particles"></canvas>
        <div class="login-modal" id="login-card-tilt">
            <span class="close-modal" onclick="toggleLoginModal(false)">&times;</span>

            <!-- Left: Visual/Highlights -->
            <div class="login-visual">
                <div class="platform-badge">
                    <span style="width:10px; height:10px; background:#10b981; border-radius:50%;"></span>
                    Enterprise Secure
                </div>
                <h3>Digital Gate Management Platform</h3>
                <p style="font-size: 1.2rem; opacity: 0.8; line-height: 1.6; margin-bottom: 40px;">Orchestrate your
                    entire facility's inward and outward logistics with high-precision tracking.</p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div
                        style="background: rgba(255, 255, 255, 0.05); padding: 20px; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.1);">
                        <strong style="display: block; font-size: 1.5rem;">2.4M</strong>
                        <span style="font-size: 0.8rem; opacity: 0.6; text-transform: uppercase;">Movements</span>
                    </div>
                    <div
                        style="background: rgba(255, 255, 255, 0.05); padding: 20px; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.1);">
                        <strong style="display: block; font-size: 1.5rem;">99.9%</strong>
                        <span style="font-size: 0.8rem; opacity: 0.6; text-transform: uppercase;">Uptime</span>
                    </div>
                </div>
            </div>

            <!-- Right: Form -->
            <div class="login-form-side">
                <img src="<?php echo htmlspecialchars($logo_src); ?>" alt="Gatepilot Logo"
                    style="height: 95px; margin-bottom: 30px; object-fit: contain;"
                    onerror="this.src='https://cdn-icons-png.flaticon.com/512/2800/2800516.png'">
                <h2 style="font-size: 2rem; font-weight: 950; margin-bottom: 10px; letter-spacing: -1px; color: #1e293b;">
                    Login</h2>
                <p style="font-size: 1.05rem; margin-bottom: 40px; color: #64748b;">Enter your credentials to manage
                    operations.</p>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error"
                        style="margin-bottom: 30px; border-radius: 15px; padding: 15px; font-weight: 600; font-size:0.95rem; background:rgba(239, 68, 68, 0.1); color:#ef4444; border:1px solid rgba(239, 68, 68, 0.2);">
                        <?php echo $error; ?>
                    </div>
                    <?php
                endif; ?>

                <form method="POST">
                    <div class="form-group" style="text-align: left; margin-bottom: 25px;">
                        <label
                            style="font-weight: 800; color: #1e293b; font-size: 0.9rem; margin-bottom: 10px; display: block;">Company
                            Code</label>
                        <input type="text" name="tenant_slug" required class="form-input" style="width:100%;"
                            placeholder="tata">
                    </div>
                    <div class="form-group" style="text-align: left; margin-bottom: 25px;">
                        <label
                            style="font-weight: 800; color: #1e293b; font-size: 0.9rem; margin-bottom: 10px; display: block;">Username</label>
                        <input type="text" name="username" required class="form-input" style="width:100%;"
                            placeholder="administrator">
                    </div>
                    <div class="form-group" style="text-align: left; margin-bottom: 35px;">
                        <label
                            style="font-weight: 800; color: #1e293b; font-size: 0.9rem; margin-bottom: 10px; display: block;">Password</label>
                        <input type="password" name="password" required class="form-input" style="width:100%;"
                            placeholder="••••••••">
                    </div>
                    <div class="form-group"
                        style="text-align: left; margin-bottom: 30px; display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="remember" id="remember_me" checked
                            style="width: 20px; height: 20px; cursor: pointer; accent-color: #4f46e5;">
                        <label for="remember_me"
                            style="font-weight: 700; color: #64748b; font-size: 0.95rem; cursor: pointer;">Keep me signed in
                            forever</label>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary"
                        style="width: 100%; padding: 20px; border-radius: 18px; font-weight: 900; font-size: 1.1rem; box-shadow: 0 15px 30px rgba(79, 70, 229, 0.3); border: none; cursor: pointer;">
                        SECURE SIGN IN
                    </button>
                </form>
            </div>
        </div>
    </div>
    </div>

    <script>


        function moveMegaSlider(trackId, index) {
            const track = document.getElementById(trackId);
            const dots = track.parentElement.querySelectorAll('.mega-dot');
            track.style.transform = `translateX(-${index * 100}%)`;
            dots.forEach((dot, i) => dot.classList.toggle('active', i === index));
        }

        function toggleLoginModal(show) {
            const modal = document.getElementById('loginModal');
            if (show) {
                modal.style.display = 'flex';
                setTimeout(() => modal.classList.add('active'), 10);
            } else {
                modal.classList.remove('active');
                setTimeout(() => modal.style.display = 'none', 400);
            }
        }

        window.onclick = function (event) {
            if (event.target == document.getElementById('loginModal')) toggleLoginModal(false);
        }

        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('./service-worker.js')
                    .then(reg => console.log('Service Worker registered'))
                    .catch(err => console.log('Service Worker failed', err));
            });
        }

        // High-End Login Effects: Particles & 3D Tilt
        const canvas = document.getElementById('login-particles');
        const ctx = canvas.getContext('2d');
        let particles = [];

        function initParticles() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            particles = [];
            for (let i = 0; i < 50; i++) {
                particles.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    vx: (Math.random() - 0.5) * 0.5,
                    vy: (Math.random() - 0.5) * 0.5,
                    size: Math.random() * 2 + 1
                });
            }
        }

        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = 'rgba(255, 255, 255, 0.2)';
            particles.forEach(p => {
                p.x += p.vx;
                p.y += p.vy;
                if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
                if (p.y < 0 || p.y > canvas.height) p.vy *= -1;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                ctx.fill();
            });
            requestAnimationFrame(animateParticles);
        }

        initParticles();
        animateParticles();
        window.addEventListener('resize', initParticles);

        // 3D Tilt Effect for Login Card
        const loginCard = document.getElementById('login-card-tilt');
        if (loginCard) {
            loginCard.addEventListener('mousemove', (e) => {
                const rect = loginCard.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = (y - centerY) / 25;
                const rotateY = (centerX - x) / 25;
                loginCard.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.02)`;
            });

            loginCard.addEventListener('mouseleave', () => {
                loginCard.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
            });
        }

        // Handle System Inactive Message
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const isReactivated = <?php echo $system_reactivated ? 'true' : 'false'; ?>;

            if (urlParams.get('msg') === 'system_inactive') {
                if (isReactivated) {
                    // System is back up! Clear the URL parameters silently
                    const newUrl = window.location.href.split('?')[0] + '?page=login';
                    window.history.replaceState({}, document.title, newUrl);

                    // Optionally show a very subtle success notice instead of a big popup
                    // or just do nothing as per user request to keep landing page clean.
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '🚫 System Inactive',
                        text: 'Your company account has been deactivated. Please contact your system administrator or platform owner for assistance.',
                        icon: 'error',
                        confirmButtonColor: '#4f46e5',
                        confirmButtonText: 'Understood'
                    });
                } else {
                    alert('System Inactive: Your company account has been deactivated.');
                }
            }
        });
    </script>

    <?php
endif; ?>