# คู่มือทำแลปแบบทีละขั้น (Security Code Review & Scanning Tools)

ระยะเวลา: 45 นาที | 10:40 - 11:25

นี่คือแกนหลักของเวิร์กช็อป: สแกนโค้ดจริง อ่านผลสแกน แก้ปัญหา แล้วสแกนซ้ำเพื่อยืนยันผล

## 4.1 เตรียมแลป (5 นาที)
หมายเหตุสำหรับผู้สอน: แนะนำให้ผู้เรียนติดตั้งเครื่องมือล่วงหน้าอย่างน้อย 1 สัปดาห์ก่อนวันเรียน

1. เลือกแอปเป้าหมาย:
   - PHP: [`targets/php-legacy/app`](../targets/php-legacy/app)
   - Flask: [`targets/flask-vulnerable/app`](../targets/flask-vulnerable/app)
2. เข้าโฟลเดอร์แลป:
   - `cd lab-devday-ep3`
3. ตรวจสอบว่าเครื่องมือพร้อมใช้งาน:
   - `semgrep --version`
   - `syft version`
   - `gitleaks version`
4. ดูเช็กลิสต์และใบให้คะแนน:
   - [`docs/lab-checklist.md`](lab-checklist.md)
   - [`docs/scoring-sheet.md`](scoring-sheet.md)
5. อธิบายโครงสร้างของแอปเป้าหมาย (2 นาที):
   - เอนด์พอยต์/หน้าเว็บอยู่ที่ไหน
   - โค้ดเชื่อมฐานข้อมูลอยู่ที่ไหน
   - ไฟล์คอนฟิก/ข้อมูลลับอยู่ที่ไหน


## ติดตั้ง Semgrep (SAST)

**macOS**:
```bash
brew install semgrep
```

**Linux**:
```bash
pip install semgrep
```

**Windows**:
```powershell
pip install semgrep
```

**ตรวจสอบ**:
```bash
semgrep --version
```
ผลลัพธ์ที่ควรเห็น: `1.xx.x` (เลขเวอร์ชัน)

### ขั้นตอน 1.2: ติดตั้ง Syft (สร้าง SBOM)

**macOS**:
```bash
brew install syft
```

**Linux**:
```bash
sudo -s
curl -sSfL https://raw.githubusercontent.com/anchore/syft/main/install.sh | sh -s -- -b /usr/local/bin
```

**Windows**:
```powershell
# ดาวน์โหลดจาก https://github.com/anchore/syft/releases
# แตก zip แล้วเพิ่มไปที่ PATH
```

**ตรวจสอบ**:
```bash
syft version
```
ผลลัพธ์ที่ควรเห็น: `syft <version>`

### ขั้นตอน 1.3: ติดตั้ง Gitleaks (สแกนข้อมูลลับ)

**macOS**:
```bash
brew install gitleaks
```

**Linux**:
```bash
# ดาวน์โหลดจาก https://github.com/gitleaks/gitleaks/releases
wget https://github.com/gitleaks/gitleaks/releases/download/v8.18.2/gitleaks_8.18.2_linux_x64.tar.gz
tar -xzf gitleaks_8.18.2_linux_x64.tar.gz
sudo mv gitleaks /usr/local/bin/
exit
```

**Windows**:
```powershell
# ดาวน์โหลดจาก https://github.com/gitleaks/gitleaks/releases
# เพิ่ม gitleaks.exe ไปที่ PATH
```

**ตรวจสอบ**:
```bash
gitleaks version
```
ผลลัพธ์ที่ควรเห็น: `8.x.x`


## 4.2 ส่วน A: สแกน SAST ด้วย Semgrep (10 นาที)
### ขั้นตอน 1: รันสแกน
```bash
chmod +x ./scripts/*
./scripts/run-semgrep.sh targets/php-legacy/app before
```

ถ้าอยากเห็นผลใน terminal :
```bash
cd targets/php-legacy/app/
semgrep scan --config auto .
```

### ขั้นตอน 2: วิเคราะห์ผล
- แยกประเด็นตามความรุนแรง (Critical/High/Medium/Low)
- ดู rule ID และข้อความอธิบาย
- ยืนยันพาธของไฟล์ + เลขบรรทัด

### ขั้นตอน 3: เปรียบเทียบ (เครื่องมือ vs คน)
- เครื่องมือเจออะไรที่คนรีวิวด้วยตาเปล่าพลาด?
- คนรีวิวเจออะไรที่เครื่องมือพลาด? (เช่น ข้อผิดพลาดเชิงตรรกะ/กติกาทางธุรกิจ)

## 4.3 ส่วน B: สแกน SCA ด้วย Syft + Dependency-Track (10 นาที)
### ขั้นตอน 1: สร้าง SBOM ด้วย Syft
```bash
./scripts/run-syft.sh targets/php-legacy/app before
#or
./scripts/run-syft.sh targets/flask-vulnerable/app before
```

### ขั้นตอน 2: อัปโหลดเข้า Dependency-Track + DefectDojo (ตัวเลือก)
ถ้ามี Dependency-Track ให้ใช้งาน (ดู `infra/dependency-track/`):
- อัปโหลด `reports/*.sbom.cyclonedx.json` (หรือใช้ `*.latest.*`) ผ่านหน้าเว็บ หรือ REST API
- ดูแดชบอร์ด: จำนวนช่องโหว่แยกตามความรุนแรง
- เจาะดู CVE, CVSS และคอมโพเนนต์ที่ได้รับผลกระทบ

ถ้าไม่มีเซิร์ฟเวอร์ Dependency-Track ให้เก็บ SBOM ไว้ และอธิบายข้อจำกัดแทน

```bash
cd infra/dependency-track/codspace
chmod +x run.sh
./run.sh
```

### Demo URL: 
```bash
Username: admin
Password: xxxxx
```
- Defectdojo = https://cmu.to/defectdojo
- Dependency-track = https://cmu.to/dependency-track

### ขั้นตอน 3: คัดแยกความเร่งด่วน
- ไลบรารีไหนเสี่ยงสุด?
- แนะนำให้แก้โดยอัปเกรดไปเวอร์ชันไหน?
- ใช้ policy เพื่อกำหนดสิ่งที่ควร “บล็อก” ใน CI ได้อย่างไร?

## 4.4 ส่วน C: สแกนข้อมูลลับด้วย Gitleaks (10 นาที)
### ขั้นตอน 1: รันสแกน
```bash
./scripts/run-gitleaks.sh targets/php-legacy/app before
```
ถ้าอยากเห็นผลใน terminal :
```bash
cd targets/php-legacy/app/
gitleaks detect --source . --verbose
```

### ขั้นตอน 2: อ่านผลสแกน
- ประเภทข้อมูลลับ (API key / password / token / private key)
- ไฟล์ + เลขบรรทัด
- True positive vs false positive

### ขั้นตอน 3: แนวทางแก้
- ย้าย secret ไปเป็น env vars หรือ secret manager
- ถ้าเป็นของจริงให้ rotate credential ทันที
- เพิ่ม allowlist ใน `configs/.gitleaks.toml` เฉพาะกรณีที่มั่นใจว่าเป็น false positive
- ติดตั้ง pre-commit hook (เสริมความเข้มงวดในอนาคต)

## 4.5 แบบฝึกหัด: แก้ไข + ยืนยันผล (10 นาที)
เลือก 3–5 ประเด็นที่สำคัญแล้วแก้:
- SQL string concatenation → parameterized query
- เพิ่ม input validation / output encoding
- ย้าย secret ที่ hardcode → ไปเป็น env variables
- อัปเดต dependency ที่มีช่องโหว่

สแกนซ้ำและยืนยันว่า issue ลดลง:
```bash
./scripts/run-semgrep.sh targets/php-legacy/app after
./scripts/run-gitleaks.sh targets/php-legacy/app after
```

เฉลย/แนวทาง: อาจจะลองใช้ AI ช่วยในรีวิวและแก้ไข Source Code หรือ ดูแนวทาง [`docs/solutions/php-legacy-exercise-4.5.md`](solutions/php-legacy-exercise-4.5.md)
