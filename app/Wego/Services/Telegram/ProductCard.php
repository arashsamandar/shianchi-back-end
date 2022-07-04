<?php
/**
 * Created by PhpStorm.
 * User: hoseinz3
 * Date: 9/9/2017 AD
 * Time: 18:03
 */

namespace App\Wego\Services\Telegram;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;

class ProductCard
{
    private $telegram, $caption, $chat_id, $photoPath;
    private $buttonText;
    private $buttonUrl;

    function __construct()
    {
        $this->telegram = new Telegram('387227715:AAH_lrY5_H9l9omXiPCiXYU1cTiPw5kANQU', 'Wegobazaar');
    }

    public function generate()
    {
        Request::sendPhoto(
            [
                'chat_id' => $this->chat_id, 'caption' => $this->caption,
                'photo' => Request::encodeFile(public_path($this->photoPath)),
                'reply_markup' => $this->generateInlineKeyboard()
            ]
        );
    }

    private function generateInlineKeyboard()
    {
        return new InlineKeyboard(['inline_keyboard' => [$this->getInlineKeyboardButton()]]);
    }

    private function getInlineKeyboardButton()
    {
        return
            [
                new InlineKeyboardButton(
                    [
                        'text' => $this->buttonText,
                        'url' => $this->buttonUrl
                    ]
                )
            ];
    }


    /**
     * @param mixed $caption
     * @return ProductCard
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;
        return $this;
    }
    /**
     * @param mixed $chat_id
     * @return ProductCard
     */
    public function setChatId($chat_id)
    {
        $this->chat_id = $chat_id;
        return $this;
    }
    /**
     * @param mixed $photoPath
     * @return ProductCard
     */
    public function setPhotoPath($photoPath)
    {
        $this->photoPath = $photoPath;
        return $this;
    }
    /**
     * @param mixed $buttonText
     * @return ProductCard
     */
    public function setButtonText($buttonText)
    {
        $this->buttonText = $buttonText;
        return $this;
    }
    /**
     * @param mixed $buttonUrl
     * @return ProductCard
     */
    public function setButtonUrl($buttonUrl)
    {
        $this->buttonUrl = $buttonUrl;
        return $this;
    }



}