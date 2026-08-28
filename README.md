# Shelf Detection

โปรเจกต์ตรวจจับสินค้าบนชั้นวางด้วย YOLO + เว็บจัดการสต็อก

## โฟลเดอร์

```
frontend/     หน้าเว็บ
  login/      เข้าสู่ระบบ
  dashboard/  ภาพรวมสต็อก
  admin/      จัดการสินค้า ชั้นวาง พนักงาน
  products/   รายการสินค้า
  alerts/     ของหมด / เหลือน้อย
  history/    ประวัติตรวจจับ
  settings/   บัญชี + สถานะระบบ + LINE OA
  assets/     CSS / JS กลาง
  includes/   เลย์เอาต์เมนู

backend/      API PHP
  api/        login, inventory, shelves, products, users, line
  config/     ต่อฐานข้อมูล + JWT + LINE

ai/           YOLO
  detect.py   เปิดกล้องด้วย OpenCV แล้วอัปเดตสต็อกทุก 2 วินาที
  line_oa.py  แจ้งเตือน LINE เมื่อเหลือน้อยหรือหมด
  models/     ไฟล์ .pt
```

## Setup เริ่มโปรเจกต์ (คร่าว ๆ)

1. ลง [XAMPP](https://www.apachefriends.org/) แล้วเปิด **Apache** กับ **MySQL**
2. วางโปรเจกต์ที่ `htdocs/Shelf-Detection`
3. เปิด phpMyAdmin สร้างฐานข้อมูลชื่อ `shelf_inventory_db`
4. สร้างตารางหลัก: `users`, `products`, `shelves`, `shelf_inventory` แล้วเพิ่ม admin
5. ลง Python 3 แล้วสร้าง venv

```bash
cd ai
python3 -m venv .venv
source .venv/bin/activate          # Windows: .venv\Scripts\activate
pip install -r requirements.txt
```

6. วางไฟล์โมเดล YOLO (`.pt`) ใน `ai/models/`
7. เปิดเว็บ แล้วรันกล้อง

เปิดเว็บที่ http://localhost/Shelf-Detection/

เข้าสู่ระบบเริ่มต้น: `admin` / `admin1234`

ตรวจจับด้วยกล้อง (ชั้น A1):

```bash
cd ai
python detect.py
```

แดชบอร์ดรีเฟรชสต็อกทุก 2 วินาที

แจ้งเตือน LINE OA: เข้า **ตั้งค่า** (admin) ใส่ Channel Access Token แล้วเปิดแจ้งเตือน  
เมื่อสินค้าเหลือน้อยหรือหมด จะส่งข้อความเข้า LINE OA 
