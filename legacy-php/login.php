<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DhakaGrid - Login</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">

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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* ANIMATED SKETCH BACKGROUND */
        .sketch-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, #BFDBFE 0%, #DBEAFE 40%, #F0F9FF 100%);
            overflow: hidden;
            transition: background 1s ease-in-out;
        }

        /* DAY THEME (Citizen) - Default */
        .sketch-bg.day {
            background: linear-gradient(180deg, #BFDBFE 0%, #DBEAFE 40%, #F0F9FF 100%);
        }

        /* SUNSET THEME (Response Team) */
        .sketch-bg.sunset {
            background: linear-gradient(180deg, #FED7AA 0%, #FDBA74 20%, #FB923C 40%, #F97316 60%, #FCA5A5 80%, #FEE2E2 100%);
        }

        /* NIGHT THEME (Admin) */
        .sketch-bg.night {
            background: linear-gradient(180deg, #0C4A6E 0%, #075985 30%, #0E7490 50%, #155E75 70%, #164E63 100%);
        }

        /* Sun/Moon */
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

        /* Sunset Sun */
        .celestial.sunset-sun {
            background: radial-gradient(circle, #FBBF24 0%, #F59E0B 50%, #D97706 100%);
            box-shadow: 0 0 80px rgba(245, 158, 11, 0.8);
            top: 30%;
        }

        /* Night Moon */
        .celestial.moon {
            background: radial-gradient(circle, #F8FAFC 0%, #E2E8F0 50%, #CBD5E1 100%);
            box-shadow: 0 0 50px rgba(226, 232, 240, 0.9);
            top: 15%;
        }

        @keyframes celestialPulse {
            0%, 100% { transform: scale(1); opacity: 0.9; }
            50% { transform: scale(1.05); opacity: 1; }
        }

        /* Stars (Night Only) */
        .star {
            position: absolute;
            width: 3px;
            height: 3px;
            background: white;
            border-radius: 50%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            animation: twinkle 3s infinite;
        }

        .sketch-bg.night .star {
            opacity: 1;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }

        /* Moving Clouds */
        .cloud {
            position: absolute;
            display: flex;
            z-index: 1;
            opacity: 0.7;
            transition: opacity 1s ease-in-out;
        }

        .sketch-bg.night .cloud {
            opacity: 0.3;
        }

        .sketch-bg.sunset .cloud {
            opacity: 0.5;
        }

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

        .sketch-bg.night .cloud::before,
        .sketch-bg.night .cloud::after {
            background: rgba(203, 213, 225, 0.4);
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

        .cloud-1 {
            top: 15%;
            left: -150px;
            animation: cloudMove 40s linear infinite;
        }

        .cloud-2 {
            top: 25%;
            left: -150px;
            animation: cloudMove 50s linear infinite;
            animation-delay: -10s;
        }

        .cloud-3 {
            top: 35%;
            left: -150px;
            animation: cloudMove 45s linear infinite;
            animation-delay: -25s;
        }

        @keyframes cloudMove {
            from { transform: translateX(0); }
            to { transform: translateX(calc(100vw + 200px)); }
        }

        /* Birds Flying */
        .bird {
            position: absolute;
            font-size: 1.2rem;
            color: rgba(71, 85, 105, 0.4);
            animation: birdFly 30s linear infinite;
            z-index: 2;
            transition: color 1s ease-in-out;
        }

        .sketch-bg.night .bird {
            color: rgba(203, 213, 225, 0.3);
        }

        .sketch-bg.sunset .bird {
            color: rgba(120, 53, 15, 0.5);
        }

        @keyframes birdFly {
            0% { transform: translate(0, 0) scale(0.8); }
            50% { transform: translate(50vw, -50px) scale(1); }
            100% { transform: translate(100vw, 0) scale(0.8); }
        }

        /* Animated Grid Lines */
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

        .sketch-bg.night .grid-lines {
            background-image: 
                repeating-linear-gradient(0deg, rgba(226, 232, 240, 0.05) 0px, transparent 1px, transparent 60px, rgba(226, 232, 240, 0.05) 61px),
                repeating-linear-gradient(90deg, rgba(226, 232, 240, 0.05) 0px, transparent 1px, transparent 60px, rgba(226, 232, 240, 0.05) 61px);
        }

        @keyframes gridPulse {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.6; }
        }

        /* Hand-Drawn City */
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

        /* Sunset Building Colors */
        .sketch-bg.sunset .building {
            stroke: #78350F;
            fill: rgba(120, 53, 15, 0.08);
        }

        /* Night Building Colors */
        .sketch-bg.night .building {
            stroke: #0F172A;
            fill: rgba(15, 23, 42, 0.3);
        }

        @keyframes drawBuilding {
            0% {
                opacity: 0;
                stroke-dasharray: 2000;
                stroke-dashoffset: 2000;
            }
            20% {
                opacity: 0.8;
            }
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

        /* Building Windows Glow at Night */
        .window-light {
            fill: rgba(250, 204, 21, 0);
            transition: fill 1s ease-in-out;
        }

        .sketch-bg.night .window-light {
            fill: rgba(250, 204, 21, 0.8);
            animation: windowBlink 3s infinite;
        }

        @keyframes windowBlink {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }

        /* Floating Icons */
        .floating-icon {
            position: absolute;
            font-size: 2.5rem;
            color: rgba(59, 130, 246, 0.25);
            z-index: 2;
            animation: iconFloat 25s infinite ease-in-out;
            transition: color 1s ease-in-out;
        }

        .sketch-bg.sunset .floating-icon {
            color: rgba(251, 146, 60, 0.3);
        }

        .sketch-bg.night .floating-icon {
            color: rgba(125, 211, 252, 0.2);
        }

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

        /* LOGIN CARD */
        .login-container {
            position: relative;
            z-index: 10;
            width: 90%;
            max-width: 400px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(25px);
            border: 2px solid rgba(255, 255, 255, 0.8);
            border-radius: 22px;
            padding: 34px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.12),
                0 0 0 1px rgba(255, 255, 255, 0.9),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            animation: cardSlideUp 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes cardSlideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 26px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border);
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 50%, #334155 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            font-size: 1.9rem;
            color: white;
            box-shadow: 
                0 10px 25px rgba(15, 23, 42, 0.25),
                0 0 0 4px rgba(59, 130, 246, 0.1);
            animation: logoPulse 3s ease-in-out infinite;
        }

        @keyframes logoPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .logo-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 4px;
            letter-spacing: -0.5px;
        }

        .logo-subtitle {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* TABS */
        .tab-container {
            display: flex;
            gap: 6px;
            background: rgba(241, 245, 249, 0.8);
            padding: 6px;
            border-radius: 12px;
            margin-bottom: 22px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .tab-btn {
            flex: 1;
            padding: 9px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.72rem;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .tab-btn.active {
            background: white;
            color: var(--primary);
            box-shadow: 
                0 4px 12px rgba(0, 0, 0, 0.1),
                0 1px 3px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .tab-btn:hover:not(.active) {
            background: rgba(255, 255, 255, 0.6);
            transform: translateY(-1px);
        }

        /* FORMS */
        .form-container {
            display: none;
        }

        .form-container.active {
            display: block;
            animation: formFadeIn 0.5s ease-out;
        }

        @keyframes formFadeIn {
            from { 
                opacity: 0; 
                transform: translateY(15px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        .form-group {
            margin-bottom: 13px;
        }

        .form-label {
            display: block;
            font-size: 0.74rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 7px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-label i {
            color: var(--accent);
            font-size: 0.7rem;
        }

        .form-input {
            width: 100%;
            padding: 11px 13px;
            border-radius: 9px;
            border: 2px solid var(--border);
            background: white;
            color: var(--text-main);
            font-size: 0.76rem;
            font-family: 'Outfit', sans-serif;
            transition: all 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
        }

        .form-input::placeholder {
            color: #94A3B8;
        }

        /* NATIVE WIDGET CONSISTENCY (autofill, selection, validation popups) */
        .form-input:-webkit-autofill,
        .form-input:-webkit-autofill:hover,
        .form-input:-webkit-autofill:focus {
            -webkit-text-fill-color: var(--text-main);
            -webkit-box-shadow: 0 0 0 1000px #FFFFFF inset;
            transition: background-color 600000s 0s;
        }

        ::selection {
            background: rgba(59, 130, 246, 0.25);
        }

        /* ERROR ALERT */
        .error-alert {
            background: linear-gradient(135deg, #FEF2F2 0%, #FEE2E2 100%);
            border: 2px solid #FCA5A5;
            border-radius: 9px;
            padding: 10px 13px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #991B1B;
            font-size: 0.74rem;
            font-weight: 500;
            animation: errorShake 0.6s;
        }

        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-12px); }
            40%, 80% { transform: translateX(12px); }
        }

        /* SUBMIT BUTTON */
        .btn-submit {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 9px;
            background: linear-gradient(135deg, var(--primary) 0%, #1E293B 100%);
            color: white;
            font-weight: 700;
            font-size: 0.82rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.35);
        }

        .btn-submit:active {
            transform: translateY(-1px);
        }

        /* FOOTER LINK */
        .footer-link {
            text-align: center;
            margin-top: 18px;
            padding-top: 15px;
            border-top: 2px solid var(--border);
        }

        .footer-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.75rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .footer-link a:hover {
            color: #2563EB;
            transform: translateY(-1px);
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .login-card {
                padding: 28px 22px;
            }

            .logo-title {
                font-size: 1.5rem;
            }

            .tab-btn {
                font-size: 0.82rem;
                padding: 10px 8px;
            }

            .tab-btn i {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- SKETCH BACKGROUND -->
    <div class="sketch-bg day" id="skyBackground">
        <!-- Celestial Body (Sun/Moon) -->
        <div class="celestial" id="celestialBody"></div>

        <!-- Stars (Night Only) -->
        <div class="star" style="top: 10%; left: 15%; animation-delay: 0s;"></div>
        <div class="star" style="top: 15%; left: 25%; animation-delay: 0.5s;"></div>
        <div class="star" style="top: 20%; left: 45%; animation-delay: 1s;"></div>
        <div class="star" style="top: 12%; left: 65%; animation-delay: 1.5s;"></div>
        <div class="star" style="top: 18%; left: 80%; animation-delay: 2s;"></div>
        <div class="star" style="top: 25%; left: 35%; animation-delay: 2.5s;"></div>
        <div class="star" style="top: 30%; left: 55%; animation-delay: 3s;"></div>
        <div class="star" style="top: 35%; left: 70%; animation-delay: 3.5s;"></div>
        <div class="star" style="top: 40%; left: 20%; animation-delay: 4s;"></div>
        <div class="star" style="top: 45%; left: 50%; animation-delay: 4.5s;"></div>

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
            <!-- Building 1 -->
            <path class="building" d="M 60,450 L 60,100 L 180,100 L 180,450 Z M 80,120 L 85,120 L 85,130 L 80,130 Z M 95,120 L 100,120 L 100,130 L 95,130 Z M 110,120 L 115,120 L 115,130 L 110,130 Z M 125,120 L 130,120 L 130,130 L 125,130 Z M 145,120 L 150,120 L 150,130 L 145,130 Z M 160,120 L 165,120 L 165,130 L 160,130 Z M 80,145 L 160,145 M 80,165 L 160,165 M 80,185 L 160,185 M 80,205 L 160,205 M 80,225 L 160,225 M 80,245 L 160,245 M 80,265 L 160,265 M 80,285 L 160,285 M 120,75 L 120,100 M 115,75 L 125,75" />
            <rect class="window-light" x="80" y="120" width="5" height="10"/>
            <rect class="window-light" x="95" y="120" width="5" height="10"/>
            <rect class="window-light" x="110" y="120" width="5" height="10"/>
            <rect class="window-light" x="125" y="120" width="5" height="10"/>
            <rect class="window-light" x="145" y="120" width="5" height="10"/>
            <rect class="window-light" x="160" y="120" width="5" height="10"/>
            
            <!-- Building 2 -->
            <path class="building" d="M 210,450 L 210,280 L 320,280 L 320,450 Z M 230,300 L 235,300 L 235,310 L 230,310 Z M 250,300 L 255,300 L 255,310 L 250,310 Z M 270,300 L 275,300 L 275,310 L 270,310 Z M 290,300 L 295,300 L 295,310 L 290,310 Z M 230,325 L 300,325 M 230,350 L 300,350 M 230,375 L 300,375" />
            <rect class="window-light" x="230" y="300" width="5" height="10"/>
            <rect class="window-light" x="250" y="300" width="5" height="10"/>
            <rect class="window-light" x="270" y="300" width="5" height="10"/>
            <rect class="window-light" x="290" y="300" width="5" height="10"/>
            
            <!-- Building 3 -->
            <path class="building" d="M 350,450 L 350,60 L 480,60 L 480,450 Z M 370,80 L 375,80 L 375,90 L 370,90 Z M 390,80 L 395,80 L 395,90 L 390,90 Z M 410,80 L 415,80 L 415,90 L 410,90 Z M 430,80 L 435,80 L 435,90 L 430,90 Z M 455,80 L 460,80 L 460,90 L 455,90 Z M 370,105 L 460,105 M 370,125 L 460,125 M 370,145 L 460,145 M 370,165 L 460,165 M 370,185 L 460,185 M 370,205 L 460,205 M 370,225 L 460,225 M 370,245 L 460,245 M 370,265 L 460,265 M 370,285 L 460,285 M 370,305 L 460,305 M 370,325 L 460,325 M 415,40 L 415,60 M 410,40 L 420,40 M 405,45 L 425,45" />
            <rect class="window-light" x="370" y="80" width="5" height="10"/>
            <rect class="window-light" x="390" y="80" width="5" height="10"/>
            <rect class="window-light" x="410" y="80" width="5" height="10"/>
            <rect class="window-light" x="430" y="80" width="5" height="10"/>
            <rect class="window-light" x="455" y="80" width="5" height="10"/>
            
            <!-- Building 4 -->
            <path class="building" d="M 510,450 L 510,170 L 640,170 L 640,450 Z M 530,190 L 535,190 L 535,200 L 530,200 Z M 550,190 L 555,190 L 555,200 L 550,200 Z M 570,190 L 575,190 L 575,200 L 570,200 Z M 590,190 L 595,190 L 595,200 L 590,200 Z M 610,190 L 615,190 L 615,200 L 610,200 Z M 530,215 L 620,215 M 530,240 L 620,240 M 530,265 L 620,265 M 530,290 L 620,290 M 530,315 L 620,315" />
            <rect class="window-light" x="530" y="190" width="5" height="10"/>
            <rect class="window-light" x="550" y="190" width="5" height="10"/>
            <rect class="window-light" x="570" y="190" width="5" height="10"/>
            <rect class="window-light" x="590" y="190" width="5" height="10"/>
            <rect class="window-light" x="610" y="190" width="5" height="10"/>
            
            <!-- Building 5 -->
            <path class="building" d="M 670,450 L 670,130 L 800,130 L 800,450 Z M 690,150 L 695,150 L 695,160 L 690,160 Z M 710,150 L 715,150 L 715,160 L 710,160 Z M 730,150 L 735,150 L 735,160 L 730,160 Z M 750,150 L 755,150 L 755,160 L 750,160 Z M 770,150 L 775,150 L 775,160 L 770,160 Z M 690,175 L 780,175 M 690,200 L 780,200 M 690,225 L 780,225 M 690,250 L 780,250 M 690,275 L 780,275" />
            <rect class="window-light" x="690" y="150" width="5" height="10"/>
            <rect class="window-light" x="710" y="150" width="5" height="10"/>
            <rect class="window-light" x="730" y="150" width="5" height="10"/>
            <rect class="window-light" x="750" y="150" width="5" height="10"/>
            <rect class="window-light" x="770" y="150" width="5" height="10"/>
            
            <!-- Building 6 -->
            <path class="building" d="M 830,450 L 830,240 L 940,240 L 940,450 Z M 850,260 L 855,260 L 855,270 L 850,270 Z M 870,260 L 875,260 L 875,270 L 870,270 Z M 890,260 L 895,260 L 895,270 L 890,270 Z M 910,260 L 915,260 L 915,270 L 910,270 Z M 850,285 L 920,285 M 850,310 L 920,310 M 850,335 L 920,335" />
            <rect class="window-light" x="850" y="260" width="5" height="10"/>
            <rect class="window-light" x="870" y="260" width="5" height="10"/>
            <rect class="window-light" x="890" y="260" width="5" height="10"/>
            <rect class="window-light" x="910" y="260" width="5" height="10"/>
            
            <!-- Building 7 -->
            <path class="building" d="M 970,450 L 970,200 L 1080,200 L 1080,450 Z M 990,220 L 995,220 L 995,230 L 990,230 Z M 1010,220 L 1015,220 L 1015,230 L 1010,230 Z M 1030,220 L 1035,220 L 1035,230 L 1030,230 Z M 1050,220 L 1055,220 L 1055,230 L 1050,230 Z M 990,245 L 1060,245 M 990,270 L 1060,270 M 990,295 L 1060,295" />
            <rect class="window-light" x="990" y="220" width="5" height="10"/>
            <rect class="window-light" x="1010" y="220" width="5" height="10"/>
            <rect class="window-light" x="1030" y="220" width="5" height="10"/>
            <rect class="window-light" x="1050" y="220" width="5" height="10"/>
            
            <!-- Building 8 -->
            <path class="building" d="M 1110,450 L 1110,150 L 1220,150 L 1220,450 Z M 1130,170 L 1135,170 L 1135,180 L 1130,180 Z M 1150,170 L 1155,170 L 1155,180 L 1150,180 Z M 1170,170 L 1175,170 L 1175,180 L 1170,180 Z M 1190,170 L 1195,170 L 1195,180 L 1190,180 Z M 1130,195 L 1200,195 M 1130,220 L 1200,220 M 1130,245 L 1200,245 M 1130,270 L 1200,270" />
            <rect class="window-light" x="1130" y="170" width="5" height="10"/>
            <rect class="window-light" x="1150" y="170" width="5" height="10"/>
            <rect class="window-light" x="1170" y="170" width="5" height="10"/>
            <rect class="window-light" x="1190" y="170" width="5" height="10"/>

            <!-- Metro Rail Track -->
            <line x1="0" y1="405" x2="1400" y2="405" stroke="#64748B" stroke-width="6" opacity="0.5" stroke-dasharray="1400" stroke-dashoffset="1400">
                <animate attributeName="stroke-dashoffset" from="1400" to="0" dur="2.5s" begin="1.5s" fill="freeze" />
            </line>
            <line x1="0" y1="410" x2="1400" y2="410" stroke="#94A3B8" stroke-width="4" opacity="0.3" stroke-dasharray="1400" stroke-dashoffset="1400">
                <animate attributeName="stroke-dashoffset" from="1400" to="0" dur="2.5s" begin="1.5s" fill="freeze" />
            </line>

            <!-- Detailed Moving Train -->
            <g id="train" opacity="0">
                <!-- Engine Car -->
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
                
                <!-- Passenger Car 1 -->
                <g transform="translate(125, 0)">
                    <rect x="0" y="380" width="100" height="30" rx="4" fill="none" stroke="#3B82F6" stroke-width="3"/>
                    <rect x="0" y="375" width="100" height="5" rx="2" fill="#1E40AF"/>
                    <rect x="10" y="385" width="18" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                    <rect x="33" y="385" width="18" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                    <rect x="56" y="385" width="18" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                    <rect x="79" y="385" width="18" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                </g>
                
                <!-- Passenger Car 2 -->
                <g transform="translate(230, 0)">
                    <rect x="0" y="380" width="100" height="30" rx="4" fill="none" stroke="#3B82F6" stroke-width="3"/>
                    <rect x="0" y="375" width="100" height="5" rx="2" fill="#1E40AF"/>
                    <rect x="10" y="385" width="18" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                    <rect x="33" y="385" width="18" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                    <rect x="56" y="385" width="18" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                    <rect x="79" y="385" width="18" height="15" fill="rgba(255,255,255,0.4)" stroke="#1E40AF" stroke-width="1"/>
                </g>
                
                <!-- Animation -->
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

    <!-- LOGIN CARD -->
    <div class="login-container">
        <div class="login-card">
            
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
                <h1 class="logo-title">DhakaGrid</h1>
                <p class="logo-subtitle">City Management Portal</p>
            </div>

            <!-- Error Message -->
            <?php if (!empty($error)): ?>
                <div class="error-alert">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>

            <!-- Tabs -->
            <div class="tab-container">
                <button class="tab-btn active" onclick="switchTab('citizen')" data-tab="citizen">
                    <i class="fa-solid fa-user"></i>
                    <span>Citizen</span>
                </button>
                <button class="tab-btn" onclick="switchTab('response')" data-tab="response">
                    <i class="fa-solid fa-truck-medical"></i>
                    <span>Response</span>
                </button>
                <button class="tab-btn" onclick="switchTab('admin')" data-tab="admin">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Admin</span>
                </button>
            </div>

            <!-- Citizen Form -->
            <div class="form-container active" id="form-citizen">
                <form method="POST" action="login_process.php">
                    <input type="hidden" name="role" value="user">
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-envelope"></i>
                            Email Address
                        </label>
                        <input type="email" name="email" class="form-input" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-lock"></i>
                            Password
                        </label>
                        <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Sign In
                    </button>
                </form>
            </div>

            <!-- Response Team Form -->
            <div class="form-container" id="form-response">
                <form method="POST" action="login_process.php">
                    <input type="hidden" name="role" value="response">
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-id-badge"></i>
                           Unit Email Address
                        </label>
                        <input type="text" name="email" class="form-input" placeholder="Enter unit identifier" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-key"></i>
                            Security Key
                        </label>
                        <input type="password" name="password" class="form-input" placeholder="Enter security key" required>
                    </div>

                    <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);">
                        <i class="fa-solid fa-truck-medical"></i>
                        Deploy Unit
                    </button>
                </form>
            </div>

            <!-- Admin Form -->
            <div class="form-container" id="form-admin">
                <form method="POST" action="login_process.php">
                    <input type="hidden" name="role" value="admin">
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-user-shield"></i>
                            Admin ID
                        </label>
                        <input type="text" name="email" class="form-input" placeholder="Enter admin code" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-shield-halved"></i>
                            System Password
                        </label>
                        <input type="password" name="password" class="form-input" placeholder="Enter system password" required>
                    </div>

                    <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%);">
                        <i class="fa-solid fa-terminal"></i>
                        Access Terminal
                    </button>
                </form>
            </div>

            <!-- Footer Link -->
            <div class="footer-link">
                <a href="register.php">
                    <i class="fa-solid fa-user-plus"></i>
                    Create New Account
                </a>
            </div>

        </div>
    </div>

    <script>
        const skyBg = document.getElementById('skyBackground');
        const celestialBody = document.getElementById('celestialBody');

        function switchTab(tabName) {
            // Update tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

            // Update form containers
            document.querySelectorAll('.form-container').forEach(form => {
                form.classList.remove('active');
            });
            document.getElementById(`form-${tabName}`).classList.add('active');

            // Change time of day based on tab
            skyBg.classList.remove('day', 'sunset', 'night');
            celestialBody.classList.remove('sunset-sun', 'moon');

            if (tabName === 'citizen') {
                // Day - Bright sky with sun
                skyBg.classList.add('day');
                celestialBody.classList.remove('sunset-sun', 'moon');
            } else if (tabName === 'response') {
                // Sunset - Orange/Pink sky with setting sun
                skyBg.classList.add('sunset');
                celestialBody.classList.add('sunset-sun');
            } else if (tabName === 'admin') {
                // Night - Dark blue sky with moon and stars
                skyBg.classList.add('night');
                celestialBody.classList.add('moon');
            }
        }
    </script>

</body>
</html>