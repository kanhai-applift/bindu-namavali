<?php

/**
 * $page_scripts can be:
 * - array of script URLs
 * - array of inline JS blocks
 */
?>

<!-- Bootstrap JS (global) -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById('toggleSidebar').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('collapsed');
  });
  document.querySelectorAll('.has-submenu > a').forEach(link => {
    link.addEventListener('click', function(e) {
      const sidebar = document.getElementById('sidebar');

      if (sidebar.classList.contains('collapsed')) {
        e.preventDefault(); // disable click collapse
      }
    });
  });
</script>

<?php
// External JS files
if (!empty($page_scripts) && is_array($page_scripts)):
  foreach ($page_scripts as $script):
    echo '<script src="' . htmlspecialchars($script) . '"></script>' . PHP_EOL;
  endforeach;
endif;

// Inline scripts
if (isset($inline_scripts) && !empty($inline_scripts)):
  echo "<script>" . PHP_EOL;
  echo $inline_scripts;
  echo PHP_EOL . "</script>";
endif;

if (isset($mysqli)) {
  $mysqli->close();
}
?>

</body>

</html>