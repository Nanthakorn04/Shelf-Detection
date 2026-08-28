<?php
require __DIR__ . '/../includes/layout.php';
page_start('สินค้า', 'products');
?>
<main class="main-content">
  <header class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
      <h1 class="h3 fw-bold mb-1">สินค้าบนชั้นวาง</h1>
      <div class="text-secondary">ดูรายการสินค้าและสถานะสต็อก</div>
    </div>
  </header>

  <section class="panel p-3 p-md-4">
    <div class="row g-2 mb-3">
      <div class="col-md-4"><input id="q" class="form-control" placeholder="ค้นหาชื่อ / รหัสสินค้า"></div>
      <div class="col-md-3">
        <select id="status" class="form-select">
          <option value="">ทุกสถานะ</option>
          <option value="Full">เต็ม</option>
          <option value="Normal">ปกติ</option>
          <option value="Low Stock">เหลือน้อย</option>
          <option value="Out of Stock">หมด</option>
        </select>
      </div>
      <div class="col-md-3"><select id="shelf" class="form-select"><option value="">ทุกชั้นวาง</option></select></div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr><th>สินค้า</th><th>ชั้นวาง</th><th>คงเหลือ</th><th>ความจุ</th><th>สถานะ</th><th>ตรวจล่าสุด</th></tr>
        </thead>
        <tbody id="table"></tbody>
      </table>
    </div>
  </section>
</main>
<?php page_end(); ?>
