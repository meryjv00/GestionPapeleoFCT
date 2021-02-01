<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Persona;
use App\Models\RolUsuario;

class AuthController extends Controller {

    public function register(Request $request) {
        //return response()->json([$request->all()]);
//        $validatedData = $request->validate([
//            'dni' => 'required|unique:users',
//            'email' => 'email|required|unique:users',
//            'password' => 'required|confirmed'
//        ]);

        if (User::where('email', $request->get('email'))->count() == 1) {
            return response()->json(['message' => 'Registro incorrecto. Revise las credenciales.', 'code' => 400], 400);
        }

        $validatedData = [
            'dni' => $request->get('dni'),
            'email' => $request->get('email'),
            'password' => $request->get('password'),
        ];

        $validatedData['password'] = \Hash::make($request->password);

        $user = User::create($validatedData);
        $user->roles()->attach(3);

        $accessToken = $user->createToken('authToken')->accessToken;

        //return response(['user' => $user, 'access_token' => $accessToken], 201);
        return response()->json(['message' => ['user' => $user, 'access_token' => $accessToken], 'code' => 201], 201);
    }

    public function login(Request $request) {
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($loginData)) {
            //return response(['message' => 'Login incorrecto. Revise las credenciales.'], 400);
            return response()->json(['message' => 'Login incorrecto. Revise las credenciales.', 'code' => 400], 400);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;
        //Obtener todos los datos del usuario
        $persona = Persona::where("correo", "=", $request->input('email'))->first();

        //Obtener el rol del usuario

        $rol = RolUsuario::where("user_id", "=", auth()->user()->id)->get();
        if ($rol[0]->role_id == 2) {
            $rolDescripcion = "Jefe de estudios";
        } else {
            $rolDescripcion = "Tutor";
        }
        //return response(['user' => auth()->user(), 'access_token' => $accessToken]);
        return response()->json(['message' => ['user' => auth()->user(), 'access_token' => $accessToken, 'datos_user' => $persona, 'rol' => $rolDescripcion], 'code' => 200], 200);
    }

}
