<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['credentials' => 'E-mail ou senha inválidos'])->withInput();
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'foto' => 'nullable|image|max:2048', // max 2MB
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('register_open', true);
        }

        $data = $validator->validated();

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);

        // tratar upload da foto (opcional)
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $path = $file->store('fotos', 'public'); // storage/app/public/fotos
            $user->foto = $path;
        }

        $user->save();

        // Não logar automaticamente: redirecionar para a home (login) para que o usuário
        // faça login manualmente após a criação da conta.
        return redirect('/')->with('registered', 'Conta criada com sucesso. Faça login.')->with('registered_email', $user->email);
    }
}
