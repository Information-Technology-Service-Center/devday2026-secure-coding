-- ===========================
-- Seed Data for Order Management System
-- Legacy PHP 7 Demo
-- ===========================

USE order_db;

-- Seed Product Status
INSERT INTO product_status (status_id, status_code, status_name) VALUES
(1, 'PENDING', 'รอยืนยันคำสั่งซื้อ'),
(2, 'CONFIRMED', 'ยืนยันคำสั่งซื้อ');

-- Seed Products
INSERT INTO products (product_number, product_name, product_description, price, stock_quantity) VALUES
('PRD001', 'สินค้า A - เสื้อยืด', 'เสื้อยืดคอกลม สีพื้น คุณภาพดี', 299.00, 50),
('PRD002', 'สินค้า B - กางเกงยีนส์', 'กางเกงยีนส์ขายาว ผ้านิ่ม ใส่สบาย', 890.00, 30),
('PRD003', 'สินค้า C - รองเท้าผ้าใบ', 'รองเท้าผ้าใบ น้ำหนักเบา สวมใส่สบาย', 1290.00, 40),
('PRD004', 'สินค้า D - กระเป๋าสะพาย', 'กระเป๋าสะพายข้าง ดีไซน์เรียบหรู', 650.00, 20),
('PRD005', 'สินค้า E - หมวกแก๊ป', 'หมวกแก๊ป ปรับขนาดได้ ระบายอากาศดี', 250.00, 60),
('PRD006', 'สินค้า F - แจ็คเก็ต', 'แจ็คเก็ตกันลม กันหนาว ใส่ได้ทั้งผู้ชายและผู้หญิง', 1500.00, 15),
('PRD007', 'สินค้า G - ถุงเท้า', 'ถุงเท้าผ้าฝ้าย ระบายอากาศดี', 99.00, 100),
('PRD008', 'สินค้า H - เข็มขัด', 'เข็มขัดหนังแท้ หัวเข็มขัดโลหะ', 350.00, 35),
('PRD009', 'สินค้า I - กระเป๋าตัง', 'กระเป๋าสตางค์หนังแท้ ช่องใส่การ์ดเยอะ', 450.00, 25),
('PRD010', 'สินค้า J - แว่นกันแดด', 'แว่นกันแดด กรองแสง UV 100%', 750.00, 18);

-- Seed Test Users (Optional - plaintext passwords!)
INSERT INTO users (email, first_name, last_name, phone, password, is_admin) VALUES
('admin@test.com', 'Admin', 'System', '0801111111', 'admin123', 1),
('user1@test.com', 'สมชาย', 'ใจดี', '0812222222', 'user123', 0),
('user2@test.com', 'สมหญิง', 'รักสวย', '0823333333', 'user123', 0);
