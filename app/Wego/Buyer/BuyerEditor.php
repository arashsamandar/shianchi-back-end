<?php
namespace Wego\Buyer;

use App\Buyer;
use App\User;
use Carbon\Carbon;
use Wego\Helpers\PersianUtil;
use Wego\PictureHandler;
use Wego\ShamsiCalender\Shamsi;

/**
 * Created by PhpStorm.
 * User: wb-2
 * Date: 6/21/16
 * Time: 4:50 PM
 */
class BuyerEditor
{
    protected $buyer;
    const GENERAL_UPDATE_ATTRIBUTE = "generalUpdateAttribute";
    const UPDATE_GENDER = "checkAndUpdateGender";
    const UPDATE_BIRTHDAY = 'checkAndUpdateBirthday';
    const UPDATE_PICTURE = "checkAndUpdatePicture";
    const UPDATE_FIRST_NAME = 'checkAndUpdateFirstName';
    protected $buyerAttributeFunctionMap = [
        "mobile_number" => self::GENERAL_UPDATE_ATTRIBUTE,
        "landline_number" => self::GENERAL_UPDATE_ATTRIBUTE,
        "address" => self::GENERAL_UPDATE_ATTRIBUTE,
        "card_number" => self::GENERAL_UPDATE_ATTRIBUTE,
        "card_owner_name" => self::GENERAL_UPDATE_ATTRIBUTE,
        "last_name" => self::GENERAL_UPDATE_ATTRIBUTE,
        "image_path" => self::UPDATE_PICTURE,
        "gender" => self::UPDATE_GENDER,
        "company_name" => self::GENERAL_UPDATE_ATTRIBUTE,
        "magazine_subscriber" => self::GENERAL_UPDATE_ATTRIBUTE,
        "job_title" => self::GENERAL_UPDATE_ATTRIBUTE,
        "national_code" => self::GENERAL_UPDATE_ATTRIBUTE,
        "birthday" => self::UPDATE_BIRTHDAY,
        "name" => self::UPDATE_FIRST_NAME
    ];

    public function getJsonInfo(User $user)
    {
        $buyer = Buyer::find($user->userable_id);
        return $this->convertToJson($user, $buyer);
    }

    public function update($updatedInfo, $buyerId)
    {
        $this->updateBuyerPart($updatedInfo, $buyerId);
        return true;
    }

    /**
     * @param mixed $buyer
     * @return $this
     */
    public function setBuyer(Buyer $buyer)
    {
        $this->buyer = $buyer;
        return $this;
    }

    private function convertToJson(User $user, Buyer $buyer)
    {
        $info = $buyer->toArray();
        unset($info['id']);
        $info['email'] = $user->email;
        $info['name'] = $user->name;
        if (!is_null($info['birthday']))
            $info['birthday'] = Shamsi::convert(Carbon::createFromFormat('Y-m-d H:i:s', $info['birthday'], 'Asia/Tehran'));
        return $info;
    }

    private function updateBuyerPart($updatedInfo, $buyerId)
    {
        $buyer = Buyer::find($buyerId);
        foreach ($this->buyerAttributeFunctionMap as $attrName => $function) {
            if (array_key_exists($attrName, $updatedInfo)) {
                if (strcmp($buyer->{$attrName}, $updatedInfo[$attrName]))
                    $this->$function($buyer, $attrName, $updatedInfo[$attrName]);
            }
        }
        $buyer->save();
    }

    private function generalUpdateAttribute($buyer, $attrName, $newAttr)
    {
        if($attrName == 'mobile_number'){
            $newAttr = PersianUtil::to_english_num($newAttr);
        }
        $buyer->{$attrName} = $newAttr;
    }

    private function checkAndUpdatePicture($buyer, $attrName, $newAttr)
    {
        $newPic[] = ['path' => $newAttr];
        (new PictureHandler())->storePicture($newPic, $buyer, $buyer->user);
    }

    private function checkAndUpdateGender($buyer, $attrName, $newAttr)
    {
        if (!strcmp($newAttr, 'male') || !strcmp($newAttr, 'female')) {
            $this->generalUpdateAttribute($buyer, $attrName, $newAttr);
        }
    }

    private function checkAndUpdateBirthday($buyer, $attrName, $newAttr)
    {
        $newAttr = PersianUtil::to_english_num($newAttr);
        $carbon = Carbon::parse($newAttr)->setTime(0, 0, 0);
        $shamsiDate = str_replace("-", "/", $carbon->toDateString());
        $georgianDate = Shamsi::convertToGeorgian($shamsiDate);
        $carbon = Carbon::parse($georgianDate)->setTime(0, 0, 0);
        $birthday = $carbon->toDateTimeString();
        $this->generalUpdateAttribute($buyer, $attrName, $birthday);

    }

    private function checkAndUpdateFirstName($buyer, $attrName, $newAttr)
    {
        if (strcmp($buyer->user->name, $newAttr)) {
            $buyer->user->update(['name' => $newAttr]);
        }
    }
}