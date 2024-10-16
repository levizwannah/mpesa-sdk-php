<?php

namespace LeviZwannah\MpesaSdk\Helpers;

use Exception;
use LeviZwannah\MpesaSdk\Mpesa;

/**
 * Mpesa Ratiba (Standing Order)
 */
class Subscription extends Mpesa
{

    /**
     * The Start date of the subscription (yyyymmdd)
     */
    public string $startDate;

    /**
     * The end date of the subscription (yyyymmdd)
     */
    public string $endDate;

    /**
     * The Plan Name for the Standing Order
     * @var string
     */
    public string $name;

    /**
     * Phone number making the payment
     * 
     * @var string
     */
    public string $phone;

    /**
     * The amount to pay
     * @var int
     */
    public int $amount;

    /**
     * The account Reference
     * @var string
     */
    public string $reference = "default";

    /**
     * Callback Url
     * 
     * @var string
     */
    public string $callback;

    /**
     * Transaction description
     * @var string
     */
    public string $description = "payment";

    /**
     * The transaction type.
     * @var string
     */
    public string $type = Constant::PAY_BILL_ONLINE;


    /**
     * Frequency of Payment Use Constant::FREQ_*
     * @var int
     */
    public int $frequency;

    /**
     * !!!Do not call this directly. It needs config from the main mpesa class.
     * Use $mpesa->subscription();
     * @param array<string,string> $config initial configurations for the stk push.
     * This is gotten from the parent Mpesa.
     */
    public function __construct(array $config)
    {
        $this->configure($config);
    }

    public function configure(array $config)
    {
        parent::configure($config);
        if (isset($config["phone"])) $this->phone($config["phone"]);
        return $this;
    }

    /**
     * Set the start date of the subscription.
     * The SDK will handle the formatting.
     * @param int $month
     * @param int $day
     * @param int $year
     * 
     */
    public function startDate(int $month, int $day, int $year)
    {

        $year = str_pad($year, 4, "0", STR_PAD_LEFT);
        $month = str_pad($month, 2, "0", STR_PAD_LEFT);
        $day = str_pad($day, 2, "0", STR_PAD_LEFT);

        $this->startDate = "{$year}{$month}{$day}";
        return $this;
    }

    /**
     * Set the end date of the subscription.
     * The SDK will handle the formatting.
     * @param int $month
     * @param int $day
     * @param int $year
     * 
     */
    public function endDate(int $month, int $day, int $year)
    {

        $year = str_pad($year, 4, "0", STR_PAD_LEFT);
        $month = str_pad($month, 2, "0", STR_PAD_LEFT);
        $day = str_pad($day, 2, "0", STR_PAD_LEFT);

        $this->endDate = "{$year}{$month}{$day}";

        return $this;
    }

    /**
     * Sets the standing order Name
     * @param string $planName
     * 
     */
    public function plan(string $planName)
    {
        return $this->name($planName);
    }

    /**
     * @param string $planName sets the plan name same as the 
     * plan method.
     * 
     */
    public function name(string $planName)
    {
        $this->name = $planName;
        return $this;
    }

    /**
     * @param Constant::FREQ_ONE_OFF|Constant::FREQ_DAILY|Constant::FREQ_WEEKLY|Constant::FREQ_MONTHLY|Constant::FREQ_BI_MONTHLY|Constant::FREQ_QUARTERLY|Constant::FREQ_HALF_YEARLY|Constant::FREQ_YEARLY $frequency
     */
    public function frequency(int $frequency = Constant::FREQ_ONE_OFF)
    {
        $this->frequency = $frequency;
        return $this;
    }

    /**
     * Sets the phone to pay to.
     * Accepts 07xxxxxxxx, +2547xxxxxxxxx, or 2547xxxxxxxxx
     * @param string $phone
     * 
     */
    public function phone(string $phone)
    {
        $phone = "254" . substr(preg_replace("/\s+/", "", $phone), -9);
        $this->phone = $phone;
        return $this;
    }

    /**
     * Sets the Amount. Kindly note, amount will be converted to int.
     * @param mixed $amount
     * 
     */
    public function amount($amount)
    {
        $this->amount = (int)$amount;
        return $this;
    }

    /**
     * Sets the account number
     * @param string $number
     * 
     */
    public function reference(string $number)
    {
        $this->reference = $number;
        return $this;
    }

    /**
     * Sets the account number for paybill
     * @param string $number
     * 
     */
    public function account(string $number)
    {
        $this->reference = $number;
        return $this;
    }

    /**
     * Sets the transaction description
     * @param string $description
     * 
     */
    public function description(string $description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Sets the command ID to Standing order for Merchant (Till numbers).
     */
    public function buygoods()
    {
        $this->type = Constant::STANDING_ORDERS_TILL;
        return $this;
    }

    /**
     * Sets the command ID to Standing order for Paybill
     */
    public function paybill()
    {
        $this->type = Constant::STANDING_ORDERS_PAYBILL;
        return $this;
    }

    /**
     * Sets the callback URL
     * @param string $callback
     * 
     */
    public function callback(string $callback)
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * Makes the STK push
     */
    public function create()
    {
        $this->okay();

        $data = [
            "StandingOrderName" => $this->name,
            "ReceiverPartyIdentifierType" => ($this->type == Constant::STANDING_ORDERS_TILL) 
                ? 2 : 4,
            "TransactionType" => $this->type,
            "BusinessShortCode" => ($this->type == Constant::STANDING_ORDERS_TILL) 
                ? $this->till : $this->code,
            "PartyA" => $this->phone,
            "Amount" => $this->amount,
            "StartDate" => $this->startDate,
            "EndDate" => $this->endDate,
            "Frequency" => $this->frequency,
            "AccountReference" => $this->reference,
            "TransactionDesc" => $this->description,
            "CallBackURL" => $this->callback,
        ];

        $this->response = $this->request($data, "/standingorder/v1/createStandingOrderExternal");
        return $this;
    }

    /**
     * Makes the STK push
     * @return object response
     */
    public function __invoke()
    {
        $this->create();

        return $this->response();
    }

    /**
     * Checks if all the required data to make an stk push exist and is not empty.
     * @return bool
     */
    public function okay()
    {
        parent::okay();

        if (
            $this->type == Constant::STANDING_ORDERS_TILL
            && empty($this->till)
        ) {
            throw new Exception("Till cannot be empty when using standing orders for merchant");
        }

        $this->assertExists("phone");
        $this->assertExists("amount");
        $this->assertExists("startDate", "Start Date");
        $this->assertExists("endDate", "End Date");
        $this->assertExists("frequency", "Frequency of Payment");
        $this->assertExists("callback", "Callback URL");
        return true;
    }
}
