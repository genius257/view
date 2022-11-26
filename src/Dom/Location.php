<?php

namespace Genius257\View\Dom;

class Location {
    /**
     * Line number (1-based)
     * @var int
     */
    protected $line;

    /**
     * Column number (1 based)
     * @var int
     */
    protected $column;

    /**
     * Offset number (0 based)
     * @var int
     */
    protected $offset;

    public function __construct(int $line, int $column, int $offset)
    {
        $this->line = $line;
        $this->column = $column;
        $this->offset = $offset;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function getOffset()
    {
        return $this->offset;
    }
}
