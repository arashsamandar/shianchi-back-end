<?php
/**
 * Created by PhpStorm.
 * User: wb-3
 * Date: 9/26/17
 * Time: 11:43 AM
 */

namespace Wego\Shipping;


use App\BuyerAddress;
use Wego\Shipping\Company\Post;
use Wego\Shipping\Company\Tipax;
use Wego\Shipping\Company\Wegobazaar;
use Wego\Shipping\Company\WegoJet;

class Shipping
{

    protected $address;
    protected $quantities;

    public function get()
    {
        $requestedProduct = new RequestedProducts($this->quantities);
        $totalPrice = $requestedProduct->getTotalPrice();
        $totalWeight = $requestedProduct->getTotalWeight();

        $companies = collect([(new Post()), (new WegoJet()), (new Wegobazaar())]);

        return $companies->map(function ($company) use ($totalPrice, $totalWeight) {
            return
                collect(
                    $company
                        ->setAddress($this->address)
                        ->setTotalProductsPrice($totalPrice)
                        ->setTotalWeight($totalWeight)
                        ->get()
                );
        })->reject(function ($item) {
            return $item->isEmpty();
        })->values();

    }

    public function setAddress(BuyerAddress $address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @param mixed $quantities
     * @return Shipping
     */
    public function setQuantities($quantities)
    {
        $this->quantities = $quantities;
        return $this;
    }

}