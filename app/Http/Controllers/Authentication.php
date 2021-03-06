<?php

namespace App\Http\Controllers;

use App\Models\PasswordCreates;
use App\Models\PasswordResets;
use App\Notifications\ForgotPassword;
use App\Notifications\Registered;
use App\User;
use Illuminate\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Authentication extends Controller
{
    public function check(): Http\JsonResponse
    {
        return response()->json(['auth' => Auth::guard('api')->check()]);
    }

    public function createPassword(): Http\JsonResponse
    {
        $email = Str::replaceFirst(' ', '+', urldecode(request()->query('email')));
        $token = request()->query('token');

        $tokens = DB::table('password_creates')
            ->where('email', '=', $email)
            ->first();

        if ($tokens === null || Hash::check($token, $tokens->token) === false) {
            return response()->json(
                [
                    'message'=>'Sorry, the email and or token you supplied are invalid'
                ],
                401
            );
        }

        $validator = Validator::make(
            request()->only(['password', 'password_confirmation']),
            [
                'password' => [
                    'required',
                    'min:10'
                ],
                'password_confirmation' => [
                    'required',
                    'same:password',
                ]
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => 'Validation error, please review the below',
                    'fields' => $validator->errors()
                ],
                422
            );
        }

        try {
            $user = User::with([])
                ->where('email', '=', $email)
                ->first();

            if ($user !== null) {
                $user->password = Hash::make(request()->input('password'));
                $user->save();

                DB::table('password_creates')
                    ->where('email', '=', request()->input(['email']))
                    ->delete();

                return response()->json([], 204);
            }

            return response()->json(['message' => 'Unable to fetch your account to create password, please try again later'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unable to create password, please try again later'], 500);
        }
    }

    public function createNewPassword(): Http\JsonResponse
    {
        $email = Str::replaceFirst(' ', '+', urldecode(request()->query('email')));
        $token = request()->query('token');

        $tokens = DB::table('password_resets')
            ->where('email', '=', $email)
            ->first();

        if ($tokens === null || Hash::check($token, $tokens->token) === false) {
            return response()->json(
                [
                    'message'=>'Sorry, the email and token you supplied are invalid'
                ],
                404
            );
        }

        $validator = Validator::make(
            request()->only(['password', 'password_confirmation']),
            [
                'password' => [
                    'required',
                    'min:10'
                ],
                'password_confirmation' => [
                    'required',
                    'same:password',
                ]
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => 'Validation error, please review the below',
                    'fields' => $validator->errors()
                ],
                422
            );
        }

        try {
            $user = User::with([])
                ->where('email', '=', $email)
                ->first();

            if ($user !== null) {
                $user->password = Hash::make(request()->input('password'));
                $user->save();

                DB::table('password_resets')
                    ->where('email', '=', request()->input(['email']))
                    ->delete();

                return response()->json([], 204);
            }

            return response()->json(['message' => 'Unable to fetch your account to create password, please try again later'], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unable to create password, please try again later'], 500);
        }
    }

    public function forgotPassword(): Http\JsonResponse
    {
        $validator = Validator::make(
            request()->only(['email']),
            [
                'email' => 'required|email',
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => 'Validation error, please review the errors below',
                    'fields' => $validator->errors()
                ],
                422
            );
        }

        $email = request()->input('email');

        $user = User::with([])
            ->where('email', '=', $email)
            ->first();

        if ($user !== null) {
            try {
                $create_token = Str::random(20);

                DB::table('password_resets')->updateOrInsert(
                    [
                        'email' => $email,
                    ],
                    [
                        'email' => $email,
                        'token' => Hash::make($create_token)
                    ]
                );

                if (app()->environment() === 'production' && request()->query('send') === null) {
                    $user->notify(new ForgotPassword($user, $create_token));
                }
            } catch (\Exception $e) {
                return response()->json(['error' => 'Unable to process your forgot password request, please try again later'], 500);
            }

            return response()->json(
                [
                    'message' => 'Request received, please check your email for instructions on how to create your new password',
                    'uris' => [
                        'create-new-password' => [
                            'uri' => Config::get('api.app.version.prefix') . '/auth/create-new-password?token=' . $create_token . '&email=' . $email,
                            'parameters' => [
                                'token' => $create_token,
                                'email' => $email
                            ]
                        ]
                    ]
                ],
                201
            );
        }

        return response()->json(['message' => 'Unable to fetch your user account, please try again later'], 404);
    }

    public function login(): Http\JsonResponse
    {
        if (
            Auth::attempt(
                [
                    'email' => request('email'),
                    'password' => request('password')
                ]
            ) === true
        ) {
            $user = Auth::user();

            if ($user !== null) {
                $token = request()->user()->createToken('costs-to-expect-api');
                return response()->json(
                    [
                        'id' => $this->hash->user()->encode($user->id),
                        'type' => 'Bearer',
                        'token' => $token->plainTextToken,
                    ],
                    201
                );
            }

            return response()->json(['message' => 'Unauthorised, credentials invalid'], 401);
        }

        return response()->json(['message' => 'Unauthorised, credentials invalid'], 401);
    }

    public function register(): Http\JsonResponse
    {
        $validator = Validator::make(
            request()->all(),
            [
                'name' => 'required',
                'email' => 'required|email',
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => 'Validation error, please review the below',
                    'fields' => $validator->errors()
                ],
                422
            );
        }

        try {
            $email = request()->input('email');

            $user = new User();
            $user->name = request()->input('name');
            $user->email = request()->input('email');
            $user->password = Hash::make(Str::random(20));
            $user->save();

            $create_token = Str::random(20);

            DB::table('password_creates')->updateOrInsert(
                [
                    'email' => $email,
                ],
                [
                    'email' => $email,
                    'token' => Hash::make($create_token)
                ]
            );

            if (app()->environment() === 'production' && request()->query('send') === null) {
                $user->notify(new Registered($user, $create_token));
            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to create the account, please try again later'], 500);
        }

        return response()->json(
            [
                'message' => 'Account created, please check you email for information on how to create your password',
                'uris' => [
                    'create-password' => [
                        'uri' => Config::get('api.app.version.prefix') . '/auth/create-password?token=' . $create_token . '&email=' . $email,
                        'parameters' => [
                            'token' => $create_token,
                            'email' => $email
                        ]
                    ]
                ]
            ],
            201
        );
    }

    public function updatePassword(): Http\JsonResponse
    {
        $validator = Validator::make(
            request()->only(['password', 'password_confirmation']),
            [
                'password' => [
                    'required',
                    'min:10'
                ],
                'password_confirmation' => [
                    'required',
                    'same:password',
                ]
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => 'Validation error, please review the messages',
                    'fields' => $validator->errors()
                ],
                422
            );
        }

        $user = auth()->guard('api')->user();

        if ($user !== null) {
            $user->password = Hash::make(request()->input('password'));
            $user->save();

            return response()->json([], 204);
        }

        return response()->json(['message' => 'Unauthorised, credentials invalid'], 401);
    }

    public function updateProfile(): Http\JsonResponse
    {
        $validator = Validator::make(
            request()->only(['name', 'email']),
            [
                'name' => [
                    'sometimes'
                ],
                'email' => [
                    'sometimes',
                    'email'
                ]
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => 'Validation error, please review the messages',
                    'fields' => $validator->errors()
                ],
                422
            );
        }

        $user = auth()->guard('api')->user();

        if ($user !== null) {
            $fields = [];
            if (request()->input('name') !== null) {
                $fields['name'] = request()->input('name');
            }
            if (request()->input('email') !== null) {
                $fields['email'] = request()->input('email');
            }

            if (count($fields) === 0) {
                return response()->json(['message' => 'You have provided any fields to change'], 400);
            }

            try {
                foreach ($fields as $field => $value) {
                    $user->$field = $value;
                }

                $user->save();
            } catch (\Exception $e) {
                return response()->json(['message' => 'Unable to update your profile, please try again'], 401);
            }

            return response()->json([], 204);
        }

        return response()->json(['message' => 'Unauthorised, credentials invalid'], 401);
    }

    public function user(): Http\JsonResponse
    {
        $user = auth()->guard('api')->user();

        if ($user !== null) {
            $user = [
                'id' => $this->hash->user()->encode($user->id),
                'name' => $user->name,
                'email' => $user->email
            ];
            return response()->json($user);
        }

        return response()->json(['message' => 'Unauthorised, credentials invalid'], 401);
    }
}
