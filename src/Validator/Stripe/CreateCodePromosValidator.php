<?php

namespace App\Validator\Stripe;

use Symfony\Component\Validator\Constraints as Assert;

class CreateCodePromosValidator
{
    /**
     * @Assert\NotBlank()
     */
    private $promotionCode;

    /**
     * @Assert\NotBlank()
     */
    private $quantity;

    /**
     * @Assert\NotBlank()
     */
    private $start_date;

    /**
     * @Assert\NotBlank()
     */
    private $end_date;

    /**
     * @Assert\NotBlank()
     */
    private $type;

    /**
     * @Assert\NotBlank()
     */
    private $value;

    public function __construct(array $params)
    {
        $this->promotionCode = @$params['promotionCode'];
        $this->quantity = @$params['quantity'];
        $this->start_date = @$params['start_date'];
        $this->end_date = @$params['end_date'];
        $this->type = @$params['type'];
        $this->value = @$params['value'];
    }

}