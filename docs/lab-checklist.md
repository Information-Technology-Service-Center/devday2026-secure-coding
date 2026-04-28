# เช็กลิสต์แลป (สแกนความปลอดภัยโค้ด)

## ก่อนเริ่มแลป
- [ ] `cd incubate/lab-devday-ep3`
- [ ] ติดตั้ง Git แล้ว
- [ ] มี IDE/โปรแกรมแก้ไขข้อความพร้อมใช้งาน
- [ ] ติดตั้ง Semgrep แล้ว (`semgrep --version`)
- [ ] ติดตั้ง Syft แล้ว (`syft version`)
- [ ] ติดตั้ง Gitleaks แล้ว (`gitleaks version`)
- [ ] (ตัวเลือก) มี Dependency-Track ให้ใช้งาน

## Part A: SAST ด้วย Semgrep
- [ ] รัน Semgrep ได้สำเร็จ
- [ ] แยกผลตรวจพบตามความรุนแรง
- [ ] ระบุ rule ID + path ของไฟล์ + เลขบรรทัดได้
- [ ] เปรียบเทียบสิ่งที่ tool เจอกับการรีวิวด้วยตาเปล่า

## Part B: SCA ด้วย Syft (+ Dependency-Track)
- [ ] สร้าง SBOM ด้วย Syft
- [ ] อ่านรายการ dependency จาก SBOM
- [ ] อัปโหลดเข้า Dependency-Track (ถ้ามี)
- [ ] ระบุ component/CVE ที่สำคัญที่สุดได้

## Part C: สแกน secret ด้วย Gitleaks
- [ ] รัน Gitleaks ได้สำเร็จ
- [ ] อ่านผล secret ที่ตรวจพบ
- [ ] แยกผลตรวจพบที่เป็น “ของจริง” กับ “ผลลวง”
- [ ] เสนอแนวทางแก้ (env vars / rotate creds / allowlist)

## แบบฝึกหัด: แก้ไข + ยืนยันผล
- [ ] แก้ปัญหา 3–5 รายการที่สำคัญ
- [ ] สแกนซ้ำ
- [ ] ยืนยันว่า issue ลดลง/ถูกแก้แล้ว
- [ ] มีรายงานผล (ตัวอย่าง): `reports/*.latest.semgrep.json`, `reports/*.latest.sbom.cyclonedx.json`, `reports/*.latest.gitleaks.json`
