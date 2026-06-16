<?php

namespace App\Support;

class CatalogData
{
    /**
     * Catalogue officiel C7Pourt3 — synchronisé avec github.com/Premier-Bouedi/C7Pourt3
     *
     * @return array<int, array{name: string, category: string, price_xaf: int, image: string, stock: int}>
     */
    public static function products(): array
    {
        return [
            ['Sac Croco Noir — Chaîne Dorée', 'Soirée', 89000, 'sac-01.png', 5],
            ['Sac Dôme Texturé Noir', 'Quotidien', 72000, 'sac-02.png', 4],
            ['Sac Matelassé Métallisé', 'Soirée', 95000, 'sac-03.png', 3],
            ['Sac Croco Noir — Fermoir Argent', 'Soirée', 92000, 'sac-04.png', 6],
            ['Sac Satchel Bleu Royal', 'Quotidien', 68000, 'sac-05.png', 8],
            ['Sac Fourrure Crème — Bandoulière', 'Luxe', 125000, 'sac-06.png', 4],
            ['Sac Tote Texturé Marron', 'Quotidien', 58000, 'sac-07.png', 7],
            ['Sac Bandoulière Monogramme', 'Bandoulière', 110000, 'sac-08.png', 5],
            ['Sac Speedy Monogramme Classique', 'Luxe', 115000, 'sac-09.png', 3],
            ['Sac Crossbody Monogramme Noir', 'Bandoulière', 98000, 'sac-10.png', 4],
        ];
    }

    public static function imagePath(string $filename): string
    {
        return '/images/products/'.$filename;
    }

    public static function priceMad(int $priceXaf): int
    {
        return (int) round($priceXaf / 60);
    }
}
