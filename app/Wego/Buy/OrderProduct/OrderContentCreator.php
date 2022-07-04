<?php
namespace Wego\Buy\OrderProduct;
use App\BuyerAddress;
use App\WegoCoin;
use Wego\Buy\BuyStorageUtil;
use Wego\Buy\FreeShipping;
use Wego\DeliveryTimeCalculator;
use Wego\Shipping\Company\AbstractShipping;
use Wego\Shipping\Price\ShippingPrice;


class OrderContentCreator
{
    protected $orderPrice = 0;
    protected $address;
    protected $result=[];
    protected $freeShippingUpperValue;
    protected $freeShippingConditionId;
    protected $freeShippingCityId;
    protected $checkFreeShipping;
    protected $currentAddressShippingCompany;
    protected $wegoCoinsToUpdate = [];
    protected $shippingDetail;

    public function create($requestedInformation,$userId){
        $indexedProductsDetail = $this->getProductsDetail($requestedInformation);
//        $userWegoCoins = $this->getUserWegoCoinsByStores($userId,$this->getStoresId($requestedInformation));
        $this->shippingDetail = $this->getShippingDetail($indexedProductsDetail,$requestedInformation);
        $orderContent = $this->createOrderProductsContent($indexedProductsDetail,$userWegoCoins);
        return $orderContent;
    }

    public function getOrderPrice(){
        $shippingPrice = !strcmp($this->shippingDetail['status'],ShippingPrice::FREE) ? 0 : $this->shippingDetail['price'];
        return $this->orderPrice + $shippingPrice;
    }

    private function createOrderProductsContent($indexedProductsDetail, $userWegoCoins){
        $result = [];
        foreach ($indexedProductsDetail as $product) {
            $product['wego_coin_use'] = $this->calculateWegoCoinUse($userWegoCoins[$product['store_id']],$product);
            $product['discount'] = $this->calculateSpecial($product,'discount');
            $product['price'] = $this->calculateProductPrice($product);
            $this->orderPrice += $product['price'];
            $result[$product['product_id']] = $this->makeOrderProductResult($product);
        }
        return $result;
    }

    ///TODO : long function
    private function makeOrderProductResult(&$product){
        return [
            "discount" => $product['discount'],
            "wego_coin_use"=>$product['wego_coin_use'],
            "wego_coin_get"=>$this->calculateSpecial($product,'wego_coin'),
            "quantity" => $product['quantity'],
            "store_id" => $product['store_id'],
            "price" => $product['price'],
            "product_id" => $product["product_id"],
            "buyer_address_id" => $product["address"],
            "shipping_id" => $product["shipping_company_id"],
            'product_persian_name' => $product['persian_name'],
            'product_english_name' => $product['english_name'],
            'product_price' => $product['current_price'],
            'store_english_name' => $product['store']['english_name'],
            'store_persian_name' => $product['store']['user']['name'],
            'store_address' => $product['store']['address'],
            'store_province_id' => $product['store']['province_id'],
            'store_city_id' => $product['store']['city_id'],
            'store_bazaar' => $product['store']['bazaar'],
            'manager_mobile' => $this->getManagerMobile($product['store']['manager_mobiles']),
            'manager_first_name' => $product['store']['manager_first_name'],
            'manager_last_name' => $product['store']['manager_last_name'],
            'phone_number' => $product['store']['store_phones'][0]['phone_number'],
            'prefix_phone_number' => $product['store']['store_phones'][0]['prefix_phone_number'],
            "delivery_time" => $this->getDeliveryTime($product['delivery_time']),
            "delivery_date" => $this->getDeliveryDate($product['delivery_time']),
            "gift_count" => $this->calculateSpecial($product,'gift'),
            "gift"=> $this->getGiftText($product['special_conditions']),
            "gift_amount" => $this->getGiftAmount($product['special_conditions']),
            'city_id' => $this->shippingDetail['address']['city_id'],
            'province_id' => $this->shippingDetail['address']['province_id'],
            'address' => $this->shippingDetail['address']['address'],
            'receiver_prefix_phone_number' => $this->shippingDetail['address']['prefix_phone_number'],
            'receiver_phone' => $this->shippingDetail['address']['phone'],
            'receiver_mobile' => $this->shippingDetail['address']['mobile'],
            'receiver_prefix_mobile_number' => $this->shippingDetail['address']['prefix_mobile_number'],
            'receiver_postal_code' => $this->shippingDetail['address']['postal_code'],
            'receiver_first_name' => $this->shippingDetail['address']['receiver_first_name'],
            'receiver_last_name' => $this->shippingDetail['address']['receiver_last_name'],
            'shipping_price' => $this->shippingDetail['price'],
            'shipping_status' => $this->shippingDetail['status'],
            'shipping_company' => $this->shippingDetail['company'],
            "wego_coins_to_update" => $this->wegoCoinsToUpdate,
            "payment_id" => (int)$product["payment_id"],
            'category_unit' => $product['category']['unit'],
            'specifications' => count($product['specifications'])? $product['specifications'] : [],
            "color_name"=>$this->getColorNameById($product['colors'],$product['color']),
            "color_id"=> $product['color'],
        ];
    }

    private function getColorNameById($colors,$colorId){
        foreach ($colors as $color) {
            if(!strcmp($color['id'],$colorId)){
                return $color['persian_name'];
            }
        }
        return null;
    }

    private function getManagerMobile($mobiles){
        if(count($mobiles) != 0 )
            return $mobiles[0];
        return null;
    }

    private function getShippingDetail($indexedProductsDetail,$requestedInformation){
        $totalProductsWeight = 0;
        $requestedProductsByStoreId = $this->groupRequestedProductsByStoreId($indexedProductsDetail);
        $totalPrice = 0;
        foreach ($requestedProductsByStoreId as $storeOrderedProducts) {
            $storeProductsWeight = 0;
            foreach ($storeOrderedProducts as $product) {
                $totalPrice += ($product['current_price'] * $product['quantity']);
                $storeProductsWeight += ($product['weight'] * $product['quantity']);
            }
            $totalProductsWeight += $storeProductsWeight;
        }
        $isFreeShipping = $this->checkFreeShippingOccur($totalPrice);
        $address = $this->getAddress($requestedInformation[0]['address']);
        $this->shippingDetail = $this->calculateShippingPrice($isFreeShipping,$totalProductsWeight,$address); //bug put first element but search and get requested company
        $this->findRelatedShippingCompany($requestedInformation);
        $returnArray = [
            'price' => $this->shippingDetail['price'],
            'status' => $this->shippingDetail['status'],
            'company' => $this->shippingDetail['company'],
            'address' => $address,
        ];
        if (array_key_exists('shipping_time',$this->shippingDetail)){
            $returnArray['shipping_time'] = $this->shippingDetail['shipping_time'];
        }
        return $returnArray;
    }

    private function calculateShippingPrice($freeShippingOccur,$totalWeight,$address)
    {
//        $shippingPrice = new ShippingHandler(new AddressRepositoryEloquent(app()));
//        $result = $shippingPrice
//            ->setDestinationAddress($address)
//            ->setFreeShipping($freeShippingOccur)
//            ->setTotalWeight($totalWeight)
//            ->generateShippingCompany()
//            ->getShippingInformation();
        return $result;
    }

    //TODO :function elasticsearch fek konam in baiad bere tooie BuyStorageUtil
    private function getProductsDetail($requestedInformation){
        $productsDetail = BuyStorageUtil::getRequestedProductsDetail(array_column($requestedInformation,'product_id'));

        $indexedProductsDetail = $this->indexProductById($productsDetail['hits']['hits']);
        foreach($requestedInformation as $requestedProduct){
            $productId = $requestedProduct['product_id'];
            $indexedProductsDetail[$productId]['_source'] += $requestedProduct;
            $indexedProductsDetail[$productId]['_source']['specifications'] = $this->addProductSpecifications($indexedProductsDetail[$productId]['_source'],$requestedProduct['specifications']);
            unset($indexedProductsDetail[$productId]['_source']['values']);
        }
        $indexedProductsDetail = array_column($indexedProductsDetail,'_source');
        return $indexedProductsDetail;
    }

    private function addProductSpecifications($productDetail,$requestedSpecifications){

        $specifications = [];
        $values = $productDetail['values'];
        foreach($requestedSpecifications as $valueId){
            $specifications[] = $this->getSpecificationAndValueDetail($values,$valueId);
        }
        return $specifications;
    }

    private function getSpecificationAndValueDetail($values,$valueId){
        foreach ($values as $value) {
            if(!strcmp($value['id'],$valueId))
                return [
                    'name' => $value['specification']['name'],
                    'value' => $value['name'],
                    'specification_id' => $value['specification_id'],
                    'value_id' => $valueId,
                ];
        }
    }

    //TODO : ie eloquent inja in fek konam baiad bere tooie BuyStorageUtil
    private function getAddress($id)
    {
        $buyerAddress = BuyerAddress::find($id);
        $result = [
            'buyer_address_id' => $buyerAddress->id,
            'city_id' => $buyerAddress->city_id,
            'province_id'=>$buyerAddress->province_id,
            'address' => $buyerAddress->address,
            'prefix_phone_number' => $buyerAddress->prefix_phone_number,
            'phone' => $buyerAddress->phone_number,
            'postal_code' => $buyerAddress->postal_code,
            'mobile' => $buyerAddress->mobile_number,
            'prefix_mobile_number' => $buyerAddress->prefix_mobile_number,
            'receiver_first_name' => $buyerAddress->receiver_first_name,
            'receiver_last_name' => $buyerAddress->receiver_last_name,
        ];
        return $result;
    }

    //TODO ie function eloquent in fek konam baiad bere tooie BuyStorageUtil;
    private function getUserWegoCoinsByStores($userId,$storesId)
    {
        $coin = WegoCoin::where('user_id','=',$userId)
            ->where('status','=','a')
            ->where('amount','!=',0)
            ->whereIn('store_id',$storesId)
            ->orderBy('expiration','asc')
            ->get()
            ->groupBy('store_id')
            ->toArray();
        foreach($storesId as $storeId){
            if(!array_key_exists($storeId,$coin)){
                $coin[$storeId] = [];
            }
        }
        return $coin;
    }

    private function calculateWegoCoinUse(&$wegoCoins,$product){
        $this->wegoCoinsToUpdate = [];
        $quantity = $product['quantity'];
        $productWegoCoinsToUpdate = WegoCoinHandler::calculateWegoCoinUse($wegoCoins,$product['wego_coin_need'],$quantity,$product['current_price']);
        $productWegoCoins = array_except($productWegoCoinsToUpdate,'total_amount');
        foreach ($productWegoCoins as &$wegoCoin) {
            $wegoCoin['product_id'] = $product['id'];
        }
        $this->wegoCoinsToUpdate = array_merge($this->wegoCoinsToUpdate,$productWegoCoins);
        return $productWegoCoinsToUpdate['total_amount'];
    }

    private function calculateSpecial($product,$specialType){
        $quantity = $product['quantity'];
        $specialDetail = $this->getProductSpecialDetail($specialType,$product);
        if($specialDetail == null)
            return 0;
        $specialDetail['quantity'] = $quantity;
        $specialDetail['price'] = $product['current_price'];
        $specialHandler = SpecialFactory::getSpecial($specialDetail,$specialType);
        return $specialHandler->calculate();
    }

    private function getIndex($searchFor,$searchElement,$column)
    {
        return array_search($searchFor,array_column($searchElement,$column));
    }

    // TODO: correct input of FreeShipping class
    private function checkFreeShippingOccur($totalPrice)
    {
        //TODO in be zudi avaz mishe;
        return $totalPrice >= 100000;
        $freeShipping = new FreeShipping();
        $isFreeShipping = $freeShipping->setConditionId($this->freeShippingConditionId)
            ->setRequestedProductPrice($totalPrice)
            ->setFreeShippingUpperValue($this->freeShippingUpperValue)
            ->setUserAddress($this->address)
            ->setFreeShippingCityId($this->freeShippingCityId)
            ->occur();
        return $isFreeShipping;
    }

    private function getProductSpecialDetail($type,$product){
        if ($this->getIndex($type,$product["special_conditions"],'type') === false){
            return null;
        }
        return $product["special_conditions"][$this->getIndex($type,$product["special_conditions"],'type')];
    }

    private function getGiftText($specialConditions){
        $index = $this->getIndex('gift',$specialConditions,'type');
        return ($index === false)?'ندارد':$specialConditions[$index]['text'];
    }

    private function getGiftAmount($specialConditions){
        $index = $this->getIndex('gift',$specialConditions,'type');
        return ($index === false) ? 0:$specialConditions[$index]['amount'];
    }

    private function calculateProductPrice($product){
        $discount = $product['discount'];
        $wegoCoinUsed = $product['wego_coin_use'];
        $quantity = $product['quantity'];
        $price = $product['current_price'];
        return ($quantity * $price) - ($wegoCoinUsed * WegoCoin::VALUE_TOMAN) - $discount;
    }

    private function indexProductById($productDetail){
        return array_combine((array_column(array_column($productDetail,'_source'),'id')),$productDetail);
    }

    private function groupRequestedProductsByStoreId($requestedProducts){
        return (collect($requestedProducts)->groupBy('store_id')->toArray());
    }

    private function getStoresId($request = []){
        return (array_unique(array_column(array_except($request,'_token'),'store_id')));
    }

    private function getDeliveryDate($deliveryDateAndTime){
        if(empty($deliveryDateAndTime) || $this->shippingDetail['company'] == AbstractShipping::WEGO_JET){
            return $this->shippingDetail['shipping_time'];
        }
        list($date,$time) = $this->getDateAndTime($deliveryDateAndTime);
        return $date;
    }

    private function getDeliveryTime($deliveryDateAndTime){
        if (empty($deliveryDateAndTime) || $this->shippingDetail['company'] == AbstractShipping::WEGO_JET){
            return 0;
        }
        list($date,$time) = $this->getDateAndTime($deliveryDateAndTime);
        return $time;
    }

    private function getDateAndTime($deliveryDateAndTime){
        if (empty($deliveryDateAndTime)){
            return 0;
        }
        return explode(DeliveryTimeCalculator::DATE_TIME_DELIMITER ,$deliveryDateAndTime);
    }

    private function findRelatedShippingCompany($requestedInformation)
    {
        foreach ($this->shippingDetail as $shipping){
            if ($shipping['shipping_id'] == $requestedInformation[0]['shipping_company_id']){
                $this->shippingDetail =$shipping;
            }
        }
    }
}