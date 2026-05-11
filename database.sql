-- ============================================================
-- Smart Urban Parking Management System - Database Schema
-- CS251 Software Engineering 1 | Capital University
-- ============================================================

CREATE DATABASE IF NOT EXISTS parking_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE parking_system;

-- ============================================================
-- USERS TABLE (Drivers, Owners, Admins)
-- ============================================================
CREATE TABLE users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone         VARCHAR(20),
    role          ENUM('driver','owner','admin') NOT NULL DEFAULT 'driver',
    is_active     TINYINT(1) DEFAULT 1,
    is_blacklisted TINYINT(1) DEFAULT 0,
    blacklist_reason VARCHAR(255) DEFAULT NULL,
    unpaid_fines_count INT DEFAULT 0,
    preferred_language VARCHAR(10) DEFAULT 'en',
    preferred_currency VARCHAR(10) DEFAULT 'EGP',
    loyalty_points INT DEFAULT 0,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- VEHICLES TABLE (Multi-Vehicle Profile per Driver)
-- ============================================================
CREATE TABLE vehicles (
    vehicle_id    INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    license_plate VARCHAR(20) NOT NULL UNIQUE,
    make          VARCHAR(50),
    model         VARCHAR(50),
    color         VARCHAR(30),
    vehicle_type  ENUM('sedan','suv','motorcycle','truck','ev') DEFAULT 'sedan',
    height_cm     DECIMAL(6,2),
    width_cm      DECIMAL(6,2),
    is_ev         TINYINT(1) DEFAULT 0,
    is_default    TINYINT(1) DEFAULT 0,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================================
-- PARKING SPOTS TABLE
-- ============================================================
CREATE TABLE parking_spots (
    spot_id           INT AUTO_INCREMENT PRIMARY KEY,
    owner_id          INT NOT NULL,
    title             VARCHAR(150) NOT NULL,
    description       TEXT,
    address           VARCHAR(255) NOT NULL,
    latitude          DECIMAL(10,8),
    longitude         DECIMAL(11,8),
    spot_type         ENUM('driveway','lot','garage','street') DEFAULT 'driveway',
    status            ENUM('available','unavailable','maintenance','owner_use','pending_verification') DEFAULT 'pending_verification',
    is_verified       TINYINT(1) DEFAULT 0,
    max_height_cm     DECIMAL(6,2),
    max_width_cm      DECIMAL(6,2),
    has_ev_charger    TINYINT(1) DEFAULT 0,
    price_per_hour    DECIMAL(8,2) NOT NULL,
    base_price        DECIMAL(8,2) NOT NULL,
    difficulty_score  DECIMAL(3,2) DEFAULT 0.00 COMMENT 'Calculated from user ratings 1-5',
    trust_score       DECIMAL(5,2) DEFAULT 0.00,
    total_reviews     INT DEFAULT 0,
    city_zone         VARCHAR(100),
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================================
-- SPOT AVAILABILITY SCHEDULE
-- ============================================================
CREATE TABLE spot_availability (
    availability_id INT AUTO_INCREMENT PRIMARY KEY,
    spot_id         INT NOT NULL,
    day_of_week     TINYINT NOT NULL COMMENT '0=Sunday, 6=Saturday',
    open_time       TIME NOT NULL,
    close_time      TIME NOT NULL,
    FOREIGN KEY (spot_id) REFERENCES parking_spots(spot_id) ON DELETE CASCADE
);

-- ============================================================
-- OWNER VERIFICATION DOCUMENTS
-- ============================================================
CREATE TABLE owner_verifications (
    verification_id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id        INT NOT NULL,
    spot_id         INT NOT NULL,
    id_document     VARCHAR(255) COMMENT 'Path to uploaded ID',
    utility_bill    VARCHAR(255) COMMENT 'Path to uploaded utility bill',
    status          ENUM('pending','approved','rejected') DEFAULT 'pending',
    admin_notes     TEXT,
    submitted_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at     TIMESTAMP NULL,
    reviewed_by     INT NULL,
    FOREIGN KEY (owner_id) REFERENCES users(user_id),
    FOREIGN KEY (spot_id)  REFERENCES parking_spots(spot_id),
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id)
);

-- ============================================================
-- RESERVATIONS TABLE
-- ============================================================
CREATE TABLE reservations (
    reservation_id  INT AUTO_INCREMENT PRIMARY KEY,
    driver_id       INT NOT NULL,
    spot_id         INT NOT NULL,
    vehicle_id      INT NOT NULL,
    start_time      DATETIME NOT NULL,
    end_time        DATETIME NOT NULL,
    actual_checkin  DATETIME NULL,
    actual_checkout DATETIME NULL,
    status          ENUM('pending','confirmed','active','completed','cancelled','no_show','extended') DEFAULT 'pending',
    is_recurring    TINYINT(1) DEFAULT 0,
    recurrence_days VARCHAR(20) COMMENT 'e.g. 1,2,3,4,5 for Mon-Fri',
    qr_code         VARCHAR(255) UNIQUE,
    total_amount    DECIMAL(10,2),
    refund_amount   DECIMAL(10,2) DEFAULT 0.00,
    cancellation_time DATETIME NULL,
    cancellation_reason TEXT NULL,
    promo_code      VARCHAR(50) NULL,
    discount_amount DECIMAL(8,2) DEFAULT 0.00,
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id)  REFERENCES users(user_id),
    FOREIGN KEY (spot_id)    REFERENCES parking_spots(spot_id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id)
);

-- ============================================================
-- PAYMENTS / TRANSACTIONS TABLE
-- ============================================================
CREATE TABLE transactions (
    transaction_id  INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id  INT NOT NULL,
    payer_id        INT NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    platform_fee    DECIMAL(8,2) DEFAULT 0.00,
    owner_earnings  DECIMAL(8,2) DEFAULT 0.00,
    tax_amount      DECIMAL(8,2) DEFAULT 0.00,
    currency        VARCHAR(10) DEFAULT 'EGP',
    payment_method  ENUM('card','wallet','cash') DEFAULT 'card',
    payment_status  ENUM('escrow','released','refunded','failed') DEFAULT 'escrow',
    escrow_released_at TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id),
    FOREIGN KEY (payer_id) REFERENCES users(user_id)
);

-- ============================================================
-- OWNER PAYOUTS
-- ============================================================
CREATE TABLE owner_payouts (
    payout_id       INT AUTO_INCREMENT PRIMARY KEY,
    owner_id        INT NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    status          ENUM('pending','processed','failed') DEFAULT 'pending',
    period_start    DATE NOT NULL,
    period_end      DATE NOT NULL,
    initiated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at    TIMESTAMP NULL,
    FOREIGN KEY (owner_id) REFERENCES users(user_id)
);

-- ============================================================
-- FINES TABLE
-- ============================================================
CREATE TABLE fines (
    fine_id         INT AUTO_INCREMENT PRIMARY KEY,
    driver_id       INT NOT NULL,
    spot_id         INT NOT NULL,
    reservation_id  INT NULL,
    fine_type       ENUM('no_reservation','overstay','damage') DEFAULT 'no_reservation',
    amount          DECIMAL(8,2) NOT NULL,
    overstay_minutes INT DEFAULT 0,
    status          ENUM('unpaid','paid','appealed','waived') DEFAULT 'unpaid',
    issued_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    paid_at         TIMESTAMP NULL,
    officer_id      INT NULL,
    FOREIGN KEY (driver_id) REFERENCES users(user_id),
    FOREIGN KEY (spot_id) REFERENCES parking_spots(spot_id),
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id),
    FOREIGN KEY (officer_id) REFERENCES users(user_id)
);

-- ============================================================
-- FINE APPEALS TABLE
-- ============================================================
CREATE TABLE fine_appeals (
    appeal_id       INT AUTO_INCREMENT PRIMARY KEY,
    fine_id         INT NOT NULL,
    driver_id       INT NOT NULL,
    description     TEXT NOT NULL,
    evidence_path   VARCHAR(255) COMMENT 'Uploaded photo/receipt path',
    status          ENUM('pending','approved','rejected') DEFAULT 'pending',
    admin_response  TEXT,
    submitted_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at     TIMESTAMP NULL,
    reviewed_by     INT NULL,
    FOREIGN KEY (fine_id) REFERENCES fines(fine_id),
    FOREIGN KEY (driver_id) REFERENCES users(user_id),
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id)
);

-- ============================================================
-- REVIEWS TABLE
-- ============================================================
CREATE TABLE reviews (
    review_id       INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id  INT NOT NULL,
    reviewer_id     INT NOT NULL,
    spot_id         INT NOT NULL,
    rating          TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    difficulty_rating TINYINT CHECK (difficulty_rating BETWEEN 1 AND 5),
    comment         TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id),
    FOREIGN KEY (reviewer_id) REFERENCES users(user_id),
    FOREIGN KEY (spot_id) REFERENCES parking_spots(spot_id)
);

-- ============================================================
-- NOTIFICATIONS TABLE
-- ============================================================
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    type            ENUM('expiry_warning','penalty_alert','booking_confirmed','fine_issued','appeal_update','payout_ready','waitlist_available','extension_approved') NOT NULL,
    title           VARCHAR(150),
    message         TEXT,
    channel         ENUM('web','email','sms') DEFAULT 'web',
    is_read         TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================================
-- WAITLIST TABLE
-- ============================================================
CREATE TABLE waitlist (
    waitlist_id     INT AUTO_INCREMENT PRIMARY KEY,
    spot_id         INT NOT NULL,
    driver_id       INT NOT NULL,
    vehicle_id      INT NOT NULL,
    desired_start   DATETIME NOT NULL,
    desired_end     DATETIME NOT NULL,
    status          ENUM('watching','notified','converted','expired') DEFAULT 'watching',
    added_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (spot_id) REFERENCES parking_spots(spot_id),
    FOREIGN KEY (driver_id) REFERENCES users(user_id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id)
);

-- ============================================================
-- PROMO CODES TABLE
-- ============================================================
CREATE TABLE promo_codes (
    promo_id        INT AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(50) NOT NULL UNIQUE,
    discount_type   ENUM('percentage','fixed') DEFAULT 'percentage',
    discount_value  DECIMAL(8,2) NOT NULL,
    max_uses        INT DEFAULT 100,
    current_uses    INT DEFAULT 0,
    valid_from      DATETIME NOT NULL,
    valid_until     DATETIME NOT NULL,
    is_active       TINYINT(1) DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- PEAK HOURS PRICING TABLE
-- ============================================================
CREATE TABLE peak_hour_rules (
    rule_id         INT AUTO_INCREMENT PRIMARY KEY,
    spot_id         INT NULL COMMENT 'NULL = applies to all spots',
    day_of_week     TINYINT NULL COMMENT 'NULL = all days',
    start_time      TIME NOT NULL,
    end_time        TIME NOT NULL,
    multiplier      DECIMAL(4,2) NOT NULL DEFAULT 1.50 COMMENT 'e.g. 1.5 = 50% price increase',
    event_name      VARCHAR(100) NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (spot_id) REFERENCES parking_spots(spot_id) ON DELETE CASCADE
);

-- ============================================================
-- P2P MESSAGES (Encrypted Driver-Owner Chat)
-- ============================================================
CREATE TABLE messages (
    message_id      INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id  INT NOT NULL,
    sender_id       INT NOT NULL,
    receiver_id     INT NOT NULL,
    message_text    TEXT NOT NULL,
    is_read         TINYINT(1) DEFAULT 0,
    sent_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id),
    FOREIGN KEY (sender_id) REFERENCES users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES users(user_id)
);

-- ============================================================
-- AUDIT LOG TABLE (Non-Repudiable)
-- ============================================================
CREATE TABLE audit_log (
    log_id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NULL,
    action          VARCHAR(100) NOT NULL,
    target_table    VARCHAR(50),
    target_id       INT NULL,
    old_value       TEXT NULL,
    new_value       TEXT NULL,
    ip_address      VARCHAR(45),
    logged_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- ============================================================
-- FAVORITES TABLE
-- ============================================================
CREATE TABLE favorites (
    favorite_id     INT AUTO_INCREMENT PRIMARY KEY,
    driver_id       INT NOT NULL,
    spot_id         INT NOT NULL,
    label           ENUM('home','work','other') DEFAULT 'other',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(driver_id, spot_id),
    FOREIGN KEY (driver_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (spot_id) REFERENCES parking_spots(spot_id) ON DELETE CASCADE
);

-- ============================================================
-- EVENT ZONES TABLE (Municipal Lockdown)
-- ============================================================
CREATE TABLE event_zones (
    zone_id         INT AUTO_INCREMENT PRIMARY KEY,
    admin_id        INT NOT NULL,
    zone_name       VARCHAR(100),
    center_lat      DECIMAL(10,8),
    center_lng      DECIMAL(11,8),
    radius_km       DECIMAL(5,2),
    reason          VARCHAR(255),
    active_from     DATETIME NOT NULL,
    active_until    DATETIME NOT NULL,
    is_active       TINYINT(1) DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(user_id)
);

-- ============================================================
-- SENSOR / SYSTEM HEALTH TABLE
-- ============================================================
CREATE TABLE sensor_health (
    sensor_id       INT AUTO_INCREMENT PRIMARY KEY,
    spot_id         INT NOT NULL,
    last_heartbeat  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status          ENUM('online','offline','warning') DEFAULT 'online',
    FOREIGN KEY (spot_id) REFERENCES parking_spots(spot_id) ON DELETE CASCADE
);

-- ============================================================
-- SEED DATA: Admin User + Sample Promo
-- ============================================================
INSERT INTO users (full_name, email, password_hash, role) VALUES
('System Admin', 'admin@parkingsystem.com', '$2y$10$examplehash', 'admin');

INSERT INTO promo_codes (code, discount_type, discount_value, valid_from, valid_until) VALUES
('WELCOME10', 'percentage', 10.00, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR));

-- Internal system promo for recurring bookings (high max_uses, long validity)
INSERT INTO promo_codes (code, discount_type, discount_value, max_uses, valid_from, valid_until) VALUES
('RECURRING_DISCOUNT', 'percentage', 10.00, 999999, '2020-01-01', '2099-12-31');

INSERT INTO peak_hour_rules (day_of_week, start_time, end_time, multiplier, event_name) VALUES
(NULL, '08:00:00', '10:00:00', 1.50, 'Morning Rush Hour'),
(NULL, '17:00:00', '20:00:00', 1.75, 'Evening Rush Hour'),
(6,   '10:00:00', '22:00:00', 1.30, 'Weekend Busy Hours');

-- ============================================================
-- GARAGES TABLE (new)
-- ============================================================
ALTER TABLE parking_spots 
  ADD COLUMN garage_id INT NULL AFTER owner_id,
  ADD COLUMN spot_number VARCHAR(10) NULL COMMENT 'e.g. A1, B3' AFTER garage_id;

CREATE TABLE IF NOT EXISTS garages (
    garage_id     INT AUTO_INCREMENT PRIMARY KEY,
    owner_id      INT NOT NULL,
    name          VARCHAR(150) NOT NULL,
    address       VARCHAR(255) NOT NULL,
    latitude      DECIMAL(10,8),
    longitude     DECIMAL(11,8),
    city_zone     VARCHAR(100),
    total_floors  INT DEFAULT 1,
    description   TEXT,
    is_verified   TINYINT(1) DEFAULT 0,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(user_id)
);

ALTER TABLE parking_spots
  ADD FOREIGN KEY (garage_id) REFERENCES garages(garage_id) ON DELETE CASCADE;
