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
     * @param Request $request
     * @return array<mixed>
     */

    public function index(Request $request): array
    {
        $validated = request()->validate([
            'email' => 'nullable|email|exists:members,email',
        ]);

        $limit = config('results.top.limit') ?? 10;
        $selfEmail = @$validated['email'];

        // if self member has no results, then skip self fetching
        if ($selfEmail && !Member::whereEmail(@$selfEmail)->has('results')->count()) {
            $selfEmail = null;
        }

        // сразу получаем рейтинги всех пользователей, чтобы не запрашивать дополнительно данные для self
        $query = DB::table('results')
            ->whereNotNull('member_id')
            ->join('members', 'members.id', '=', 'results.member_id')
            ->selectRaw("min(milliseconds) as milliseconds, members.email, member_id")
            ->groupBy('member_id')
            ->orderBy('milliseconds');

        // ускоряем запрос выбирая только первые записи, если не требуется определять место для self
        if (!$selfEmail) {
            $query->take($limit);
        }

        $topResults = $query->get();

        $data = [];

        foreach ($topResults as $key => $result) {
            if ($result->email === $selfEmail) {
                $data['self'] = [
                    'email' => $result->email,
                    'milliseconds' => $result->milliseconds,
                    'place' => $key + 1,
                ];

                if ($key >= $limit) {
                    break;
                }
            }

            // Прекращаем заполнять top свыше лимита
            if ($key >= $limit) {
                continue;
            }

            $data['top'][] = [
                'email' => substr_replace($result->email, '*****', 2, 5),
                'milliseconds' => $result->milliseconds,
                'place' => $key + 1,
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
