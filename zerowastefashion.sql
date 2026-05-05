-- ============================================================
--  ZERO WASTE FASHION — Complete Database Setup
--  Run in phpMyAdmin or: mysql -u root -p < zerowastefashion.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS zerowastefashion
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zerowastefashion;

-- Drop in safe FK order
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS production;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS garment;
DROP TABLE IF EXISTS machine;
DROP TABLE IF EXISTS users;
DROP VIEW IF EXISTS waste_grams_view;
DROP VIEW IF EXISTS waste_view;

-- ── TABLES ───────────────────────────────────────────────────

CREATE TABLE garment (
  garment_id   INT PRIMARY KEY,
  garment_name VARCHAR(100) NOT NULL,
  material_type VARCHAR(100) NOT NULL
);

CREATE TABLE machine (
  machine_id   INT PRIMARY KEY,
  machine_type VARCHAR(100) NOT NULL,
  status       ENUM('Active','Inactive') DEFAULT 'Active'
);

CREATE TABLE products (
  product_id               INT PRIMARY KEY,
  product_name             VARCHAR(100) NOT NULL,
  material_type            VARCHAR(100) NOT NULL,
  material_used_grams      INT NOT NULL,
  traditional_waste_percent DECIMAL(5,2) NOT NULL,
  printing_waste_percent   DECIMAL(5,2) NOT NULL,
  price                    DECIMAL(10,2) NOT NULL
);

CREATE TABLE production (
  production_id   INT PRIMARY KEY,
  production_date DATE NOT NULL,
  material_used   INT NOT NULL,
  waste_generated INT NOT NULL,
  garment_id      INT NOT NULL,
  machine_id      INT NOT NULL,
  FOREIGN KEY (garment_id)  REFERENCES garment(garment_id),
  FOREIGN KEY (machine_id)  REFERENCES machine(machine_id)
);

CREATE TABLE orders (
  order_id   INT PRIMARY KEY,
  order_date DATE NOT NULL,
  quantity   INT NOT NULL,
  garment_id INT NOT NULL,
  FOREIGN KEY (garment_id) REFERENCES garment(garment_id)
);

CREATE TABLE users (
  user_id  INT PRIMARY KEY AUTO_INCREMENT,
  name     VARCHAR(100) NOT NULL,
  email    VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role     ENUM('admin','user') DEFAULT 'user'
);

-- ── SEED DATA ────────────────────────────────────────────────

INSERT INTO garment VALUES
  (1, '3D Printed Dress',    'PLA Bioplastic'),
  (2, 'Eco Printed Shoes',   'Recycled TPU'),
  (3, 'Zero Waste Jacket',   'Recycled PET'),
  (4, 'Smart Fabric Hoodie', 'Organic Cotton');

INSERT INTO machine VALUES
  (1, '3D Printer - PLA',          'Active'),
  (2, '3D Printer - TPU',          'Active'),
  (3, 'Laser Cutting Machine',      'Inactive'),
  (4, 'Industrial Sewing Machine',  'Active');

INSERT INTO products VALUES
  (1, '3D Printed Dress',  'PLA Bioplastic', 500, 20.00, 5.00,  3500.00),
  (2, 'Eco Printed Shoes', 'Recycled TPU',   300, 18.00, 4.00,  2500.00),
  (3, 'Zero Waste Jacket', 'Recycled PET',   450, 22.00, 6.00,  4200.00),
  (4, 'Smart Hoodie',      'Organic Cotton', 400, 25.00, 10.00, 3000.00);

INSERT INTO production VALUES
  (1, '2026-01-10', 500, 25, 1, 1),
  (2, '2026-01-12', 300, 12, 2, 2),
  (3, '2026-01-15', 450, 27, 3, 1),
  (4, '2026-01-18', 400, 60, 4, 4);

INSERT INTO orders VALUES
  (1, '2026-01-20', 10, 1),
  (2, '2026-01-22', 15, 2),
  (3, '2026-01-25',  8, 3),
  (4, '2026-01-28', 12, 4),
  (5, '2026-01-20', 10, 1),
  (6, '2026-01-22', 15, 2),
  (7, '2026-01-25',  8, 3),
  (8, '2026-01-28', 12, 4);

INSERT INTO users (name, email, password, role) VALUES
  ('Admin User',  'admin@gmail.com', 'admin123', 'admin'),
  ('Normal User', 'user@gmail.com',  'user123',  'user');

-- ── VIEWS ────────────────────────────────────────────────────

CREATE VIEW waste_grams_view AS
  SELECT product_name,
         ROUND(material_used_grams * (traditional_waste_percent - printing_waste_percent) / 100, 4)
           AS waste_saved_grams
  FROM products;

CREATE VIEW waste_view AS
  SELECT product_name,
         (traditional_waste_percent - printing_waste_percent) AS waste_saved
  FROM products;

-- ── HIGH IMPACT VIEW TABLE ───────────────────────────────────

CREATE TABLE IF NOT EXISTS high_impact AS
  SELECT * FROM products WHERE (traditional_waste_percent - printing_waste_percent) >= 14;
