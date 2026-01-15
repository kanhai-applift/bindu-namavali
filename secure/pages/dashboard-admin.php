<!-- My Profile -->
<div class="col-xl-3 col-md-6">
  <div class="card dashboard-card bg-blue text-white">
    <div class="card-body d-flex align-items-center justify-content-center text-center">
      <h3 class="mb-0">My Profile</h3>
    </div>
    <div class="card-footer text-center bg-light">
      <a href="<?= baseUrl('profile') ?>" class="text-muted text-decoration-none fw-semibold">
        VIEW DETAILS →
      </a>
    </div>
  </div>
</div>

<!-- Bindnamavali -->
<div class="col-xl-3 col-md-6">
  <div class="card dashboard-card bg-sky text-white">
    <div class="card-body d-flex align-items-center justify-content-center text-center">
      <h3 class="mb-0">
        नवीन पद निवडा
        <div class="muted sfs-2">(Create New Designations)</div>
      </h3>
    </div>
    <div class="card-footer text-center bg-light">
      <a href="<?= baseUrl('designations-add') ?>" class="text-muted text-decoration-none fw-semibold">
        CREATE POST →
      </a>
    </div>
  </div>
</div>

<!-- List Saved Posts -->
<div class="col-xl-3 col-md-6">
  <div class="card dashboard-card bg-green text-white">
    <div class="card-body d-flex align-items-center justify-content-center text-center">
      <h3 class="mb-0">
        सेव्ह केलेल्या पदनाम
        <div class="muted sfs-2">(Saved Designations)</div>
      </h3>
    </div>
    <div class="card-footer text-center bg-light">
      <a href="<?= baseUrl('designations') ?>" class="text-muted text-decoration-none fw-semibold">
        LIST SAVED POST →
      </a>
    </div>
  </div>
</div>

<!-- Registered Post -->
<div class="col-xl-3 col-md-6">
  <div class="card dashboard-card bg-orange text-white">
    <div class="card-body d-flex align-items-center justify-content-center text-center">
      <h3 class="mb-0">
        नोंदणीकृत पोस्ट
        <div class="muted sfs-2">(Registered Post)</div>
      </h3>
    </div>
    <div class="card-footer text-center bg-light">
      <a href="<?= baseUrl('organisations-post') ?>" class="text-muted text-decoration-none fw-semibold">
        VIEW REGISTERED POSTS →
      </a>
    </div>
  </div>
</div>