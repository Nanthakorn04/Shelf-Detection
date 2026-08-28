<?php
require __DIR__ . '/../includes/layout.php';
page_start('จัดการระบบ', 'admin');
?>
<main class="main-content">
  <header class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 fw-bold mb-1">จัดการระบบ</h1>
      <div class="text-secondary">สินค้า ชั้นวาง และพนักงาน</div>
    </div>
    <span class="badge text-bg-primary">Admin</span>
  </header>
  <div id="pageAlert" class="alert d-none"></div>

  <section class="panel p-3 p-md-4">
    <ul class="nav nav-tabs mb-4">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabP">สินค้า</button></li> 
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabU">พนักงาน</button></li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane fade show active" id="tabP">
        <div class="d-flex justify-content-between gap-2 mb-3">
          <input id="pSearch" class="form-control" style="max-width:260px" placeholder="ค้นหาสินค้า...">
          <button id="pAdd" class="btn btn-primary" type="button"><i class="fa-solid fa-plus me-1"></i>เพิ่ม</button>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>รหัส</th><th>ชื่อ</th><th>YOLO</th><th></th></tr></thead>
            <tbody id="pTable"></tbody>
          </table>
        </div>
      </div>

    

      <div class="tab-pane fade" id="tabA">
        <div class="d-flex flex-wrap gap-2 mb-3">
          <select id="aShelf" class="form-select" style="max-width:280px"><option value="">เลือกชั้นวาง</option></select>
          <button id="aAdd" class="btn btn-primary" type="button" disabled>เพิ่มสินค้าบนชั้นนี้</button>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>สินค้า</th><th>คงเหลือ</th><th>ความจุ</th><th>จุดเตือน</th><th>สถานะ</th><th></th></tr></thead>
            <tbody id="aTable"></tbody>
          </table>
        </div>
      </div>

      <div class="tab-pane fade" id="tabU">
        <div class="text-end mb-3">
          <button id="uAdd" class="btn btn-primary" type="button"><i class="fa-solid fa-plus me-1"></i>เพิ่มพนักงาน</button>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>ชื่อผู้ใช้</th><th>สิทธิ์</th><th>สร้างเมื่อ</th><th></th></tr></thead>
            <tbody id="uTable"></tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</main>

<div class="modal fade" id="pModal" tabindex="-1">
  <div class="modal-dialog"><form id="pForm" class="modal-content">
    <div class="modal-header"><h2 class="modal-title h5" id="pTitle">สินค้า</h2><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
    <div class="modal-body">
      <input type="hidden" id="pId">
      <label class="form-label">รหัส</label><input id="pCode" class="form-control mb-2" required>
      <label class="form-label">ชื่อ</label><input id="pName" class="form-control mb-2" required>
      <label class="form-label">YOLO class</label><input id="pYolo" class="form-control" required>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">ยกเลิก</button>
      <button class="btn btn-primary" type="submit">บันทึก</button>
    </div>
  </form></div>
</div>

<div class="modal fade" id="sModal" tabindex="-1">
  <div class="modal-dialog"><form id="sForm" class="modal-content">
    <div class="modal-header"><h2 class="modal-title h5" id="sTitle">ชั้นวาง</h2><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
    <div class="modal-body">
      <input type="hidden" id="sId">
      <label class="form-label">รหัสชั้น</label><input id="sCode" class="form-control mb-2" required>
      <label class="form-label">ชื่อชั้นวาง</label><input id="sName" class="form-control" required>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">ยกเลิก</button>
      <button class="btn btn-primary" type="submit">บันทึก</button>
    </div>
  </form></div>
</div>

<div class="modal fade" id="aModal" tabindex="-1">
  <div class="modal-dialog"><form id="aForm" class="modal-content">
    <div class="modal-header"><h2 class="modal-title h5">เพิ่มสินค้าบนชั้น</h2><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
    <div class="modal-body">
      <label class="form-label">สินค้า</label><select id="aProduct" class="form-select mb-2" required></select>
      <label class="form-label">จำนวน</label><input id="aQty" type="number" min="0" class="form-control mb-2" value="0">
      <label class="form-label">ความจุ</label><input id="aCap" type="number" min="1" class="form-control mb-2" value="8">
      <label class="form-label">จุดเตือน</label><input id="aLow" type="number" min="0" class="form-control" value="2">
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">ยกเลิก</button>
      <button class="btn btn-primary" type="submit">เพิ่ม</button>
    </div>
  </form></div>
</div>

<div class="modal fade" id="iModal" tabindex="-1">
  <div class="modal-dialog"><form id="iForm" class="modal-content">
    <div class="modal-header"><h2 class="modal-title h5">แก้สต็อก</h2><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
    <div class="modal-body">
      <input type="hidden" id="iId">
      <label class="form-label">จำนวน</label><input id="iQty" type="number" min="0" class="form-control mb-2" required>
      <label class="form-label">ความจุ</label><input id="iCap" type="number" min="1" class="form-control mb-2" required>
      <label class="form-label">จุดเตือน</label><input id="iLow" type="number" min="0" class="form-control" required>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">ยกเลิก</button>
      <button class="btn btn-primary" type="submit">บันทึก</button>
    </div>
  </form></div>
</div>
<div class="modal fade" id="uModal" tabindex="-1">
  <div class="modal-dialog"><form id="uForm" class="modal-content">
    <div class="modal-header"><h2 class="modal-title h5">เพิ่มพนักงาน</h2><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
    <div class="modal-body">
      <label class="form-label">ชื่อผู้ใช้</label>
      <input id="uName" class="form-control mb-2" required minlength="3">
      <label class="form-label">รหัสผ่าน</label>
      <input id="uPass" type="password" class="form-control" required minlength="4">
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">ยกเลิก</button>
      <button class="btn btn-primary" type="submit">เพิ่ม</button>
    </div>
  </form></div>
</div>
<?php page_end(); ?>
