<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialHub - Logo Showcase</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            color: #1a365d;
            margin-bottom: 50px;
            font-size: 2.5rem;
        }

        .section {
            margin-bottom: 60px;
        }

        .section-title {
            font-size: 1.8rem;
            color: #2d3748;
            margin-bottom: 30px;
            padding-left: 10px;
            border-left: 4px solid #2563eb;
        }

        /* Grid layouts */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .grid-4 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .card-dark {
            background: #1a202c;
            color: white;
        }

        .card-label {
            margin-top: 20px;
            font-size: 0.9rem;
            color: #718096;
            text-align: center;
        }

        .card-dark .card-label {
            color: #cbd5e0;
        }

        /* Icon and logo display */
        svg {
            max-width: 100%;
            height: auto;
        }

        .icon-display {
            width: 180px;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-display {
            width: 100%;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .app-icon {
            width: 120px;
            height: 120px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .typography-section {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .font-showcase {
            margin: 30px 0;
        }

        .font-showcase h3 {
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .font-sample {
            font-size: 1.2rem;
            color: #4a5568;
            margin: 10px 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
        }

        .color-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .color-box {
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            color: white;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .color-name {
            font-size: 0.85rem;
            margin-top: 10px;
            opacity: 0.9;
        }

        .description {
            color: #718096;
            text-align: center;
            margin-top: 10px;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 1.8rem;
            }

            .icon-display {
                width: 120px;
                height: 120px;
            }

            .grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>SocialHub Logo System</h1>

        <!-- Icon Only -->
        <div class="section">
            <h2 class="section-title">Icon Only</h2>
            <div class="grid-2">
                <!-- Light Version -->
               

                <!-- Dark Version -->
                <div class="card card-dark">
                    <div class="icon-display">
                        <svg viewBox="0 0 200 200" width="180" height="180">
                            <defs>
                                <linearGradient id="whiteGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#ffffff;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#f3f4f6;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            
                            <circle cx="60" cy="70" r="35" fill="url(#whiteGradient)" opacity="0.95"/>
                            <circle cx="140" cy="100" r="40" fill="url(#whiteGradient)" opacity="0.85"/>
                            <circle cx="100" cy="30" r="25" fill="url(#whiteGradient)" opacity="1"/>
                            
                            <line x1="85" y1="50" x2="100" y2="35" stroke="url(#whiteGradient)" stroke-width="3" stroke-linecap="round" opacity="0.6"/>
                            <line x1="90" y1="100" x2="115" y2="90" stroke="url(#whiteGradient)" stroke-width="3" stroke-linecap="round" opacity="0.6"/>
                            
                            <path d="M 35 100 L 25 115 L 40 105 Z" fill="url(#whiteGradient)" opacity="0.95"/>
                            <circle cx="100" cy="80" r="8" fill="#1a202c" opacity="0.2"/>
                        </svg>
                    </div>
                    <p class="card-label">Dark Version</p>
                </div>
            </div>
        </div>

        <!-- Logo with Text -->
        <div class="section">
            <h2 class="section-title">Logo with Text</h2>
            <div class="grid-2">
                <!-- Light Logo -->
                <div class="card">
                    <div class="logo-display">
                        <svg viewBox="0 0 400 200" width="100%">
                            <defs>
                                <linearGradient id="blueGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#2563eb;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#1e40af;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            
                            <!-- Icon -->
                            <circle cx="50" cy="60" r="25" fill="url(#blueGrad)" opacity="0.9"/>
                            <circle cx="100" cy="80" r="28" fill="url(#blueGrad)" opacity="0.8"/>
                            <circle cx="75" cy="25" r="18" fill="url(#blueGrad)" opacity="0.95"/>
                            <line x1="65" y1="42" x2="75" y2="30" stroke="url(#blueGrad)" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <line x1="70" y1="75" x2="88" y2="68" stroke="url(#blueGrad)" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <path d="M 32 85 L 24 98 L 37 88 Z" fill="url(#blueGrad)" opacity="0.9"/>
                            
                            <!-- Text -->
                            <text x="140" y="95" font-family="system-ui, -apple-system, sans-serif" font-size="48" font-weight="700" fill="#1a365d">SocialHub</text>
                        </svg>
                    </div>
                    <p class="card-label">Light Version</p>
                </div>

                <!-- Dark Logo -->
                <div class="card card-dark">
                    <div class="logo-display">
                        <svg viewBox="0 0 400 200" width="100%">
                            <defs>
                                <linearGradient id="whiteGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#ffffff;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#f3f4f6;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            
                            <!-- Icon -->
                            <circle cx="50" cy="60" r="25" fill="url(#whiteGrad)" opacity="0.95"/>
                            <circle cx="100" cy="80" r="28" fill="url(#whiteGrad)" opacity="0.85"/>
                            <circle cx="75" cy="25" r="18" fill="url(#whiteGrad)" opacity="1"/>
                            <line x1="65" y1="42" x2="75" y2="30" stroke="url(#whiteGrad)" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <line x1="70" y1="75" x2="88" y2="68" stroke="url(#whiteGrad)" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <path d="M 32 85 L 24 98 L 37 88 Z" fill="url(#whiteGrad)" opacity="0.95"/>
                            
                            <!-- Text -->
                            <text x="140" y="95" font-family="system-ui, -apple-system, sans-serif" font-size="48" font-weight="700" fill="#ffffff">SocialHub</text>
                        </svg>
                    </div>
                    <p class="card-label">Dark Version</p>
                </div>
            </div>
        </div>

        <!-- App Icon Style -->
        <div class="section">
            <h2 class="section-title">App Icon Styles</h2>
            <div class="grid-4">
                <!-- Rounded Square - Blue -->
                <div class="card">
                    <div class="app-icon" style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); border-radius: 24px;">
                        <svg viewBox="0 0 200 200" width="80" height="80">
                            <circle cx="60" cy="70" r="25" fill="white" opacity="0.9"/>
                            <circle cx="100" cy="85" r="28" fill="white" opacity="0.8"/>
                            <circle cx="80" cy="35" r="18" fill="white" opacity="0.95"/>
                            <line x1="72" y1="48" x2="80" y2="38" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <line x1="75" y1="78" x2="90" y2="72" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <path d="M 38 88 L 30 102 L 43 93 Z" fill="white" opacity="0.9"/>
                        </svg>
                    </div>
                    <p class="card-label">Rounded Square</p>
                </div>

                <!-- Pill Shape - Blue -->
                <div class="card">
                    <div class="app-icon" style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); border-radius: 50px;">
                        <svg viewBox="0 0 200 200" width="80" height="80">
                            <circle cx="60" cy="70" r="25" fill="white" opacity="0.9"/>
                            <circle cx="100" cy="85" r="28" fill="white" opacity="0.8"/>
                            <circle cx="80" cy="35" r="18" fill="white" opacity="0.95"/>
                            <line x1="72" y1="48" x2="80" y2="38" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <line x1="75" y1="78" x2="90" y2="72" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <path d="M 38 88 L 30 102 L 43 93 Z" fill="white" opacity="0.9"/>
                        </svg>
                    </div>
                    <p class="card-label">Pill Shape</p>
                </div>

                <!-- Circle - Blue -->
                <div class="card">
                    <div class="app-icon" style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); border-radius: 50%;">
                        <svg viewBox="0 0 200 200" width="80" height="80">
                            <circle cx="60" cy="70" r="25" fill="white" opacity="0.9"/>
                            <circle cx="100" cy="85" r="28" fill="white" opacity="0.8"/>
                            <circle cx="80" cy="35" r="18" fill="white" opacity="0.95"/>
                            <line x1="72" y1="48" x2="80" y2="38" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <line x1="75" y1="78" x2="90" y2="72" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <path d="M 38 88 L 30 102 L 43 93 Z" fill="white" opacity="0.9"/>
                        </svg>
                    </div>
                    <p class="card-label">Circle</p>
                </div>

                <!-- Gradient with Cyan - Rounded Square -->
                <div class="card">
                    <div class="app-icon" style="background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%); border-radius: 24px;">
                        <svg viewBox="0 0 200 200" width="80" height="80">
                            <circle cx="60" cy="70" r="25" fill="white" opacity="0.9"/>
                            <circle cx="100" cy="85" r="28" fill="white" opacity="0.8"/>
                            <circle cx="80" cy="35" r="18" fill="white" opacity="0.95"/>
                            <line x1="72" y1="48" x2="80" y2="38" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <line x1="75" y1="78" x2="90" y2="72" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <path d="M 38 88 L 30 102 L 43 93 Z" fill="white" opacity="0.9"/>
                        </svg>
                    </div>
                    <p class="card-label">Blue + Cyan</p>
                </div>

                <!-- Blue + Purple -->
                <div class="card">
                    <div class="app-icon" style="background: linear-gradient(135deg, #2563eb 0%, #a855f7 100%); border-radius: 24px;">
                        <svg viewBox="0 0 200 200" width="80" height="80">
                            <circle cx="60" cy="70" r="25" fill="white" opacity="0.9"/>
                            <circle cx="100" cy="85" r="28" fill="white" opacity="0.8"/>
                            <circle cx="80" cy="35" r="18" fill="white" opacity="0.95"/>
                            <line x1="72" y1="48" x2="80" y2="38" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <line x1="75" y1="78" x2="90" y2="72" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <path d="M 38 88 L 30 102 L 43 93 Z" fill="white" opacity="0.9"/>
                        </svg>
                    </div>
                    <p class="card-label">Blue + Purple</p>
                </div>

                <!-- Solid Blue -->
                <div class="card">
                    <div class="app-icon" style="background: #2563eb; border-radius: 24px;">
                        <svg viewBox="0 0 200 200" width="80" height="80">
                            <circle cx="60" cy="70" r="25" fill="white" opacity="0.9"/>
                            <circle cx="100" cy="85" r="28" fill="white" opacity="0.8"/>
                            <circle cx="80" cy="35" r="18" fill="white" opacity="0.95"/>
                            <line x1="72" y1="48" x2="80" y2="38" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <line x1="75" y1="78" x2="90" y2="72" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <path d="M 38 88 L 30 102 L 43 93 Z" fill="white" opacity="0.9"/>
                        </svg>
                    </div>
                    <p class="card-label">Solid Blue</p>
                </div>
            </div>
        </div>

        <!-- Color Palette -->
        <div class="section">
            <h2 class="section-title">Color Palette</h2>
            <div class="typography-section">
                <div class="color-grid">
                    <div class="color-box" style="background: #2563eb;">
                        Primary Blue
                        <div class="color-name">#2563eb</div>
                    </div>
                    <div class="color-box" style="background: #1e40af;">
                        Dark Blue
                        <div class="color-name">#1e40af</div>
                    </div>
                    <div class="color-box" style="background: #dbeafe;">
                        Light Blue
                        <div class="color-name">#dbeafe</div>
                    </div>
                    <div class="color-box" style="background: #a855f7;">
                        Purple Accent
                        <div class="color-name">#a855f7</div>
                    </div>
                    <div class="color-box" style="background: #06b6d4;">
                        Cyan Accent
                        <div class="color-name">#06b6d4</div>
                    </div>
                    <div class="color-box" style="background: #ffffff; color: #2d3748; border: 2px solid #e2e8f0;">
                        White
                        <div class="color-name">#ffffff</div>
                    </div>
                    <div class="color-box" style="background: #f3f4f6; color: #2d3748;">
                        Light Gray
                        <div class="color-name">#f3f4f6</div>
                    </div>
                    <div class="color-box" style="background: #1a202c;">
                        Dark Gray
                        <div class="color-name">#1a202c</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Typography -->
        <div class="section">
            <h2 class="section-title">Typography</h2>
            <div class="typography-section">
                <div class="font-showcase">
                    <h3>Font Family: System UI Sans-Serif</h3>
                    <p class="font-sample" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif; font-weight: 400;">
                        Regular (400) - The quick brown fox jumps over the lazy dog
                    </p>
                    <p class="font-sample" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif; font-weight: 600;">
                        Semibold (600) - The quick brown fox jumps over the lazy dog
                    </p>
                    <p class="font-sample" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif; font-weight: 700;">
                        Bold (700) - The quick brown fox jumps over the lazy dog
                    </p>
                </div>

                <div class="font-showcase">
                    <h3>Recommended Sizes</h3>
                    <p style="font-size: 2rem; font-weight: 700; color: #1a365d; margin: 10px 0;">Display Large (32px)</p>
                    <p style="font-size: 1.5rem; font-weight: 700; color: #2d3748; margin: 10px 0;">Heading 1 (24px)</p>
                    <p style="font-size: 1.25rem; font-weight: 600; color: #2d3748; margin: 10px 0;">Heading 2 (20px)</p>
                    <p style="font-size: 1rem; font-weight: 600; color: #4a5568; margin: 10px 0;">Heading 3 / Body Large (16px)</p>
                    <p style="font-size: 0.95rem; font-weight: 400; color: #4a5568; margin: 10px 0;">Body (15px)</p>
                    <p style="font-size: 0.875rem; font-weight: 400; color: #718096; margin: 10px 0;">Caption (14px)</p>
                </div>
            </div>
        </div>

        <!-- Design Guidelines -->
        <div class="section">
            <h2 class="section-title">Design Guidelines</h2>
            <div class="typography-section">
                <h3 style="color: #2d3748; margin-bottom: 20px;">Icon Design Principles</h3>
                <ul style="color: #4a5568; line-height: 1.8; margin-left: 20px;">
                    <li><strong>Connected Bubbles:</strong> Represents three chat bubbles connected by lines, symbolizing seamless communication</li>
                    <li><strong>Modern & Minimal:</strong> Clean flat design with smooth curves and rounded shapes</li>
                    <li><strong>Scalable:</strong> Works well from 16px favicon size to large 512px app icons</li>
                    <li><strong>Friendly Feel:</strong> Soft gradients and organic shapes create an approachable, welcoming vibe</li>
                    <li><strong>Versatile:</strong> Works in light and dark modes, with white text on dark backgrounds and blue on light</li>
                </ul>

                <h3 style="color: #2d3748; margin-top: 30px; margin-bottom: 20px;">Usage Guidelines</h3>
                <ul style="color: #4a5568; line-height: 1.8; margin-left: 20px;">
                    <li><strong>Minimum Size:</strong> Do not use smaller than 16px in digital contexts</li>
                    <li><strong>Spacing:</strong> Maintain clear space around the logo - at least 1/4 of the icon height on all sides</li>
                    <li><strong>Color Variations:</strong> Use blue gradient on light backgrounds, white on dark backgrounds</li>
                    <li><strong>Accessibility:</strong> Ensure sufficient contrast ratio (WCAG AA minimum 4.5:1 for text)</li>
                    <li><strong>Background:</strong> Icon works on white, light gray, and solid color backgrounds</li>
                </ul>

                <h3 style="color: #2d3748; margin-top: 30px; margin-bottom: 20px;">Why This Design</h3>
                <p style="color: #4a5568; line-height: 1.8;">
                    The three connected chat bubbles represent the core value of social media: connection and communication. 
                    The design is abstract enough to be original (inspired by but distinct from Facebook and Twitter) while remaining 
                    immediately recognizable. The modern minimalist style ensures it works across all platforms and devices, from web to mobile apps. 
                    The rounded, friendly shapes create an inviting, professional appearance suitable for a Laravel-based social networking platform.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
