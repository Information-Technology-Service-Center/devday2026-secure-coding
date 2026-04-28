# Dependency-Track (ตัวเลือก)

สำหรับแลประยะ 45 นาที แนะนำให้มอง Dependency-Track เป็นระบบเสริมที่ **ผู้สอนเตรียมรันไว้ล่วงหน้า**  
การเปิดครั้งแรกอาจใช้เวลานาน (init ฐานข้อมูล + sync ข้อมูลช่องโหว่) จึงไม่เหมาะให้ผู้เรียนทุกคนสตาร์ทแบบ cold start

## วิธีรัน
```bash
cd infra/dependency-track
docker compose up -d
```

## ลิงก์
- API: `http://localhost:${DTRACK_API_PORT:-18081}`
- หน้าเว็บ: `http://localhost:${DTRACK_UI_PORT:-18082}`

การจำกัดขอบเขต: compose bind พอร์ตไว้ที่ `127.0.0.1` เท่านั้น

## บัญชีเริ่มต้น (ถ้าไม่ได้เปลี่ยน)
- ผู้ใช้: `admin`
- รหัสผ่าน: `admin`

## SBOM Upload
สร้าง SBOM:
```bash
./scripts/run-syft.sh targets/php-legacy/app
```

จากนั้นอัปโหลดไฟล์ `reports/*.sbom.cyclonedx.json` ผ่านหน้าเว็บของ Dependency-Track (หรือใช้ไฟล์ `*.latest.*`)

---

# DefectDojo (ตัวเลือก)

DefectDojo คือระบบจัดการช่องโหว่/การคัดแยกความเร่งด่วน เพื่อเก็บผลตรวจพบจากหลายเครื่องมือไว้รวมกัน

## วิธีรัน (พร้อม Dependency-Track)

```bash
cd infra/dependency-track
docker compose -f docker-compose.yml -f docker-compose.defectdojo.yml up -d
```

## ลิงก์
- UI: `http://localhost:${DD_PORT:-18083}`

## บัญชีผู้ดูแลระบบ (admin)

DefectDojo จะสร้าง admin และพิมพ์รหัสผ่านไว้ใน logs ของ initializer:

```bash
docker compose -f docker-compose.yml -f docker-compose.defectdojo.yml logs dojo-initializer | grep "Admin password:" || true
```

หมายเหตุ: ครั้งแรกอาจใช้เวลา ~2–3 นาที
