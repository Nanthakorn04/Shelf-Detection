$(function () {
  if (!initLayout()) return;
  api("GET", "/inventory.php").done(function (res) {
    const rows = (res.data || []).sort(function (a, b) {
      return String(b.last_detected_at || b.updated_at || "")
        .localeCompare(String(a.last_detected_at || a.updated_at || ""));
    });
    if (!rows.length) {
      $("#table").html(emptyRow(5, "ยังไม่มีประวัติ"));
      return;
    }
    let html = "";
    rows.forEach(function (row) {
      const s = statusInfo(row);
      html += `<tr>
        <td>${esc(row.last_detected_at || row.updated_at || "-")}</td>
        <td>${esc(row.product_name)}</td>
        <td>${esc(row.shelf_code)}</td>
        <td>${esc(row.quantity)} / ${esc(row.capacity)}</td>
        <td><span class="badge ${s.badge}">${s.text}</span></td>
      </tr>`;
    });
    $("#table").html(html);
  });
});
