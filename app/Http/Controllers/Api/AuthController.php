<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user || $user->status === 0) {
            $response = [
                'success' => false,
                'message' => 'Al parecer esta cuenta no existe.'
            ];

            return response()->json($response, 401);
        }

        if (Auth::guard('web')->attempt($validatedData)) {
            $user = $request->user();
            $user->device = $request->userAgent();

            $user->tokens()->delete();
            $user->save();

            $response = [
                'success' => true,
                'token' => $user->createToken('login', ['*'], now()->addDays(15))->plainTextToken,
                'message' => 'Inicio sesión correctamente.'
            ];

            return response()->json($response, 200);
        } else {
            $response = [
                'success' => false,
                'message' => 'La dirección de correo electrónico o la contraseña no son válidas.'
            ];

            return response()->json($response, 401);
        }
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'names' => ['required'],
                'surnames' => ['required'],
                'email' => ['required', 'email', 'unique:users,email,' . $request->id],
                'password' => ['required'],
            ]);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => $validator->errors()
                ];
                return response()->json($response);
            }

            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $input['device'] = $request->userAgent();
            $user = User::create($input);

            $response = [
                'success' => true,
                'token' => $user->createToken('register', ['*'], now()->addDays(15))->plainTextToken,
                'message' => 'Usuario registrado con éxito.'
            ];

            return response()->json($response, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $user->device = null;
            $user->save();
            $user->currentAccessToken()->delete();
            return response()->json([
                'success' => true,
                'message' => 'Ha cerrado la sesión correctamente.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => ['required'],
                'password' => ['required', 'string', 'min:8', 'confirmed']
            ]);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => $validator->errors()
                ];
                return response()->json($response);
            }

            $user = $request->user();

            $credentials = [
                'email' => $user->email,
                'password' => $request->current_password
            ];

            if (Auth::guard('web')->attempt($credentials)) {
                $user->password = bcrypt($request->password);
                $user->save();

                $response = [
                    'success' => true,
                    'message' => [
                        "notificación" => 'Contraseña actualizada correctamente.'
                    ]
                ];
                return response()->json($response);
            } else {
                $response = [
                    'success' => false,
                    'message' => [
                        "notificación" => 'La contraseña actual es incorrecta.'
                    ]
                ];
                return response()->json($response);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function updateUserInfo(Request $request)
    {
        try {
            $user = $request->user();
            $data = $request->all();

            $validator = Validator::make($data, [
                'names' => ['required', 'string', 'max:255'],
                'surnames' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            ]);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => $validator->errors()
                ];
                return response()->json($response);
            }

            if (isset($data['names'])) {
                $user->names = $data['names'];
            }
            if (isset($data['surnames'])) {
                $user->surnames = $data['surnames'];
            }
            if (isset($data['email'])) {
                $user->email = $data['email'];
            }

            $user->save();

            $response = [
                'success' => true,
                'message' => 'Perfil actualizado correctamente.',
            ];
            return response()->json($response, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function updateProfilePhoto(Request $request)
    {
        try {
            $user = $request->user();

            if ($request->file('photo')) {
                Storage::makeDirectory('public/profile');

                $url = Storage::put('public/profile/' . uniqid(), $request->file('photo'));

                $previousPhotoUrl = $user->photo;

                if ($previousPhotoUrl) {
                    Storage::delete($previousPhotoUrl);
                }

                $user->photo = $url;
                $user->save();

                $response = [
                    'success' => true,
                    'message' => 'Foto de perfil actualizada correctamente.'
                ];
                return response()->json($response, 200);
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No se ha cargado ninguna foto.'
                ];
                return response()->json($response, 400);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'La dirección de correo electrónico no está registrada.'
                ], 404);
            }

            $token = Str::random(60);

            $user->update([
                'remember_token' => $token
            ]);

            $user->sendPasswordResetNotification($token);

            return response()->json([
                'success' => true,
                'message' => 'Se ha enviado un correo electrónico con un enlace para restablecer la contraseña.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getUser(Request $request)
    {
        try {
            $user = $request->user();
            $user->photo_url = null;
            if ($user->photo) {
                $user->photo_url = url(Storage::url($user->photo));
            }

            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function deleteUser(Request $request)
    {
        try {
            $user = $request->user();
            $user = User::find($user->id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no existe.'
                ], 404);
            }

            $user->status = 0;
            $user->tokens()->delete();
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'El usuario ha sido eliminado.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
