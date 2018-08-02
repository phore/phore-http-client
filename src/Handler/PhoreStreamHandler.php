<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 02.08.18
 * Time: 14:06
 */

namespace Phore\HttpClient\Handler;


interface PhoreStreamHandler
{

    /**
     * String with data bubble - null after stream is closed
     *
     * @param $data
     * @return mixed
     */
    public function message($data);
}
