#  Zero Waste Fashion — Database & Analytics Dashboard

> A MySQL-based database system to store, manage, and analyze production and waste data — evaluating how **3D printing reduces textile waste** compared to traditional manufacturing methods.

---

##  Project Overview

The textile industry generates significant waste due to traditional cut-and-sew manufacturing techniques. There is no proper system to analyze production data and measure waste efficiency at a granular level.

This project designs and implements a **MySQL relational database** backed by a **PHP web dashboard** to:

- Store garment, production, machine, and order data
- Measure and visualize how 3D printing reduces textile waste
- Compare waste percentages between traditional and additive manufacturing
- Prove sustainability impact with real production numbers

**Result:** Our system demonstrates a **67% reduction in material waste** — from an industry average of 21% down to just 7% — across 4 3D-printed garment types.

---

## 🗂️ Project Structure

```
zerowastefashion/
│
├── db.php                  # MySQL database connection
├── index.php               # Redirects to login
├── login.php               # Authentication (session-based)
├── logout.php              # Session destroy & redirect
├── navbar.php              # Shared navigation bar
├── style.css               # Global stylesheet
│
├── dashboard.php           # KPI overview + charts
├── products.php            # Products table + waste analysis charts
├── orders.php              # Orders with revenue, sorting, charts
├── production.php          # Production runs + material vs waste chart
├── machines.php            # Machine status cards
├── waste_report.php        # waste_grams_view + waste_view display
├── why3d.php               # Why 3D printing? — case studies + data
│
├── chart.umd.js            # Chart.js v4.4.1 (local copy)
└── zerowastefashion.sql    # Full database setup + seed data
```

---

## 🗃️ Database Schema

```
zerowastefashion
│
├── garment          (garment_id, garment_name, material_type)
├── machine          (machine_id, machine_type, status)
├── products         (product_id, product_name, material_type,
│                     material_used_grams, traditional_waste_percent,
│                     printing_waste_percent, price)
├── production       (production_id, production_date, material_used,
│                     waste_generated, garment_id FK, machine_id FK)
├── orders           (order_id, order_date, quantity, garment_id FK)
├── users            (user_id, name, email, password, role)
│
├── VIEW: waste_grams_view   → waste saved in grams per product
├── VIEW: waste_view         → waste saved in % per product
└── TABLE: high_impact       → products with highest waste reduction
```

**Normalization:** All tables are normalized up to **5NF** — covering 1NF through BCNF, 4NF (multi-valued dependencies), and 5NF (join dependencies).

**Transactions:** 5 documented TCL transactions using `SAVEPOINT`, `COMMIT`, and `ROLLBACK` covering insert, update, batch, and machine safety scenarios.

**Concurrency:** Row-level locking with `SELECT ... FOR UPDATE` demonstrated via two-session simulation.

---

## 🖥️ Pages & Features

| Page | URL | Description |
|------|-----|-------------|
| Login | `/login.php` | Session-based authentication |
| Dashboard | `/dashboard.php` | KPI cards, waste charts, progress bars, recent orders |
| Products | `/products.php` | All products with radar chart + efficiency ratings |
| Orders | `/orders.php` | Sortable orders table, donut chart, revenue KPIs |
| Production | `/production.php` | Production runs, waste % per run, bar chart |
| Machines | `/machines.php` | Machine cards with usage stats |
| Waste Report | `/waste_report.php` | Both database views displayed with charts |
| Why 3D? | `/why3d.php` | Case studies, industry data, live DB proof, sources |

---

## 📊 Key Results

| Product | Traditional Waste | 3D Printing Waste | Saved |
|---------|-------------------|-------------------|-------|
| 3D Printed Dress | 20% | 5% | 75g |
| Eco Printed Shoes | 18% | 4% | 42g |
| Zero Waste Jacket | 22% | 6% | 72g |
| Smart Fabric Hoodie | 25% | 10% | 60g |
| **Total** | **21% avg** | **7% avg** | **249g** |

---

##  Setup Instructions

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8.x)
- Web browser (Chrome / Edge recommended)

### Steps

**1. Clone or download this repository**
```bash
git clone https://github.com/yourusername/zerowastefashion.git
```

**2. Move the folder to XAMPP htdocs**
```
C:\xampp\htdocs\zerowastefashion\
```

**3. Import the database**
- Start Apache and MySQL in XAMPP Control Panel
- Open `http://localhost/phpmyadmin`
- Click **Import** → select `zerowastefashion.sql` → click **Go**

**4. Add Chart.js locally**
- Download from: `https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js`
- Save as `chart.umd.js` inside the project folder

**5. Open in browser**
```
http://localhost/zerowastefashion/login.php
```

**6. Login credentials**
```
Admin:  admin@gmail.com  /  admin123
User:   user@gmail.com   /  user123
```

---

##  Technologies Used

| Layer | Technology |
|-------|-----------|
| Database | MySQL / MariaDB (via XAMPP) |
| Backend | PHP 8.2 |
| Frontend | HTML5, CSS3, JavaScript |
| Charts | Chart.js v4.4.1 |
| Server | Apache (XAMPP) |
| Fonts | Google Fonts — DM Sans, DM Serif Display |

---

##  Normalization Summary

| Normal Form | Status | Key Action |
|-------------|--------|------------|
| 1NF | ✅ Applied | Atomic values, no repeating groups |
| 2NF | ✅ Applied | Removed partial dependencies |
| 3NF | ✅ Applied | Removed transitive dependencies |
| BCNF | ✅ Applied | All determinants are superkeys |
| 4NF | ✅ Applied | Removed multi-valued dependencies |
| 5NF | ✅ Applied | Decomposed join dependencies |

---

## 🌱 Why 3D Printing?

Real-world case studies referenced in this project:

- **Adidas Futurecraft 4D** — 60% waste reduction, 100K+ units ([carbon3d.com](https://www.carbon3d.com/case-studies/adidas))
- **Tamicare Cosyflex** — under 2% waste, −98% water usage ([tamicare.com](https://tamicare.com/technology))
- **Ministry of Supply** — 0% cutting waste, 90-min on-demand blazer ([fastcompany.com](https://www.fastcompany.com/90278440/this-blazer-is-3d-knit-on-demand-in-90-minutes))
- **Iris van Herpen** — 99%+ material efficiency, zero chemical dyes ([irisvanherpen.com](https://www.irisvanherpen.com))

Industry sources: [UNEP](https://www.unep.org), [Ellen MacArthur Foundation](https://ellenmacarthurfoundation.org/a-new-textiles-economy), [McKinsey Fashion on Climate](https://www.mckinsey.com/industries/retail/our-insights/fashion-on-climate), [Grand View Research](https://www.grandviewresearch.com/industry-analysis/3d-printing-market)

---

##  Author

**Jagrati**
- Department of Computing Technologies
- SRMIST,Kattankulathur
- Academic Year: 2025–2026

---

##  License

This project is submitted as an academic database project. All industry data and case study references are cited within the application. Not intended for commercial use.

---

> *"3D printing doesn't just change how we make clothes — it changes how much we waste making them."*
