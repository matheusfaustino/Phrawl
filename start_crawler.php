<?php

require __DIR__.'/vendor/autoload.php';

// without thread
(new \Phpcrawler\ProcessorPoolRequest(new \Phpcrawler\SpiderStackOverflow()))->run();