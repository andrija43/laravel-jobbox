<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Traits\HasDeleteManyItemsTrait;
use Botble\JobBoard\Enums\AccountTypeEnum;
use Botble\JobBoard\Forms\AccountForm;
use Botble\JobBoard\Http\Requests\AccountCreateRequest;
use Botble\JobBoard\Http\Requests\AccountEditRequest;
use Botble\JobBoard\Http\Resources\AccountResource;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Tables\AccountTable;
use Botble\Media\Models\MediaFile;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountController extends BaseController
{
    use HasDeleteManyItemsTrait;

    public function index(AccountTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/job-board::account.name'));

        return $dataTable->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::account.create'));

        return $formBuilder
            ->create(AccountForm::class)
            ->remove('is_change_password')
            ->renderForm();
    }

    public function store(AccountCreateRequest $request, BaseHttpResponse $response)
    {
        $request->merge(['password' => Hash::make($request->input('password'))]);

        $account = new Account();
        $account->fill($request->input());
        $account->is_featured = $request->input('is_featured', false);
        $account->confirmed_at = Carbon::now();

        if ($request->input('avatar_image')) {
            $image = MediaFile::query()
                ->where('url', $request->input('avatar_image'))
                ->first();

            if ($image) {
                $account->avatar_id = $image->id;
            }
        }

        $account->save();

        event(new CreatedContentEvent(ACCOUNT_MODULE_SCREEN_NAME, $request, $account));

        return $response
            ->setPreviousUrl(route('accounts.index'))
            ->setNextUrl(route('accounts.edit', $account->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Account $account, Request $request, FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $account->name]));

        $account->password = null;

        event(new BeforeEditContentEvent($request, $account));

        return $formBuilder
            ->create(AccountForm::class, ['model' => $account])
            ->renderForm();
    }

    public function update(Account $account, AccountEditRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_change_password') == 1) {
            $request->merge(['password' => Hash::make($request->input('password'))]);
            $data = $request->input();
        } else {
            $data = $request->except('password');
        }

        $account->fill($data);
        $account->is_featured = $request->input('is_featured', false);

        if ($request->input('avatar_image')) {
            $imageId = MediaFile::query()
                ->where('url', $request->input('avatar_image'))
                ->value('id');

            if ($imageId) {
                $account->avatar_id = $imageId;
            }
        }

        $account->save();

        event(new UpdatedContentEvent(ACCOUNT_MODULE_SCREEN_NAME, $request, $account));

        return $response
            ->setPreviousUrl(route('accounts.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Account $account, Request $request, BaseHttpResponse $response)
    {
        try {
            $account->delete();
            event(new DeletedContentEvent(ACCOUNT_MODULE_SCREEN_NAME, $request, $account));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function deletes(Request $request, BaseHttpResponse $response)
    {
        return $this->executeDeleteItems($request, $response, new Account(), ACCOUNT_MODULE_SCREEN_NAME);
    }

    public function getList(Request $request, BaseHttpResponse $response)
    {
        $keyword = BaseHelper::stringify($request->input('q'));

        if (! $keyword) {
            return $response->setData([]);
        }

        $data = Account::query()
            ->where('jb_accounts.type', AccountTypeEnum::EMPLOYER)
            ->where('jb_accounts.first_name', 'LIKE', '%' . $keyword . '%')
            ->orWhere('jb_accounts.last_name', 'LIKE', '%' . $keyword . '%')
            ->select(['jb_accounts.id', 'jb_accounts.first_name', 'jb_accounts.last_name', 'jb_accounts.email'])
            ->take(10)
            ->get();

        return $response->setData(AccountResource::collection($data));
    }

    public function getAllEmployers()
    {
        return Account::query()
            ->select(DB::raw('CONCAT(jb_accounts.first_name, " ", jb_accounts.last_name) as name'))
            ->where('type', AccountTypeEnum::EMPLOYER)
            ->pluck('name')
            ->all();
    }
}
