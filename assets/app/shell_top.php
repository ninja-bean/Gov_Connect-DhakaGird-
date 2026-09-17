<?php
// ============================================================
// DhakaGrid **app shell — top half**  (design system §4)
// Display-only partial: NO SQL, NO business logic, NO state.
// Every value arrives ready-to-render from the calling screen:
//   $shell_title       page <title>
//   $shell_user        ['avatar','name','role']
//   $shell_nav         [ [ 'icon','label','href','active' ], ... ]  role-aware
//   $shell_brand       brand anchor href
//   $shell_footer      footer caption (rolled into $shell_footer_lines opts)
//   $shell_notice      notice banner html|'' (optional)
//   $shell_flash       ['kind','text']|null   → toast on load
//   $root              relative base for asset links ('../' or './')
// ============================================================
if (empty($root)) { $root = './'; }
$u = htmlspecialchars((string)($shell_user['name'] ?? 'Citizen'), ENT_QUOTES);
$r = htmlspecialchars((string)($shell_user['role'] ?? 'citizen'), ENT_QUOTES);
$brand = htmlspecialchars((string)($shell_brand ?? 'user_dashboard.php'), ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($shell_title ?? 'DhakaGrid') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="<?= $root ?>assets/app/tokens.css">
    <link rel="stylesheet" href="<?= $root ?>assets/app/base.css">
    <link rel="stylesheet" href="<?= $root ?>assets/app/components.css">
    <script src="<?= $root ?>assets/app/core.js" defer></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body class="app-body">

    <a class="skip" href="#dg-main">Skip to content</a>
    <div class="app-frame" data-dg-drawer-root>

        <!-- ===== TOPBAR (§4) ===== -->
        <header class="topbar" data-dg-topbar aria-label="Primary">
            <div class="topbar__left">
                <button class="topbar__burger" type="button" data-dg-drawer-open aria-label="Open menu" aria-expanded="false" aria-controls="dgSidebar">
                    <i class="fa-solid fa-bars" aria-hidden="true"></i>
                </button>
                <a class="brand" href="<?= $brand ?>">
                    <span class="brand__mark" aria-hidden="true">
                        <i class="fa-solid fa-network-wired"></i>
                    </span>
                    <span class="brand__name">DhakaGrid</span>
                </a>
            </div>
            <div class="topbar__right">
                <button class="btn-icon" type="button" data-dg-bell aria-label="Notifications">
                    <i class="fa-regular fa-bell" aria-hidden="true"></i>
                    <span class="dot" data-dg-bell-dot hidden aria-hidden="true"></span>
                </button>
                <span class="user-chip">
                    <span class="user-chip__avatar">
                        <?php if (!empty($shell_user['avatar'])): ?>
                            <img src="<?= htmlspecialchars($shell_user['avatar'], ENT_QUOTES) ?>" alt="">
                        <?php else: ?>
                            <i class="fa-solid fa-user" aria-hidden="true"></i>
                        <?php endif; ?>
                    </span>
                    <span class="user-chip__meta">
                        <span class="user-chip__name"><?= $u ?></span>
                        <span class="user-chip__role"><?= $r ?></span>
                    </span>
                </span>
                <a class="btn btn-ghost btn-sm" href="<?= $root ?>logout.php">Sign out</a>
            </div>
        </header>

        <div class="app-shell">
            <!-- ===== SIDEBAR / DRAWER (§4) ===== -->
            <aside class="sidebar" id="dgSidebar" data-dg-drawer aria-label="Sidebar">
                <div class="drawer__head">
                    <span class="brand__name">DhakaGrid</span>
                    <button class="btn-icon" type="button" data-dg-drawer-close aria-label="Close menu" data-dg-drawer-escape>
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <nav class="sidebar__nav" aria-label="Sections">
                    <?php foreach ($shell_nav as $item): ?>
                        <?php
                        $cls = 'nav-item' . (!empty($item['active']) ? ' is-active' : '');
                        $cur = !empty($item['active']) ? ' aria-current="page"' : '';
                        ?>
                        <a class="<?= $cls ?>" href="<?= htmlspecialchars($item['href'] ?? '#', ENT_QUOTES) ?>"<?= $cur ?>>
                            <i class="fa-solid <?= htmlspecialchars($item['icon'] ?? 'fa-circle', ENT_QUOTES) ?>" aria-hidden="true"></i>
                            <span><?= htmlspecialchars($item['label'] ?? '', ENT_QUOTES) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <div class="sidebar__foot">
                    <span class="type-caption">DhakaGrid v2 · Citizen</span>
                </div>
            </aside>

            <main class="app-main" id="dg-main" tabindex="-1">
                <?php if (!empty($shell_notice)): ?>
                    <div class="notice notice--banner" data-dg-notice>
                        <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
                        <?= $shell_notice /* caller-escaped */ ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($shell_flash)): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        DG.toast("<?= htmlspecialchars($shell_flash['text'], ENT_QUOTES) ?>",
                                 "<?= htmlspecialchars($shell_flash['kind'] ?? 'success', ENT_QUOTES) ?>");
                    });
                </script>
                <?php endif; ?>