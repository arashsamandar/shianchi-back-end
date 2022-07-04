<?php

namespace Wego\Commands;

use App\Http\Controllers\TelegramController;
use App\Store;
use App\TelbotStatus;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class StartCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "start";

    /**
     * @var string Command Description
     */
    protected $description = "Start Command to get you started";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
//        $keyboard = [
//            ['7', '8', '9'],
//            ['4', '5', '6'],
//            ['1', '2', '3'],
//                ['0']
//        ];
        $keyboard = [['تغییر موجودی کالا'],['تغییر قیمت کالا']];

        $reply_markup = Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);



        $this->replyWithMessage(['text' => 'سلام به ویگوبازار خوش آمدید','reply_markup' => $reply_markup]);


        //$commands = $this->getTelegram()->getCommands();

//        $response = '';
//        foreach ($commands as $name => $command) {
//            $response .= sprintf('/%s - %s' . PHP_EOL, $name, $command->getDescription());
//        }
//
//        // Reply with the commands list
//        $this->replyWithMessage(['text' => $response]);
        $chatid= $this->getUpdate()->getMessage()['chat']['id'];

        $status = TelbotStatus::find($chatid);
        $status->status = 1;
        $status->save();

        //$text= $this->getUpdate()->getMessage()->getText();

        //$this->getArguments();

        //$this->replyWithChatAction(['action' => Actions::TYPING]);

        // $reply_markup = Telegram::replyKeyboardHide();






    }
}