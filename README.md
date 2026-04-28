# Lab-DevDay-EP3: Workshop - (Security Code Review & Scanning Tools)

> ระยะเวลา: 45 นาที | 10:40 - 11:25
> Repo นี้ใช้สำหรับเวิร์กช็อปเท่านั้น และมีโค้ดที่ “ตั้งใจทำให้มีช่องโหว่” เพื่อการเรียนรู้  
> ห้ามนำไปใช้งานจริง และห้ามเปิดเผยสู่สาธารณะ/อินเทอร์เน็ต

## Goal
ผู้เรียนจะสแกนโค้ดจริง หา finding จริง และแก้ปัญหาอย่างน้อย 3–5 รายการ
- Semgrep: SAST
- Syft + Dependency-Track: สร้าง SBOM และรีวิวช่องโหว่ (ข้ามได้ถ้าไม่มีเซิร์ฟเวอร์)
- Gitleaks: ตรวจ secret ที่หลุด

## Safety Rules
- รันบน localhost เท่านั้น (ห้ามเปิดพอร์ตออก public / ห้าม expose ออกอินเทอร์เน็ต)
- ห้ามใช้ credential/token จริงในแลปนี้
- หลังจบแลปให้ปิดและลบ container ที่ใช้
- ห้ามนำ target ที่มีช่องโหว่ไป deploy บนเครื่องสาธารณะ

## Lab Targets
- [`targets/php-legacy/app/`](targets/php-legacy/app/) (แอป PHP แบบ legacy ที่มีช่องโหว่โดยตั้งใจ: SQLi / รหัสผ่าน plaintext ฯลฯ)
- [`targets/flask-vulnerable/app/`](targets/flask-vulnerable/app/) (intentionally vulnerable Flask app, 12 issues)

แต่ละ target จะมีไฟล์ `UPSTREAM.md` เพื่ออ้างอิงแหล่งที่มาของโค้ดต้นฉบับ

## Tech Stack ของ Targets

### `targets/php-legacy/app/`
- ภาษา/เว็บ: PHP 7.4 (โค้ด PHP แบบ legacy ไม่มีเฟรมเวิร์ก)
- เว็บเซิร์ฟเวอร์: Apache
- ฐานข้อมูล: MySQL 5.7
- การรันในแลป: Docker + Docker Compose (ดูไฟล์ `Dockerfile` และ `docker-compose.yml` ในโฟลเดอร์ target)

## Quick Start
1. `cd lab-devday-ep3`
2. เลือก target ที่จะทำ
3. รันสแกนทั้งหมด (ผลลัพธ์อยู่ที่ `reports/` และมีไฟล์ `*.latest.*` สำหรับเปิดดูเร็ว)

```bash
./scripts/run-all.sh targets/php-legacy/app
# or
./scripts/run-all.sh targets/flask-vulnerable/app
```

## Timeline (10:40 - 11:25)
1. เตรียมแลป (5 นาที): เลือก target, เช็คว่าเครื่องมีเครื่องมือครบ
2. Part A: Semgrep (SAST) (10 นาที)
3. Part B: Syft + Dependency-Track (SCA) (10 นาที)
4. Part C: Gitleaks (สแกน secret) (10 นาที)
5. แบบฝึกหัด: แก้ไข + ยืนยันผล (10 นาที)

## Tooling Requirements (Pre-install)
- Semgrep
- Syft
- Gitleaks
- (ตัวเลือก) เซิร์ฟเวอร์ Dependency-Track (ดู [`infra/dependency-track/`](infra/dependency-track/))

## Docs
- [`docs/lab-prerequisite.md`](docs/lab-prerequisite.md) 
- [`docs/lab-instructions.md`](docs/lab-instructions.md)
- [`docs/lab-checklist.md`](docs/lab-checklist.md)
- [`docs/scoring-sheet.md`](docs/scoring-sheet.md)
