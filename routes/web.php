<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/login');

require __DIR__ . '/adminauth.php';
require __DIR__ . '/admin.php';
