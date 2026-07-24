<?php
declare(strict_types=1);

/**
 * Central SmartBooking branding paths.
 *
 * The selected official logo is the black-background NBW mark with:
 * - gold border
 * - gold swoosh
 * - white-and-gold NBW lettering
 */
function smartBookingBrandLogo(): string
{
    return '/smartbooking_v2/assets/images/branding/'
        . 'nbw-logo-official-black.webp';
}

function smartBookingBrandLogoFallback(): string
{
    return '/smartbooking_v2/assets/images/branding/'
        . 'nbw-logo-official-black.png';
}

function smartBookingSidebarLogo(): string
{
    return '/smartbooking_v2/assets/images/branding/'
        . 'nbw-logo-official-sidebar.webp';
}

function smartBookingBrandFavicon(): string
{
    return '/smartbooking_v2/assets/images/branding/nbw-favicon.png';
}
