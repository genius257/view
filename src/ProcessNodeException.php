<?php

namespace Genius257\View;

use Error;
use Genius257\View\Dom\Location;
use Throwable;
use Exception;
use ReflectionClass;

class ProcessNodeException extends Exception {
    /** @var string */
    protected $viewFile;

    /** @var Throwable */
    protected $originalThrowable;

    public function __construct(Throwable $previous, string $viewFile, string $className, ?Location $location) {
        parent::__construct($previous->getMessage(), $previous->getCode());
        
        $this->originalThrowable = $previous;

        $this->viewFile = $viewFile;

        $currentTrace = $this->extractTrace($this);
        $trace = $this->extractTrace($previous);
        $traceIndex = count($trace) - count($currentTrace);

        $trace[$traceIndex]['file'] = $this->viewFile;
        $trace[$traceIndex]['line'] = is_null($location) ? 0 : $location->getLine();
        $trace[$traceIndex]['class'] = $className;
        $trace[$traceIndex]['function'] = "render";
        $trace[$traceIndex]['type'] = "::";
        $ro = $this->extractThrowableReflection($this);
        $rp = $ro->getProperty('trace');
        $rp->setAccessible(true);
        $rp->setValue($this, $trace);
    }

    protected function extractThrowableReflection(Throwable $throwable): ReflectionClass
    {
        $ro = new \ReflectionObject($throwable);

        while($ro !== false && !in_array($ro->getName(), [Exception::class, Error::class])) {
            $ro = $ro->getParentClass();
        }

        if ($ro === false) {
            throw new Exception('Could not extract throwable class');
        }

        return $ro;
    }

    protected function extractTrace(Throwable $throwable)
    {
        $ro = $this->extractThrowableReflection($throwable);
        $rp = $ro->getProperty('trace');
        $rp->setAccessible(true);

        return $rp->getValue($throwable);
    }
}
