-- Insert 6 categories
INSERT INTO categories (name) VALUES
('Apparel Collection'),
('Accessories Series'),
('Study Essentials'),
('Campus Lifestyle'),
('Commemorative Gifts'),
('Sports Gear');

-- 服装系列（category_id=1）
INSERT INTO products (name, description, price, category_id) VALUES
('PolyU Performance Polo Shirt', 'Moisture-wicking fabric with breathable mesh panels and embroidered crest', 228.00, 1),
('Campus Heritage Windbreaker', 'Water-resistant outerwear featuring retro campus map lining', 450.00, 1),
('Graduation Blazer Set', 'Tailored wool-blend blazer with commemorative buttons', 880.00, 1),
('Varsity Track Jacket', 'Lightweight nylon with contrast piping and 3D logo', 320.00, 1),
('Scholar''s Cardigan', 'Merino wool blend with hidden pocket for campus card', 280.00, 1),
('Campus Tour T-Shirt', 'Heather grey cotton tee with minimalist building outlines', 150.00, 1),
('Sports Day Shorts', 'Quick-dry fabric with moisture-wicking technology', 180.00, 1),
('Alumni Dinner Dress', 'Cocktail-length dress with subtle crest pattern', 650.00, 1),
('Lab Tech Coat', 'Functional white coat with anti-static finish and embroidered nameplate', 420.00, 1),
('Campus Crewneck Sweatshirt', 'French terry fabric with vintage-style screen print', 260.00, 1),
('Diploma Frame Scarf', 'Silk-cashmere blend with woven degree scroll design', 380.00, 1),
('Smart Lecture Pants', 'Wrinkle-resistant trousers with phone charging pocket', 350.00, 1);

-- 配饰系列（category_id=2）
INSERT INTO products (name, description, price, category_id) VALUES
('Campus Skyline Cufflinks', 'Sterling silver with laser-etched building profiles', 480.00, 2),
('Student ID Charm', 'Personalizable stainless steel tag for lanyards', 75.00, 2),
('Library Bookmark Set', 'Leather-tipped set with dewey decimal system guide', 95.00, 2),
('Graduation Stole Clips', 'Weighted clips with anti-slip silicone coating', 45.00, 2),
('Sports Day Medal Rack', 'Wall-mounted display with engraved motto', 220.00, 2),
('Smart Watch Band', 'Interchangeable bands with campus color options', 160.00, 2),
('Architectural Earrings', 'Miniature 3D-printed campus landmarks', 180.00, 2),
('Lab Safety Goggle Strap', 'Silicone strap with glow-in-the-dark logo', 30.00, 2),
('Campus Compass Keychain', 'Functional compass with laser-etched map', 120.00, 2),
('Thesis Defense Tie Bar', 'Adjustable silver bar with micro-engraved text', 110.00, 2),
('Alumni Lapel Pin Set', 'Annual collectible pins 2010-2023', 280.00, 2),
('Portable Art Portfolio', 'Water-resistant case with campus-inspired liner', 340.00, 2);

-- 学习用品（category_id=3）
INSERT INTO products (name, description, price, category_id) VALUES 
('Smart Lecture Notebook', 'Dot-grid pages with NFC-triggered digital backup', 95.00, 3),
('Ergo Scholar Pen', 'Weight-balanced brass barrel with ink level indicator', 220.00, 3),
('Lab Data Flask', 'Insulated tumbler with measurement markings', 80.00, 3),
('Campus Architecture Ruler', 'Stainless steel with engraved building dimensions', 45.00, 3),
('Thesis Writing Kit', 'Includes fountain pen, ink, and stress-relief tools', 180.00, 3),
('Portable Whiteboard', 'Foldable magnetic surface with campus map grid', 150.00, 3),
('Math Formula Stickers', 'Waterproof equation decals for laptops', 25.00, 3),
('Lecture Recording Headset', 'Noise-cancelling mic with lecture highlight button', 320.00, 3),
('Campus Wi-Fi Power Bank', '10000mAh with built-in network speed test', 130.00, 3),
('3D Molecule Model Kit', 'Snap-together organic chemistry components', 280.00, 3),
('Blue Light Glasses', 'Anti-glare with subtle crest pattern on temples', 90.00, 3),
('Portable Document Scanner', 'Pocket-sized with auto-crop technology', 450.00, 3);

-- 校园生活（category_id=4）
INSERT INTO products (name, description, price, category_id) VALUES
('Dorm Room Skyline Lamp', 'LED-lit acrylic campus architecture display', 220.00, 4),
('Campus Navigation Rug', 'Non-slip mat with color-coded building zones', 180.00, 4),
('Laundry Day Backpack', 'Waterproof compartment with RFID pocket', 250.00, 4),
('Microfiber Duvet Cover', 'Temperature-regulating fabric with building blueprints', 320.00, 4),
('Instant Breakfast Station', 'Compact kettle with meal reminder timer', 150.00, 4),
('Portable AC Unit', 'Personal cooling for dorm rooms', 680.00, 4),
('Noise-Masking Clock', 'Combines white noise and gentle wake-up light', 120.00, 4),
('Campus Bike Lock', 'GPS-enabled with automatic light system', 240.00, 4),
('Shower Caddy System', 'Modular compartments with quick-dry tech', 75.00, 4),
('Lecture Snack Pouch', 'Insulated compartment for 8-hour freshness', 45.00, 4),
('Dorm Door Whiteboard', 'Magnetic surface with calendar templates', 60.00, 4),
('Bedside Charge Hub', '6 USB ports with surge protection', 90.00, 4);

-- 纪念礼品（category_id=5）
INSERT INTO products (name, description, price, category_id) VALUES 
('Anniversary Crystal Globe', 'Hand-cut with internal campus model', 850.00, 5),
('Bronze Graduation Bell', 'Cast from original campus bell alloy', 420.00, 5),
('Time Capsule Kit', 'Archival-quality storage with decade calendar', 180.00, 5),
('Heritage Paperweight Set', 'Marble bases with historical seal impressions', 220.00, 5),
('Alumni Journey Map', 'Interactive touchscreen timeline display', 920.00, 5),
('Ceramic Faculty Dishes', 'Microwave-safe with department motifs', 75.00, 5),
('Memory Jar Kit', 'Includes 100 curated prompts and tokens', 120.00, 5),
('Silver Song Lyrics Plaque', 'Engraved school anthem in musical notation', 280.00, 5),
('Campus Tree Sapling', 'Grown from landmark tree cuttings', 150.00, 5),
('Digital Photo Frame', 'Cloud-connected with alumni event updates', 320.00, 5),
('Tradition Recipe Book', 'Staff/family contributed meals since 1937', 65.00, 5),
('Legacy Candles Set', 'Scented with campus garden botanicals', 95.00, 5);

-- 运动装备（category_id=6）
INSERT INTO products (name, description, price, category_id) VALUES
('Varsity Training Mask', 'Adjustable altitude simulation', 180.00, 6),
('Team Sports Bag', 'Compartmentalized with shoe ventilation', 240.00, 6),
('Compression Sleeve Set', 'Moisture-wicking with kinesiology taping', 95.00, 6),
('Smart Jump Rope', 'Tracks reps and calorie burn via app', 150.00, 6),
('Gym Floor Flip Flops', 'Antimicrobial with arch support', 45.00, 6),
('Recovery Massage Gun', '6-speed with campus color scheme', 380.00, 6),
('Hydration Vest', '2L bladder with emergency whistle', 280.00, 6),
('Grip Strength Trainer', 'Adjustable resistance up to 100kg', 75.00, 6),
('Sports Wristbands Set', 'Sweat-activated school motto display', 25.00, 6),
('Collapsible Water Bottle', '1L with built-time markers', 60.00, 6),
('Reflective Arm Sleeves', '360° visibility for night runs', 85.00, 6),
('Team Spirit Headphones', 'Wireless with crowd noise filter', 320.00, 6);