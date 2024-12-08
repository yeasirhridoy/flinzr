<?php

use Illuminate\Support\Facades\Route;

Route::get('/',function (){
    return redirect('/admin');
});

require __DIR__.'/auth.php';

Route::get('logs',function (){
    return response(file_get_contents(storage_path('logs/laravel.log')))->header('Content-Type', 'text/plain');
});

Route::get('clear-log',function (){
    file_put_contents(storage_path('logs/laravel.log'),'');
    return redirect('logs');
});
