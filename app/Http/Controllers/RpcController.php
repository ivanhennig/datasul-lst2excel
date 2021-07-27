<?php

namespace App\Http\Controllers;

use App\Exceptions\EAuth;
use App\Exceptions\EValidation;
use Hennig\Builder\Jsonable;
use Hennig\Common\AllowGuest;
use Hennig\Common\AllowUser;
use Hennig\Common\ESimple;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RpcController extends Controller
{
    public function handleGroup(Request $request)
    {
        $calls = json_decode($request->getContent(), true);
        $response = [];
        foreach ($calls as $call) {
            $response[] = $this->handle($call['object'], $call['method'], $call['params']);
        }
        return $response;
    }

    /**
     * Handle the request
     *
     * @param $class
     * @param $method
     */
    public function handle($class, $method, $params = [])
    {
        $object = is_string($class) ? app()->make("\\App\\Http\\Controllers\\$class") : $class;
        try {
            $this->checkPermission($object);
            $result = call_user_func_array([$object, $method], $params);
            if ($result instanceof Jsonable) {
                $result = $result->toJson();
            }

            if ($result instanceof \Iterator) {
                $result = iterator_to_array($result);
            }

            return [
                'result' => $result,
                'error' => null
            ];
        } catch (ModelNotFoundException $exception) {
            $model_class = $exception->getModel();
            $info = $model_class::$label ?? $model_class;

            return [
                'result' => null,
                'error' => [
                    'message' => "Este registro ($info) não foi encontrado",
                ]
            ];
        } catch (ValidationException|EValidation $exception) {
            return [
                'result' => null,
                'error' => [
                    'message' => $exception->getMessage(),
                    'data' => $exception->errors()
                ]
            ];
        } catch (EAuth $exception) {
            $redirect = ['method' => 'redirectLogin', 'params' => []];
            $error = [
                'result' => null,
                'error' => [
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode()
                ]
            ];
            return [$redirect, $error];
        } catch (ESimple $exception) {
            return [
                'result' => null,
                'error' => [
                    'message' => $exception->getMessage()
                ]
            ];
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());

            return [
                'result' => null,
                'error' => [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTrace(),
                    'file' => $exception->getFile() . ':' . $exception->getLine()
                ]
            ];
        }
    }

    /**
     * @param $object
     * @param int $level
     * @throws ESimple
     */
    public function checkPermission($object, $level = 1)
    {
        $uses = class_uses($object) ?? [];
        if (in_array(AllowGuest::class, $uses)) {
            return;
        }

        if (!auth()->check()) {
            throw new EAuth();
        }

        if (in_array(AllowUser::class, $uses)) {
            return;
        }

        $user_level = auth()->user()->user_level;
        if (!$user_level) {
            throw new ESimple('Usuário precisa ser administrador');
        }

        if ($user_level < $level) {
            throw new ESimple('Usuário precisa uma permissão mais alta');
        }
    }

    public function handleSingle(Request $request, $class, $method, $params = [])
    {
        if ($request) {
            $method = $request->input('method', $method);
            $params = $request->input('params', $params);
        }

        return $this->handle($class, $method, $params);
    }
}
