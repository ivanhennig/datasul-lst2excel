<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () use ($router) {
    return redirect('/dist');
});

$router->get('/download/{file}', function ($file) use ($router) {
    return response()->download(storage_path("app/$file"));
});

$router->post('/rpc/calls', 'RpcController@handleGroup');
$router->post('/rpc/{class}/{method}', 'RpcController@handle');
