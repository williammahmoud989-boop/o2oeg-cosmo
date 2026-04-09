<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>O2O EG - منصة الجمال الذكية لعام 2026</title>
    
    <!-- Design System & Modular Styles -->
    <link rel="stylesheet" href="/css/design.css">
    <link rel="stylesheet" href="/css/feature-grid.css">
    <link rel="stylesheet" href="/css/modal.css">
    <link rel="stylesheet" href="/css/ai-consultant.css">
</head>
<body style="background-color: #050505; color: white;">
    <div class="grain-overlay"></div>
    <div id="custom-cursor"></div>
    <div class="mesh-bg"></div>

    <nav class="glass" style="position: sticky; top: 0; z-index: 1000; padding: 1rem 0;">
        <div class="container" style="margin: 0 auto; max-width: 1250px; width: 100%;" style="display: flex; justify-content: space-between; align-items: center;">
            <a href="/" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: white; font-weight: 900; font-size: clamp(1.2rem, 4vw, 1.8rem);">
                <img src="/images/logo-new.png" alt="O2O EG" style="height: clamp(35px, 6vw, 50px); border-radius: 10px;">
                <span>O2O EG</span>
            </a>
            <div class="nav-links" style="display: flex; gap: clamp(1rem, 3vw, 2.5rem); align-items: center;">
                <a href="#services" class="mobile-hide" style="text-decoration: none; color: #94A3B8; font-weight: 600;">خدماتنا</a>
                <a href="#ai-consultant" class="mobile-hide" style="text-decoration: none; color: #94A3B8; font-weight: 600;">خبير AI</a>
                <a href="/admin/login" class="btn btn-orange" style="padding: 0.6rem 1.5rem; border-radius: 50px;">دخول</a>
            </div>
       <main>
        <!-- Hero Section - FIXED STRUCTURE -->
        <section class="hero" style="padding: clamp(5rem, 15vw, 10rem) 0; text-align: center; position: relative; z-index: 5;">
            <div class="container" style="margin: 0 auto; max-width: 1250px; width: 100%;">
                <span style="color: #FF5C00; font-weight: 800; text-transform: uppercase; letter-spacing: 2px;">مستقبل صالونات التجميل</span>
                <h1 style="font-size: clamp(2.2rem, 8vw, 4.5rem); font-weight: 900; line-height: 1.1; margin: 1.5rem 0;">التحول الرقمي لقطاع الجمال: <span style="color: #FF5C00;">O2O EG</span></h1>
                <p style="font-size: clamp(1rem, 3vw, 1.4rem); color: #94A3B8; max-width: 800px; margin: 0 auto 3rem; line-height: 1.8;">
                    الجسر المتكامل بين أرقى صالونات التجميل في مصر وعملائها. منظومة إدارية ذكية وقوة بيع رقمية في منصة واحدة تعتمد على تقنيات 2026.
                </p>
                <div style="display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap;">
                    <a href="/admin/login" class="btn btn-orange">سجل صالونك الآن</a>
                    <a href="#ai-consultant" class="btn" style="border: 1px solid rgba(255,255,255,0.1); color: white;">تجربة خبير AI</a>
                </div>
            </div>
        </section>

                <!-- AI Consultant Section -->
        <section id="ai-consultant" class="ai-section reveal">
            <div class="container" style="margin: 0 auto; max-width: 1250px; width: 100%;">
                <div class="section-header" style="text-align: center; margin: 0 auto 4rem auto; max-width: 800px;">
                    <span class="section-tag">خبير التجميل الذكي 2026</span>
                    <h2 class="section-title">حللي جمالك بالذكاء الاصطناعي</h2>
                    <p class="modal-subtitle" style="margin: 0 auto; max-width: 750px;">ارفعي صورتك الآن ودعي محرك Gemini يحلل احتياجاتك ويقترح لك الخدمات المثالية.</p>
                </div>

                <div class="ai-scanner-container">
                    <div class="upload-area" id="uploadArea">
                        <div id="uploadPlaceholder">
                            <div class="icon" style="font-size: 4rem; margin-bottom: 1.5rem;">📸</div>
                            <h3>اضغطي هنا لرفع الصورة</h3>
                            <p>أو اسحبي الصورة مباشرة (JPG, PNG)</p>
                        </div>
                        
                        <div id="previewContainer" class="preview-container">
                            <img id="previewImage" src="" alt="Preview">
                            <div id="scannerLine" class="scanner-line"></div>
                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 2rem;">
                        <input type="file" id="imageInput" accept="image/*" style="display: none;">
                        <button id="analyzeBtn" class="btn btn-orange" style="display: none;">
                            ابدأ التحليل الرقمي ⚡
                        </button>
                    </div>

                    <!-- Result Card -->
                    <div id="aiResultCard" class="ai-result-card">
                        <h3 style="color: var(--primary-orange); display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1.5rem;">
                            <span>✨</span> نتيجة التحليل الذكي
                        </h3>
                        <div id="aiAnalysisText" style="font-size: 1.1rem; line-height: 1.8;"></div>
                        
                        <div class="ai-reasoning">
                            <strong>لماذا هذه الخدمات؟</strong>
                            <p id="aiReasoningText" style="margin-top: 0.5rem;"></p>
                        </div>

                        <div class="recommendation-chips" id="recommendationChips">
                            <!-- Chips injected via JS -->
                        </div>

                        <div style="margin-top: 2.5rem; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1.5rem;">
                            <a href="#services" class="btn btn-orange" style="width: 100%; justify-content: center;">حجز الموعد الآن</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Modular Grid - RESTORED -->
        <section id="services" style="padding: clamp(5rem, 15vw, 10rem) 0; background: rgba(255, 92, 0, 0.02); border-top: 1px solid rgba(255,255,255,0.05);">
            <div class="container" style="margin: 0 auto; max-width: 1250px; width: 100%;">
                <div style="text-align: center; margin-bottom: 5rem;">
                    <span style="color: #FF5C00; font-weight: 800; text-transform: uppercase;">منظومة القوة الذكية</span>
                    <h2 style="font-size: clamp(1.8rem, 5vw, 2.8rem); font-weight: 900; margin-top: 1.5rem; color: white;">محركات النمو الرقمي</h2>
                    <p style="color: #94A3B8; margin-top: 1rem;">اضغط على أي موديول لاستكشاف المواصفات التقنية الكاملة.</p>
                </div>

                <div class="module-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2.5rem;">
                    <div class="module-card glass" onclick="openModule('finance')" style="padding: 2.5rem; border-radius: 2.5rem; cursor: pointer;">
                        <div style="font-size: 2.5rem; margin-bottom: 1rem;">💰</div>
                        <h3 style="color: white; margin-bottom: 0.75rem;">المالية والمحاسبة</h3>
                        <p style="color: #94A3B8;">إدارة التدفقات النقدية، فواتير POS، وتقارير الربحية اللحظية بدقة متناهية.</p>
                        <div style="margin-top: 1.5rem; color: #FF5C00; font-weight: 700;">التفاصيل ⬅️</div>
                    </div>

                    <div class="module-card glass" onclick="openModule('hr')" style="padding: 2.5rem; border-radius: 2.5rem; cursor: pointer;">
                        <div style="font-size: 2.5rem; margin-bottom: 1rem;">👔</div>
                        <h3 style="color: white; margin-bottom: 0.75rem;">الموارد البشرية</h3>
                        <p style="color: #94A3B8;">الحضور بالبصمة الحيوية، حساب العمولات آلياً، وجدولة الورديات الذكية.</p>
                        <div style="margin-top: 1.5rem; color: #FF5C00; font-weight: 700;">التفاصيل ⬅️</div>
                    </div>

                    <!-- Marketing Card -->
                    <div class="module-card glass" onclick="openModule('marketing')" style="padding: 3rem; border-radius: 2.5rem; transition: all 0.4s ease; cursor: pointer;">
                        <div style="font-size: 3rem; margin-bottom: 1.5rem;">🚀</div>
                        <h3 style="font-size: 1.8rem; margin-bottom: 1rem; color: white;">التسويق و CRM</h3>
                        <p style="color: #94A3B8; line-height: 1.7;">أتمتة الواتساب، برامج نقاط الولاء المتطورة، وإدارة حملات الإحالة لضمان عودة العميل.</p>
                        <div style="margin-top: 2rem; color: #FF5C00; font-weight: 700;">التفاصيل ⬅️</div>
                    </div>
                </div>
            </div>
        </section>
�لمعقدة آلياً، وجدولة الورديات الذكية لضمان كفاءة الطاقم.</p>
                        <div style="margin-top: 2rem; color: #FF5C00; font-weight: 700;">استكشف المواصفات ⬅️</div>
                    </div>

                    <!-- Marketing Card -->
                    <!-- Affiliate Card -->
                    <div class="module-card glass" onclick="openModule('affiliate')">
                        <span class="icon">🤝</span>
                        <h3>الأفيليت والشركاء</h3>
                        <p>نظام تتبع الروابط للمؤثرين، صرف عمولات آلي، وتوسيع سوق الجمال رقمياً.</p>
                        <span class="learn-more">استكشف التفاصيل التقنية ⬅️</span>
                    </div>

                    <!-- Inventory Card -->
                    <div class="module-card glass" onclick="openModule('inventory')">
                        <span class="icon">📦</span>
                        <h3>المخزون والمشتريات</h3>
                        <p>تتبع المنتجات بالباركود، تنبيهات النفاذ، وإدارة الموردين في جميع الأفرع.</p>
                        <span class="learn-more">استكشف التفاصيل التقنية ⬅️</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Vision Section -->
        <section id="about" style="padding: clamp(5rem, 15vw, 10rem) 0;">
            <div class="container vision-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: clamp(2rem, 10vw, 6rem); align-items: center;">
                <div>
                    <span class="section-tag">رؤية O2O EG</span>
                    <h2 class="section-title">تمكين عصر الجمال الرقمي</h2>
                    <p style="color: var(--text-dim); font-size: clamp(1rem, 2.5vw, 1.25rem); line-height: 1.8;">
                        نحن لا نبيع برنامجاً، نحن نبني مستقبلاً. في O2O EG، ندمج أرقى خبرات التجميل مع أحدث ما توصل إليه العلم في معالجة البيانات والذكاء الاصطناعي لنخلق تجربة تفوق التوقعات.
                    </p>
                </div>
                <div style="position: relative;">
                    <img src="/images/hero.png" alt="O2O Vision" style="width: 100%; border-radius: clamp(1.5rem, 5vw, 3rem); border: 1px solid var(--glass-border); box-shadow: 0 40px 80px -20px rgba(0,0,0,0.4);">
                </div>
            </div>
        </section>
    </main>

    <footer style="padding: clamp(3rem, 10vw, 6rem) 0; border-top: 1px solid var(--glass-border); text-align: center;">
        <div class="container" style="margin: 0 auto; max-width: 1250px; width: 100%;">
            <h2 style="font-weight: 900; margin-bottom: 1.5rem; color: white;">O2O EG</h2>
            <p style="color: var(--text-dim); font-size: 0.9rem;">&copy; {{ date('Y') }} O2O EG Cosmo. جميع الحقوق محفوظة.</p>
        </div>
    </footer>

    <!-- Module Detail Modal Container -->
    <div id="moduleModal" class="modal">
        <div class="modal-content glass">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <div id="modalBody">
                <!-- Content loaded via AJAX from /modals/{type}.html -->
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/feature-grid.js') }}"></script>
    <script src="{{ asset('js/modal.js') }}"></script>
    <script src="{{ asset('js/ai-consultant.js') }}?v={{ filemtime(public_path('js/ai-consultant.js')) }}"></script>
    <script>
        // Custom Cursor Movement
        const cursor = document.getElementById('custom-cursor');
        document.addEventListener('mousemove', (e) => {
            cursor.style.left = e.clientX + 'px';
            cursor.style.top = e.clientY + 'px';
        });

        // Toggle cursor visibility
        document.addEventListener('mouseenter', () => cursor.style.opacity = '0.6');
        document.addEventListener('mouseleave', () => cursor.style.opacity = '0');
    </script>
</body>
</html>
