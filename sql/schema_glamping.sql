CREATE DATABASE IF NOT EXISTS glamping_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE glamping_db;

-- Skema database: Website Penyedia Wisata Glamping
-- Gaya MySQL/MariaDB. Sesuaikan jika memakai PostgreSQL atau ORM (Laravel migration, Prisma, dll.).

CREATE TABLE users (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  phone         VARCHAR(20),
  password_hash VARCHAR(255) NOT NULL,            -- hasil hash, bukan password asli
  role          ENUM('admin','staff','user') NOT NULL DEFAULT 'user',
  is_active     BOOLEAN NOT NULL DEFAULT TRUE,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE units (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name            VARCHAR(100) NOT NULL,
  category        VARCHAR(50)  NOT NULL,          -- dome, cabin, treehouse, dst.
  description     TEXT,
  capacity        INT NOT NULL CHECK (capacity > 0),
  total_units     INT NOT NULL CHECK (total_units >= 0),   -- stok total
  price_per_night DECIMAL(12,2) NOT NULL CHECK (price_per_night >= 0),
  image_url       VARCHAR(255),
  is_active       BOOLEAN NOT NULL DEFAULT TRUE,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE bookings (
  id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_code   VARCHAR(20) NOT NULL UNIQUE,     -- contoh: GLM-20261110-A7K2
  user_id        BIGINT UNSIGNED NULL,            -- NULL bila booking tanpa login
  unit_id        BIGINT UNSIGNED NOT NULL,
  check_in       DATE NOT NULL,
  check_out      DATE NOT NULL,
  nights         INT NOT NULL,
  qty_units      INT NOT NULL CHECK (qty_units > 0),
  guest_name     VARCHAR(100) NOT NULL,
  contact_email  VARCHAR(150) NOT NULL,
  contact_phone  VARCHAR(20)  NOT NULL,
  has_insurance  BOOLEAN NOT NULL DEFAULT FALSE,
  insurance_fee  DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_amount   DECIMAL(12,2) NOT NULL,
  status         ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_dates CHECK (check_out > check_in),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (unit_id) REFERENCES units(id),
  INDEX idx_availability (unit_id, check_in, check_out)
);

CREATE TABLE payments (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id  BIGINT UNSIGNED NOT NULL,
  amount      DECIMAL(12,2) NOT NULL,
  method      VARCHAR(30) NOT NULL,               -- dummy_transfer, dummy_qris, dst.
  status      ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  reference   VARCHAR(50),
  paid_at     DATETIME NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

CREATE TABLE cancellations (
  id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id     BIGINT UNSIGNED NOT NULL UNIQUE, -- maksimal satu pengajuan per booking
  requested_by   BIGINT UNSIGNED NOT NULL,
  reason         TEXT NOT NULL,
  status         ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  refund_percent INT NOT NULL DEFAULT 0 CHECK (refund_percent IN (0,50,100)),
  refund_amount  DECIMAL(12,2) NOT NULL DEFAULT 0, -- hanya catatan, bukan transfer nyata
  staff_note     TEXT NULL,
  processed_by   BIGINT UNSIGNED NULL,
  requested_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  processed_at   DATETIME NULL,
  FOREIGN KEY (booking_id)   REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY (requested_by) REFERENCES users(id),
  FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE articles (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  author_id   BIGINT UNSIGNED NOT NULL,
  title       VARCHAR(200) NOT NULL,
  slug        VARCHAR(220) NOT NULL UNIQUE,
  excerpt     VARCHAR(300),
  content     LONGTEXT NOT NULL,
  cover_image VARCHAR(255),
  status      ENUM('draft','published') NOT NULL DEFAULT 'draft',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users(id)
);

CREATE TABLE audit_logs (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     BIGINT UNSIGNED NOT NULL,
  action      VARCHAR(50) NOT NULL,               -- approve_cancellation, update_unit, dst.
  entity_type VARCHAR(50) NOT NULL,
  entity_id   BIGINT UNSIGNED NULL,
  description TEXT,
  ip_address  VARCHAR(45),
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX idx_log_user_time (user_id, created_at)
);

-- Cek ketersediaan unit untuk rentang :tgl_masuk s/d :tgl_keluar
-- (booking diperbolehkan jika sisa >= jumlah unit yang diminta)
-- SELECT u.total_units - COALESCE(SUM(b.qty_units), 0) AS sisa
-- FROM units u
-- LEFT JOIN bookings b ON b.unit_id = u.id
--   AND b.status IN ('pending','confirmed')
--   AND b.check_in  < :tgl_keluar
--   AND b.check_out > :tgl_masuk
-- WHERE u.id = :unit_id
-- GROUP BY u.id;
