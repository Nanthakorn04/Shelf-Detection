$(function () {
  if (!initLayout()) return;
  api("GET", "/shelves.php").done(function (res) {
    fillShelfSelect($("#shelfFilter"), res.data);
  });
  $("#refreshBtn").on("click", load);
  $("#shelfFilter").on("change", load);
  load();
  setInterval(load, 2000);
});

function load() {
  const shelf = $("#shelfFilter").val();
  const q = shelf ? "?shelf=" + encodeURIComponent(shelf) : "";
  api("GET", "/inventory.php" + q).done(function (res) {
    const rows = res.data || [];
    renderSummary(rows);
    renderGrid(rows);
    renderAlerts(rows);
    $("#lastUpdated").text(new Date().toLocaleTimeString("th-TH"));
  });
}

function renderSummary(rows) {
  let full = 0, low = 0, out = 0;
  rows.forEach(function (row) {
    const k = statusInfo(row).key;
    if (k === "out") out++;
    else if (k === "low") low++;
    else full++;
  });
  $("#totalProducts").text(rows.length);
  $("#fullCount").text(full);
  $("#lowCount").text(low);
  $("#outCount").text(out);
}

function renderGrid(rows) {
  if (!rows.length) {
    $("#productGrid").html('<div class="col-12 text-center text-secondary py-5">ยังไม่มีข้อมูลสินค้า</div>');
    return;
  }
  let html = "";
  rows.forEach(function (row) {
    const s = statusInfo(row);
    html += `<div class="col-md-6 col-xl-4">
      <article class="panel product-card ${s.card}">
        <div class="d-flex justify-content-between gap-3">
          <div class="d-flex gap-3">
            <div class="product-image"><i class="fa-solid fa-box"></i></div>
            <div>
              <h3 class="h6 fw-bold mb-1">${esc(row.product_name)}</h3>
              <div class="small text-secondary">${esc(row.product_code)}</div>
              <div class="small text-secondary">${esc(row.shelf_name || row.shelf_code)}</div>
            </div>
          </div>
          <span class="badge ${s.badge}">${s.text}</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between align-items-end">
          <span class="text-secondary">จำนวนที่ตรวจพบ</span>
          <span class="stock-count">${esc(row.quantity)}
            <small class="fs-6 fw-normal text-secondary">/ ${esc(row.capacity)} ชิ้น</small>
          </span>
        </div>
      </article>
    </div>`;
  });
  $("#productGrid").html(html);
}

function renderAlerts(rows) {
  const alerts = rows.filter(function (row) {
    const k = statusInfo(row).key;
    return k === "low" || k === "out";
  }).slice(0, 8);

  if (!alerts.length) {
    $("#alertTable").html(emptyRow(5, "ไม่มีการแจ้งเตือน"));
    return;
  }
  let html = "";
  alerts.forEach(function (row) {
    const s = statusInfo(row);
    html += `<tr>
      <td>${esc(row.last_detected_at || row.updated_at || "-")}</td>
      <td><div class="fw-semibold">${esc(row.product_name)}</div>
          <div class="small text-secondary">${esc(row.product_code)}</div></td>
      <td>${esc(row.shelf_code)}</td>
      <td><strong>${esc(row.quantity)}</strong> / ${esc(row.capacity)}</td>
      <td><span class="badge ${s.badge}">${s.text}</span></td>
    </tr>`;
  });
  $("#alertTable").html(html);
}
