<?php
/**
 * laravel-google-cloud-print
 *
 * @author    Jérémy GAULIN <jeremy@bnb.re>
 * @copyright 2016 - B&B Web Expertise
 */

namespace Bnb\GoogleCloudPrint;

/**
 * Class PrintJob
 *
 * @property string id
 * @property string printerid
 * @property string status
 * @property string title
 *
 * @package Bnb\GoogleCloudPrint
 */
class PrintJob
{

    public function __construct($job)
    {
        $this->data = $job;
    }


    public function __get($attribute)
    {
        if (isset($this->data->{$attribute})) {
            return $this->data->{$attribute};
        }

        return null;
    }
}