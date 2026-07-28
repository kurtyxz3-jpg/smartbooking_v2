USE smartbooking_v2;

-- Run 073_inventory_management.sql before this sample-data file.
-- This seed is idempotent: importing it again will not duplicate products.

INSERT INTO suppliers
    (branch_id, supplier_name, contact_person, phone, email, address, status)
SELECT
    b.id,
    CONCAT('NBW Central Supply - ', b.branch_name),
    'Sample Supply Coordinator',
    '0917-000-0000',
    CONCAT(LOWER(REPLACE(b.branch_code, '-', '')), '@nbw.local'),
    b.address,
    'active'
FROM branches b
WHERE b.status = 'active'
  AND NOT EXISTS (
      SELECT 1
      FROM suppliers s
      WHERE s.branch_id = b.id
        AND s.supplier_name = CONCAT('NBW Central Supply - ', b.branch_name)
  );

INSERT INTO inventory
    (
        branch_id, category_id, supplier_id, item_name, sku, barcode,
        quantity, reorder_level, unit, cost_price, notes, status
    )
SELECT
    b.id,
    c.id,
    s.id,
    sample.item_name,
    CONCAT(b.branch_code, '-', sample.sku_suffix),
    CONCAT('880', LPAD(b.id, 3, '0'), sample.barcode_suffix),
    CASE b.display_order
        WHEN 1 THEN sample.main_quantity
        WHEN 2 THEN sample.branch2_quantity
        ELSE sample.branch3_quantity
    END,
    sample.reorder_level,
    sample.unit,
    sample.cost_price,
    'Sample stock record for demonstration and testing.',
    CASE
        WHEN (
            CASE b.display_order
                WHEN 1 THEN sample.main_quantity
                WHEN 2 THEN sample.branch2_quantity
                ELSE sample.branch3_quantity
            END
        ) <= 0 THEN 'out_of_stock'
        WHEN (
            CASE b.display_order
                WHEN 1 THEN sample.main_quantity
                WHEN 2 THEN sample.branch2_quantity
                ELSE sample.branch3_quantity
            END
        ) <= sample.reorder_level THEN 'low_stock'
        ELSE 'in_stock'
    END
FROM branches b
INNER JOIN suppliers s
    ON s.branch_id = b.id
   AND s.supplier_name = CONCAT('NBW Central Supply - ', b.branch_name)
INNER JOIN (
    SELECT 'Base Coat 15ml' item_name, 'NAIL-BASE-15' sku_suffix,
           '001001' barcode_suffix, 'Nail Supplies' category_name,
           24 main_quantity, 16 branch2_quantity, 10 branch3_quantity,
           8 reorder_level, 'bottle' unit, 145.00 cost_price
    UNION ALL SELECT 'Top Coat 15ml', 'NAIL-TOP-15', '001002',
           'Nail Supplies', 22, 14, 7, 8, 'bottle', 155.00
    UNION ALL SELECT 'Gel Polish - Assorted Colors', 'NAIL-GEL-ASST', '001003',
           'Nail Supplies', 48, 34, 20, 15, 'bottle', 185.00
    UNION ALL SELECT 'Soft Gel Nail Tips', 'NAIL-SOFT-TIP', '001004',
           'Nail Supplies', 12, 8, 4, 5, 'box', 480.00
    UNION ALL SELECT 'Nail Art Rhinestones', 'NAIL-RHINE', '001005',
           'Nail Supplies', 18, 11, 5, 6, 'pack', 95.00
    UNION ALL SELECT 'Acetone 1 Liter', 'NAIL-ACETONE-1L', '001006',
           'Nail Supplies', 10, 6, 3, 4, 'bottle', 210.00
    UNION ALL SELECT 'Foot Scrub 500g', 'SPA-FOOT-SCRUB', '002001',
           'Spa and Massage', 14, 9, 4, 5, 'jar', 260.00
    UNION ALL SELECT 'Massage Oil 1 Liter', 'SPA-OIL-1L', '002002',
           'Spa and Massage', 9, 5, 2, 4, 'bottle', 320.00
    UNION ALL SELECT 'Disposable Face Towels', 'SPA-FACE-TOWEL', '002003',
           'Spa and Massage', 30, 20, 12, 10, 'pack', 175.00
    UNION ALL SELECT 'Hair Treatment Mask 1kg', 'HAIR-MASK-1K', '003001',
           'Hair Care', 8, 5, 2, 3, 'tub', 540.00
    UNION ALL SELECT 'Facial Cleanser 500ml', 'SKIN-CLEAN-500', '004001',
           'Skin Care', 11, 6, 3, 4, 'bottle', 295.00
    UNION ALL SELECT 'Nitrile Gloves', 'SAN-GLOVES', '005001',
           'Sanitation', 20, 12, 6, 8, 'box', 285.00
    UNION ALL SELECT '70% Isopropyl Alcohol 1L', 'SAN-ALCOHOL-1L', '005002',
           'Sanitation', 16, 9, 4, 5, 'bottle', 125.00
    UNION ALL SELECT 'Disposable Face Masks', 'SAN-MASK', '005003',
           'Sanitation', 15, 8, 0, 5, 'box', 130.00
) sample
INNER JOIN inventory_categories c
    ON c.category_name = sample.category_name
WHERE b.status = 'active'
  AND NOT EXISTS (
      SELECT 1
      FROM inventory existing_product
      WHERE existing_product.branch_id = b.id
        AND existing_product.sku = CONCAT(b.branch_code, '-', sample.sku_suffix)
        AND existing_product.archived_at IS NULL
  );

-- Record the sample opening quantities in movement history.
INSERT INTO inventory_movements
    (
        inventory_id, branch_id, movement_type, quantity,
        quantity_before, quantity_after, note, created_by
    )
SELECT
    i.id,
    i.branch_id,
    'in',
    i.quantity,
    0,
    i.quantity,
    'Sample opening stock',
    NULL
FROM inventory i
WHERE i.notes = 'Sample stock record for demonstration and testing.'
  AND i.quantity > 0
  AND NOT EXISTS (
      SELECT 1
      FROM inventory_movements m
      WHERE m.inventory_id = i.id
        AND m.note = 'Sample opening stock'
  );

-- Summary that phpMyAdmin will display after a successful import.
SELECT
    b.branch_name,
    COUNT(i.id) AS sample_products,
    SUM(i.status = 'in_stock') AS in_stock,
    SUM(i.status = 'low_stock') AS low_stock,
    SUM(i.status = 'out_of_stock') AS out_of_stock,
    ROUND(SUM(i.quantity * i.cost_price), 2) AS estimated_stock_value
FROM branches b
LEFT JOIN inventory i
    ON i.branch_id = b.id
   AND i.notes = 'Sample stock record for demonstration and testing.'
   AND i.archived_at IS NULL
WHERE b.status = 'active'
GROUP BY b.id, b.branch_name
ORDER BY b.display_order, b.branch_name;
