<?php

require __DIR__ . '/vendor/autoload.php';



// without thread
//(new \Phpcrawler\ProcessorPoolRequest(new \Phpcrawler\SpiderStackOverflow()))->run();

// with thread
if (extension_loaded('pthreads')) {
    // multithread, dahora. Problema agora é que tem coisa que pode ser multithread e coisas que nao.
    // a fila nao pode e tbm o starts_url eu creio que nao, ou na vdd, só a parte do request e da funcao do cara pode
    $pool = new \Pool(2, \Auto\AutoloaderComposerWorker::class, [__DIR__ . '/vendor/autoload.php']);

    $pool->submit(new \Phpcrawler\ProcessThread(new \Phpcrawler\SpiderStackOverflow()));
    $pool->submit(new \Phpcrawler\ProcessThread(new \Phpcrawler\SpiderStackOverflow()));

    $pool->shutdown();
}
