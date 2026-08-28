$(function () {
  if (!initLayout()) return;
  const params = new URLSearchParams(location.search);
  if (params.get("q")) $("#q").val(params.get("q"));

  api("GET", "/shelves.php").done(function (res) {
    fillShelfSelect($("#shelf"), res.data);
  });
  $("#q").on("input", load);
  $("#status, #shelf").on("change", load);
  load();
});

function load() {
  const query = $.param({
    q: $("#q").val(),
    status: $("#status").val(),
    shelf: $("#shelf").val(),
  });
  api("GET", "/inventory.php?" + query).done(function (res) {
    const rows = res.data || [];
    if (!rows.length) {
      $("#table").html(emptyRow(6, "ไม่พบสินค้า"));
      return;
    }
    let html = "";
    rows.forEach(function (row) {
      const s = statusInfo(row);
      html += `<tr>
        <td><div class="fw-semibold">${esc(row.product_name)}</div>
            <div class="small text-secondary">${esc(row.product_code)}</div></td>
        <td>${esc(row.shelf_code)}</td>
        <td>${esc(row.quantity)}</td>
        <td>${esc(row.capacity)}</td>
        <td><span class="badge ${s.badge}">${s.text}</span></td>
        <td>${esc(row.last_detected_at || "-")}</td>
      </tr>`;
    });
    $("#table").html(html);
  });
}
