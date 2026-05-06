-- ============================================================
-- تحديث حسابات الاختبار
-- شغّل الـ SQL ده في phpMyAdmin بعد import database.sql
-- ============================================================
USE parking_system;

-- ── تحديث حساب الـ Admin ──────────────────────────────────
-- Password: admin123
UPDATE users 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE email = 'admin@parkingsystem.com';

-- ── إضافة حساب Driver للتجربة ─────────────────────────────
-- Email: driver@test.com | Password: admin123
INSERT IGNORE INTO users (full_name, email, password_hash, role, phone) VALUES
('Ahmed Driver', 'driver@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver', '01012345678');

-- ── إضافة حساب Owner للتجربة ──────────────────────────────
-- Email: owner@test.com | Password: admin123
INSERT IGNORE INTO users (full_name, email, password_hash, role, phone) VALUES
('Sara Owner', 'owner@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', '01098765432');

-- ── إضافة Spot تجريبي للـ Owner ───────────────────────────
INSERT IGNORE INTO parking_spots 
(owner_id, title, description, address, latitude, longitude, spot_type, status, is_verified, price_per_hour, base_price, city_zone)
VALUES (
    (SELECT user_id FROM users WHERE email = 'owner@test.com'),
    'Secure Driveway - Maadi',
    'Clean, safe driveway with 24/7 camera surveillance. Easy access from ring road.',
    '15 Road 9, Maadi, Cairo',
    29.9607, 31.2498,
    'driveway', 'available', 1,
    30.00, 30.00, 'Maadi'
);

-- ── إضافة Vehicle للـ Driver ──────────────────────────────
INSERT IGNORE INTO vehicles (user_id, license_plate, make, model, color, vehicle_type, is_default)
VALUES (
    (SELECT user_id FROM users WHERE email = 'driver@test.com'),
    'ABC 1234', 'Toyota', 'Corolla', 'White', 'sedan', 1
);

-- ── إضافة Sensor للـ Spot ─────────────────────────────────
INSERT IGNORE INTO sensor_health (spot_id, last_heartbeat, status)
SELECT spot_id, NOW(), 'online'
FROM parking_spots WHERE title = 'Secure Driveway - Maadi';

-- ── تحقق من الحسابات ──────────────────────────────────────
SELECT user_id, full_name, email, role FROM users;
