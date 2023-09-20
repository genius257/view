<?php

namespace Genius257\View;

use Error;
use Genius257\View\Dom\Location;
use Throwable;
use Exception;
use ReflectionClass;

class ProcessNodeException extends Exception
{
    /**
     * View file path.
     *
     * @var string
     */
    protected $viewFile;

    /**
     * The Throwable the trace modifications is based on.
     *
     * @var Throwable
     */
    protected $originalThrowable;

    /**
     * Initialize a new ProcessNodeException class.
     *
     * @param Throwable     $previous  Previous throwable.
     * @param string        $viewFile  View file path.
     * @param string        $className View component class name.
     * @param Location|null $location  View content throwable source location.
     */
    public function __construct(Throwable $previous, string $viewFile, string $className, ?Location $location)
    {
        parent::__construct($previous->getMessage(), $previous->getCode());

        $this->originalThrowable = $previous;

        $this->viewFile = $viewFile;

        $currentTrace = $this->extractTrace($this);
        $trace        = $this->extractTrace($previous);
        $traceIndex   = (count($trace) - count($currentTrace));

        $trace[$traceIndex]['file']     = $this->viewFile;
        $trace[$traceIndex]['line']     = ($location === null) ? 0 : $location->getLine();
        $trace[$traceIndex]['class']    = $className;
        $trace[$traceIndex]['function'] = 'render';
        $trace[$traceIndex]['type']     = '::';
        $ro = $this->extractThrowableReflection($this);
        $rp = $ro->getProperty('trace');
        $rp->setAccessible(true);
        $rp->setValue($this, $trace);
    }

    /**
     * Extract ReflectionClass of Throwable parent class from provided Throwable class inheritance hierarchy.
     *
     * @param Throwable $throwable instanceof throwable to extract actual throwable from
     *
     * @return ReflectionClass<Throwable>
     *
     * @throws Exception If extraction of throwable fails
     */
    protected function extractThrowableReflection(Throwable $throwable): ReflectionClass
    {
        $ro = new \ReflectionObject($throwable);

        while ($ro !== false && !in_array($ro->getName(), [Exception::class, Error::class])) {
            $ro = $ro->getParentClass();
        }

        if ($ro === false) {
            throw new Exception('Could not extract throwable class');
        }

        return $ro;
    }

    /**
     * Extract trace array from Throwable.
     *
     * @param Throwable $throwable
     *
     * @return array<array{file: string, line: int, class?: string, function: string, type?: string}>
     */
    protected function extractTrace(Throwable $throwable)
    {
        $ro = $this->extractThrowableReflection($throwable);
        $rp = $ro->getProperty('trace');
        $rp->setAccessible(true);

        /** @var array<array{file: string, line: int, class?: string, function: string, type?: string}> */
        return $rp->getValue($throwable);
    }
}
