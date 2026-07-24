USE smartbooking_v2;

-- Replace scanner-watermarked service-card images with clean temporary placeholders.

UPDATE services
SET image = 'assets/images/services/temporary-placeholders/ombre-brows.webp'
WHERE name = 'Ombre Brows - First Session';

UPDATE services
SET image = 'assets/images/services/temporary-placeholders/nano-combo-brows.webp'
WHERE name = 'Nano Combo Brows - First Session';

UPDATE services
SET image = 'assets/images/services/temporary-placeholders/nano-machine-strokes.webp'
WHERE name = 'Nano Machine Strokes - First Session';

UPDATE services
SET image = 'assets/images/services/temporary-placeholders/menstrokes.webp'
WHERE name = 'Menstrokes - First Session';

UPDATE services
SET image = 'assets/images/services/temporary-placeholders/eyeliner-tattoo.webp'
WHERE name = 'Classic/Winged Eyeliner Tattoo - First Session';

UPDATE services
SET image = 'assets/images/services/temporary-placeholders/brow-reconstruction.webp'
WHERE name = 'Brow Reconstruction';
