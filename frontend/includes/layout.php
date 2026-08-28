<?php
function nav_link(string $href, string $icon, string $label, string $name, string $active, bool $adminOnly = false): string
{
    $cls = 'nav-link';
    if ($name === $active) $cls .= ' active';
    if ($adminOnly) $cls .= ' admin-only d-none';
    return '<a href="' . $href . '" class="' . $cls . '">'
        . '<i class="fa-solid ' . $icon . ' fa-fw me-2"></i>' . $label . '</a>';
}

function page_start(string $title, string $active): void
{
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?> — Shelf Monitor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="../assets/app.css" rel="stylesheet">
</head>
<body>
<script>if (!localStorage.getItem("shelf_token")) location.replace("../login/index.php");</script>
<aside class="sidebar">
  <div class="brand"><i class="fa-solid fa-boxes-stacked me-2 text-primary"></i>Shelf Monitor</div>
  <nav class="nav flex-column">
    <?= nav_link('../dashboard/index.php', 'fa-chart-pie', 'Dashboard', 'dashboard', $active) ?>
    <?= nav_link('../admin/index.php', 'fa-user-shield', 'จัดการระบบ', 'admin', $active, true) ?>
    <?= nav_link('../products/index.php', 'fa-box', 'สินค้า', 'products', $active) ?>
    <?= nav_link('../alerts/index.php', 'fa-bell', 'การแจ้งเตือน', 'alerts', $active) ?>
    <?= nav_link('../history/index.php', 'fa-clock-rotate-left', 'ประวัติ', 'history', $active) ?>
    <?= nav_link('../settings/index.php', 'fa-gear', 'ตั้งค่า', 'settings', $active) ?>
  </nav>
  <div class="sidebar-footer">
    <div id="currentUser" class="sidebar-user">-</div>
    <button id="logoutBtn" type="button" class="nav-link w-100 text-start border-0 bg-transparent">
      <i class="fa-solid fa-right-from-bracket fa-fw me-2"></i>ออกจากระบบ
    </button>
  </div>
</aside>
<?php
}

function page_end(string $script = 'index.js'): void
{
?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/app.js"></script>
<script src="<?= htmlspecialchars($script) ?>"></script>
</body>
</html>
<?php
}
