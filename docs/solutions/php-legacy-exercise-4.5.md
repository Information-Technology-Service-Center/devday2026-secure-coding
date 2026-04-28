# เฉลย 4.5 แบบฝึกหัด: แก้ไข + ยืนยันผล (targets/php-legacy/app)

เอกสารนี้เป็น “แนวทางเฉลย” สำหรับช่วง **4.5 แบบฝึกหัด: แก้ไข + ยืนยันผล**: เลือก 3–5 ประเด็นที่สำคัญ แล้วแก้ไข + สแกนซ้ำ เพื่อยืนยันว่าผลตรวจพบลดลง

> เป้าหมายในแลบ: ให้ผู้เรียน “ลงมือแก้จริง” ใน `targets/php-legacy/app/`  
> เอกสารนี้จึงบอก **จุดที่ต้องแก้ / แนวทางแก้ / ตัวอย่างโค้ด** (ไม่ใช่ patch อัตโนมัติ)

---

## A) รันสแกน (ก่อนแก้)

จาก root ของ repo `lab-devday-ep3`:

```bash
./scripts/run-semgrep.sh targets/php-legacy/app before
./scripts/run-gitleaks.sh targets/php-legacy/app before
./scripts/run-syft.sh targets/php-legacy/app before # optional
```

ผลลัพธ์จะอยู่ใน `reports/` และมีไฟล์ `*.latest.*` ให้เปิดดูเร็ว

---

## B) เลือก 5 ประเด็นที่แก้ง่ายแต่ได้ผลสูง

### 1) SQL Injection (SQLi) (High/Critical)
**สาเหตุ:** ประกอบสตริง SQL โดยใส่ค่าจาก user input ตรง ๆ  
**ไฟล์ที่เกี่ยวข้อง (ตัวอย่าง):**
- `targets/php-legacy/app/includes/db_helper.php` (`getUserByEmail`, `insertUser`, `getProductByNumber`, `getOrderByNumber`)
- `targets/php-legacy/app/includes/functions.php` (`processOrder`, `updateOrderItems`, `confirmOrderWithAddress`, `searchOrders`)

**แนวทางแก้ (แบบสั้นสำหรับแลบ):**
- เปลี่ยนเป็น **prepared statements** (`mysqli_prepare` + `bind_param`) ใน query ที่รับ input
- สร้าง helper แบบบาง ๆ เพื่อให้แก้หลายจุดเร็ว (เช่น `db_select_one`, `db_execute`)

**ตัวอย่าง (แนวคิด):**
```php
// BAD: "SELECT * FROM users WHERE email = '$email'"
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
```

---

### 2) การเปิดเผยรายละเอียดข้อผิดพลาดของ SQL (Medium/High)
**สาเหตุ:** `die("SQL Error: ... Query: ...")` ทำให้ผู้โจมตีเห็นรายละเอียด DB/Query  
**ไฟล์:** `targets/php-legacy/app/includes/db_helper.php` (`executeQuery`)

**แนวทางแก้:**
- ฝั่งผู้ใช้: แสดงข้อความทั่วไป เช่น `Database error`
- ฝั่ง server: log รายละเอียดไว้ใน `error_log(...)`

**ตัวอย่าง:**
```php
if (!$result) {
  error_log("[db] query failed: " . mysqli_error($conn));
  die("Database error");
}
```

---

### 3) เก็บรหัสผ่านเป็น plaintext (High)
**สาเหตุ:** เก็บรหัสผ่านเป็น plaintext และ login เปรียบเทียบตรง ๆ  
**ไฟล์:**
- `targets/php-legacy/app/public/register.php`
- `targets/php-legacy/app/public/login.php`
- `targets/php-legacy/app/includes/db_helper.php` (`insertUser`)

**แนวทางแก้ (ขั้นต่ำ):**
- ตอนสมัครสมาชิก: `password_hash($password, PASSWORD_DEFAULT)`
- ตอน login: `password_verify($password, $user['password'])`

**ตัวอย่าง (register):**
```php
$hash = password_hash($password, PASSWORD_DEFAULT);
// insertUser(..., $hash) หรือให้ insertUser ทำ hash ภายในก็ได้
```

**ตัวอย่าง (login):**
```php
if ($user && password_verify($password, $user['password'])) {
  // login ok
}
```

**หมายเหตุเรื่องผู้ใช้ที่มากับ seed ใน `sql/seed.sql`:**
- ตอนแก้จริง อาจต้องอัปเดต seed ให้เป็น hash (หรือยอมให้ seed เป็น plaintext แล้วทำ “ย้ายรูปแบบรหัสผ่านตอนล็อกอิน” สำหรับแลบ)

แนวคิด “ย้ายรูปแบบรหัสผ่านตอนล็อกอิน” (ทำให้ผู้ใช้เดิมล็อกอินได้ แล้วอัปเกรดเป็น hash):
```php
$stored = $user['password'];
$ok = password_verify($password, $stored);
if (!$ok && $stored === $password) {
  // plaintext แบบเดิมตรงกัน -> อัปเกรดเป็น hash
  $newHash = password_hash($password, PASSWORD_DEFAULT);
  // update users.password ให้เป็น $newHash
  $ok = true;
}
```

---

### 4) XSS (Medium/High)
**สาเหตุ:** echo ค่าจาก DB / POST / GET ลง HTML โดยไม่ encode  
**ไฟล์ที่เห็นชัด:**
- `targets/php-legacy/app/public/confirm_order.php` (shipping_address แสดงด้วย `nl2br(...)` แต่ไม่ได้ `htmlspecialchars`)
- `targets/php-legacy/app/public/order.php`, `targets/php-legacy/app/public/update_order.php` (product_name/description)
- `targets/php-legacy/app/public/admin/orders.php` (ข้อมูลลูกค้า, ที่อยู่จัดส่ง)

**แนวทางแก้:**
ใช้ `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')` ทุกครั้งที่ echo ลง HTML

**ตัวอย่าง (แนวคิด):**
```php
echo htmlspecialchars($order_number, ENT_QUOTES, 'UTF-8');
echo nl2br(htmlspecialchars($address, ENT_QUOTES, 'UTF-8'));
```

---

### 5) ช่องโหว่จากการตรวจสอบอินพุตไม่พอ (Medium)
**สาเหตุ:** หลาย endpoint รับค่าที่ควรถูกจำกัดรูปแบบ/ช่วง แต่ยังรับได้กว้าง  
**ตัวอย่างจุดรับอินพุต:**
- `order_number` จาก `$_GET`/`$_POST` (ควรเป็นรูปแบบ `ORD-<digits>-<digits>`)
- `product_number` (ควรเป็น `PRD###`)
- `quantity` (ควรเป็นเลขจำนวนเต็ม, 1..N)
- `shipping_address` (ควรจำกัดความยาว)

**แนวทางแก้:**
เพิ่ม validation ก่อนเรียก DB:
```php
if (!preg_match('/^ORD-\\d+-\\d+$/', $order_number)) { /* reject */ }
if (!preg_match('/^PRD\\d{3}$/', $pn)) { /* reject */ }
$qty = (int)$qty; if ($qty < 1 || $qty > 999) { /* reject */ }
if (mb_strlen($shipping_address) > 500) { /* reject */ }
```

---

## C) สแกนซ้ำ (หลังแก้) เพื่อยืนยันผล

```bash
./scripts/run-semgrep.sh targets/php-legacy/app after
./scripts/run-gitleaks.sh targets/php-legacy/app after
./scripts/run-syft.sh targets/php-legacy/app after # ตัวเลือก
```

### สิ่งที่ควรเห็น
- Semgrep: จำนวน finding กลุ่ม SQLi ลดลง (อย่างน้อยใน functions ที่แก้)
- Gitleaks: ถ้ามีข้อมูลลับจริงใน target จะลดลง/หายไป (ถ้าเดิมไม่พบ ก็ถือว่าผ่าน)
- Syft/Dependency-Track (ถ้าทำ): เห็น SBOM และใช้เป็นฐานสำหรับคุยเรื่อง dependency risk

---

## D) เช็กลิสต์สำหรับการสาธิตในห้อง (แบบเร็ว)

1. ก่อนแก้ เปิด `reports/*.latest.semgrep.json` ให้ดู 1–2 จุด SQLi
2. แก้ 3–5 จุดตามหัวข้อ B
3. สแกนซ้ำแล้วเทียบ `before` vs `after` (จำนวน/ชนิด finding ลดลง)
4. สรุปว่า “เครื่องมือช่วยหา” แต่ “นักพัฒนาต้องเลือกแก้ + ยืนยันผล”

---

## E) ขยายผล (ตัวเลือก ถ้ามีเวลา)

- เพิ่ม transaction ใน `processOrder`/`updateOrderItems` (กัน partial writes)
- เพิ่ม CSRF token สำหรับฟอร์ม POST
- ลดการใช้ `global $conn` (refactor แบบค่อยเป็นค่อยไป)
