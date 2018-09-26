<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 26.09.18
 * Time: 10:28
 */

namespace Phore\HttpClient\Promise;


class PhoreHttpPromise
{


    private $success;
    private $error;

    private $next = null;

    public function then (callable $success=null, callable $error=null) : self
    {
        $this->success = $success;
        $this->error = $error;
        $this->next = new self();
        return $this->next;
    }


    public function resolve($value)
    {
        try {
            if ($this->success !== null)
                $return = ($this->success)($value);
            if ($return instanceof PhoreHttpPromise)

            if ($this->next !== null)
                $this->next->resolve($return);
        } catch (\Exception $e) {
            $this->next->reject($e);
        }
    }

    public function reject($reason)
    {
        if ($this->error !== null)
            ($this->error)($reason);
        if ($this->next !== null)
            $this->next->reject($reason);
    }




}
