<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 02.08.18
 * Time: 14:06
 */

namespace Phore\HttpClient\Handler;


class PhoreHttpLineStream implements PhoreStreamHandler
{
    private $buf = "";
    private $lineCallback;
    private $lineIndex = 0;

    /**
     * PhoreHttpLineStream constructor.
     *
     * <example>
     *
     * </example>
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->lineCallback = $callback;
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
            // end of stream
            foreach (explode("\n", $this->buf) as $line) {
                ($this->lineCallback)($line, $this->lineIndex++);
            }
        } else {
            $this->buf .= $data;
            while (true) {
                $pos = strpos($this->buf, "\n");
                if ($pos === false) {
                    break;
                }
                $lineData = substr($this->buf, 0, $pos);
                $this->buf = substr($this->buf,  $pos+1);
                ($this->lineCallback)($lineData, $this->lineIndex++);
            }

        }
    }
}
