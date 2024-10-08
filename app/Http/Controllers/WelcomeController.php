<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function __invoke(Request $requeset) {

        // $user = User::query()->create([
        //     'name' => 'Vincenzo',
        //     'email' => 'vincenzo@email.com',
        //     'password' => '123456',
        // ]);

        // dd($user);

        return view('welcome');
    }
}
