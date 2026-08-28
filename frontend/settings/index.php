<?php
require __DIR__ . '/../includes/layout.php';
page_start('ตั้งค่า', 'settings');
?>
<main class="main-content">
  <header class="mb-4">
    <h1 class="h3 fw-bold mb-1">ตั้งค่า</h1>
    <div class="text-secondary">ข้อมูลบัญชี สถานะระบบ และแจ้งเตือน LINE OA</div>
  </header>

  <div class="row g-3">
    <div class="col-lg-6">
      <section class="panel p-4">
        <h2 class="h5 fw-bold mb-3">บัญชีผู้ใช้</h2>
        <div class="mb-2"><span class="text-secondary">ชื่อผู้ใช้</span><div id="uName" class="fw-semibold">-</div></div>
        <div class="mb-2"><span class="text-secondary">สิทธิ์</span><div id="uRole">-</div></div>
        <div><span class="text-secondary">สร้างเมื่อ</span><div id="uCreated">-</div></div>
      </section>
    </div>
    <div class="col-lg-6">
      <section class="panel p-4">
        <h2 class="h5 fw-bold mb-3">สถานะระบบ</h2>
        <div class="mb-2"><span class="text-secondary">API</span><div id="hMsg">กำลังตรวจสอบ...</div></div>
        <div class="mb-2"><span class="text-secondary">ฐานข้อมูล</span><div id="hDb">-</div></div>
        <div><span class="text-secondary">เวลาเซิร์ฟเวอร์</span><div id="hTime">-</div></div>
      </section>
    </div>
  </div>

  <section id="lineBox" class="panel p-4 mt-3 d-none">
    <h2 class="h5 fw-bold mb-2">แจ้งเตือน LINE OA</h2>
    <p class="small text-secondary mb-3">
      กดส่งแจ้งเตือนเพื่อยิงรายการสินค้าที่เหลือน้อยหรือหมดเข้า LINE ตามข้อมูลตอนนี้
      กล้อง <code>python detect.py</code> จะส่งให้อัตโนมัติเมื่อจำนวนเปลี่ยน
      User ID ต้องเป็นรหัสยาวที่ขึ้นต้นด้วย U (คน) หรือ C (กลุ่ม) ไม่ใช่ชื่อเล่น
    </p>
    <div id="pageAlert" class="alert d-none"></div>
    <div class="form-check form-switch mb-3">
      <input id="lineEnabled" class="form-check-input" type="checkbox">
      <label class="form-check-label" for="lineEnabled">เปิดแจ้งเตือน</label>
    </div>
    <div class="mb-3">
      <label class="form-label">Channel Access Token</label>
      <input id="lineToken" type="text" class="form-control" autocomplete="off" spellcheck="false" placeholder="วาง Long-lived channel access token">
      <div id="lineTokenHint" class="form-text"></div>
    </div>
    <div class="mb-3">
      <label class="form-label">User ID / Group ID (ไม่บังคับ)</label>
      <input id="lineUser" class="form-control" autocomplete="off" placeholder="U... หรือ C... หรือเว้นว่างเพื่อ broadcast">
      <div class="form-text">เว้นว่างไว้ก่อนได้ ถ้ายังไม่มี User ID</div>
    </div>
    <div class="d-flex gap-2">
      <button id="lineSave" type="button" class="btn btn-primary">บันทึก</button>
      <button id="lineTest" type="button" class="btn btn-outline-primary">ส่งแจ้งเตือนสต็อก</button>
    </div>
  </section>
</main>
<?php page_end(); ?>
