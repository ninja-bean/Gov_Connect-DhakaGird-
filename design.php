<?php

declare(strict_types=1);

// DhakaGrid Design System inventory (DESIGN.md §9). Static showcase only:
// no SQL, no business logic, system classes only. Kept intentionally free of
// authentication so any developer can QA the component library.

header('Content-Type: text/html; charset=utf-8');

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DhakaGrid Design System — Inventory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/app/tokens.css">
    <link rel="stylesheet" href="assets/app/base.css">
    <link rel="stylesheet" href="assets/app/components.css">
</head>
<body>
    <div class="container" style="padding-block: var(--sp-8);">

        <header class="stack" style="margin-bottom: var(--sp-7);">
            <span class="rank-pill">Design system v1</span>
            <h1 style="font-size: var(--type-title);">Component inventory</h1>
            <p>Every family from DESIGN.md §9. Classes carry the tokens; screens must reuse these, never invent values.</p>
        </header>

        <!-- ============================ Typography ============================ -->
        <section class="card" style="margin-bottom: var(--sp-6);">
            <div class="card-header"><h2 class="card-title">Typography</h2></div>
            <div style="display:grid; gap: var(--sp-2);">
                <div style="font-size: var(--type-display); font-weight: var(--type-weight-extrabold);">Display 2.5rem / 800</div>
                <div style="font-size: var(--type-title); font-weight: var(--type-weight-extrabold);">Title 2rem / 800</div>
                <div style="font-size: var(--type-h1); font-weight: var(--type-weight-bold);">H1 1.5rem / 700</div>
                <div style="font-size: var(--type-h2); font-weight: var(--type-weight-bold);">H2 1.25rem / 700</div>
                <div style="font-size: var(--type-h3); font-weight: var(--type-weight-bold);">H3 1.05rem / 700</div>
                <div style="font-size: var(--type-body);">Body 0.95rem — the default reading text size.</div>
                <div style="font-size: var(--type-small); color: var(--ink-muted);">Small 0.85rem — secondary text, cells.</div>
                <div style="font-size: var(--type-caption); font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--ink-muted);">Caption 0.78rem — labels, table headers.</div>
                <div style="font-size: var(--type-micro); color: var(--ink-faint);">Micro 0.72rem — meta, timestamps.</div>
            </div>
        </section>

        <!-- ============================== Colours ============================== -->
        <section class="card" style="margin-bottom: var(--sp-6);">
            <div class="card-header"><h2 class="card-title">Colours</h2></div>
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(140px,1fr)); gap: var(--sp-3);">
                <?php
                $swatches = [
                    'bg' => '#F8FAFC', 'surface' => '#FFFFFF', 'surface-muted' => '#F1F5F9',
                    'ink' => '#0F172A', 'ink-muted' => '#64748B', 'ink-faint' => '#94A3B8',
                    'primary' => '#0F172A', 'accent' => '#3B82F6', 'accent-strong' => '#2563EB',
                    'accent-wash' => '#EFF6FF', 'danger' => '#EF4444', 'warning' => '#F59E0B',
                    'success' => '#10B981', 'info' => '#06B6D4', 'border' => '#E2E8F0',
                ];
                foreach ($swatches as $name => $hex):
                ?>
                <div>
                    <div style="height:56px; border-radius: var(--radius-md); background: <?= esc($hex) ?>; border: 1px solid var(--border);"></div>
                    <div style="font-size: var(--type-micro); margin-top: var(--sp-1);">--<?= esc($name) ?></div>
                    <div style="font-size: var(--type-micro); color: var(--ink-faint);"><?= esc($hex) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ============================== Buttons ============================== -->
        <section class="card" style="margin-bottom: var(--sp-6);">
            <div class="card-header"><h2 class="card-title">Buttons</h2></div>
            <div class="row" style="flex-wrap: wrap;">
                <button class="btn btn-primary">Primary</button>
                <button class="btn btn-accent">Accent</button>
                <button class="btn btn-outline">Outline</button>
                <button class="btn btn-ghost">Ghost</button>
                <button class="btn btn-danger">Danger</button>
                <button class="btn btn-primary btn-sm">Small</button>
                <button class="btn btn-accent btn-lg">Large</button>
                <button class="btn btn-primary" disabled>Disabled</button>
                <button class="btn-icon" aria-label="Edit"><span aria-hidden="true">&#9998;</span></button>
                <button class="btn btn-sos">SOS</button>
            </div>
        </section>

        <!-- =============================== Pills =============================== -->
        <section class="card" style="margin-bottom: var(--sp-6);">
            <div class="card-header"><h2 class="card-title">Status &amp; priority pills</h2></div>
            <div class="row" style="flex-wrap: wrap;">
                <span class="pill pill-status-pending">Pending</span>
                <span class="pill pill-status-verified">Verified</span>
                <span class="pill pill-status-assigned">Assigned</span>
                <span class="pill pill-status-working">Working</span>
                <span class="pill pill-status-resolved">Resolved</span>
                <span class="pill pill-status-rejected">Rejected</span>
            </div>
            <div class="row" style="flex-wrap: wrap; margin-top: var(--sp-3);">
                <span class="pill pill-priority-low"><span class="dot"></span>Low</span>
                <span class="pill pill-priority-medium"><span class="dot"></span>Medium</span>
                <span class="pill pill-priority-high"><span class="dot"></span>High</span>
                <span class="pill pill-priority-sos"><span class="dot"></span>SOS</span>
            </div>
        </section>

        <!-- =============================== Cards =============================== -->
        <section class="card" style="margin-bottom: var(--sp-6);">
            <div class="card-header">
                <h2 class="card-title">Cards</h2>
                <div class="row"><span class="pill pill-status-resolved">Resolved</span><button class="btn btn-ghost btn-sm">Action</button></div>
            </div>
            <div class="grid-3">
                <div class="stat-card">
                    <div class="stat-value">128</div>
                    <div class="stat-label">Open reports</div>
                    <span class="delta-up">&#9650; 12%</span>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: var(--danger);">9</div>
                    <div class="stat-label">SOS alerts</div>
                    <span class="delta-down">&#9660; 3</span>
                </div>
                <div class="stat-card">
                    <div class="stat-value">74%</div>
                    <div class="stat-label">Resolution rate</div>
                </div>
            </div>
            <div class="list-row" style="margin-top: var(--sp-4);">
                <div class="row"><span class="pill pill-priority-sos"><span class="dot"></span>SOS</span><span style="font-size: var(--type-small);">Water-logging near Hatirjheel</span></div>
                <span class="pill pill-status-working">Working</span>
            </div>
            <div class="list-row">
                <div class="row"><span class="pill pill-priority-medium"><span class="dot"></span>Medium</span><span style="font-size: var(--type-small);">Streetlight fault in Mirpur</span></div>
                <span class="pill pill-status-verified">Verified</span>
            </div>
        </section>

        <!-- =============================== Forms =============================== -->
        <section class="card" style="margin-bottom: var(--sp-6);">
            <div class="card-header"><h2 class="card-title">Forms</h2></div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label" for="inv-name">Full name <span class="req">*</span></label>
                    <input class="form-input" id="inv-name" type="text" placeholder="e.g. Rahim Uddin">
                    <span class="form-hint">As shown on your profile.</span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="inv-cat">Category</label>
                    <select class="form-select" id="inv-cat">
                        <option>Police</option><option>Medical</option><option>Fire</option><option>Government</option><option>Other</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="inv-desc">Description</label>
                <textarea class="form-textarea" id="inv-desc" placeholder="Describe the issue…"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Attachments</label>
                <div class="dropzone">Drag &amp; drop photos here, or click to browse.</div>
            </div>
            <div class="form-group">
                <label class="form-label">With error state</label>
                <input class="form-input has-error" type="text" value="not a location">
                <span class="form-error">Please pick a location from the map.</span>
            </div>
        </section>

        <!-- =============================== Tables =============================== -->
        <section class="card" style="margin-bottom: var(--sp-6);">
            <div class="card-header"><h2 class="card-title">Data table</h2><button class="btn btn-outline btn-sm">Filter</button></div>
            <div class="table-shell">
                <table class="data-table">
                    <thead><tr><th class="sortable">Problem</th><th>Priority</th><th>Status</th><th>Reported</th><th></th></tr></thead>
                    <tbody>
                        <tr><td>Water-logging near Hatirjheel</td><td><span class="pill pill-priority-sos"><span class="dot"></span>SOS</span></td><td><span class="pill pill-status-working">Working</span></td><td style="color: var(--ink-faint);">2m ago</td><td><button class="btn-icon" aria-label="Open"><span aria-hidden="true">&#8594;</span></button></td></tr>
                        <tr><td>Streetlight fault in Mirpur</td><td><span class="pill pill-priority-medium"><span class="dot"></span>Medium</span></td><td><span class="pill pill-status-verified">Verified</span></td><td style="color: var(--ink-faint);">1h ago</td><td><button class="btn-icon" aria-label="Open"><span aria-hidden="true">&#8594;</span></button></td></tr>
                        <tr><td>Drain blocked, Banani</td><td><span class="pill pill-priority-low"><span class="dot"></span>Low</span></td><td><span class="pill pill-status-resolved">Resolved</span></td><td style="color: var(--ink-faint);">3d ago</td><td><button class="btn-icon" aria-label="Open"><span aria-hidden="true">&#8594;</span></button></td></tr>
                    </tbody>
                </table>
                <div class="table-footer">
                    <span>1–3 of 3</span>
                    <nav class="pager" aria-label="Pagination"><button class="page active">1</button></nav>
                </div>
            </div>
            <div class="card">
                <div class="empty-state">
                    <span class="ico" aria-hidden="true">&#9632;</span>
                    <h2>No problems found</h2>
                    <span>Try adjusting the filters.</span>
                </div>
            </div>
        </section>

        <!-- ============================ Widgets ============================ -->
        <section class="card" style="margin-bottom: var(--sp-6);">
            <div class="card-header"><h2 class="card-title">Tabs, notice, activity, XP</h2></div>

            <div class="tabs" role="tablist" style="margin-bottom: var(--sp-5);">
                <button class="tab active" role="tab">Overview</button>
                <button class="tab" role="tab">Moderation</button>
                <button class="tab" role="tab">Deleted</button>
            </div>

            <div class="notice" style="margin-bottom: var(--sp-5);">
                <div><div class="notice-title">Broadcast test</div><div class="notice-body">City-wide notices use this banner variant.</div></div>
            </div>

            <div class="activity" style="margin-bottom: var(--sp-5);">
                <div class="activity-item"><span class="activity-dot"></span><div><div>Report marked as verified</div><div class="activity-meta">2 minutes ago</div></div></div>
                <div class="activity-item"><span class="activity-dot"></span><div><div>SOS assigned to Police unit 3</div><div class="activity-meta">11 minutes ago</div></div></div>
            </div>

            <div class="card" style="box-shadow: none;">
                <div class="row">
                    <div class="xp-avatar"><span aria-hidden="true">R</span></div>
                    <div>
                        <div class="row"><b>Rahim Uddin</b><span class="rank-pill">Rank 4</span></div>
                        <div style="width: 240px; margin-top: var(--sp-2);"><div class="progress"><div class="progress-fill" style="width: 64%;"></div></div></div>
                    </div>
                </div>
                <div class="xp-stats"><div class="xp-stat"><b>1,280</b><span>XP</span></div><div class="xp-stat"><b>34</b><span>Reports</span></div><div class="xp-stat"><b>12</b><span>Resolved</span></div></div>
            </div>
        </section>

        <!-- ============================ Interactions ============================ -->
        <section class="card">
            <div class="card-header"><h2 class="card-title">Interactions (core.js)</h2></div>
            <div class="row" style="flex-wrap: wrap;">
                <button class="btn btn-accent" data-dg-toast="Inventory loaded." data-dg-toast-type="success">Success toast</button>
                <button class="btn btn-danger" data-dg-toast="That action failed." data-dg-toast-type="error">Error toast</button>
                <button class="btn btn-outline" data-dg-toast="Heads up." data-dg-toast-type="info">Info toast</button>
                <button class="btn btn-danger" id="dg-confirm-demo">Confirm dialog</button>
                <button class="btn btn-primary" id="dg-modal-demo">Open modal</button>
            </div>
        </section>
    </div>

    <div class="drawer-backdrop"></div>
    <aside class="sidebar floating" aria-hidden="true" hidden>
        <nav class="stack">
            <button class="btn btn-ghost" data-dg-drawer-close>Close drawer</button>
        </nav>
    </aside>

    <div class="modal-backdrop" id="dg-modal-demo-target" role="dialog" aria-modal="true" style="display:none;">
        <div class="modal">
            <button class="btn-icon modal-close" data-dg-modal-close aria-label="Close">&#215;</button>
            <h2 class="modal-title">Demo modal</h2>
            <p class="modal-body">Focus is trapped, Esc closes, focus returns to the trigger.</p>
            <div class="modal-actions"><button class="btn btn-outline" data-dg-modal-close>Cancel</button><button class="btn btn-accent" data-dg-modal-close>Save</button></div>
        </div>
    </div>

    <script src="assets/app/core.js"></script>
    <script>
        document.getElementById('dg-confirm-demo').addEventListener('click', function () {
            DG.confirm('Delete this report permanently?', { danger: true, confirmLabel: 'Delete' })
                .then(function (confirmed) { DG.toast(confirmed ? 'Confirmed via the dialog.' : 'Cancelled.', confirmed ? 'success' : 'info'); });
        });
        document.getElementById('dg-modal-demo').addEventListener('click', function () {
            var target = document.getElementById('dg-modal-demo-target');
            target.style.display = 'flex';
            DG.modal.open(target.querySelector('.modal'), this);
        });
        document.querySelectorAll('[data-dg-modal-close]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var backdrop = btn.closest('.modal-backdrop');
                backdrop.style.display = 'none';
                DG.modal.close();
            });
        });
    </script>
</body>
</html>