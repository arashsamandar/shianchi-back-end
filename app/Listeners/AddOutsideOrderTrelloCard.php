<?php

namespace App\Listeners;

use App\Events\OutsideOrderSubmitted;
use App\OutsideOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Trello\Client;
use Trello\Model\Card;

class AddOutsideOrderTrelloCard implements ShouldQueue

{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OutsideOrderSubmitted  $event
     * @return void
     */
    public function handle(OutsideOrderSubmitted $event)
    {
        $client = new Client();
        $client->authenticate('8d581336eb2100cb4b6cb3d9ec657143', '5e89a0c974950df3936d6cdf58d06f518b16b351acf3b211a0fd004e60bfd787', Client::AUTH_URL_CLIENT_ID);
        $card = new Card($client);
        $count = OutsideOrder::count();
        $addition = '0';
        $listId = '5c7662bb1a19840616b23da5';
        if($count % 2 == 1){
            $addition = '1';
        }
        $card
            ->setBoardId('593faea9869fcbdb20dc5273')
            ->setListId($listId)
            ->setName("سفارش خارج از سایت-".$addition."\nنام: ".$event->outsideOrder->name.
                "\nشماره تماس: ".$event->outsideOrder->phone_number."\nلینک کالا: ".$event->outsideOrder->link.
                "\nتوضیحات: ".$event->outsideOrder->description)
            ->save();
    }
}
