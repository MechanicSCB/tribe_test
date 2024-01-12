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
        //
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
