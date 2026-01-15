<?php
require_once('csrf.php');
require_once('helper.php');
// Default title (fallback)
$page_title = $page_title ?? "Board Officer Management System";

$organizationName = '';
$userType = '';
$userName = 'My Account';
if (!empty($_SESSION)) {

  $role = $_SESSION['role'] ?? null;

  $userType = match ($role) {
    'superadmin' => '(Super Admin)',
    'admin'      => '(Admin)',
    default      => '',
  };

  $userName = !empty($_SESSION['name'])
    ? htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8')
    : 'My Account';

  $organizationName = isset($_SESSION['office_name']) ? " - " . $_SESSION['office_name'] : '';
}

// cache version 
$v = getCacheVersion();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($page_title) ?></title>

  <link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
  <link rel="shortcut icon" href="/favicon/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
  <link rel="manifest" href="/favicon/site.webmanifest" />

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?php
  $page_styles = [
    'https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css',
    // baseUrl('new/assets/css/font-awesome.min.css'),
    baseUrl('assets/css/style.css?v=' . $v),
  ];
  ?>

  <?php if (isset($page_styles) && !empty($page_styles)): ?>
    <?php foreach ($page_styles as $style): ?>
      <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>">
    <?php endforeach; ?>
  <?php endif; ?>

  <script>
    function baseUrl(path) {
      return "<?= BASE_URL; ?>" + path;
    }
  </script>
</head>

<body>

  <!-- Top Navbar -->
  <nav class="navbar navbar-expand top-navbar px-3 no-print">
    <button class="btn btn-outline-light me-3" id="toggleSidebar">
      <i class="bi bi-list"></i>
    </button>

    <a class="navbar-brand" href="<?= baseUrl('') ?>">
      बिंदू नामावली नोंदणी <?= $organizationName ?>
    </a>

    <span class="ms-auto text-white">
      <?= $userType; ?>
    </span>
    <?php if (!empty($userType)): ?>
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?= $userName ?>
          </a>

          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= baseUrl('profile') ?>">Profile</a></li>
            <li><a class="dropdown-item" href="<?= baseUrl('change-password') ?>">Change Password</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item text-danger" href="<?= baseUrl('logout') ?>">Logout</a></li>
          </ul>
        </li>
      </ul>
    <?php endif; ?>
  </nav>