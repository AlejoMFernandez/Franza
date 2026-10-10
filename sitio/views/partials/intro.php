<?php
/**
 * intro.php — Escena de arranque "Del plano a la obra" (versión B2, barrido).
 * Se muestra una vez por sesión en la home. Se saltea con "prefers-reduced-motion".
 */
?>
<script>
(function () {
    var visto = false;
    try { visto = sessionStorage.getItem('fz-intro') === '1'; } catch (e) {}
    var reducir = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.documentElement.classList.add(visto || reducir ? 'fz-sin-intro' : 'fz-con-intro');
})();
</script>
<div class="fz-intro" id="fz-intro">
    <div class="fz-intro__plano" aria-hidden="true">
        <div class="fz-intro__lockup">
            <span class="fz-intro__cota fz-intro__cota--h"><i></i><em>10,88</em><i></i></span>
            <span class="fz-intro__cota fz-intro__cota--v"><i></i><em>4,09</em><i></i></span>
            <svg class="fz-intro__iso" viewBox="0 0 646 880" aria-hidden="true">
                <defs><linearGradient id="fz-intro-g" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#EFD581"/><stop offset="1" stop-color="#B98D36"/></linearGradient></defs>
                <g fill="url(#fz-intro-g)" stroke="#EFD581" stroke-width="3">
                    <polygon class="fz-i-barra" style="animation-delay:.05s,1.6s" points="150,177 196,163 196,609 150,563"/>
                    <polygon class="fz-i-barra" style="animation-delay:.1s,1.62s" points="225,100 275,88 275,688 225,638"/>
                    <polygon class="fz-i-barra" style="animation-delay:0s,1.64s" points="302,16 324,8 346,16 346,715 324,737 302,715"/>
                    <polygon class="fz-i-barra" style="animation-delay:.15s,1.66s" points="379,92 421,104 421,640 379,682"/>
                    <polygon class="fz-i-barra" style="animation-delay:.2s,1.68s" points="450,167 496,179 496,565 450,611"/>
                </g>
                <g fill="none" stroke="#F1EEE7" stroke-linejoin="round">
                    <path class="fz-i-base" d="M120 400 L46 474 Q30 492 46 510 L304 768 Q323 787 342 768 L600 510 Q616 492 600 474 L526 400" stroke-width="3"/>
                    <path class="fz-i-base2" d="M10 556 L304 850 Q323 869 342 850 L636 556" stroke-width="3" stroke-linecap="round"/>
                </g>
            </svg>
            <div class="fz-intro__marca">
                <svg viewBox="0 0 740 230" aria-hidden="true">
                    <defs><clipPath id="fz-intro-clip"><rect class="fz-i-scan" x="0" y="0" width="740" height="240"/></clipPath></defs>
                    <g clip-path="url(#fz-intro-clip)">
                        <text class="fz-i-txt" x="0" y="150" textLength="736" lengthAdjust="spacingAndGlyphs" style="font-family:'Montserrat',sans-serif;font-weight:800;font-size:178px">FRANZA</text>
                        <text class="fz-i-txt" x="6" y="218" textLength="724" lengthAdjust="spacing" style="font-family:'Montserrat',sans-serif;font-weight:300;font-size:50px">CONSTRUCCIONES</text>
                    </g>
                </svg>
                <span class="fz-i-plot"></span>
            </div>
        </div>
        <div class="fz-intro__rotulo">
            <div class="fz-intro__rotulo-fila"><span>Obra<b>FRANZA Construcciones</b></span><span>Rubro<b>Civil · Industrial</b></span></div>
            <div class="fz-intro__avance">
                <span>Avance de obra</span>
                <strong><span id="fz-intro-pct">000</span>%</strong>
                <i><b id="fz-intro-barra"></b></i>
            </div>
        </div>
        <span class="fz-intro__lamina">Lámina 00 · Portada · Esc 1:1</span>
    </div>
    <button type="button" class="fz-intro__saltar" id="fz-intro-saltar">Saltar</button>
</div>
