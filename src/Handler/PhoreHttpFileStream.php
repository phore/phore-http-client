<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 26.11.18
 * Time: 12:46
 */

namespace Phore\HttpClient\Handler;


use Phore\FileSystem\FileStream;

class PhoreHttpFileStream implements PhoreStreamHandler
{

    private $fstream = null;
    
    public function __construct($file="php://output")
    {
        if ( ! $file instanceof FileStream)
            $file = phore_file($file)->fopen("w");
        
        $this->fstream = $file;
    }


    /**
     * String with data bubble - null after stream is closed
     *
     * @param $data
     * @return mixed
     */
    public function message($data)
    {
        if ($data === null) {
            $this->fstream->close();
            return;
        }
        $this->fstream->fwrite($data);        
    }
}
