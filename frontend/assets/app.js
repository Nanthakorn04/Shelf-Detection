const API = "/Shelf-Detection/backend/api";
const TOKEN_KEY = "shelf_token";
const USER_KEY = "shelf_user";

function authHeader() {
  const token = localStorage.getItem(TOKEN_KEY);
  return token ? { Authorization: "Bearer " + token } : {};
}

function currentUser() {
  try { return JSON.parse(localStorage.getItem(USER_KEY) || "{}"); }
  catch (e) { return {}; }
}

function logout() {
  localStorage.removeItem(TOKEN_KEY);
  localStorage.removeItem(USER_KEY);
  location.href = "../login/index.php";
}

function initLayout() {
  if (!localStorage.getItem(TOKEN_KEY)) {
    location.href = "../login/index.php";
    return false;
  }
  const user = currentUser();
  $("#currentUser").text((user.username || "ผู้ใช้") + (user.role ? " · " + user.role : ""));
  if (user.role === "admin") $(".admin-only").removeClass("d-none");
  $("#logoutBtn").on("click", logout);
  return true;
}

function requireAdmin() {
  if (!initLayout()) return false;
  if (currentUser().role !== "admin") {
    location.href = "../dashboard/index.php";
    return false;
  }
  return true;
}

function api(method, path, data) {
  const opt = {
    type: method,
    url: API + path,
    headers: authHeader(),
    dataType: "json",
  };
  if (data) {
    opt.contentType = "application/json";
    opt.data = JSON.stringify(data);
  }
  return $.ajax(opt).fail(function (xhr) {
    if (xhr.status === 401) logout();
  });
}

function esc(v) {
  return String(v ?? "")
    .replace(/&/g, "&amp;").replace(/</g, "&lt;")
    .replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}

function statusInfo(row) {
  const q = Number(row.quantity ?? 0);
  const cap = Number(row.capacity ?? 0);
  const low = Number(row.low_stock_threshold ?? 2);
  if (q === 0) return { key: "out", text: "หมด", badge: "bg-danger", card: "out" };
  if (q <= low) return { key: "low", text: "เหลือน้อย", badge: "bg-warning text-dark", card: "low" };
  if (cap > 0 && q >= cap) return { key: "full", text: "เต็ม", badge: "bg-primary", card: "full" };
  return { key: "ok", text: "ปกติ", badge: "bg-success", card: "full" };
}

function emptyRow(cols, text) {
  return `<tr><td colspan="${cols}" class="text-center text-secondary py-4">${text}</td></tr>`;
}

function fillShelfSelect($el, items, placeholder) {
  let html = `<option value="">${placeholder || "ทุกชั้นวาง"}</option>`;
  (items || []).forEach(function (s) {
    html += `<option value="${esc(s.shelf_code)}">${esc(s.shelf_code)} — ${esc(s.shelf_name)}</option>`;
  });
  $el.html(html);
}

function toast(msg, type) {
  const $el = $("#pageAlert");
  if (!$el.length) { window.alert(msg); return; }
  $el.removeClass("d-none alert-success alert-danger")
    .addClass("alert-" + (type || "success"))
    .text(msg);
  clearTimeout(toast.t);
  toast.t = setTimeout(function () { $el.addClass("d-none"); }, 3000);
}
