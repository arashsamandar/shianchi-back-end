<?php

namespace Wego\Commands;

use App\Http\Controllers\TelegramController;
use App\Store;
use App\TelbotStatus;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class AlakiCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "alaki";

    /**
     * @var string Command Description
     */
    protected $description = "alaki Command to get you started";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {

        $this->replyWithMessage(['text' => 'به ویگوبازار خوش آمدید']);
        $status = $this->getUpdate()->getStatus();

        $this->replyWithMessage(['text'=>$status]);
        $chatid= $this->getUpdate()->getMessage()['chat']['id'];
        $status = TelbotStatus::find($chatid);
        $status->status = 2;
        $status->save();


    }
}