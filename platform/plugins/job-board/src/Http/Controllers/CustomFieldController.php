<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\ACL\Models\User;
use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Forms\CustomFieldForm;
use Botble\JobBoard\Http\Requests\CustomFieldRequest;
use Botble\JobBoard\Http\Resources\CustomFieldResource;
use Botble\JobBoard\Models\CustomField;
use Botble\JobBoard\Repositories\Interfaces\CustomFieldInterface;
use Botble\JobBoard\Tables\CustomFieldTable;
use Closure;
use Exception;
use Illuminate\Http\Request;

class CustomFieldController extends BaseController
{
    public function __construct(protected CustomFieldInterface $customFieldRepository)
    {
        $this->middleware(function (Request $request, Closure $next) {
            if (! JobBoardHelper::isEnabledCustomFields()) {
                abort(404);
            }

            return $next($request);
        });
    }

    public function index(CustomFieldTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::custom-fields.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::custom-fields.create'));

        return $formBuilder->create(CustomFieldForm::class)->renderForm();
    }

    public function store(CustomFieldRequest $request, BaseHttpResponse $response)
    {
        $customField = new CustomField();
        $customField->fill($request->validated());
        $customField->authorable_type = User::class;
        $customField->authorable_id = auth()->id();
        $customField->save();

        $customField->saveRelations($request->validated());

        event(new CreatedContentEvent(JOB_BOARD_CUSTOM_FIELD_MODULE_SCREEN_NAME, $request, $customField));

        return $response
            ->setPreviousUrl(route('job-board.custom-fields.index'))
            ->setNextUrl(route('job-board.custom-fields.edit', $customField->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request)
    {
        $customField = CustomField::query()->with(['options'])->findOrFail($id);

        event(new BeforeEditContentEvent($request, $customField));

        PageTitle::setTitle(trans('plugins/job-board::custom-fields.edit', ['name' => $customField->name]));

        return $formBuilder->create(CustomFieldForm::class, ['model' => $customField])->renderForm();
    }

    public function update(int|string $id, CustomFieldRequest $request, BaseHttpResponse $response)
    {
        $customField = CustomField::query()->findOrFail($id);

        $customField->fill($request->validated());
        $customField->save();

        $customField->saveRelations($request->validated());

        event(new UpdatedContentEvent(JOB_BOARD_CUSTOM_FIELD_MODULE_SCREEN_NAME, $request, $customField));

        return $response
            ->setPreviousUrl(route('job-board.custom-fields.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $customField = CustomField::query()->findOrFail($id);

            $customField->delete();

            event(new DeletedContentEvent(JOB_BOARD_CUSTOM_FIELD_MODULE_SCREEN_NAME, $request, $customField));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function getInfo(Request $request)
    {
        $customField = CustomField::query()->with(['options'])->findOrFail($request->input('id'), );

        return new CustomFieldResource($customField);
    }
}
