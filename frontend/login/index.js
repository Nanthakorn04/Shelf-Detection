const API_BASE = "/Shelf-Detection/backend/api";
const TOKEN_KEY = "shelf_token";
const USER_KEY = "shelf_user";
const DASHBOARD_URL = "../dashboard/index.php";
const ADMIN_URL = "../admin/index.php";

$(document).ready(function () {
  redirectIfLoggedIn();

  $("#togglePassword").on("click", function () {
    const $input = $("#password");
    const isHidden = $input.attr("type") === "password";
    $input.attr("type", isHidden ? "text" : "password");
    $(this)
      .find("i")
      .toggleClass("fa-eye fa-eye-slash");
  });

  $("#loginForm").on("submit", function (event) {
    event.preventDefault();
    login();
  });
});

function redirectIfLoggedIn() {
  const token = localStorage.getItem(TOKEN_KEY);
  if (!token) {
    return;
  }

  $.ajax({
    type: "GET",
    url: API_BASE + "/auth.php",
    headers: { Authorization: "Bearer " + token },
    dataType: "json",
    success: function (response) {
      if (response && response.success) {
        window.location.href = homeUrl({ role: response.role });
      }
    },
    error: function () {
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(USER_KEY);
    },
  });
}

function login() {
  const username = $.trim($("#username").val());
  const password = $("#password").val();

  hideAlert();

  if (!username || !password) {
    showAlert("กรุณากรอกชื่อผู้ใช้และรหัสผ่าน");
    return;
  }

  setLoading(true);

  $.ajax({
    type: "POST",
    url: API_BASE + "/login.php",
    contentType: "application/json",
    dataType: "json",
    data: JSON.stringify({ username, password }),
    success: function (response) {
      if (!response || response.success !== true || !response.token) {
        showAlert(response && response.message ? response.message : "เข้าสู่ระบบไม่สำเร็จ");
        setLoading(false);
        return;
      }

      localStorage.setItem(TOKEN_KEY, response.token);
      localStorage.setItem(USER_KEY, JSON.stringify(response.user || {}));
      window.location.href = homeUrl(response.user);
    },
    error: function (xhr) {
      const message = xhr.responseJSON && xhr.responseJSON.message;
      if (xhr.status === 401) {
        showAlert("ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง");
      } else if (xhr.status === 400) {
        showAlert("กรุณากรอกชื่อผู้ใช้และรหัสผ่าน");
      } else {
        showAlert(message || "ไม่สามารถเข้าสู่ระบบได้ กรุณาลองใหม่");
      }
      setLoading(false);
    },
  });
}

function setLoading(isLoading) {
  const $btn = $("#loginBtn");
  $btn.prop("disabled", isLoading);
  $btn.find(".btn-label").toggleClass("d-none", isLoading);
  $btn.find(".btn-loading").toggleClass("d-none", !isLoading);
}

function showAlert(message) {
  $("#loginAlert").removeClass("d-none").text(message);
}

function hideAlert() {
  $("#loginAlert").addClass("d-none").text("");
}

function homeUrl(user) {
  return user && user.role === "admin" ? ADMIN_URL : DASHBOARD_URL;
}
