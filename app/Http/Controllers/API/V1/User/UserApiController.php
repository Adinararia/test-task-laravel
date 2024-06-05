<?php

namespace App\Http\Controllers\API\V1\User;

use App\Events\Token\SetWasUsedTokenEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserCreateRequest;
use App\Http\Requests\User\UserIndexRequest;
use App\Http\Resources\User\UserIndexResource;
use App\Http\Resources\User\UserShowResource;
use App\Models\User;
use App\Services\TinyPngService;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserApiController extends Controller
{
    /**
     * Default view length for the list users
     */
    private const DEFAULT_LIST_COUNT = 6;

    /**
     * @param $id
     * @return UserShowResource|JsonResponse
     */
    public function show($id): JsonResponse|UserShowResource
    {
        $validator = $this->customValidate($id);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => "The user with the requested id does not exist",
                    'fails'   => $validator->errors(),
                ],
                400
            );
        }
        // для одного использования данного запроса не стал создавать репозиторий, в правильном варианте тут должно быть обращение к репозиторию
        $user = $this->getUserWithPositionById($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ]);
        }

        return response()->json(['success' => true, 'user' => new UserIndexResource($user)]);
    }


    /**
     * @param UserIndexRequest $request
     * @return JsonResponse
     */
    public function index(UserIndexRequest $request)
    {
        $count = $request->get('count') ? $request->get('count') : self::DEFAULT_LIST_COUNT;
        $page  = $request->get('page') ? $request->get('page') : 1;

        // аналогично этот запрос должен быть в репозитории
        $users = $this->getUsersWithPaginate($count, $page);

        return response()->json(
            [
                'success'     => true,
                'page'        => $page,
                'total_pages' => $users->lastPage(),
                'total_users' => $users->total(),
                'count'       => $count,
                'links'       => [
                    'next_url' => $this->customPageUrl($users->nextPageUrl(), $count),
                    'prev_url' => $this->customPageUrl($users->previousPageUrl(), $count),
                ],
                'users'       => UserIndexResource::collection($users)
            ]
        );
    }

    /**
     * @param UserCreateRequest $request
     * @return JsonResponse
     */
    public function create(UserCreateRequest $request): JsonResponse
    {
        $file           = $request->file('photo');
        $token          = $request->bearerToken();
        $tinyPngService = new TinyPngService();
        $fileName       = $tinyPngService->optimizeImage($file);

        if (is_null($fileName)) {
            return response()->json(
                ['success' => false, 'message' => 'User is not created, optimize image has error'],
                500
            );
        }

        $user = User::query()->create([
            'name'        => $request->get('name'),
            'phone'       => $request->get('phone'),
            'email'       => $request->get('email'),
            'password'    => Hash::make($request->get('email')),
            'position_id' => $request->get('position_id'),
            'photo'       => $fileName,
        ]);

        event(new SetWasUsedTokenEvent($token));

        return response()->json(
            ['success' => true, 'user_id' => $user->id, 'message' => "New user successfully registered"]
        );
    }

    /**
     * @param $id
     * @return \Illuminate\Validation\Validator
     */
    private function customValidate($id)
    {
        return Validator::make(['userId' => $id], [
            'userId' => 'required|integer',
        ], [
            'userId.integer'  => 'The userId must be an integer.',
            'userId.required' => 'The userId required.',
        ]);
    }

    /**
     * @param $id
     * @return User|null
     */
    private function getUserWithPositionById($id): ?User
    {
        return User::query()->select(['id', 'name', 'photo', 'email', 'phone', 'position_id'])->with([
            'position' => function ($q) {
                $q->select('id', 'name');
            }
        ])->where('id', $id)->first();
    }

    /**
     * @param $count
     * @param $page
     * @return LengthAwarePaginator
     */
    private function getUsersWithPaginate($count, $page): LengthAwarePaginator
    {
        return User::query()->select(['id', 'name', 'photo', 'email', 'phone', 'position_id', 'created_at'])->with([
            'position' => function ($q) {
                $q->select('id', 'name');
            }
        ])->orderByDesc('id')->paginate(perPage: $count, page: $page);
    }

    /**
     * @param $pageUrl
     * @param $count
     * @return string|null
     */
    public function customPageUrl($pageUrl, $count): ?string
    {
        if (!empty($pageUrl) && $count != self::DEFAULT_LIST_COUNT) {
            $pageUrl = $pageUrl . '&count=' . $count;
        }
        return $pageUrl;
    }
}
