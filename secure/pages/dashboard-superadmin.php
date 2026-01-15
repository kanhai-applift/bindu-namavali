<!-- New  -->
<div class="col-xl-3 col-md-6">
  <div class="card dashboard-card bg-sky text-white">
    <div class="card-body d-flex align-items-center justify-content-center text-center">
      <h3 class="mb-0">
        मान्यताप्राप्त नोंदणीकृत पोस्ट
        <div class="muted sfs-2">(Approved Post)</div>
      </h3>
    </div>
    <div class="card-footer text-center bg-light">
      <a href="<?= baseUrl('organisations-post/approved') ?>" class="text-muted text-decoration-none fw-semibold">
        VIEW APPROVED POST →
      </a>
    </div>
  </div>
</div>

<!-- Approved Post -->
<div class="col-xl-3 col-md-6">
  <div class="card dashboard-card bg-green text-white">
    <div class="card-body d-flex align-items-center justify-content-center text-center">
      <h3 class="mb-0">
        नवीन नोंदणीकृत पोस्ट
        <div class="muted sfs-2">(New Post waiting Approval)</div>
      </h3>
    </div>
    <div class="card-footer text-center bg-light">
      <a href="<?= baseUrl('organisations-post/new') ?>" class="text-muted text-decoration-none fw-semibold">
        VIEW NEW POSTS →
      </a>
    </div>
  </div>
</div>

<!-- List Saved Posts -->
<div class="col-xl-3 col-md-6">
  <div class="card dashboard-card bg-orange text-white">
    <div class="card-body d-flex align-items-center justify-content-center text-center">
      <h3 class="mb-0">
        वापरकर्त्यांनी जतन केलेल्या पदनामांची यादी
        <div class="muted sfs-2">(Users Saved Post)</div>
      </h3>
    </div>
    <div class="card-footer text-center bg-light">
      <a href="<?= baseUrl('all-designations') ?>" class="text-muted text-decoration-none fw-semibold">
        LIST USERS POST →
      </a>
    </div>
  </div>
</div>


<!-- List Shasan Nirnay Posts -->
<div class="col-xl-3 col-md-6">
  <div class="card dashboard-card bg-primary text-white">
    <div class="card-body d-flex align-items-center justify-content-center text-center">
      <h3 class="mb-0">
        शासन निर्णय यादी
        <div class="muted sfs-2">(List of Government Decisions)</div>
      </h3>
    </div>
    <div class="card-footer text-center bg-light">
      <a href="<?= baseUrl('shasan-nirnay') ?>" class="text-muted text-decoration-none fw-semibold">
        VIEW LIST →
      </a>
    </div>
  </div>
</div>