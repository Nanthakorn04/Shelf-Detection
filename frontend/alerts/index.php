<?php
require __DIR__ . '/../includes/layout.php';
page_start('การแจ้งเตือน', 'alerts');
?>
<main class="main-content">
  <header class="mb-4">
    <h1 class="h3 fw-bold mb-1">การแจ้งเตือน</h1>
    <div class="text-secondary">สินค้าที่เหลือน้อยหรือหมด</div>
  </header>

  <section class="panel p-3 p-md-4">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr><th>เวลา</th><th>สินค้า</th><th>ชั้นวาง</th><th>คงเหลือ</th><th>จุดเตือน</th><th>สถานะ</th></tr>
        </thead>
        <tbody id="table"></tbody>
      </table>
    </div>
  </section>
</main>
<?php page_end(); ?>
