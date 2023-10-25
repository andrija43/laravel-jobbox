<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Http\Requests\CreateTransactionRequest;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionController extends BaseController
{
    public function postCreate(int|string $id, CreateTransactionRequest $request, BaseHttpResponse $response)
    {
        $account = Account::query()->findOrFail($id);

        $request->merge([
            'user_id' => Auth::user()->getKey(),
            'account_id' => $id,
        ]);

        Transaction::query()->create($request->input());

        $account->credits += $request->input('credits');
        $account->save();

        return $response
            ->setMessage(trans('core/base::notices.create_success_message'));
    }
}
