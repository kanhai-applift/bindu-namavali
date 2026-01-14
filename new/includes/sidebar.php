<!-- Sidebar -->
<aside class="sidebar no-print" id="sidebar">
  <div class="text-uppercase text-muted small px-3 mt-3 mb-2">
    Main
  </div>

  <ul class="nav flex-column">

    <?php if (isset($_SESSION) && isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>


      <li class="nav-item">
        <a class="nav-link" href="<?= baseUrl('dashboard') ?>">
          <i class="bi bi-people"></i>
          <span>Dashboard</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="<?= baseUrl('registration') ?>">
          <i class="bi bi-people"></i>
          <span>User Registration</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="<?= baseUrl('users_list') ?>">
          <i class="bi bi-people"></i>
          <span>View Users</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="<?= baseUrl('access_logs') ?>">
          <i class="bi bi-person-lock"></i>
          <span>User Access Logs</span>
        </a>
      </li>

    <?php elseif (isset($_SESSION) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>

      <li class="nav-item">
        <a class="nav-link" href="<?= baseUrl('dashboard') ?>">
          <i class="bi bi-house"></i>
          <span>Dashboard</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="<?= baseUrl('organisations-post-add') ?>">
          <i class="bi bi-file-earmark-plus-fill"></i>
          <span>New Post Registration</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="<?= baseUrl('organisations-post') ?>">
          <i class="bi bi-people"></i>
          <span>Registerd Post list</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="<?= baseUrl('profile') ?>">
          <i class="bi bi-person-lock"></i>
          <span>My Profile</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="<?= baseUrl('change-password') ?>">
          <i class="bi bi-person-lock"></i>
          <span>Update Password</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="<?= baseUrl('login_history') ?>">
          <i class="bi bi-person-lock"></i>
          <span>User Access Logs</span>
        </a>
      </li>

    <?php else: ?>

      <li class="nav-item">
        <a class="nav-link" href="<?= baseUrl('') ?>">
          <i class="bi bi-house"></i>
          <span>Home</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="<?= baseUrl('login') ?>">
          <i class="bi bi-people"></i>
          <span>User Login</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="<?= baseUrl('login') ?>">
          <i class="bi bi-person-lock"></i>
          <span>Admin Login</span>
        </a>
      </li>

    <?php endif; ?>
  </ul>

</aside>