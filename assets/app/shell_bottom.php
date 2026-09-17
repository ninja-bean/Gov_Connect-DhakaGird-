<?php
// ============================================================
// Citzen/Admin/Response **app shell — bottom half**
// Display-only partial: NO SQL, NO business logic.
// Caller sets BEFORE this include:
//   $dg_footer_note   string  optional inline footer line ('' hides)
//   $dg_sos_role      string  'citizen' => render SOS modal + trigger
//   $dg_js            string  page-specific JS to emit before the closing
//                             shell JS (map, chart, weather, ticker init)
// ============================================================
?>
                </main>

                <footer class="app-footer">
                    <span class="type-caption">
                        © 2026 DhakaGrid · Citi-Connect
                        <?php if (!empty($dg_footer_note)): ?>
                            · <?= htmlspecialchars($dg_footer_note) ?>
                        <?php endif; ?>
                    </span>
                </footer>
            </div> <!-- /app-frame -->
        </div> <!-- /app-body -->

        <!-- ===== SOS MODAL (citizen, per DESIGN §5.3) ===== -->
        <?php if (($dg_sos_role ?? '') === 'citizen'): ?>
        <div class="modal" id="dgSosModal" role="dialog" aria-modal="true" aria-labelledby="dgSosTitle" hidden data-dg-modal>
            <div class="modal__box modal__box--sos">
                <button type="button" class="icon-btn modal__close" data-dg-close title="Close" aria-label="Close SOS">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
                <div class="sos-flow" id="dgSosStep1">
                    <span class="sos-ico"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
                    <h2 id="dgSosTitle">Emergency SOS</h2>
                    <p class="type-muted">Broadcast your real GPS location to the nearest response team.</p>
                    <p class="notice-inline notice-inline--warn">
                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        Only confirmed emergencies. False alarms carry a moderation flag.
                    </p>
                    <div class="btn-row btn-row--center">
                        <button type="button" class="btn btn-outline" data-dg-close>Cancel</button>
                        <button type="button" class="btn btn-danger" id="dgSosProceed">CONFIRM &amp; LOCATE</button>
                    </div>
                </div>
                <div class="sos-flow" id="dgSosStep2" hidden>
                    <span class="sos-ico sos-ico--spin">
                        <i class="fa-solid fa-satellite-dish" aria-hidden="true"></i>
                    </span>
                    <h2>Acquiring GPS…</h2>
                    <p class="type-muted" data-dg-sos-msg>Please allow location access.</p>
                </div>
                <div class="sos-flow" id="dgSosStep3" hidden>
                    <span class="sos-ico sos-ico--ok">
                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                    </span>
                    <h2>Location Sent</h2>
                    <p class="type-muted">Response teams have been notified. Stay where you are.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ===== TOAST REGION (aria-live, per DESIGN §2 toasts) ===== -->
        <div class="toast-region" aria-live="polite" role="status" data-dg-toasts></div>

        <script src="assets/app/core.js" defer></script>
        <?php if (!empty($dg_js)): ?>
            <script><?= $dg_js /* page JS built under the caller's control; app JS is static */ ?></script>
        <?php endif; ?>
    </body>
</html>
