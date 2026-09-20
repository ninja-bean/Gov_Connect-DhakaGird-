<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - DhakaGrid</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
:root {
            --bg: #F8FAFC;
            --surface: #FFFFFF;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --primary: #0F172A; 
            --accent: #3B82F6;
            --border: #E2E8F0;
            color-scheme: light;
            accent-color: var(--accent);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        /* ===== ANIMATED SKETCH BACKGROUND (like Sign In) ===== */
        .sketch-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, #BFDBFE 0%, #DBEAFE 40%, #F0F9FF 100%);
            overflow: hidden;
            transition: background 1s ease-in-out;
            z-index: 0;
        }

        .sketch-bg.day {
            background: linear-gradient(180deg, #BFDBFE 0%, #DBEAFE 40%, #F0F9FF 100%);
        }

        .sketch-bg.sunset {
            background: linear-gradient(180deg, #FED7AA 0%, #FDBA74 20%, #FB923C 40%, #F97316 60%, #FCA5A5 80%, #FEE2E2 100%);
        }

        .celestial {
            position: absolute;
            top: 12%;
            right: 15%;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: radial-gradient(circle, #FEF08A 0%, #FDE047 50%, #FACC15 100%);
            box-shadow: 0 0 60px rgba(250, 204, 21, 0.6);
            animation: celestialPulse 4s ease-in-out infinite;
            z-index: 0;
            transition: all 1s ease-in-out;
        }

        .celestial.sunset-sun {
            background: radial-gradient(circle, #FBBF24 0%, #F59E0B 50%, #D97706 100%);
            box-shadow: 0 0 80px rgba(245, 158, 11, 0.8);
            top: 30%;
        }

        @keyframes celestialPulse {
            0%, 100% { transform: scale(1); opacity: 0.9; }
            50% { transform: scale(1.05); opacity: 1; }
        }

        .cloud {
            position: absolute;
            display: flex;
            z-index: 1;
            opacity: 0.7;
            transition: opacity 1s ease-in-out;
        }

        .sketch-bg.sunset .cloud { opacity: 0.5; }

        .cloud::before,
        .cloud::after {
            content: '';
            position: relative;
            display: inline-block;
            background: white;
            border-radius: 50%;
            transition: background 1s ease-in-out;
        }

        .sketch-bg.sunset .cloud::before,
        .sketch-bg.sunset .cloud::after {
            background: rgba(255, 255, 255, 0.8);
        }

        .cloud::before {
            width: 60px;
            height: 60px;
        }

        .cloud::after {
            width: 80px;
            height: 50px;
            top: 10px;
            margin-left: -30px;
        }

        .cloud-1 { top: 15%; left: -150px; animation: cloudMove 40s linear infinite; }
        .cloud-2 { top: 25%; left: -150px; animation: cloudMove 50s linear infinite; animation-delay: -10s; }
        .cloud-3 { top: 35%; left: -150px; animation: cloudMove 45s linear infinite; animation-delay: -25s; }

        @keyframes cloudMove {
            from { transform: translateX(0); }
            to { transform: translateX(calc(100vw + 200px)); }
        }

        .bird {
            position: absolute;
            font-size: 1.2rem;
            color: rgba(71, 85, 105, 0.4);
            animation: birdFly 30s linear infinite;
            z-index: 2;
            transition: color 1s ease-in-out;
        }

        .sketch-bg.sunset .bird { color: rgba(120, 53, 15, 0.5); }

        @keyframes birdFly {
            0% { transform: translate(0, 0) scale(0.8); }
            50% { transform: translate(50vw, -50px) scale(1); }
            100% { transform: translate(100vw, 0) scale(0.8); }
        }

        .grid-lines {
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                repeating-linear-gradient(0deg, rgba(148, 163, 184, 0.08) 0px, transparent 1px, transparent 60px, rgba(148, 163, 184, 0.08) 61px),
                repeating-linear-gradient(90deg, rgba(148, 163, 184, 0.08) 0px, transparent 1px, transparent 60px, rgba(148, 163, 184, 0.08) 61px);
            animation: gridPulse 5s ease-in-out infinite alternate;
            transition: background-image 1s ease-in-out;
        }

        .sketch-bg.sunset .grid-lines {
            background-image: 
                repeating-linear-gradient(0deg, rgba(251, 146, 60, 0.06) 0px, transparent 1px, transparent 60px, rgba(251, 146, 60, 0.06) 61px),
                repeating-linear-gradient(90deg, rgba(251, 146, 60, 0.06) 0px, transparent 1px, transparent 60px, rgba(251, 146, 60, 0.06) 61px);
        }

        @keyframes gridPulse {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.6; }
        }

        .city-sketch {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 65%;
            z-index: 3;
        }

        .building {
            stroke: #334155;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: rgba(51, 65, 85, 0.05);
            filter: drop-shadow(2px 4px 6px rgba(0, 0, 0, 0.1));
            animation: drawBuilding 2.5s ease-out forwards;
            transition: stroke 1s ease-in-out, fill 1s ease-in-out;
        }

        .sketch-bg.sunset .building {
            stroke: #78350F;
            fill: rgba(120, 53, 15, 0.08);
        }

        @keyframes drawBuilding {
            0% {
                opacity: 0;
                stroke-dasharray: 2000;
                stroke-dashoffset: 2000;
            }
            20% { opacity: 0.8; }
            100% {
                opacity: 0.8;
                stroke-dasharray: 2000;
                stroke-dashoffset: 0;
            }
        }

        .building:nth-child(1) { animation-delay: 0s; }
        .building:nth-child(2) { animation-delay: 0.2s; }
        .building:nth-child(3) { animation-delay: 0.4s; }
        .building:nth-child(4) { animation-delay: 0.6s; }
        .building:nth-child(5) { animation-delay: 0.8s; }
        .building:nth-child(6) { animation-delay: 1s; }
        .building:nth-child(7) { animation-delay: 1.2s; }
        .building:nth-child(8) { animation-delay: 1.4s; }

        .floating-icon {
            position: absolute;
            font-size: 2.5rem;
            color: rgba(59, 130, 246, 0.25);
            z-index: 2;
            animation: iconFloat 25s infinite ease-in-out;
            transition: color 1s ease-in-out;
        }

        .sketch-bg.sunset .floating-icon { color: rgba(251, 146, 60, 0.3); }

        @keyframes iconFloat {
            0%, 100% { 
                transform: translateY(0) rotate(0deg); 
                opacity: 0.2; 
            }
            25% { 
                transform: translateY(-40px) rotate(10deg); 
                opacity: 0.4; 
            }
            50% { 
                transform: translateY(-80px) rotate(-10deg); 
                opacity: 0.25; 
            }
            75% { 
                transform: translateY(-40px) rotate(10deg); 
                opacity: 0.4; 
            }
        }

        /* MAIN CONTAINER */
        .main-container {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            z-index: 10;
        }

        .register-wrapper {
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
        }

        /* PROGRESS STEPS */
        .steps-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 32px;
        }

        .step-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--border);
            transition: all 0.3s;
        }

        .step-dot.active {
            background: var(--accent);
            width: 28px;
            border-radius: 5px;
        }

        /* CARD */
        .card {
            background: var(--surface);
            border-radius: 20px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            animation: cardSlideUp 0.9s cubic-bezier(0.4, 0, 0.2, 1);
            transform: scale(0.82);
        }

        @keyframes cardSlideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.78);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(0.82);
            }
        }

        .card-head {
            padding: 32px 40px 24px;
            text-align: center;
            border-bottom: 1px solid var(--border);
        }

        .role-toggle {
            display: inline-flex;
            gap: 6px;
            background: var(--bg);
            padding: 6px;
            border-radius: 12px;
            margin-bottom: 24px;
        }

        .role-btn {
            padding: 10px 20px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .role-btn.active {
            background: var(--surface);
            color: var(--primary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .card-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .card-body {
            padding: 32px 40px 40px;
        }

        /* MESSAGES */
        .message {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }

        .message.success {
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            color: #065F46;
        }

        /* FORM STEPS */
        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .step-heading {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .step-heading i {
            color: var(--accent);
            font-size: 0.95rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 13px 16px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-main);
            font-size: 0.92rem;
            font-family: 'Outfit', sans-serif;
            transition: all 0.2s;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748B' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 40px;
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1rem;
        }

        .password-toggle:hover {
            color: var(--text-main);
        }

        .field-hint {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 6px;
        }

        /* NATIVE WIDGET CONSISTENCY */
        .form-input[type="date"]::-webkit-calendar-picker-indicator {
            background: transparent url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHZpZXdCb3g9JzAgMCAyNCAyNCcgZmlsbD0nbm9uZScgc3Ryb2tlPSclMjM2NDc0OEInIHN0cm9rZS13aWR0aD0nMicgc3Ryb2tlLWxpbmVjYXA9J3JvdW5kJyBzdHJva2UtbGluZWpvaW49J3JvdW5kJz48cmVjdCB4PSczJyB5PSc0JyB3aWR0aD0nMTgnIGhlaWdodD0nMTgnIHJ4PSczJy8+PHBhdGggZD0nTTE2IDJ2NE04IDJ2NE0zIDEwaDE4Jy8+PC9zdmc+") no-repeat center center;
            background-size: 16px 16px;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            cursor: pointer;
            opacity: 1;
            transition: background-color 0.2s;
        }

        .form-input[type="date"]::-webkit-calendar-picker-indicator:hover {
            background-color: rgba(59, 130, 246, 0.12);
        }

        .form-input[type="date"]:not(:valid)::-webkit-datetime-edit {
            color: #94A3B8;
        }

        .form-input[type="number"]::-webkit-inner-spin-button,
        .form-input[type="number"]::-webkit-outer-spin-button {
            border-radius: 6px;
            opacity: 0.6;
            cursor: pointer;
        }

        .form-select option {
            background: #FFFFFF;
            color: var(--text-main);
            padding: 6px 10px;
        }

        .form-select option:checked {
            background: #DBEAFE;
            color: var(--primary);
        }

        .form-input:-webkit-autofill,
        .form-input:-webkit-autofill:hover,
        .form-input:-webkit-autofill:focus,
        .form-select:-webkit-autofill {
            -webkit-text-fill-color: var(--text-main);
            -webkit-box-shadow: 0 0 0 1000px #FFFFFF inset;
            transition: background-color 600000s 0s;
        }

        ::selection {
            background: rgba(59, 130, 246, 0.25);
        }

        /* STEP NAVIGATION */
        .step-nav {
            display: flex;
            gap: 12px;
            margin-top: 28px;
        }

        .btn {
            flex: 1;
            padding: 15px;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            font-size: 0.95rem;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #1E293B;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }

        .btn-secondary {
            background: var(--bg);
            color: var(--text-main);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--surface);
            border-color: var(--primary);
        }

        /* INFO BANNER */
        .info-banner {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 12px;
            padding: 16px 18px;
            margin-bottom: 24px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .info-banner i {
            color: var(--accent);
            font-size: 1.1rem;
            margin-top: 2px;
        }

        .info-banner-text {
            font-size: 0.85rem;
            color: #1E40AF;
            line-height: 1.5;
        }

        /* FOOTER LINK */
        .footer-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .footer-link a {
            color: var(--accent);
            font-weight: 600;
            text-decoration: none;
        }

        .footer-link a:hover {
            color: #2563EB;
        }

        /* RESPONSIVE */
        @media (max-width: 640px) {
            .card-head,
            .card-body {
                padding: 24px 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .role-btn span {
                display: none;
            }

            .step-nav {
                flex-direction: column-reverse;
            }
        }
    </style>
</head>
<body>

    <!-- SKETCH BACKGROUND (like Sign In) -->
    <div class="sketch-bg day" id="skyBackground">
        <!-- Celestial Body -->
        <div class="celestial" id="celestialBody"></div>

        <!-- Grid Lines -->
        <div class="grid-lines"></div>

        <!-- Moving Clouds -->
        <div class="cloud cloud-1"></div>
        <div class="cloud cloud-2"></div>
        <div class="cloud cloud-3"></div>

        <!-- Flying Birds -->
        <div class="bird" style="top: 18%; left: -50px; animation-delay: 0s;">
            <i class="fa-solid fa-dove"></i>
        </div>
        <div class="bird" style="top: 28%; left: -50px; animation-delay: 5s;">
            <i class="fa-solid fa-dove"></i>
        </div>

        <!-- Floating Icons -->
        <div class="floating-icon" style="top: 12%; left: 8%; animation-delay: 0s;">
            <i class="fa-solid fa-file-contract"></i>
        </div>
        <div class="floating-icon" style="top: 22%; right: 12%; animation-delay: 3s;">
            <i class="fa-solid fa-clipboard-list"></i>
        </div>
        <div class="floating-icon" style="bottom: 45%; left: 6%; animation-delay: 6s;">
            <i class="fa-solid fa-folder-tree"></i>
        </div>
        <div class="floating-icon" style="top: 55%; right: 10%; animation-delay: 9s;">
            <i class="fa-solid fa-chart-column"></i>
        </div>

        <!-- Detailed City Skyline -->
        <svg class="city-sketch" viewBox="0 0 1400 450" preserveAspectRatio="none">
            <path class="building" d="M 60,450 L 60,100 L 180,100 L 180,450 Z M 80,120 L 85,120 L 85,130 L 80,130 Z M 95,120 L 100,120 L 100,130 L 95,130 Z M 110,120 L 115,120 L 115,130 L 110,130 Z M 125,120 L 130,120 L 130,130 L 125,130 Z M 145,120 L 150,120 L 150,130 L 145,130 Z M 160,120 L 165,120 L 165,130 L 160,130 Z M 80,145 L 160,145 M 80,165 L 160,165 M 80,185 L 160,185 M 80,205 L 160,205 M 80,225 L 160,225 M 80,245 L 160,245 M 80,265 L 160,265 M 80,285 L 160,285 M 120,75 L 120,100 M 115,75 L 125,75" />
            <path class="building" d="M 210,450 L 210,280 L 320,280 L 320,450 Z M 230,300 L 235,300 L 235,310 L 230,310 Z M 250,300 L 255,300 L 255,310 L 250,310 Z M 270,300 L 275,300 L 275,310 L 270,310 Z M 290,300 L 295,300 L 295,310 L 290,310 Z M 230,325 L 300,325 M 230,350 L 300,350 M 230,375 L 300,375" />
            <path class="building" d="M 350,450 L 350,60 L 480,60 L 480,450 Z M 370,80 L 375,80 L 375,90 L 370,90 Z M 390,80 L 395,80 L 395,90 L 390,90 Z M 410,80 L 415,80 L 415,90 L 410,90 Z M 430,80 L 435,80 L 435,90 L 430,90 Z M 455,80 L 460,80 L 460,90 L 455,90 Z M 370,105 L 460,105 M 370,125 L 460,125 M 370,145 L 460,145 M 370,165 L 460,165 M 370,185 L 460,185 M 370,205 L 460,205 M 370,225 L 460,225 M 370,245 L 460,245 M 370,265 L 460,265 M 370,285 L 460,285 M 370,305 L 460,305 M 370,325 L 460,325 M 415,40 L 415,60 M 410,40 L 420,40 M 405,45 L 425,45" />
            <path class="building" d="M 510,450 L 510,170 L 640,170 L 640,450 Z M 530,190 L 535,190 L 535,200 L 530,200 Z M 550,190 L 555,190 L 555,200 L 550,200 Z M 570,190 L 575,190 L 575,200 L 570,200 Z M 590,190 L 595,190 L 595,200 L 590,200 Z M 610,190 L 615,190 L 615,200 L 610,200 Z M 530,215 L 620,215 M 530,240 L 620,240 M 530,265 L 620,265 M 530,290 L 620,290 M 530,315 L 620,315" />
            <path class="building" d="M 670,450 L 670,130 L 800,130 L 800,450 Z M 690,150 L 695,150 L 695,160 L 690,160 Z M 710,150 L 715,150 L 715,160 L 710,160 Z M 730,150 L 735,150 L 735,160 L 730,160 Z M 750,150 L 755,150 L 755,160 L 750,160 Z M 770,150 L 775,150 L 775,160 L 770,160 Z M 690,175 L 780,175 M 690,200 L 780,200 M 690,225 L 780,225 M 690,250 L 780,250 M 690,275 L 780,275" />
            <path class="building" d="M 830,450 L 830,240 L 940,240 L 940,450 Z M 850,260 L 855,260 L 855,270 L 850,270 Z M 870,260 L 875,260 L 875,270 L 870,270 Z M 890,260 L 895,260 L 895,270 L 890,270 Z M 910,260 L 915,260 L 915,270 L 910,270 Z M 850,285 L 920,285 M 850,310 L 920,310 M 850,335 L 920,335" />
            <path class="building" d="M 970,450 L 970,200 L 1080,200 L 1080,450 Z M 990,220 L 995,220 L 995,230 L 990,230 Z M 1010,220 L 1015,220 L 1015,230 L 1010,230 Z M 1030,220 L 1035,220 L 1035,230 L 1030,230 Z M 1050,220 L 1055,220 L 1055,230 L 1050,230 Z M 990,245 L 1060,245 M 990,270 L 1060,270 M 990,295 L 1060,295" />
            <path class="building" d="M 1110,450 L 1110,150 L 1220,150 L 1220,450 Z M 1130,170 L 1135,170 L 1135,180 L 1130,180 Z M 1150,170 L 1155,170 L 1155,180 L 1150,180 Z M 1170,170 L 1175,170 L 1175,180 L 1170,180 Z M 1190,170 L 1195,170 L 1195,180 L 1190,180 Z M 1130,195 L 1200,195 M 1130,220 L 1200,220 M 1130,245 L 1200,245 M 1130,270 L 1200,270" />

            <line x1="0" y1="405" x2="1400" y2="405" stroke="#64748B" stroke-width="6" opacity="0.5" stroke-dasharray="1400" stroke-dashoffset="1400">
                <animate attributeName="stroke-dashoffset" from="1400" to="0" dur="2.5s" begin="1.5s" fill="freeze" />
            </line>
            <line x1="0" y1="410" x2="1400" y2="410" stroke="#94A3B8" stroke-width="4" opacity="0.3" stroke-dasharray="1400" stroke-dashoffset="1400">
                <animate attributeName="stroke-dashoffset" from="1400" to="0" dur="2.5s" begin="1.5s" fill="freeze" />
            </line>

            <g id="train" opacity="0">
                <g>
                    <rect x="0" y="380" width="120" height="30" rx="4" fill="none" stroke="#3B82F6" stroke-width="3"/>
                    <rect x="0" y="375" width="120" height="5" rx="2" fill="#1E40AF"/>
                    <rect x="10" y="385" width="20" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                    <rect x="35" y="385" width="20" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                    <rect x="60" y="385" width="20" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                    <rect x="85" y="385" width="20" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                    <circle cx="110" cy="395" r="5" fill="#FEF08A" opacity="0.9">
                        <animate attributeName="opacity" values="0.9;1;0.9" dur="1s" repeatCount="indefinite"/>
                    </circle>
                    <rect x="45" y="385" width="15" height="20" fill="rgba(30,64,175,0.2)" stroke="#1E40AF" stroke-width="1"/>
                </g>
                <g transform="translate(125, 0)">
                    <rect x="0" y="380" width="100" height="30" rx="4" fill="none" stroke="#3B82F6" stroke-width="3"/>
                    <rect x="0" y="375" width="100" height="5" rx="2" fill="#1E40AF"/>
                    <rect x="10" y="385" width="18" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                    <rect x="33" y="385" width="18" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                    <rect x="56" y="385" width="18" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                    <rect x="79" y="385" width="18" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                </g>
                <g transform="translate(230, 0)">
                    <rect x="0" y="380" width="100" height="30" rx="4" fill="none" stroke="#3B82F6" stroke-width="3"/>
                    <rect x="0" y="375" width="100" height="5" rx="2" fill="#1E40AF"/>
                    <rect x="10" y="385" width="18" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                    <rect x="33" y="385" width="18" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                    <rect x="56" y="385" width="18" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                    <rect x="79" y="385" width="18" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                </g>
                <animate attributeName="opacity" from="0" to="0.85" dur="0.8s" begin="3s" fill="freeze"/>
                <animateTransform
                    attributeName="transform"
                    type="translate"
                    from="0 0"
                    to="1700 0"
                    dur="16s"
                    begin="3s"
                    repeatCount="indefinite"/>
            </g>
        </svg>
    </div>

    <div class="main-container">
        <div class="register-wrapper">

            <div class="card">
                <div class="card-head">

                    <!-- Role Toggle -->
                    <div class="role-toggle">
                        <button type="button" class="role-btn active" id="roleUserBtn" onclick="setRole('user')">
                            <i class="fa-solid fa-user"></i> <span>Citizen</span>
                        </button>
                        <button type="button" class="role-btn" id="roleResponseBtn" onclick="setRole('response')">
                            <i class="fa-solid fa-truck-fast"></i> <span>Response Team</span>
                        </button>
                    </div>

                    <div class="card-title" id="cardTitle">Create Your Account</div>
                    <div class="card-subtitle" id="cardSubtitle">Join DhakaGrid to report and track city issues</div>
                </div>

                <div class="card-body">

                    <?php if ($error): ?>
                        <div class="message error">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span><?= str_replace(['<b>','</b>'], '', $error) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="message success">
                            <i class="fa-solid fa-circle-check"></i>
                            <span><?= str_replace(['<b>','</b>'], '', $success) ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- ============ CITIZEN FORM ============ -->
                    <form id="userForm" method="POST" action="register_process.php">
                        <input type="hidden" name="role" value="user">

                        <!-- Step 1: Personal Info -->
                        <div class="form-step active" data-step="1" data-form="user">
                            <div class="step-heading">
                                <i class="fa-solid fa-id-card"></i> Personal Information
                            </div>

                            <div class="form-group full">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-input" placeholder="Enter your full name" required>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-input" placeholder="you@example.com" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-input" placeholder="01XXXXXXXXX" required>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">National ID (NID)</label>
                                    <input type="text" name="nid" class="form-input" placeholder="NID number" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="dob" class="form-input" required>
                                </div>
                            </div>

                            <div class="step-nav">
                                <button type="button" class="btn btn-primary" onclick="nextStep('user')">
                                    Continue <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Address & Security -->
                        <div class="form-step" data-step="2" data-form="user">
                            <div class="step-heading">
                                <i class="fa-solid fa-lock"></i> Address & Security
                            </div>

                            <div class="form-group full">
                                <label class="form-label">Permanent Address</label>
                                <input type="text" name="location" class="form-input" placeholder="House, Road, Area, City" required>
                            </div>

                            <div class="form-group full">
                                <label class="form-label">Password</label>
                                <div class="password-wrapper">
                                    <input type="password" name="password" id="userPassword" class="form-input" placeholder="Create a password" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('userPassword', this)">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <div class="field-hint">Use at least 8 characters with letters and numbers</div>
                            </div>

                            <div class="step-nav">
                                <button type="button" class="btn btn-secondary" onclick="prevStep('user')">
                                    <i class="fa-solid fa-arrow-left"></i> Back
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-check"></i> Create Account
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- ============ RESPONSE TEAM FORM ============ -->
                    <form id="responseForm" method="POST" action="register_process.php" style="display: none;">
                        <input type="hidden" name="role" value="response">

                        <div class="info-banner">
                            <i class="fa-solid fa-circle-info"></i>
                            <div class="info-banner-text">
                                Response Team accounts require admin approval. Your account status will show as <strong>Pending</strong> until it's reviewed.
                            </div>
                        </div>

                        <!-- Step 1: Team Info -->
                        <div class="form-step active" data-step="1" data-form="response">
                            <div class="step-heading">
                                <i class="fa-solid fa-building-shield"></i> Team Information
                            </div>

                            <div class="form-group full">
                                <label class="form-label">Team Name</label>
                                <input type="text" name="name" class="form-input" placeholder="e.g. Gulshan Fire Unit 3" required>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Category</label>
                                    <select name="category" class="form-select" required>
                                        <option value="">Select category</option>
                                        <option value="police">Police</option>
                                        <option value="medical">Medical</option>
                                        <option value="fire">Fire</option>
                                        <option value="gov">Government</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Station Code</label>
                                    <input type="text" name="identification" class="form-input" placeholder="Station identifier" required>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Team Location / Address</label>
                                    <input type="text" name="location" class="form-input" placeholder="Station address" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Number of Employees</label>
                                    <input type="number" name="employee_number" class="form-input" placeholder="e.g. 12" min="1" required>
                                </div>
                            </div>

                            <div class="step-nav">
                                <button type="button" class="btn btn-primary" onclick="nextStep('response')">
                                    Continue <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Team Contact -->
                        <div class="form-step" data-step="2" data-form="response">
                            <div class="step-heading">
                                <i class="fa-solid fa-address-book"></i> Official Team Contact
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Official Team Phone</label>
                                    <input type="text" name="phone" class="form-input" placeholder="Team phone number" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Official Team Email</label>
                                    <input type="email" name="email" class="form-input" placeholder="team@example.com" required>
                                </div>
                            </div>

                            <div class="step-nav">
                                <button type="button" class="btn btn-secondary" onclick="prevStep('response')">
                                    <i class="fa-solid fa-arrow-left"></i> Back
                                </button>
                                <button type="button" class="btn btn-primary" onclick="nextStep('response')">
                                    Continue <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Incharge Info -->
                        <div class="form-step" data-step="3" data-form="response">
                            <div class="step-heading">
                                <i class="fa-solid fa-user-tie"></i> Incharge Details
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Incharge Name</label>
                                    <input type="text" name="incharge_name" class="form-input" placeholder="Full name" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Incharge ID</label>
                                    <input type="text" name="incharge_id" class="form-input" placeholder="Employee ID" required>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Incharge Email</label>
                                    <input type="email" name="incharge_email" class="form-input" placeholder="incharge@example.com" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Incharge Phone</label>
                                    <input type="text" name="incharge_phone" class="form-input" placeholder="Contact number" required>
                                </div>
                            </div>

                            <div class="step-nav">
                                <button type="button" class="btn btn-secondary" onclick="prevStep('response')">
                                    <i class="fa-solid fa-arrow-left"></i> Back
                                </button>
                                <button type="button" class="btn btn-primary" onclick="nextStep('response')">
                                    Continue <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 4: Security -->
                        <div class="form-step" data-step="4" data-form="response">
                            <div class="step-heading">
                                <i class="fa-solid fa-lock"></i> Account Security
                            </div>

                            <div class="form-group full">
                                <label class="form-label">Password</label>
                                <div class="password-wrapper">
                                    <input type="password" name="password" id="responsePassword" class="form-input" placeholder="Create a password" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('responsePassword', this)">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <div class="field-hint">Use at least 8 characters with letters and numbers</div>
                            </div>

                            <div class="step-nav">
                                <button type="button" class="btn btn-secondary" onclick="prevStep('response')">
                                    <i class="fa-solid fa-arrow-left"></i> Back
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-check"></i> Submit Application
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="footer-link">
                        Already have an account? <a href="login.php">Sign in</a>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script>
        const skyBg = document.getElementById('skyBackground');
        const celestialBody = document.getElementById('celestialBody');

        let currentRole = 'user';
        const totalSteps = { user: 2, response: 4 };
        let currentStep = { user: 1, response: 1 };

        function setRole(role) {
            currentRole = role;

            // Update toggle buttons
            document.getElementById('roleUserBtn').classList.toggle('active', role === 'user');
            document.getElementById('roleResponseBtn').classList.toggle('active', role === 'response');

            // Show/hide forms
            document.getElementById('userForm').style.display = role === 'user' ? 'block' : 'none';
            document.getElementById('responseForm').style.display = role === 'response' ? 'block' : 'none';

            // Update title
            if (role === 'user') {
                document.getElementById('cardTitle').textContent = 'Create Your Account';
                document.getElementById('cardSubtitle').textContent = 'Join DhakaGrid to report and track city issues';
            } else {
                document.getElementById('cardTitle').textContent = 'Register Response Team';
                document.getElementById('cardSubtitle').textContent = 'Set up your team account for admin approval';
            }

            // Switch sky theme (day for citizen, sunset for response)
            skyBg.classList.remove('day', 'sunset');
            celestialBody.classList.remove('sunset-sun');

            if (role === 'response') {
                skyBg.classList.add('sunset');
                celestialBody.classList.add('sunset-sun');
            } else {
                skyBg.classList.add('day');
            }
        }

        function showStep(role, step) {
            document.querySelectorAll(`.form-step[data-form="${role}"]`).forEach(el => {
                el.classList.remove('active');
            });
            document.querySelector(`.form-step[data-form="${role}"][data-step="${step}"]`).classList.add('active');
            currentStep[role] = step;
        }

        function nextStep(role) {
            // Validate current step's required fields before proceeding
            const current = document.querySelector(`.form-step[data-form="${role}"][data-step="${currentStep[role]}"]`);
            const inputs = current.querySelectorAll('input[required], select[required]');
            for (const input of inputs) {
                if (!input.checkValidity()) {
                    input.reportValidity();
                    return;
                }
            }

            if (currentStep[role] < totalSteps[role]) {
                showStep(role, currentStep[role] + 1);
            }
        }

        function prevStep(role) {
            if (currentStep[role] > 1) {
                showStep(role, currentStep[role] - 1);
            }
        }

        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Auto-dismiss messages
        document.querySelectorAll('.message').forEach(msg => {
            setTimeout(() => {
                msg.style.opacity = '0';
                setTimeout(() => msg.remove(), 400);
            }, 5000);
        });
    </script>

</body>
</html>