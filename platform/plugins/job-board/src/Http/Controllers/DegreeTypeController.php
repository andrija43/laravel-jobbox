<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Forms\DegreeTypeForm;
use Botble\JobBoard\Http\Requests\DegreeTypeRequest;
use Botble\JobBoard\Models\DegreeType;
use Botble\JobBoard\Tables\DegreeTypeTable;
use Exception;
use Illuminate\Http\Request;

class DegreeTypeController extends BaseController
{
    public function index(DegreeTypeTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::degree-type.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::degree-type.create'));

        return $formBuilder->create(DegreeTypeForm::class)->renderForm();
    }

    public function store(DegreeTypeRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            DegreeType::query()->where('id', '>', 0)->update(['is_default' => 0]);
        }

        $degreeType = DegreeType::query()->create($request->input());

        event(new CreatedContentEvent(DEGREE_TYPE_MODULE_SCREEN_NAME, $request, $degreeType));

        return $response
            ->setPreviousUrl(route('degree-types.index'))
            ->setNextUrl(route('degree-types.edit', $degreeType->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(DegreeType $degreeType, FormBuilder $formBuilder, Request $request)
    {
        event(new BeforeEditContentEvent($request, $degreeType));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $degreeType->name]));

        return $formBuilder->create(DegreeTypeForm::class, ['model' => $degreeType])->renderForm();
    }

    public function update(DegreeType $degreeType, DegreeTypeRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            DegreeType::query()->where('id', '!=', $degreeType->getKey())->update(['is_default' => 0]);
        }

        $degreeType->fill($request->input());
        $degreeType->save();

        event(new UpdatedContentEvent(DEGREE_TYPE_MODULE_SCREEN_NAME, $request, $degreeType));

        return $response
            ->setPreviousUrl(route('degree-types.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(DegreeType $degreeType, Request $request, BaseHttpResponse $response)
    {
        try {
            $degreeType->delete();

            event(new DeletedContentEvent(DEGREE_TYPE_MODULE_SCREEN_NAME, $request, $degreeType));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function deletes(Request $request, BaseHttpResponse $response)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        foreach ($ids as $id) {
            $degreeType = DegreeType::query()->findOrFail($id);
            $degreeType->delete();
            event(new DeletedContentEvent(DEGREE_TYPE_MODULE_SCREEN_NAME, $request, $degreeType));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
