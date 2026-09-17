<?php
/*
   sketch_bg.php — shared animated "sketch sky" background.
   Usage:
     $sketch_theme = 'day'; // day | sunset | night
     include __DIR__ . '/assets/partials/sketch_bg.php';
*/
$sketch_theme = $sketch_theme ?? 'day';
?>
<div class="sketch-bg <?= htmlspecialchars($sketch_theme) ?>" id="skyBackground" aria-hidden="true">
    <div class="celestial<?= $sketch_theme === 'sunset' ? ' sunset-sun' : ($sketch_theme === 'night' ? ' moon' : '') ?>"></div>

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

    <div class="grid-lines"></div>

    <div class="cloud cloud-1"></div>
    <div class="cloud cloud-2"></div>
    <div class="cloud cloud-3"></div>

    <div class="bird" style="top: 18%; left: -50px; animation-delay: 0s;"><i class="fa-solid fa-dove"></i></div>
    <div class="bird" style="top: 28%; left: -50px; animation-delay: 5s;"><i class="fa-solid fa-dove"></i></div>

    <div class="floating-icon" style="top: 12%; left: 8%; animation-delay: 0s;"><i class="fa-solid fa-file-contract"></i></div>
    <div class="floating-icon" style="top: 22%; right: 12%; animation-delay: 3s;"><i class="fa-solid fa-clipboard-list"></i></div>
    <div class="floating-icon" style="bottom: 45%; left: 6%; animation-delay: 6s;"><i class="fa-solid fa-folder-tree"></i></div>
    <div class="floating-icon" style="top: 55%; right: 10%; animation-delay: 9s;"><i class="fa-solid fa-chart-column"></i></div>

    <svg class="city-sketch" viewBox="0 0 1400 450" preserveAspectRatio="none">
        <path class="building" d="M 60,450 L 60,100 L 180,100 L 180,450 Z M 80,120 L 85,120 L 85,130 L 80,130 Z M 95,120 L 100,120 L 100,130 L 95,130 Z M 110,120 L 115,120 L 115,130 L 110,130 Z M 125,120 L 130,120 L 130,130 L 125,130 Z M 145,120 L 150,120 L 150,130 L 145,130 Z M 160,120 L 165,120 L 165,130 L 160,130 Z M 80,145 L 160,145 M 80,165 L 160,165 M 80,185 L 160,185 M 80,205 L 160,205 M 80,225 L 160,225 M 80,245 L 160,245 M 80,265 L 160,265 M 80,285 L 160,285 M 120,75 L 120,100 M 115,75 L 125,75" />
        <rect class="window-light" x="80" y="120" width="5" height="10"/>
        <rect class="window-light" x="95" y="120" width="5" height="10"/>
        <rect class="window-light" x="110" y="120" width="5" height="10"/>
        <rect class="window-light" x="125" y="120" width="5" height="10"/>
        <rect class="window-light" x="145" y="120" width="5" height="10"/>
        <rect class="window-light" x="160" y="120" width="5" height="10"/>

        <path class="building" d="M 210,450 L 210,280 L 320,280 L 320,450 Z M 230,300 L 235,300 L 235,310 L 230,310 Z M 250,300 L 255,300 L 255,310 L 250,310 Z M 270,300 L 275,300 L 275,310 L 270,310 Z M 290,300 L 295,300 L 295,310 L 290,310 Z M 230,325 L 300,325 M 230,350 L 300,350 M 230,375 L 300,375" />
        <rect class="window-light" x="230" y="300" width="5" height="10"/>
        <rect class="window-light" x="250" y="300" width="5" height="10"/>
        <rect class="window-light" x="270" y="300" width="5" height="10"/>
        <rect class="window-light" x="290" y="300" width="5" height="10"/>

        <path class="building" d="M 350,450 L 350,60 L 480,60 L 480,450 Z M 370,80 L 375,80 L 375,90 L 370,90 Z M 390,80 L 395,80 L 395,90 L 390,90 Z M 410,80 L 415,80 L 415,90 L 410,90 Z M 430,80 L 435,80 L 435,90 L 430,90 Z M 455,80 L 460,80 L 460,90 L 455,90 Z M 370,105 L 460,105 M 370,125 L 460,125 M 370,145 L 460,145 M 370,165 L 460,165 M 370,185 L 460,185 M 370,205 L 460,205 M 370,225 L 460,225 M 370,245 L 460,245 M 370,265 L 460,265 M 370,285 L 460,285 M 370,305 L 460,305 M 370,325 L 460,325 M 415,40 L 415,60 M 410,40 L 420,40 M 405,45 L 425,45" />
        <rect class="window-light" x="370" y="80" width="5" height="10"/>
        <rect class="window-light" x="390" y="80" width="5" height="10"/>
        <rect class="window-light" x="410" y="80" width="5" height="10"/>
        <rect class="window-light" x="430" y="80" width="5" height="10"/>
        <rect class="window-light" x="455" y="80" width="5" height="10"/>

        <path class="building" d="M 510,450 L 510,170 L 640,170 L 640,450 Z M 530,190 L 535,190 L 535,200 L 530,200 Z M 550,190 L 555,190 L 555,200 L 550,200 Z M 570,190 L 575,190 L 575,200 L 570,200 Z M 590,190 L 595,190 L 595,200 L 590,200 Z M 610,190 L 615,190 L 615,200 L 610,200 Z M 530,215 L 620,215 M 530,240 L 620,240 M 530,265 L 620,265 M 530,290 L 620,290 M 530,315 L 620,315" />
        <rect class="window-light" x="530" y="190" width="5" height="10"/>
        <rect class="window-light" x="550" y="190" width="5" height="10"/>
        <rect class="window-light" x="570" y="190" width="5" height="10"/>
        <rect class="window-light" x="590" y="190" width="5" height="10"/>
        <rect class="window-light" x="610" y="190" width="5" height="10"/>

        <path class="building" d="M 670,450 L 670,130 L 800,130 L 800,450 Z M 690,150 L 695,150 L 695,160 L 690,160 Z M 710,150 L 715,150 L 715,160 L 710,160 Z M 730,150 L 735,150 L 735,160 L 730,160 Z M 750,150 L 755,150 L 755,160 L 750,160 Z M 770,150 L 775,150 L 775,160 L 770,160 Z M 690,175 L 780,175 M 690,200 L 780,200 M 690,225 L 780,225 M 690,250 L 780,250 M 690,275 L 780,275" />
        <rect class="window-light" x="690" y="150" width="5" height="10"/>
        <rect class="window-light" x="710" y="150" width="5" height="10"/>
        <rect class="window-light" x="730" y="150" width="5" height="10"/>
        <rect class="window-light" x="750" y="150" width="5" height="10"/>
        <rect class="window-light" x="770" y="150" width="5" height="10"/>

        <path class="building" d="M 830,450 L 830,240 L 940,240 L 940,450 Z M 850,260 L 855,260 L 855,270 L 850,270 Z M 870,260 L 875,260 L 875,270 L 870,270 Z M 890,260 L 895,260 L 895,270 L 890,270 Z M 910,260 L 915,260 L 915,270 L 910,270 Z M 850,285 L 920,285 M 850,310 L 920,310 M 850,335 L 920,335" />
        <rect class="window-light" x="850" y="260" width="5" height="10"/>
        <rect class="window-light" x="870" y="260" width="5" height="10"/>
        <rect class="window-light" x="890" y="260" width="5" height="10"/>
        <rect class="window-light" x="910" y="260" width="5" height="10"/>

        <path class="building" d="M 970,450 L 970,200 L 1080,200 L 1080,450 Z M 990,220 L 995,220 L 995,230 L 990,230 Z M 1010,220 L 1015,220 L 1015,230 L 1010,230 Z M 1030,220 L 1035,220 L 1035,230 L 1030,230 Z M 1050,220 L 1055,220 L 1055,230 L 1050,230 Z M 990,245 L 1060,245 M 990,270 L 1060,270 M 990,295 L 1060,295" />
        <rect class="window-light" x="990" y="220" width="5" height="10"/>
        <rect class="window-light" x="1010" y="220" width="5" height="10"/>
        <rect class="window-light" x="1030" y="220" width="5" height="10"/>
        <rect class="window-light" x="1050" y="220" width="5" height="10"/>

        <path class="building" d="M 1110,450 L 1110,150 L 1220,150 L 1220,450 Z M 1130,170 L 1135,170 L 1135,180 L 1130,180 Z M 1150,170 L 1155,170 L 1155,180 L 1150,180 Z M 1170,170 L 1175,170 L 1175,180 L 1170,180 Z M 1190,170 L 1195,170 L 1195,180 L 1190,180 Z M 1130,195 L 1200,195 M 1130,220 L 1200,220 M 1130,245 L 1200,245 M 1130,270 L 1200,270" />
        <rect class="window-light" x="1130" y="170" width="5" height="10"/>
        <rect class="window-light" x="1150" y="170" width="5" height="10"/>
        <rect class="window-light" x="1170" y="170" width="5" height="10"/>
        <rect class="window-light" x="1190" y="170" width="5" height="10"/>

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
            <animateTransform attributeName="transform" type="translate" from="0 0" to="1700 0" dur="16s" begin="3s" repeatCount="indefinite"/>
        </g>
    </svg>
</div>