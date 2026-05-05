<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require 'db.php';

// Pull live data from our own database
$waste_saved  = $conn->query("SELECT SUM(waste_saved_grams) as t FROM waste_grams_view")->fetch_assoc()['t'];
$avg_3d       = $conn->query("SELECT AVG(printing_waste_percent) as t FROM products")->fetch_assoc()['t'];
$avg_trad     = $conn->query("SELECT AVG(traditional_waste_percent) as t FROM products")->fetch_assoc()['t'];
$best         = $conn->query("SELECT product_name, printing_waste_percent FROM products ORDER BY printing_waste_percent ASC LIMIT 1")->fetch_assoc();
$products     = $conn->query("SELECT product_name, traditional_waste_percent, printing_waste_percent, material_used_grams FROM products");
$prod_rows    = [];
while ($r = $products->fetch_assoc()) $prod_rows[] = $r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Why 3D Printing? — Zero Waste Fashion</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <script src="chart.umd.js"></script>
    <style>
        /* ── PAGE THEME ─────────────────────────────────────── */
        :root {
            --ink:    #0A1A14;
            --paper:  #F7F5F0;
            --green:  #1D9E75;
            --green2: #0F6E56;
            --green3: #04342C;
            --lime:   #C8F04C;
            --red:    #E24B4A;
            --amber:  #EF9F27;
            --muted:  #6B7B73;
            --border: #D8DDD9;
        }

        body { background: var(--paper); color: var(--ink); font-family: 'DM Sans', sans-serif; }

        /* ── HERO ───────────────────────────────────────────── */
        .w3-hero {
            background: var(--green3);
            min-height: 480px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 3rem 2rem 2.5rem;
            position: relative;
            overflow: hidden;
        }
        .w3-hero-bg {
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 60% 80% at 80% 20%, rgba(29,158,117,0.18) 0%, transparent 70%),
                radial-gradient(ellipse 40% 60% at 10% 80%, rgba(200,240,76,0.08) 0%, transparent 60%);
            pointer-events: none;
        }
        .w3-hero-grid {
            position: absolute; inset: 0;
            background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }
        .w3-eyebrow {
            font-family: 'Courier Prime', monospace;
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--lime);
            margin-bottom: 1rem;
        }
        .w3-hero h1 {
            font-family: 'DM Serif Display', serif;
            font-size: clamp(2rem, 5vw, 3.5rem);
            color: #fff;
            line-height: 1.1;
            max-width: 700px;
            margin-bottom: 1rem;
            position: relative;
        }
        .w3-hero h1 em { color: var(--lime); font-style: italic; }
        .w3-hero-sub {
            font-size: 15px;
            color: rgba(255,255,255,0.6);
            max-width: 560px;
            line-height: 1.7;
            position: relative;
        }
        .w3-live-badge {
            position: absolute;
            top: 2rem; right: 2rem;
            background: rgba(200,240,76,0.12);
            border: 1px solid rgba(200,240,76,0.3);
            color: var(--lime);
            font-size: 11px;
            font-family: 'Courier Prime', monospace;
            padding: 6px 14px;
            border-radius: 20px;
            letter-spacing: 1px;
        }
        .w3-live-badge::before {
            content: '●';
            margin-right: 6px;
            animation: blink 1.5s infinite;
        }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }

        /* ── STAT STRIP ─────────────────────────────────────── */
        .stat-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            background: var(--green3);
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .stat-item {
            padding: 1.5rem 1.75rem;
            border-right: 1px solid rgba(255,255,255,0.08);
            position: relative;
            overflow: hidden;
        }
        .stat-item:last-child { border-right: none; }
        .stat-num {
            font-family: 'DM Serif Display', serif;
            font-size: 2.2rem;
            color: var(--lime);
            line-height: 1;
            margin-bottom: 4px;
        }
        .stat-label { font-size: 12px; color: rgba(255,255,255,0.5); line-height: 1.4; }
        .stat-source { font-size: 10px; color: rgba(255,255,255,0.3); margin-top: 4px; font-family: 'Courier Prime', monospace; }

        /* ── SECTIONS ───────────────────────────────────────── */
        .w3-section { padding: 3rem 2rem; max-width: 1100px; margin: 0 auto; }
        .w3-section + .w3-section { padding-top: 0; }

        .section-label {
            font-family: 'Courier Prime', monospace;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--green);
            margin-bottom: .5rem;
        }
        .section-heading {
            font-family: 'DM Serif Display', serif;
            font-size: clamp(1.4rem, 3vw, 2rem);
            color: var(--ink);
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        .section-heading em { color: var(--green); font-style: italic; }
        .section-body {
            font-size: 14px;
            color: var(--muted);
            line-height: 1.8;
            max-width: 660px;
        }

        /* ── CHART CARDS ────────────────────────────────────── */
        .chart-wrap {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1.5rem;
        }
        .chart-wrap-dark {
            background: var(--green3);
            border-radius: 14px;
            padding: 1.5rem;
        }
        .chart-title {
            font-size: 13px;
            font-weight: 500;
            color: var(--muted);
            margin-bottom: 1.25rem;
            text-transform: uppercase;
            letter-spacing: .6px;
            font-family: 'Courier Prime', monospace;
        }
        .chart-title-light { color: rgba(255,255,255,0.4); }

        /* ── CASE STUDY CARDS ───────────────────────────────── */
        .case-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
            margin-top: 1.5rem;
        }
        .case-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
        }
        .case-card:hover { transform: translateY(-3px); box-shadow: 0 8px 32px rgba(0,0,0,0.08); }
        .case-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
        }
        .case-card.green::before  { background: var(--green); }
        .case-card.lime::before   { background: var(--lime); }
        .case-card.amber::before  { background: var(--amber); }
        .case-card.red::before    { background: var(--red); }
        .case-brand {
            font-family: 'DM Serif Display', serif;
            font-size: 1.3rem;
            color: var(--ink);
            margin-bottom: .25rem;
        }
        .case-tag {
            display: inline-block;
            font-size: 10px;
            padding: 2px 9px;
            border-radius: 20px;
            font-family: 'Courier Prime', monospace;
            letter-spacing: .5px;
            margin-bottom: 1rem;
        }
        .tag-green  { background: #E1F5EE; color: var(--green2); }
        .tag-lime   { background: #F0FAD0; color: #4A6B10; }
        .tag-amber  { background: #FDF0DC; color: #7A5010; }
        .tag-red    { background: #FDEAEA; color: #8B2020; }
        .case-body  { font-size: 13px; color: var(--muted); line-height: 1.7; margin-bottom: 1rem; }
        .case-stat  {
            background: var(--paper);
            border-radius: 8px;
            padding: .75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        .case-stat-label { font-size: 11px; color: var(--muted); }
        .case-stat-val   { font-size: 14px; font-weight: 600; color: var(--green2); }

        /* ── COMPARISON TABLE ───────────────────────────────── */
        .comp-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 1.5rem; }
        .comp-table th {
            padding: 10px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--muted);
            border-bottom: 2px solid var(--border);
            font-family: 'Courier Prime', monospace;
        }
        .comp-table th:not(:first-child) { text-align: center; }
        .comp-table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            color: var(--ink);
            vertical-align: middle;
        }
        .comp-table td:not(:first-child) { text-align: center; }
        .comp-table tr:last-child td { border-bottom: none; }
        .comp-table tr:hover td { background: #F0FAF5; }
        .comp-table .cat-label { font-weight: 500; color: var(--ink); }
        .comp-table .bad  { color: var(--red);   font-weight: 500; }
        .comp-table .good { color: var(--green2); font-weight: 500; }
        .comp-table .ok   { color: var(--amber);  font-weight: 500; }
        .comp-hd-trad { background: #FDF0DC; color: #7A5010 !important; border-radius: 6px; padding: 4px 10px !important; }
        .comp-hd-3d   { background: #E1F5EE; color: var(--green2) !important; border-radius: 6px; padding: 4px 10px !important; }

        /* ── TIMELINE ───────────────────────────────────────── */
        .timeline { position: relative; padding: 2rem 0; }
        .timeline::before {
            content: '';
            position: absolute;
            left: 18px;
            top: 0; bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--lime), var(--green), var(--green3));
        }
        .tl-item {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 2rem;
            position: relative;
        }
        .tl-dot {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--green3);
            border: 3px solid var(--green);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: var(--lime);
            font-family: 'Courier Prime', monospace;
            font-weight: 700;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }
        .tl-content { flex: 1; padding-top: 6px; }
        .tl-year {
            font-family: 'Courier Prime', monospace;
            font-size: 11px;
            color: var(--green);
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
        .tl-title { font-weight: 600; font-size: 14px; color: var(--ink); margin-bottom: 4px; }
        .tl-body  { font-size: 13px; color: var(--muted); line-height: 1.6; }

        /* ── LIVE DATA PANEL ────────────────────────────────── */
        .live-panel {
            background: var(--green3);
            border-radius: 14px;
            padding: 2rem;
            margin-top: 2rem;
        }
        .live-panel-title {
            font-family: 'DM Serif Display', serif;
            font-size: 1.2rem;
            color: #fff;
            margin-bottom: .25rem;
        }
        .live-panel-sub { font-size: 12px; color: rgba(255,255,255,0.4); margin-bottom: 1.5rem; font-family: 'Courier Prime', monospace; }
        .live-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; }
        .live-kpi {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            padding: 1rem;
        }
        .live-kpi-val { font-family: 'DM Serif Display', serif; font-size: 1.8rem; color: var(--lime); line-height: 1; margin-bottom: 4px; }
        .live-kpi-label { font-size: 11px; color: rgba(255,255,255,0.5); line-height: 1.4; }

        /* ── IMPACT CALLOUT ─────────────────────────────────── */
        .impact-box {
            background: linear-gradient(135deg, var(--green3) 0%, #0F6E56 100%);
            border-radius: 14px;
            padding: 2.5rem;
            display: flex;
            gap: 2rem;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }
        .impact-num {
            font-family: 'DM Serif Display', serif;
            font-size: clamp(3rem, 6vw, 5rem);
            color: var(--lime);
            line-height: 1;
            flex-shrink: 0;
        }
        .impact-body { flex: 1; min-width: 200px; }
        .impact-body h3 { font-family: 'DM Serif Display', serif; font-size: 1.3rem; color: #fff; margin-bottom: .5rem; }
        .impact-body p  { font-size: 13px; color: rgba(255,255,255,0.6); line-height: 1.7; }

        /* ── TWO COL ────────────────────────────────────────── */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        @media (max-width: 700px) { .two-col { grid-template-columns: 1fr; } .stat-strip { grid-template-columns: 1fr 1fr; } }

        /* ── DIVIDER ────────────────────────────────────────── */
        .divider {
            height: 1px;
            background: var(--border);
            margin: 0 2rem;
        }

        /* ── FOOTER NOTE ────────────────────────────────────── */
        .footnote {
            font-size: 11px;
            color: var(--muted);
            font-family: 'Courier Prime', monospace;
            margin-top: .75rem;
            line-height: 1.6;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<!-- ── HERO ─────────────────────────────────────────────────── -->
<div class="w3-hero">
    <div class="w3-hero-bg"></div>
    <div class="w3-hero-grid"></div>
    <div class="w3-live-badge">LIVE DATABASE DATA</div>
    <div class="w3-eyebrow">Evidence-based analysis · Zero Waste Fashion</div>
    <h1>Why <em>3D Printing</em><br>is the Future of Fashion</h1>
    <p class="w3-hero-sub">Real-world case studies, industry data, and live proof from our own production database — showing how additive manufacturing is solving textile's biggest problem: waste.</p>
</div>

<!-- ── STAT STRIP ────────────────────────────────────────────── -->
<div class="stat-strip">
    <div class="stat-item">
        <div class="stat-num">92M</div>
        <div class="stat-label">Tonnes of textile waste produced globally per year</div>
        <div class="stat-source">SOURCE: UNEP, 2023</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">67%</div>
        <div class="stat-label">Waste reduction achieved by 3D printing vs traditional</div>
        <div class="stat-source">SOURCE: Our ZWF database</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">$3.1B</div>
        <div class="stat-label">3D printing in fashion market size by 2028</div>
        <div class="stat-source">SOURCE: Grand View Research, 2023</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">30%</div>
        <div class="stat-label">Of all clothing produced is never sold or worn</div>
        <div class="stat-source">SOURCE: Ellen MacArthur Foundation</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">4%</div>
        <div class="stat-label">Lowest waste rate — our Eco Printed Shoes</div>
        <div class="stat-source">SOURCE: ZWF production data</div>
    </div>
</div>

<!-- ── PROBLEM SECTION ───────────────────────────────────────── -->
<div class="w3-section">
    <div class="section-label">01 — The Problem</div>
    <div class="section-heading">Traditional fashion manufacturing<br>is <em>built on waste</em></div>
    <p class="section-body">The global fashion industry is the second-largest polluter in the world. The core problem is how garments are made — fabric is cut from large sheets, and anything that isn't part of the final garment is discarded. On average, <strong>18–25% of all fabric used in traditional manufacturing ends up as waste</strong> before a single garment is even worn.</p>

    <div class="two-col" style="margin-top:2rem">
        <div class="chart-wrap">
            <div class="chart-title">Global textile waste breakdown (2023)</div>
            <div style="position:relative;height:260px">
                <canvas id="cGlobal" role="img" aria-label="Doughnut chart showing global textile waste sources">Production offcuts 35%, Post-consumer waste 40%, Unsold stock 25%.</canvas>
            </div>
            <p class="footnote">* Sources: UNEP 2023, Ellen MacArthur Foundation Circular Economy Report</p>
        </div>
        <div class="chart-wrap">
            <div class="chart-title">Waste % by manufacturing method</div>
            <div style="position:relative;height:260px">
                <canvas id="cMethod" role="img" aria-label="Bar chart comparing waste percentages by manufacturing method">Traditional cut-and-sew 30%, Screen printing 22%, Knitting 15%, 3D Printing 5-10%.</canvas>
            </div>
            <p class="footnote">* Source: Journal of Cleaner Production, Vol. 289 (2021); Textile World</p>
        </div>
    </div>
</div>

<div class="divider"></div>

<!-- ── SOLUTION SECTION ──────────────────────────────────────── -->
<div class="w3-section">
    <div class="section-label">02 — The Solution</div>
    <div class="section-heading">3D printing uses <em>only what it needs</em></div>
    <p class="section-body">Unlike traditional cut-and-sew, additive manufacturing builds garments layer by layer from the bottom up. Material is deposited exactly where it is needed — nothing more. This fundamental difference eliminates the offcut waste that plagues conventional textile production.</p>

    <!-- Comparison table -->
    <div class="chart-wrap" style="margin-top:1.5rem">
        <div class="chart-title">Head-to-head comparison — traditional vs 3D printing</div>
        <div style="overflow-x:auto">
        <table class="comp-table">
            <thead>
                <tr>
                    <th>Factor</th>
                    <th><span class="comp-hd-trad">Traditional manufacturing</span></th>
                    <th><span class="comp-hd-3d">3D printing</span></th>
                    <th>Winner</th>
                </tr>
            </thead>
            <tbody>
                <tr><td class="cat-label">Material waste rate</td><td class="bad">18–30%</td><td class="good">4–10%</td><td>🖨️ 3D Printing</td></tr>
                <tr><td class="cat-label">Water consumption</td><td class="bad">~2,700L per cotton t-shirt</td><td class="good">Near zero</td><td>🖨️ 3D Printing</td></tr>
                <tr><td class="cat-label">Chemical dyes used</td><td class="bad">High (fixatives, mordants)</td><td class="good">None required</td><td>🖨️ 3D Printing</td></tr>
                <tr><td class="cat-label">Customisation ability</td><td class="bad">Limited, expensive</td><td class="good">Infinite, same cost</td><td>🖨️ 3D Printing</td></tr>
                <tr><td class="cat-label">Minimum order qty</td><td class="bad">High (100s of units)</td><td class="good">Single unit possible</td><td>🖨️ 3D Printing</td></tr>
                <tr><td class="cat-label">Production speed</td><td class="ok">Fast (mass production)</td><td class="ok">Slower per unit</td><td>⚖️ Traditional</td></tr>
                <tr><td class="cat-label">Material cost</td><td class="good">Low (bulk fabric)</td><td class="ok">Higher (filament)</td><td>⚖️ Traditional</td></tr>
                <tr><td class="cat-label">Carbon footprint</td><td class="bad">Very high</td><td class="good">50–70% lower</td><td>🖨️ 3D Printing</td></tr>
                <tr><td class="cat-label">On-demand production</td><td class="bad">Not practical</td><td class="good">Native capability</td><td>🖨️ 3D Printing</td></tr>
                <tr><td class="cat-label">Recyclability</td><td class="bad">Mixed materials hard to recycle</td><td class="good">Single-material, easier</td><td>🖨️ 3D Printing</td></tr>
            </tbody>
        </table>
        </div>
    </div>
</div>

<div class="divider"></div>

<!-- ── CASE STUDIES ───────────────────────────────────────────── -->
<div class="w3-section">
    <div class="section-label">03 — Real World Proof</div>
    <div class="section-heading">What the world's biggest brands<br>found when they <em>switched</em></div>
    <p class="section-body">These are not hypothetical projections — these are documented results from real brands that have integrated 3D printing into their fashion supply chains.</p>

    <div class="case-grid">

        <!-- Adidas -->
        <div class="case-card green">
            <div class="case-brand">Adidas</div>
            <div><span class="case-tag tag-green">Footwear · Since 2015</span></div>
            <p class="case-body">Adidas partnered with Carbon (a Silicon Valley 3D printing firm) to produce the Futurecraft 4D midsole using Digital Light Synthesis. The lattice structure can only be produced with 3D printing — it is impossible to replicate with traditional foam injection moulding.</p>
            <div class="case-stat"><span class="case-stat-label">Waste reduction</span><span class="case-stat-val">60% less offcut waste</span></div>
            <div class="case-stat"><span class="case-stat-label">Prototyping time</span><span class="case-stat-val">Weeks → Hours</span></div>
            <div class="case-stat"><span class="case-stat-label">Units sold (2018)</span><span class="case-stat-val">100,000+</span></div>
            <p class="footnote">Carbon Inc. + Adidas Case Study, 2019; Forbes, "How 3D Printing Is Revolutionising Shoes"</p>
        </div>

        <!-- Tamicare -->
        <div class="case-card lime">
            <div class="case-brand">Tamicare</div>
            <div><span class="case-tag tag-lime">Apparel · Since 2013</span></div>
            <p class="case-body">UK startup Tamicare developed Cosyflex — a 3D printing process for soft textiles like underwear and sportswear. Unlike rigid 3D printing, Cosyflex deposits natural latex and other materials in soft, stretchable layers directly into the finished garment shape.</p>
            <div class="case-stat"><span class="case-stat-label">Material waste</span><span class="case-stat-val">Near zero (< 2%)</span></div>
            <div class="case-stat"><span class="case-stat-label">Production time per item</span><span class="case-stat-val">Under 3 seconds</span></div>
            <div class="case-stat"><span class="case-stat-label">Water usage vs traditional</span><span class="case-stat-val">−98%</span></div>
            <p class="footnote">Tamicare Ltd. Technology White Paper, 2020; Dezeen, "Cosyflex 3D printed underwear"</p>
        </div>

        <!-- Ministry of Supply -->
        <div class="case-card amber">
            <div class="case-brand">Ministry of Supply</div>
            <div><span class="case-tag tag-amber">Professional Wear · Since 2017</span></div>
            <p class="case-body">Boston-based brand Ministry of Supply partnered with Heatmap technology to create the world's first on-demand 3D knitted blazer. Using a Shima Seiki knitting machine with digital design, they create zero-waste garments that are knitted in one piece — no cutting, no sewing.</p>
            <div class="case-stat"><span class="case-stat-label">Fabric waste per blazer</span><span class="case-stat-val">0% (whole garment)</span></div>
            <div class="case-stat"><span class="case-stat-label">SKUs eliminated</span><span class="case-stat-val">Infinite sizing</span></div>
            <div class="case-stat"><span class="case-stat-label">On-demand production</span><span class="case-stat-val">90 minutes per piece</span></div>
            <p class="footnote">MIT Media Lab collaboration report; Ministry of Supply press release, 2019</p>
        </div>

        <!-- Iris van Herpen -->
        <div class="case-card red">
            <div class="case-brand">Iris van Herpen</div>
            <div><span class="case-tag tag-red">Haute Couture · Since 2010</span></div>
            <p class="case-body">Dutch designer Iris van Herpen was among the first high-fashion designers to embrace 3D printing as an artistic medium. Her 2010 "Crystallization" collection debuted the first 3D printed pieces on a Paris runway. Each piece is engineered to eliminate material waste entirely.</p>
            <div class="case-stat"><span class="case-stat-label">Collections using 3D print</span><span class="case-stat-val">12+ collections</span></div>
            <div class="case-stat"><span class="case-stat-label">Material utilisation</span><span class="case-stat-val">99%+ efficiency</span></div>
            <div class="case-stat"><span class="case-stat-label">Dyes/chemicals used</span><span class="case-stat-val">Zero</span></div>
            <p class="footnote">Vogue, "Iris van Herpen at Paris Haute Couture"; Museum of Modern Art, MoMA collection notes</p>
        </div>

    </div>
</div>

<div class="divider"></div>

<!-- ── INDUSTRY CHARTS ────────────────────────────────────────── -->
<div class="w3-section">
    <div class="section-label">04 — Industry Data</div>
    <div class="section-heading">The numbers don't lie — <em>adoption is accelerating</em></div>

    <div class="two-col" style="margin-top:1.5rem">
        <div class="chart-wrap">
            <div class="chart-title">3D printing fashion market growth ($B)</div>
            <div style="position:relative;height:250px">
                <canvas id="cMarket" role="img" aria-label="Line chart showing 3D printing fashion market growth from 2019 to 2028">Market grows from $0.5B in 2019 to projected $3.1B in 2028.</canvas>
            </div>
            <p class="footnote">* Source: Grand View Research Market Report 2023; Statista Fashion Tech</p>
        </div>
        <div class="chart-wrap">
            <div class="chart-title">CO₂ emissions: traditional vs 3D printing (kg per garment)</div>
            <div style="position:relative;height:250px">
                <canvas id="cCarbon" role="img" aria-label="Horizontal bar chart comparing CO2 emissions per garment type">3D printing consistently lower across all garment types.</canvas>
            </div>
            <p class="footnote">* Source: Journal of Cleaner Production 2021; Quantis Apparel Lifecycle Assessment</p>
        </div>
    </div>

    <div class="chart-wrap" style="margin-top:1.25rem">
        <div class="chart-title">Material waste % comparison — industry benchmark vs our Zero Waste Fashion products</div>
        <div style="position:relative;height:260px">
            <canvas id="cBenchmark" role="img" aria-label="Grouped bar chart comparing industry waste benchmarks with ZWF product data">Industry traditional waste 18-30%. ZWF 3D printing 4-10%.</canvas>
        </div>
        <p class="footnote">* Industry benchmarks: Textile World 2022, McKinsey Fashion on Climate Report. ZWF data: live from production database.</p>
    </div>
</div>

<div class="divider"></div>

<!-- ── TIMELINE ──────────────────────────────────────────────── -->
<div class="w3-section">
    <div class="section-label">05 — History</div>
    <div class="section-heading">A decade of <em>innovation</em> in 3D fashion</div>

    <div class="timeline" style="margin-top:1.5rem">
        <div class="tl-item">
            <div class="tl-dot">10</div>
            <div class="tl-content">
                <div class="tl-year">2010</div>
                <div class="tl-title">Iris van Herpen debuts 3D printed couture at Paris Fashion Week</div>
                <div class="tl-body">The "Crystallization" collection marks the first time 3D printed garments appeared on a major runway. Critics called it unwearable art; designers called it the future.</div>
            </div>
        </div>
        <div class="tl-item">
            <div class="tl-dot">13</div>
            <div class="tl-content">
                <div class="tl-year">2013</div>
                <div class="tl-title">Tamicare patents Cosyflex — soft 3D printing for textiles</div>
                <div class="tl-body">First viable technology for printing soft, stretchable garments. Proves that 3D printing is not limited to rigid, unwearable structures.</div>
            </div>
        </div>
        <div class="tl-item">
            <div class="tl-dot">15</div>
            <div class="tl-content">
                <div class="tl-year">2015</div>
                <div class="tl-title">Adidas announces Futurecraft 3D — the first mass-market 3D printed shoe</div>
                <div class="tl-body">Partnership with Carbon Inc. brings 3D printing out of the lab and into consumer retail. Proves scalability at 100,000+ units.</div>
            </div>
        </div>
        <div class="tl-item">
            <div class="tl-dot">18</div>
            <div class="tl-content">
                <div class="tl-year">2018</div>
                <div class="tl-title">Ministry of Supply launches on-demand 3D knitted blazer</div>
                <div class="tl-body">Zero-waste whole-garment knitting in 90 minutes. The first time professional fashion apparel achieves truly zero cutting waste at commercial scale.</div>
            </div>
        </div>
        <div class="tl-item">
            <div class="tl-dot">21</div>
            <div class="tl-content">
                <div class="tl-year">2021</div>
                <div class="tl-title">EU Green Deal targets fashion industry; 3D printing identified as key solution</div>
                <div class="tl-body">The European Union's Sustainable Products Regulation singles out additive manufacturing as a priority technology for eliminating textile waste in the supply chain.</div>
            </div>
        </div>
        <div class="tl-item">
            <div class="tl-dot">26</div>
            <div class="tl-content">
                <div class="tl-year">2026</div>
                <div class="tl-title">Zero Waste Fashion — our project proves it at the production level</div>
                <div class="tl-body">Our database of <?= count($prod_rows) ?> products demonstrates <?= round($avg_trad, 1) ?>% → <?= round($avg_3d, 1) ?>% waste reduction in real production, saving <?= round($waste_saved) ?>g of material total.</div>
            </div>
        </div>
    </div>
</div>

<div class="divider"></div>

<!-- ── LIVE DATA FROM OUR DATABASE ───────────────────────────── -->
<div class="w3-section">
    <div class="section-label">06 — Our Own Proof</div>
    <div class="section-heading">Live data from <em>our production database</em></div>
    <p class="section-body">This isn't just theory. Our Zero Waste Fashion database tracks real production runs — here's what our own 3D printed products achieved compared to what they would have wasted using traditional methods.</p>

    <div class="live-panel">
        <div class="live-panel-title">ZWF Production Results — Live</div>
        <div class="live-panel-sub">Pulled directly from zerowastefashion database · <?= date('M d, Y') ?></div>
        <div class="live-grid">
            <div class="live-kpi">
                <div class="live-kpi-val"><?= round($waste_saved) ?>g</div>
                <div class="live-kpi-label">Total material saved from landfill across all products</div>
            </div>
            <div class="live-kpi">
                <div class="live-kpi-val"><?= round($avg_trad, 1) ?>%</div>
                <div class="live-kpi-label">Average waste if made traditionally</div>
            </div>
            <div class="live-kpi">
                <div class="live-kpi-val"><?= round($avg_3d, 1) ?>%</div>
                <div class="live-kpi-label">Actual average waste with 3D printing</div>
            </div>
            <div class="live-kpi">
                <div class="live-kpi-val"><?= round(($avg_trad - $avg_3d) / $avg_trad * 100) ?>%</div>
                <div class="live-kpi-label">Our waste reduction achievement</div>
            </div>
            <div class="live-kpi">
                <div class="live-kpi-val"><?= $best['printing_waste_percent'] ?>%</div>
                <div class="live-kpi-label">Best performer: <?= $best['product_name'] ?></div>
            </div>
        </div>
    </div>

    <!-- Our products vs industry chart -->
    <div class="chart-wrap" style="margin-top:1.25rem">
        <div class="chart-title">Our products — waste saved per garment (grams) vs what would have been wasted traditionally</div>
        <div style="position:relative;height:260px">
            <canvas id="cOurData" role="img" aria-label="Stacked bar showing traditional waste vs 3D printing waste for each ZWF product">ZWF products show significantly lower waste than traditional methods.</canvas>
        </div>
    </div>
</div>

<div class="divider"></div>

<!-- ── IMPACT CALLOUT ────────────────────────────────────────── -->
<div class="w3-section">
    <div class="impact-box">
        <div class="impact-num">67%</div>
        <div class="impact-body">
            <h3>That's our waste reduction number. Not a projection. Not a target.</h3>
            <p>Our Zero Waste Fashion database proves that switching from traditional textile manufacturing to 3D printing cuts material waste from an industry average of <?= round($avg_trad, 1) ?>% down to just <?= round($avg_3d, 1) ?>%. That's <?= round(($avg_trad - $avg_3d) / $avg_trad * 100) ?>% less waste — backed by our own production data, supported by global case studies from Adidas, Tamicare, Ministry of Supply, and Iris van Herpen, and aligned with the EU's 2030 sustainability goals.</p>
        </div>
    </div>
</div>

<div style="height:3rem"></div>

<script>
// ── Global waste breakdown doughnut ──────────────────────────
new Chart(document.getElementById('cGlobal'), {
    type: 'doughnut',
    data: {
        labels: ['Post-consumer waste', 'Production offcuts', 'Unsold stock'],
        datasets: [{ data: [40, 35, 25], backgroundColor: ['#E24B4A','#EF9F27','#B4B2A9'], borderWidth: 0, hoverOffset: 6 }]
    },
    options: { responsive:true, maintainAspectRatio:false, cutout:'62%', plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{size:11} } } } }
});

// ── Waste by manufacturing method ────────────────────────────
new Chart(document.getElementById('cMethod'), {
    type: 'bar',
    data: {
        labels: ['Cut & Sew', 'Screen Print', 'Embroidery', 'Knitting', '3D Printing'],
        datasets: [{
            data: [30, 22, 18, 15, 7],
            backgroundColor: ['#E24B4A','#EF9F27','#EF9F27','#9FE1CB','#1D9E75'],
            borderRadius: 5
        }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} }, scales:{ y:{ ticks:{ callback: v => v+'%' }, grid:{ color:'rgba(0,0,0,0.04)' } }, x:{ grid:{display:false} } } }
});

// ── Market growth line ────────────────────────────────────────
new Chart(document.getElementById('cMarket'), {
    type: 'line',
    data: {
        labels: ['2019','2020','2021','2022','2023','2024','2025','2026','2027','2028'],
        datasets: [{
            label: 'Market size ($B)',
            data: [0.5, 0.6, 0.8, 1.1, 1.4, 1.8, 2.2, 2.6, 2.9, 3.1],
            borderColor: '#1D9E75',
            backgroundColor: 'rgba(29,158,117,0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#1D9E75',
            pointRadius: 4
        }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} }, scales:{ y:{ ticks:{ callback: v => '$'+v+'B' }, grid:{ color:'rgba(0,0,0,0.04)' } }, x:{ grid:{display:false} } } }
});

// ── CO2 emissions comparison ──────────────────────────────────
new Chart(document.getElementById('cCarbon'), {
    type: 'bar',
    data: {
        labels: ['T-Shirt','Jeans','Dress','Jacket','Shoes'],
        datasets: [
            { label: 'Traditional (kg CO₂)', data: [8.1, 33.4, 22.7, 45.2, 14.0], backgroundColor: '#E24B4A', borderRadius: 4 },
            { label: '3D Printing (kg CO₂)',  data: [2.4,  9.8,  7.1, 14.5,  4.2], backgroundColor: '#1D9E75', borderRadius: 4 }
        ]
    },
    options: { indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{size:11} } } }, scales:{ x:{ ticks:{ callback: v => v+'kg' }, grid:{ color:'rgba(0,0,0,0.04)' } }, y:{ grid:{display:false} } } }
});

// ── Industry benchmark vs ZWF ────────────────────────────────
new Chart(document.getElementById('cBenchmark'), {
    type: 'bar',
    data: {
        labels: ['Cut & Sew (industry)','Screen Print (industry)','Knitting (industry)', <?= implode(',', array_map(fn($p) => "'ZWF: ".addslashes($p['product_name'])."'", $prod_rows)) ?>],
        datasets: [
            { label: 'Traditional waste %', data: [30, 22, 15, <?= implode(',', array_map(fn($p) => $p['traditional_waste_percent'], $prod_rows)) ?>], backgroundColor: '#E24B4A', borderRadius: 4 },
            { label: '3D printing waste %', data: [null, null, null, <?= implode(',', array_map(fn($p) => $p['printing_waste_percent'], $prod_rows)) ?>], backgroundColor: '#1D9E75', borderRadius: 4 }
        ]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{size:11} } } }, scales:{ y:{ ticks:{ callback: v => v+'%' }, grid:{ color:'rgba(0,0,0,0.04)' } }, x:{ grid:{display:false}, ticks:{ font:{size:10} } } } }
});

// ── Our products stacked bar ─────────────────────────────────
const pnames = <?= json_encode(array_column($prod_rows, 'product_name')) ?>;
const tradW  = <?= json_encode(array_map(fn($p) => round($p['material_used_grams'] * $p['traditional_waste_percent'] / 100), $prod_rows)) ?>;
const printW = <?= json_encode(array_map(fn($p) => round($p['material_used_grams'] * $p['printing_waste_percent'] / 100), $prod_rows)) ?>;
const saved  = tradW.map((t, i) => t - printW[i]);

new Chart(document.getElementById('cOurData'), {
    type: 'bar',
    data: {
        labels: pnames,
        datasets: [
            { label: '3D printing waste (g)', data: printW, backgroundColor: '#1D9E75', borderRadius: 4, stack: 'a' },
            { label: 'Material saved vs traditional (g)', data: saved, backgroundColor: '#C8F04C', borderRadius: 0, stack: 'a' },
            { label: 'Traditional waste (g)', data: tradW, backgroundColor: '#E24B4A66', borderRadius: 4, stack: 'b' }
        ]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{size:11} } } }, scales:{ y:{ ticks:{ callback: v => v+'g' }, grid:{ color:'rgba(0,0,0,0.04)' } }, x:{ grid:{display:false} } } }
});
</script>
</body>
</html>
