<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 07/09/17
 * Time: 15:22
 */

namespace Wego\Helpers;


use App\Category;

class TopCategories
{
    private $categories =
        [
            'lamp', 'decorative-light', 'television', 'game-console-accessories', 'microwave',
            'toaster', 'electric-pots', 'refrigerator-and-freezer', 'juicer', 'dishwasher',
            'cordless-vacuum-cleaner', 'vacuum-cleaner', 'fan', 'cooler',
            'air-purifiers', 'Iron-and-ironing-board', 'novel-books',
            'intellectual-games', 'educational-toys', 'doll', 'creatable-and-modeling',
            'experimental-toys', 'phones', 'fax', 'mobile-tablet-accessories-charger',
            'mobile-tablet-accessories-cable', 'mobile-tablet-accessories-headphone',
            'mobile-tablet-accessories-speaker', 'mobile-tablet-accessories-powerbank',
            'main-mobile-phones'
        ];

    public function getRandomName()
    {
        $categoryName = $this->categories[array_rand($this->categories)];
        return Category::where('name', $categoryName)->first();
    }

}