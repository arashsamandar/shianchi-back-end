<?php
namespace Wego\UserHandle;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Validator;


class RestValidators extends Validator {

    static $count = -1;
    /**
     * Add an error message to the validator's collection of messages.
     *
     * @param  string  $attribute
     * @param  string  $rule
     * @param  array   $parameters
     * @return void
     */
    protected function addError($attribute, $rule, $parameters)
    {
        $message = $this->getMessage($attribute, $rule);
        $message = $this->doReplacements($message, $attribute, $rule, $parameters);
        $customMessage = new MessageBag();

        $customMessage->merge(['code' => strtolower($attribute)]);
        $customMessage->merge(['message' => $message]);
        $this->messages->add(++self::$count, $customMessage);
    }

}