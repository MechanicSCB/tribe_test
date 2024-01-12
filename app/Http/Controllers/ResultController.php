<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use Inertia\ResponseFactory;

class ResultController extends Controller
{
    /**
     * Show top results list.
     *
     * @OA\Get(
     *      path="/api/top",
     *      operationId="GetTopList",
     *      description="The members top results list",
     *
     *      @OA\Response(response="200", description="The members top results list")
     *  )
     *
     * @OA\Info(title="Tribe test", description="Tribe test API", version="1.0")
     *
     * @param Request $request
     * @return array<mixed>
     */
    public function index(Request $request): array
    {
        // нужно ли реализовывать кэширование в каком-либо виде?
        $data['top'] = [];

        $validated = $request->validate([
            // TODO нужно ли проверять существование email в таблице members и валидность email с возвратом errors?
            'email' => 'nullable|email|exists:members,email',
        ]);

        $limit = config('results.top.limit'); // 10

        $selfEmail = @$validated['email'];

        // TODO вынести запросы и логику в отдельные методы/классы в зависимости от структуры проекта
        // Получаем значение лучшего результата игрока (self)
        $selfMember = $selfEmail ? Member::whereEmail(@$selfEmail)->first() : null;

        if ($bestTime = $selfMember?->bestTime) {
            $betterCount = DB::table('results')
                ->where('milliseconds', '<=', $bestTime - 1)
                ->whereNotNull('member_id')
                ->count(DB::raw('DISTINCT member_id'));

            $data['self'] = [
                'email' => $selfMember->email,
                'milliseconds' => $bestTime,
                'place' => $betterCount + 1,
            ];
        }

        $topResults = DB::table('results')
            ->whereNotNull('member_id')
            ->join('members', 'members.id', '=', 'results.member_id')
            ->selectRaw("min(milliseconds) as milliseconds, members.email, member_id")
            ->groupBy('member_id')
            ->orderBy('milliseconds')
            ->take($limit)
            ->get();

        foreach ($topResults as $key => $result) {
            $prev = @$data['top'][$key - 1];
            // сделал одинаковые места для игроков с одинаковым результатом (например place = 1,2,2,4 если второй результат имеют сразу два игрока)
            $place = $result->milliseconds === @$prev['milliseconds'] ? $prev['place'] : $key + 1;

            $data['top'][] = [
                // можно скрывать email и по-умнее, рассчитав длину строки до @, но я предположил, что это не принципиально
                'email' => substr_replace($result->email, '*****', 2, 5),
                'milliseconds' => $result->milliseconds,
                'place' => $place,
            ];
        }

        return ['data' => $data];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return array<mixed>
     */
    public function store(Request $request): array
    {
        $validated = $request->validate([
            // TODO проверять ли существование email в таблице members?
            'email' => 'nullable|email|exists:members,email',
            'milliseconds' => 'required|numeric|min:0',
        ]);

        $input = ['milliseconds' => @$validated['milliseconds']];

        if (@$validated['email']) {
            $input['member_id'] = Member::whereEmail($validated['email'])->first()?->id;
        }

        return Result::query()->create($input)->toArray();
    }
}
