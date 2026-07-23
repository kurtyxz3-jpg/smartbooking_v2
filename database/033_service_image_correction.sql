USE smartbooking_v2;

-- Replace text-heavy pricelist screenshots with real procedure photos.

UPDATE services
SET image = 'assets/images/services/semi-permanent/ombre-brows.webp'
WHERE name = 'Ombre Brows - First Session';

UPDATE services
SET image = 'assets/images/services/semi-permanent/nano-combo-brows.webp'
WHERE name = 'Nano Combo Brows - First Session';

UPDATE services
SET image = 'assets/images/services/semi-permanent/nano-machine-strokes.webp'
WHERE name = 'Nano Machine Strokes - First Session';

UPDATE services
SET image = 'assets/images/services/semi-permanent/menstrokes.webp'
WHERE name = 'Menstrokes - First Session';

UPDATE services
SET image = 'assets/images/services/semi-permanent/lip-micropigmentation.webp'
WHERE name = 'Lip Micropigmentation - First Session';

UPDATE services
SET image = 'assets/images/services/semi-permanent/eyeliner-tattoo.webp'
WHERE name = 'Classic/Winged Eyeliner Tattoo - First Session';

UPDATE services
SET image = 'assets/images/services/semi-permanent/brow-reconstruction.webp'
WHERE name = 'Brow Reconstruction';

-- Verification query
SELECT name, image
FROM services
WHERE name IN (
    'Ombre Brows - First Session',
    'Nano Combo Brows - First Session',
    'Nano Machine Strokes - First Session',
    'Menstrokes - First Session',
    'Lip Micropigmentation - First Session',
    'Classic/Winged Eyeliner Tattoo - First Session',
    'Brow Reconstruction'
);