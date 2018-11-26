<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 26.11.18
 * Time: 12:46
 */

namespace Phore\HttpClient\Handler;


class PhoreHttpFileStream implements PhoreStreamHandler
{

    public function __construct($file)
    {

    }


    /**
     * String with data bubble - null after stream is closed
     *
     * @param $data
     * @return mixed
     */
    public function message($data)
    {
        // TODO: Implement message() method.
    }
}
