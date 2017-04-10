<?php


namespace Shaarli\Links\Exceptions;


class LinkNotFoundException extends \Exception
{
    protected $message = 'The link you are trying to reach does not exist or has been deleted.';
}