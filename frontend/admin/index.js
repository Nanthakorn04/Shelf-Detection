let products = [], shelves = [], items = [], users = [];
let pModal, sModal, aModal, iModal, uModal;

$(function () {
  if (!requireAdmin()) return;
  pModal = new bootstrap.Modal("#pModal");
  sModal = new bootstrap.Modal("#sModal");
  aModal = new bootstrap.Modal("#aModal");
  iModal = new bootstrap.Modal("#iModal");
  uModal = new bootstrap.Modal("#uModal");

  $("#pAdd").on("click", function () { openP(); });
  $("#pForm").on("submit", saveP);
  $("#pSearch").on("input", renderP);
  $("#pTable").on("click", ".edit", function () { openP(find(products, $(this).data("id"))); });
  $("#pTable").on("click", ".del", function () { delP($(this).data("id")); });

  $("#sAdd").on("click", function () { openS(); });
  $("#sForm").on("submit", saveS);
  $("#sTable").on("click", ".edit", function () { openS(find(shelves, $(this).data("id"))); });
  $("#sTable").on("click", ".del", function () { delS($(this).data("id")); });

  $("#aShelf").on("change", loadItems);
  $("#aAdd").on("click", openA);
  $("#aForm").on("submit", saveA);
  $("#aTable").on("click", ".edit", function () { openI($(this).data()); });
  $("#aTable").on("click", ".del", function () { unassign($(this).data("pid")); });
  $("#iForm").on("submit", saveI);

  $("#uAdd").on("click", function () {
    $("#uName").val("");
    $("#uPass").val("");
    uModal.show();
  });
  $("#uForm").on("submit", saveU);
  $("#uTable").on("click", ".del", function () { delU($(this).data("id")); });

  loadP();
  loadS();
  loadU();
});

function find(arr, id) {
  return arr.find(function (x) { return Number(x.id) === Number(id); });
}

function btns(id) {
  return `<td class="text-end">
    <button type="button" class="btn btn-sm btn-outline-primary edit" data-id="${id}">แก้</button>
    <button type="button" class="btn btn-sm btn-outline-danger del" data-id="${id}">ลบ</button>
  </td>`;
}

function loadP() {
  api("GET", "/products.php").done(function (res) {
    products = res.data || [];
    renderP();
    fillAProducts();
  });
}

function renderP() {
  const q = $.trim($("#pSearch").val()).toLowerCase();
  const rows = products.filter(function (p) {
    return !q || (p.product_code + p.product_name + p.yolo_class_name).toLowerCase().indexOf(q) >= 0;
  });
  if (!rows.length) { $("#pTable").html(emptyRow(4, "ยังไม่มีสินค้า")); return; }
  $("#pTable").html(rows.map(function (p) {
    return `<tr><td>${esc(p.product_code)}</td><td>${esc(p.product_name)}</td><td>${esc(p.yolo_class_name)}</td>${btns(p.id)}</tr>`;
  }).join(""));
}

function openP(row) {
  $("#pId").val(row ? row.id : "");
  $("#pCode").val(row ? row.product_code : "");
  $("#pName").val(row ? row.product_name : "");
  $("#pYolo").val(row ? row.yolo_class_name : "");
  $("#pTitle").text(row ? "แก้ไขสินค้า" : "เพิ่มสินค้า");
  pModal.show();
}

function saveP(e) {
  e.preventDefault();
  const id = $("#pId").val();
  const body = {
    product_code: $.trim($("#pCode").val()),
    product_name: $.trim($("#pName").val()),
    yolo_class_name: $.trim($("#pYolo").val()),
  };
  const req = id ? api("PUT", "/products.php", Object.assign({ id: Number(id) }, body))
                 : api("POST", "/products.php", body);
  req.done(function () { pModal.hide(); toast("บันทึกสินค้าแล้ว"); loadP(); })
     .fail(function (xhr) { toast((xhr.responseJSON && xhr.responseJSON.message) || "บันทึกไม่สำเร็จ", "danger"); });
}

function delP(id) {
  if (!confirm("ลบสินค้านี้?")) return;
  api("DELETE", "/products.php?id=" + id).done(function () { toast("ลบแล้ว"); loadP(); loadItems(); });
}

function loadS() {
  api("GET", "/shelves.php").done(function (res) {
    shelves = res.data || [];
    if (!shelves.length) $("#sTable").html(emptyRow(3, "ยังไม่มีชั้นวาง"));
    else $("#sTable").html(shelves.map(function (s) {
      return `<tr><td>${esc(s.shelf_code)}</td><td>${esc(s.shelf_name)}</td>${btns(s.id)}</tr>`;
    }).join(""));
    const cur = $("#aShelf").val();
    let opt = '<option value="">เลือกชั้นวาง</option>';
    shelves.forEach(function (s) {
      opt += `<option value="${s.id}">${esc(s.shelf_code)} — ${esc(s.shelf_name)}</option>`;
    });
    $("#aShelf").html(opt).val(cur);
  });
}

function openS(row) {
  $("#sId").val(row ? row.id : "");
  $("#sCode").val(row ? row.shelf_code : "");
  $("#sName").val(row ? row.shelf_name : "");
  $("#sTitle").text(row ? "แก้ไขชั้นวาง" : "เพิ่มชั้นวาง");
  sModal.show();
}

function saveS(e) {
  e.preventDefault();
  const id = $("#sId").val();
  const body = { shelf_code: $.trim($("#sCode").val()), shelf_name: $.trim($("#sName").val()) };
  const req = id ? api("PUT", "/shelves.php", Object.assign({ id: Number(id) }, body))
                 : api("POST", "/shelves.php", Object.assign({ action: "create" }, body));
  req.done(function () { sModal.hide(); toast("บันทึกชั้นวางแล้ว"); loadS(); });
}

function delS(id) {
  if (!confirm("ลบชั้นวางนี้?")) return;
  api("DELETE", "/shelves.php?id=" + id).done(function () {
    if (String($("#aShelf").val()) === String(id)) $("#aShelf").val("");
    toast("ลบแล้ว"); loadS(); loadItems();
  });
}

function shelfId() { return Number($("#aShelf").val() || 0); }

function loadItems() {
  const id = shelfId();
  $("#aAdd").prop("disabled", !id);
  if (!id) { items = []; $("#aTable").html(emptyRow(6, "เลือกชั้นวาง")); return; }
  api("GET", "/shelves.php?id=" + id).done(function (res) {
    items = (res.data && res.data.inventory) || [];
    if (!items.length) $("#aTable").html(emptyRow(6, "ยังไม่มีสินค้าบนชั้นนี้"));
    else $("#aTable").html(items.map(function (r) {
      const s = statusInfo(r);
      return `<tr>
        <td>${esc(r.product_name)}<div class="small text-secondary">${esc(r.product_code)}</div></td>
        <td>${esc(r.quantity)}</td><td>${esc(r.capacity)}</td><td>${esc(r.low_stock_threshold)}</td>
        <td><span class="badge ${s.badge}">${s.text}</span></td>
        <td class="text-end">
          <button type="button" class="btn btn-sm btn-outline-primary edit"
            data-inventory-id="${r.inventory_id}" data-quantity="${r.quantity}"
            data-capacity="${r.capacity}" data-threshold="${r.low_stock_threshold}">แก้</button>
          <button type="button" class="btn btn-sm btn-outline-danger del" data-pid="${r.product_id}">ถอด</button>
        </td></tr>`;
    }).join(""));
    fillAProducts();
  });
}

function fillAProducts() {
  const used = {};
  items.forEach(function (r) { used[r.product_id] = true; });
  const avail = products.filter(function (p) { return !used[p.id]; });
  $("#aProduct").html(avail.length
    ? avail.map(function (p) { return `<option value="${p.id}">${esc(p.product_code)} — ${esc(p.product_name)}</option>`; }).join("")
    : '<option value="">ไม่มีสินค้าเหลือ</option>');
}

function openA() {
  fillAProducts();
  $("#aQty").val(0); $("#aCap").val(8); $("#aLow").val(2);
  aModal.show();
}

function saveA(e) {
  e.preventDefault();
  api("POST", "/shelves.php", {
    action: "assign",
    shelf_id: shelfId(),
    product_id: Number($("#aProduct").val()),
    quantity: Number($("#aQty").val()),
    capacity: Number($("#aCap").val()),
    low_stock_threshold: Number($("#aLow").val()),
  }).done(function () { aModal.hide(); toast("จัดสินค้าแล้ว"); loadItems(); });
}

function openI(d) {
  $("#iId").val(d.inventoryId);
  $("#iQty").val(d.quantity);
  $("#iCap").val(d.capacity);
  $("#iLow").val(d.threshold);
  iModal.show();
}

function saveI(e) {
  e.preventDefault();
  api("POST", "/shelves.php", {
    action: "update_inventory",
    inventory_id: Number($("#iId").val()),
    quantity: Number($("#iQty").val()),
    capacity: Number($("#iCap").val()),
    low_stock_threshold: Number($("#iLow").val()),
  }).done(function () { iModal.hide(); toast("อัปเดตสต็อกแล้ว"); loadItems(); });
}

function loadU() {
  api("GET", "/users.php").done(function (res) {
    users = res.data || [];
    const me = currentUser().username;
    if (!users.length) { $("#uTable").html(emptyRow(4, "ยังไม่มีผู้ใช้")); return; }
    $("#uTable").html(users.map(function (u) {
      const canDel = u.role !== "admin" && u.username !== me;
      const delBtn = canDel
        ? `<button type="button" class="btn btn-sm btn-outline-danger del" data-id="${u.id}">ลบ</button>`
        : "";
      const role = u.role === "admin"
        ? '<span class="badge text-bg-primary">admin</span>'
        : '<span class="badge text-bg-secondary">พนักงาน</span>';
      return `<tr>
        <td>${esc(u.username)}</td>
        <td>${role}</td>
        <td>${esc(u.created_at || "-")}</td>
        <td class="text-end">${delBtn}</td>
      </tr>`;
    }).join(""));
  });
}

function saveU(e) {
  e.preventDefault();
  api("POST", "/users.php", {
    username: $.trim($("#uName").val()),
    password: $("#uPass").val(),
  }).done(function () {
    uModal.hide();
    toast("เพิ่มพนักงานแล้ว");
    loadU();
  }).fail(function (xhr) {
    toast((xhr.responseJSON && xhr.responseJSON.message) || "เพิ่มไม่สำเร็จ", "danger");
  });
}

function delU(id) {
  if (!confirm("ลบพนักงานคนนี้?")) return;
  api("DELETE", "/users.php?id=" + id).done(function () {
    toast("ลบพนักงานแล้ว");
    loadU();
  }).fail(function (xhr) {
    toast((xhr.responseJSON && xhr.responseJSON.message) || "ลบไม่สำเร็จ", "danger");
  });
}

function unassign(pid) {
  if (!confirm("ถอดสินค้าออกจากชั้น?")) return;
  api("POST", "/shelves.php", {
    action: "unassign",
    shelf_id: shelfId(),
    product_id: Number(pid),
  }).done(function () { toast("ถอดออกแล้ว"); loadItems(); });
}
