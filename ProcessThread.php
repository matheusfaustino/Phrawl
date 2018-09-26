<?php

namespace Phpcrawler;

class ProcessThread extends \Threaded
{
    /**
     * @var BaseCrawler
     */
    protected $baseCrawler;

    /**
     * ProcessThread constructor.
     * @param BaseCrawler $baseCrawler
     */
    public function __construct(BaseCrawler $baseCrawler)
    {
        $this->baseCrawler = $baseCrawler;
    }

    public function run()
    {
        (new \Phpcrawler\ProcessorPoolRequest($this->baseCrawler))->run();
    }
}
