<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f5f7fc">
    <title>Bài tập thực hành Vue 3</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            color: #172033;
            background: #f5f7fc;
            font-synthesis: none;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(ellipse at 12% 12%, rgb(99 102 241 / 8%), transparent 34rem),
                radial-gradient(ellipse at 92% 78%, rgb(14 165 233 / 7%), transparent 30rem),
                #f5f7fc;
        }

        a {
            color: inherit;
        }

        .page {
            width: min(1120px, calc(100% - 40px));
            margin: 0 auto;
            padding: 28px 0 24px;
        }

        .section-heading,
        .course-card,
        .course-link {
            display: flex;
        }

        .section-heading {
            align-items: center;
            justify-content: space-between;
        }

        h1 {
            margin: 0;
            font-size: clamp(28px, 4vw, 38px);
            letter-spacing: -.045em;
            line-height: 1.2;
        }

        .course-section {
            padding: clamp(48px, 12vh, 120px) 0 40px;
        }

        .section-heading {
            gap: 20px;
            margin-bottom: 20px;
        }

        .section-title {
            margin: 0;
            font-size: 22px;
            letter-spacing: -.03em;
        }

        .course-count {
            flex: 0 0 auto;
            padding: 8px 12px;
            border: 1px solid #e3e8f2;
            border-radius: 999px;
            color: #4c5870;
            background: rgb(255 255 255 / 75%);
            font-size: 12px;
            font-weight: 700;
        }

        .course-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .course-card {
            min-height: 225px;
            flex-direction: column;
            padding: 22px;
            border: 1px solid #e6eaf2;
            border-radius: 18px;
            background: rgb(255 255 255 / 88%);
            box-shadow: 0 8px 25px rgb(35 48 78 / 4%);
            transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
        }

        .course-card:hover {
            transform: translateY(-5px);
            border-color: #cdd4ff;
            box-shadow: 0 18px 35px rgb(35 48 78 / 10%);
        }

        .card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .course-icon {
            display: grid;
            width: 48px;
            height: 48px;
            place-items: center;
            border-radius: 14px;
        }

        .icon-indigo {
            color: #4f46e5;
            background: #eef0ff;
        }

        .icon-teal {
            color: #0f8b82;
            background: #e4f8f4;
        }

        .icon-orange {
            color: #d97706;
            background: #fff3df;
        }

        .course-number {
            color: #a0a9ba;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
        }

        .course-card h3 {
            margin: 22px 0 0;
            font-size: 18px;
            letter-spacing: -.025em;
        }

        .course-link {
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: auto;
            padding: 14px 0 0;
            color: #4f46e5;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
        }

        .course-link svg {
            transition: transform 180ms ease;
        }

        .course-link:hover svg {
            transform: translateX(4px);
        }

        .course-link:focus-visible {
            outline: 3px solid #818cf8;
            outline-offset: 4px;
            border-radius: 5px;
        }

        @media (max-width: 800px) {
            .course-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 560px) {
            .page {
                width: min(100% - 28px, 440px);
                padding-top: 18px;
            }

            .course-section {
                padding: 64px 0 34px;
            }

            .course-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .course-card {
                min-height: 190px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="course-section" aria-labelledby="course-heading">
            <div class="section-heading">
                <h1 class="section-title" id="course-heading">Chọn bài tập</h1>
                <span class="course-count">03 bài tập</span>
            </div>

            <div class="course-grid">
                <article class="course-card">
                    <div class="card-top">
                        <span class="course-icon icon-indigo" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M4 5.5h16v13H4v-13Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="M4 9.5h16M9 9.5v9M6.5 7.5h.01M9 7.5h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="course-number">BÀI 01</span>
                    </div>
                    <h3>Quản lý sản phẩm</h3>
                    <a class="course-link" href="bai1/index.php">
                        <span>Bắt đầu bài tập</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </article>

                <article class="course-card">
                    <div class="card-top">
                        <span class="course-icon icon-teal" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect x="3.5" y="4" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                                <rect x="13.5" y="4" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                                <rect x="3.5" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                                <rect x="13.5" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </span>
                        <span class="course-number">BÀI 02</span>
                    </div>
                    <h3>Component sản phẩm</h3>
                    <a class="course-link" href="bai2/index.php">
                        <span>Bắt đầu bài tập</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </article>

                <article class="course-card">
                    <div class="card-top">
                        <span class="course-icon icon-orange" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M3.5 4h2l2.2 11.1a2 2 0 0 0 2 1.6h8.5a2 2 0 0 0 1.9-1.4L22 8H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="10" cy="20" r="1.3" fill="currentColor"/>
                                <circle cx="18" cy="20" r="1.3" fill="currentColor"/>
                            </svg>
                        </span>
                        <span class="course-number">BÀI 03</span>
                    </div>
                    <h3>Giỏ hàng</h3>
                    <a class="course-link" href="bai3/index.php">
                        <span>Bắt đầu bài tập</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </article>
            </div>
        </section>

    </main>
</body>
</html>
