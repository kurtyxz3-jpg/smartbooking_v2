USE smartbooking_v2;

UPDATE branches
SET gcash_merchant_name = 'NBW Main Branch (DEMO ONLY)',
    gcash_number = '0917-XXX-0001',
    gcash_qr_path = 'uploads/payment-qr/demo-gcash-branch-1.svg'
WHERE id = 1;

UPDATE branches
SET gcash_merchant_name = 'NBW Branch 2 (DEMO ONLY)',
    gcash_number = '0917-XXX-0002',
    gcash_qr_path = 'uploads/payment-qr/demo-gcash-branch-2.svg'
WHERE id = 2;

UPDATE branches
SET gcash_merchant_name = 'NBW Branch 3 (DEMO ONLY)',
    gcash_number = '0917-XXX-0003',
    gcash_qr_path = 'uploads/payment-qr/demo-gcash-branch-3.svg'
WHERE id = 3;
