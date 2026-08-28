<?php
require __DIR__ . '/../includes/layout.php';
page_start('Dashboard', 'dashboard');
?>
<main class="main-content">
  <header class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
      <h1 class="h3 fw-bold mb-1">ภาพรวมสินค้าบนชั้นวาง</h1>
      <div class="text-secondary">
        <span class="live-dot me-2"></span>อัปเดตล่าสุด <span id="lastUpdated">-</span>
      </div>
    </div>
    <div class="d-flex gap-2">
      <select id="shelfFilter" class="form-select"><option value="">ทุกชั้นวาง</option></select>
      <button id="refreshBtn" type="button" class="btn btn-primary">
        <i class="fa-solid fa-rotate me-2"></i>รีเฟรช
      </button>
    </div>
  </header>

  <section class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
      <div class="panel summary-card d-flex align-items-center gap-3">
        <div class="summary-icon bg-primary-subtle text-primary"><i class="fa-solid fa-boxes-stacked"></i></div>
        <div><div id="totalProducts" class="summary-number">0</div><div class="small text-secondary">สินค้าทั้งหมด</div></div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="panel summary-card d-flex align-items-center gap-3">
        <div class="summary-icon bg-success-subtle text-success"><i class="fa-solid fa-circle-check"></i></div>
        <div><div id="fullCount" class="summary-number">0</div><div class="small text-secondary">สินค้าเพียงพอ</div></div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="panel summary-card d-flex align-items-center gap-3">
        <div class="summary-icon bg-warning-subtle text-warning"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div><div id="lowCount" class="summary-number">0</div><div class="small text-secondary">สินค้าเหลือน้อย</div></div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="panel summary-card d-flex align-items-center gap-3">
        <div class="summary-icon bg-danger-subtle text-danger"><i class="fa-solid fa-circle-xmark"></i></div>
        <div><div id="outCount" class="summary-number">0</div><div class="small text-secondary">สินค้าหมด</div></div>
      </div>
    </div>
  </section>

  <section class="panel p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="h5 fw-bold mb-0">สถานะสินค้าล่าสุด</h2>
      <span class="small text-secondary">ตรวจจับด้วย YOLO11</span>
    </div>
    <div id="productGrid" class="row g-3"></div>
  </section>

  <section class="panel p-3 p-md-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="h5 fw-bold mb-0">การแจ้งเตือนล่าสุด</h2>
      <a href="../alerts/index.php" class="btn btn-sm btn-outline-secondary">ดูทั้งหมด</a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>เวลา</th><th>สินค้า</th><th>ชั้นวาง</th><th>คงเหลือ</th><th>สถานะ</th></tr></thead>
        <tbody id="alertTable"></tbody>
      </table>
    </div>
  </section>
</main>
<?php page_end(); ?>
