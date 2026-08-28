<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>เข้าสู่ระบบ — Shelf Monitor</title>

  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    rel="stylesheet"
  >

  <style>
    :root {
      --sidebar: #111827;
      --background: #f3f6fb;
      --border: #e5e7eb;
    }

    body {
      min-height: 100vh;
      margin: 0;
      background: var(--background);
      color: #1f2937;
    }

    .login-shell {
      min-height: 100vh;
    }

    .brand-panel {
      position: relative;
      overflow: hidden;
      background: var(--sidebar);
      color: #fff;
    }

    .brand-panel::before {
      position: absolute;
      inset: auto -80px -80px auto;
      width: 280px;
      height: 280px;
      border-radius: 50%;
      background: rgba(59, 130, 246, 0.18);
      content: "";
    }

    .brand-panel::after {
      position: absolute;
      top: -60px;
      left: -40px;
      width: 180px;
      height: 180px;
      border-radius: 50%;
      background: rgba(34, 197, 94, 0.12);
      content: "";
    }

    .brand-content {
      position: relative;
      z-index: 1;
      max-width: 420px;
    }

    .brand-mark {
      display: grid;
      width: 56px;
      height: 56px;
      place-items: center;
      border-radius: 16px;
      background: #1f2937;
      color: #60a5fa;
      font-size: 1.4rem;
    }

    .feature-item {
      display: flex;
      gap: 12px;
      color: #d1d5db;
    }

    .feature-item i {
      margin-top: 3px;
      color: #60a5fa;
    }

    .form-panel {
      display: grid;
      place-items: center;
      padding: 32px 20px;
    }

    .login-card {
      width: 100%;
      max-width: 420px;
      padding: 36px 32px;
      border: 1px solid var(--border);
      border-radius: 16px;
      background: #fff;
      box-shadow: 0 4px 20px rgba(31, 41, 55, 0.04);
    }

    .form-control {
      padding: 11px 14px;
      border-radius: 10px;
    }

    .input-group .btn {
      border-color: #dee2e6;
    }

    .btn-login {
      padding: 11px 16px;
      border-radius: 10px;
      font-weight: 600;
    }

    @media (max-width: 991px) {
      .brand-panel {
        min-height: auto;
        padding: 28px 20px 24px;
      }

      .brand-panel .lead,
      .brand-panel .feature-item {
        display: none;
      }
    }
  </style>
</head>
<body>
  <div class="login-shell row g-0">
    <aside class="col-lg-5 brand-panel d-flex align-items-center p-4 p-xl-5">
      <div class="brand-content">
        <div class="brand-mark mb-4">
          <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <h1 class="h3 fw-bold mb-2">Shelf Monitor</h1>
        <p class="lead text-white-50 mb-4">
          ระบบติดตามสินค้าบนชั้นวางแบบเรียลไทม์
        </p>
        <div class="d-grid gap-3">
          <div class="feature-item">
            <i class="fa-solid fa-eye"></i>
            <div>ตรวจจับสินค้าด้วย YOLO11</div>
          </div>
          <div class="feature-item">
            <i class="fa-solid fa-bell"></i>
            <div>แจ้งเตือนเมื่อสต็อกเหลือน้อยหรือหมด</div>
          </div>
          <div class="feature-item">
            <i class="fa-solid fa-chart-pie"></i>
            <div>ดูภาพรวมชั้นวางได้ทันที</div>
          </div>
        </div>
      </div>
    </aside>

    <main class="col-lg-7 form-panel">
      <div class="login-card">
        <h2 class="h4 fw-bold mb-1">เข้าสู่ระบบ</h2>
        <p class="text-secondary mb-4">กรอกบัญชีผู้ใช้เพื่อเข้าใช้งานแดชบอร์ด</p>

        <div id="loginAlert" class="alert alert-danger d-none" role="alert"></div>

        <form id="loginForm" autocomplete="on" novalidate>
          <div class="mb-3">
            <label for="username" class="form-label">ชื่อผู้ใช้</label>
            <div class="input-group">
              <span class="input-group-text bg-white">
                <i class="fa-solid fa-user text-secondary"></i>
              </span>
              <input
                id="username"
                name="username"
                type="text"
                class="form-control"
                placeholder="เช่น admin"
                required
                autofocus
              >
            </div>
          </div>

          <div class="mb-4">
            <label for="password" class="form-label">รหัสผ่าน</label>
            <div class="input-group">
              <span class="input-group-text bg-white">
                <i class="fa-solid fa-lock text-secondary"></i>
              </span>
              <input
                id="password"
                name="password"
                type="password"
                class="form-control"
                placeholder="กรอกรหัสผ่าน"
                required
              >
              <button
                id="togglePassword"
                class="btn btn-outline-secondary"
                type="button"
                aria-label="แสดงรหัสผ่าน"
              >
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>

          <button id="loginBtn" type="submit" class="btn btn-primary btn-login w-100">
            <span class="btn-label">
              <i class="fa-solid fa-right-to-bracket me-2"></i>
              เข้าสู่ระบบ
            </span>
            <span class="btn-loading d-none">
              <span class="spinner-border spinner-border-sm me-2" role="status"></span>
              กำลังเข้าสู่ระบบ...
            </span>
          </button>
        </form>
      </div>
    </main>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="index.js"></script>
</body>
</html>
