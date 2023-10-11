<?php

namespace App\IService;

interface IMailService
{
    public static function send($content, string $subject, string $from, string $to, ?string $Cc = null);
}
