<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\usuarios_y_permisos\Usuario;
use App\Models\usuarios_y_permisos\Permiso;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\agenda\Agenda;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Services\usuarios_y_permisos\PermisoService;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Autentica un usuario y genera un token JWT
     *
     * Valida las credenciales recibidas, verifica manualmente el usuario
     * y su contraseña en texto plano, y genera un token JWT si la autenticación
     * es exitosa.
     *
     * @param Request $request solicitud HTTP con username y password
     *
     * @return \Illuminate\Http\JsonResponse respuesta JSON con el token o error
     * @access public
     */
    public function login(Request $request)
    {

        /* 
         * Se crea el validador para comprobar que los campos requeridos
         * existan y cumplan con las reglas de longitud y tipo
         */
        $validator = Validator::make(
            $request->all(),
            [
                'username' => 'required|string|min:3|max:50',
                'password' => 'required|string|min:3|max:50',
            ],
            [
                'username.required' => 'El campo usuario es obligatorio',
                'password.required' => 'El campo contraseña es obligatorio',
                'username.min' => 'El campo usuario debe tener al menos 3 caracteres',
                'password.min' => 'El campo contraseña debe tener al menos 3 caracteres',
                'username.max' => 'El campo usuario debe tener como maximo 50 caracteres',
                'password.max' => 'El campo contraseña debe tener como maximo 50 caracteres',
            ]
        );

        /*
         * Si la validación falla, se retorna una respuesta JSON
         * con los errores y un código HTTP 400 (Bad Request)
         */
        if ($validator->fails()) {
            /* return response()->json(['error' => $validator->errors()], response::HTTP_BAD_REQUEST); */
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        /*
         * Se extraen únicamente las credenciales necesarias
         * desde la solicitud HTTP
         */
        $credentials = $request->only(['username', 'password']);

        /*
         * Se busca el usuario en la base de datos por username
         * (autenticación manual sin hashing)
         */
        $user = Usuario::where('username', $credentials['username'])->first();


        /*
         * Se valida que el usuario exista y que la contraseña coincida
         * En caso contrario, se devuelve un error de autenticación
         */
        if (!$user || $user->password !== $credentials['password']) {
            /* return response()->json(['error' => 'Los datos ingresados son incorrectos'], response::HTTP_UNAUTHORIZED); */
            return response()->json([
                'message' => 'Los datos ingresados son incorrectos'
            ], response::HTTP_UNAUTHORIZED);
        }

        /*
         * Se genera manualmente el token JWT a partir del usuario autenticado
         */
        $token = JWTAuth::fromUser($user);

        /*
         * Se retorna la respuesta estándar con el token generado
         */
        return $this->respondWithToken($token);
    }

    /**
     * Devuelve una respuesta JSON con el token JWT
     *
     * Construye la estructura estándar del token incluyendo
     * tipo, tiempo de expiración y código HTTP correspondiente.
     *
     * @param string $token token JWT generado
     *
     * @return \Illuminate\Http\JsonResponse respuesta JSON con datos del token
     * @access protected
     */
    protected function respondWithToken($token)
    {
        /*
         * Se retorna la respuesta JSON con la información
         * necesaria para la autenticación basada en JWT
         */
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ], response::HTTP_OK);
    }

    /**
     * Registra un nuevo usuario en el sistema
     *
     * Valida los datos recibidos, verifica que el username
     * no exista previamente y crea el usuario en la base de datos.
     *
     * @param Request $request solicitud HTTP con datos del usuario
     *
     * @return \Illuminate\Http\JsonResponse respuesta JSON con estado de creación
     * @access public
     */
    public function register(Request $request)
    {
        DB::beginTransaction();
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|min:3|max:50',
                'username' => 'required|string|min:3|max:50',
                'password' => 'required|string|min:3|max:50',
                'telf_interno' => 'nullable|integer|min:3',
                'telf_laboral' => 'nullable|integer|min:10',
                'fecha_nac' => 'nullable|date',
                'email_interno' => 'nullable|string|max:30',
                'email_externo' => 'nullable|string|max:30',
            ],
            [
                'name.required' => 'El campo nombre es obligatorio',
                'username.required' => 'El campo usuario es obligatorio',
                'password.required' => 'El campo contraseña es obligatorio',

                'fecha_nac.date' => 'El campo fecha de nacimiento debe ser una fecha valida',

                'name.min' => 'El campo nombre debe tener al menos 3 caracteres',
                'username.min' => 'El campo usuario debe tener al menos 3 caracteres',
                'password.min' => 'El campo contraseña debe tener al menos 3 caracteres',
                'telf_interno.min' => 'El campo telefono interno debe tener al menos 10 caracteres',
                'telf_laboral.min' => 'El campo telefono laboral debe tener al menos 10 caracteres',

                'telf_interno.max' => 'El campo telefono interno debe tener como maximo 30 caracteres',
                'telf_laboral.max' => 'El campo telefono laboral debe tener como maximo 30 caracteres',
                'username.max' => 'El campo usuario debe tener como maximo 50 caracteres',
                'password.max' => 'El campo contraseña debe tener como maximo 50 caracteres',
                'email_interno.max' => 'El campo email interno debe tener como maximo 30 caracteres',
                'email_externo.max' => 'El campo email externo debe tener como maximo 30 caracteres',
                'name.max' => 'El campo nombre debe tener como maximo 50 caracteres',
            ]
        );

        /*
         * Si la validación falla, se retorna un error con
         * código HTTP 400
         */
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], response::HTTP_BAD_REQUEST);
        }

        /*
         * Se verifica si ya existe un usuario con el mismo username
         */
        $exists = Usuario::where('username', $request->input('username'))->first();


        if (!$exists) {
            try {
                /*
             * Se crea el nuevo usuario con los datos recibidos
             */

                $new = Usuario::create([
                    'name' => $request->input('name'),
                    'username' => $request->input('username'),
                    'password' => $request->input('password'),
                    'admin' => 0,
                    'telf_interno' => $request->input('telf_interno'),
                    'telf_laboral' => $request->input('telf_laboral'),
                    'fecha_nac' => $request->input('fecha_nac'),
                    'email_interno' => $request->input('email_interno') ?? null,
                    'email_externo' => $request->input('email_externo') ?? null
                ]);



                /*
             * Si la creación falla, se devuelve un error interno
             */
                if (!$new) {
                    return response()->json(['error' => 'error al crear el usuario'], response::HTTP_INTERNAL_SERVER_ERROR);
                }

                /*
             * Procesar los permisos del usuario
             */
                $permisos = $request->input('permisos', []);

                if (!empty($permisos)) {
                    (new PermisoService())->asignarPermisos($new->id, $permisos);
                }
                DB::commit();

                /*
             * Usuario creado correctamente
             */
                return response()->json(['message' => 'usuario creado correctamente'], response::HTTP_CREATED);
            } catch (\Throwable $e) {
                DB::rollback();
                return response()->json(['error' => 'Error al crear el usuario'], response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    /**
     * Obtiene la información del usuario autenticado
     *
     * Devuelve el usuario asociado al token JWT actual.
     *
     * @return \Illuminate\Http\JsonResponse datos del usuario autenticado
     * @access public
     */
    public function me()
    {
        $user = auth('api')->user();
        /*
         * Se retorna el usuario autenticado mediante el guard api
         */
        return response()->json(auth('api')->user());
    }

    /**
     * Cierra la sesión del usuario autenticado
     *
     * Invalida el token JWT actual evitando su reutilización.
     *
     * @return \Illuminate\Http\JsonResponse respuesta con estado del logout
     * @access public
     */
    public function logout()
    {
        try {
            /*
             * Se obtiene el token actual desde la petición
             */
            $token = JWTAuth::getToken();
            /*
             * Si no existe token, se retorna un error
             */
            if (!$token) {
                return response()->json(['error' => 'token invalido'], response::HTTP_BAD_REQUEST);
            }
            /*
             * Se invalida el token para cerrar la sesión
             */
            JWTAuth::invalidate($token);
            return response()->json(['message' => 'logout correcto'], Response::HTTP_OK);
        } catch (TokenInvalidException $e) {
            /*
             * El token ya no es válido
             */
            return response()->json(['error' => 'logout correcto'], Response::HTTP_UNAUTHORIZED);
        } catch (Exception $e) {
            /*
             * Error inesperado durante el proceso de logout
             */
            return response()->json(['error' => 'No se pudo cerrar sesion'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Renueva el token JWT actual
     *
     * Genera un nuevo token y revoca el anterior.
     *
     * @return \Illuminate\Http\JsonResponse respuesta JSON con nuevo token
     * @access public
     */
    public function refresh()
    {
        try {
            /*
             * Se obtiene el token actual
             */
            $token = JWTAuth::getToken();
            /*
             * Si no existe token, se retorna error
             */
            if (!$token) {
                return response()->json(['error' => 'Token no encontrado'], Response::HTTP_BAD_REQUEST);
            }
            /*
             * Se genera un nuevo token a partir del actual
             */
            $nuevo_token = JWTAuth::refresh($token);

            /*
             * Se invalida el token anterior
             */
            JWTAuth::invalidate($token);

            /*
             * Se retorna el nuevo token en formato estándar
             */
            return $this->respondWithToken($nuevo_token);
        } catch (TokenInvalidException $e) {
            /*
             * El token no es válido
             */
            return response()->json(['error' => 'Token invalido'], Response::HTTP_UNAUTHORIZED);
        } catch (Exception $e) {
            /*
             * Error inesperado durante la renovación del token
             */
            return response()->json(['error' => 'No se pudo cerrar sesion'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
