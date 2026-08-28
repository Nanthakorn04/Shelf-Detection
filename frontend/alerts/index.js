$(function () {
  if (!initLayout()) return;
  api("GET", "/inventory.php?alerts=1").done(function (res) {
    const rows = res.data || [];
    if (!rows.length) {
      $("#table").html(emptyRow(6, "ไม่มีการแจ้งเตือน"));
      return;
    }
    let html = "";
    rows.forEach(function (row) {
      const s = statusInfo(row);
      html += `<tr>
        <td>${esc(row.last_detected_at || row.updated_at || "-")}</td>
        <td><div class="fw-semibold">${esc(row.product_name)}</div>
            <div class="small text-secondary">${esc(row.product_code)}</div></td>
        <td>${esc(row.shelf_code)}</td>
        <td><strong>${esc(row.quantity)}</strong> / ${esc(row.capacity)}</td>
        <td>${esc(row.low_stock_threshold)}</td>
        <td><span class="badge ${s.badge}">${s.text}</span></td>
      </tr>`;
    });
    $("#table").html(html);
  });
});
