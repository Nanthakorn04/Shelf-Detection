<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Shelf Monitor</title>
  <script>
    (function () {
      var token = localStorage.getItem("shelf_token");
      if (!token) {
        window.location.replace("frontend/login/index.php");
        return;
      }
      var user = {};
      try {
        user = JSON.parse(localStorage.getItem("shelf_user") || "{}");
      } catch (e) {}
      window.location.replace(
        user.role === "admin" ? "frontend/admin/index.php" : "frontend/dashboard/index.php"
      );
    })();
  </script>
  <noscript>
    <meta http-equiv="refresh" content="0;url=frontend/login/index.php">
  </noscript>
</head>
<body>
  <p>กำลังเปิดระบบ… <a href="frontend/login/index.php">เข้าสู่ระบบ</a></p>
</body>
</html>
