<?php

namespace App\Http\Controllers;

use App\CourseOrder;
use App\Http\Requests\StoreCourseRequest;
use App\Participant;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Log;
use Wego\Buy\OnlinePayment\AbstractBank;
use Wego\Buy\OnlinePayment\BankFactory;
use Wego\Buy\ServiceType\CourseTransaction;

class CourseController extends ApiController
{
    const COURSE_PRICE = 100000;

    protected $participant;
    protected $courseIds;
    protected $courseOrder;

    public function handle(StoreCourseRequest $request)
    {
        $this->setParticipant($request->participant);
        $this->setCourseIds($request->courses);
        $this->addParticipantCourse();
        $this->addOrder();
        $url = '/'. CourseTransaction::paymentUrl($this->courseOrder->id,$this->participant->id);
        return $this->respondOk($url,'path');
    }

    public function setParticipant($participant)
    {
        try{
            $this->participant = Participant::create($participant);
        }catch (QueryException $e){
            $this->participant = Participant::where(['email'=>$participant['email']])->firstOrFail();
            $this->participant->email = $participant['email'];
            $this->participant->name= $participant['name'];
            $this->participant->type = $participant['type'];
            $this->participant->phone_number = $participant['phone_number'];
            $this->participant->save();
        }
    }

    public function setCourseIds($courseIds)
    {
        $this->courseIds = $courseIds;
    }

    public function addParticipantCourse()
    {
        $this->participant->courses()->sync($this->courseIds);
    }

    public function addOrder()
    {
        $courseOrder = [
            'total_price'=>$this->getTotalPrice(),
            'participant_id'=>$this->participant->id,
            'total_discount'=>$this->getTotalDiscount(),
        ];
        Log::info($courseOrder);
        $this->courseOrder = CourseOrder::create($courseOrder);
    }
    private function getTotalPrice()
    {
        return count($this->courseIds) * (1 - $this->getParticipantDiscountPercentage()) * self::COURSE_PRICE;
    }

    private function getTotalDiscount()
    {
        return count($this->courseIds) * ($this->getParticipantDiscountPercentage()) * self::COURSE_PRICE;
    }
    public function getParticipantDiscountPercentage()
    {
        return Participant::$discountPerType[Participant::$type[$this->participant->type]];
    }

    public function bankTransactionProcessForCourse(Request $request)
    {
        $this->participant = Participant::find(decrypt($request->participant_id));
        $this->courseOrder = CourseOrder::find(decrypt($request->order_id));
        if($this->courseOrder->status == CourseOrder::PAYED)
            return redirect('/');
        $this->courseIds = array_column($this->participant->courses->toArray(),'id');
        $bank = BankFactory::getBank('saman');
        $transaction = $bank->createTransaction(
            $this->getTotalPrice() * AbstractBank::TOMAN_RIAL_RATIO,
            $this->courseOrder->id,
            AbstractBank::COURSE_TRANSACTION,$this->participant->id,
            $request->ip());
        return $bank->startTransaction($transaction);

    }
}

