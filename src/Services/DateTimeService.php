<?php
namespace App\Services;

class DateTimeService extends \DateTime
{
    public function getNow() {
        return new \DateTime();
    }
}