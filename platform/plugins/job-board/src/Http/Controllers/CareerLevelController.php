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
use Botble\JobBoard\Forms\CareerLevelForm;
use Botble\JobBoard\Http\Requests\CareerLevelRequest;
use Botble\JobBoard\Models\CareerLevel;
use Botble\JobBoard\Tables\CareerLevelTable;
use Exception;
use Illuminate\Http\Request;

class CareerLevelController extends BaseController
{
    public function index(CareerLevelTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::career-level.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::career-level.create'));

        return $formBuilder->create(CareerLevelForm::class)->renderForm();
    }

    public function store(CareerLevelRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            CareerLevel::query()->where('id', '>', 0)->update(['is_default' => 0]);
        }

        $careerLevel = CareerLevel::query()->create($request->input());

        event(new CreatedContentEvent(CAREER_LEVEL_MODULE_SCREEN_NAME, $request, $careerLevel));

        return $response
            ->setPreviousUrl(route('career-levels.index'))
            ->setNextUrl(route('career-levels.edit', $careerLevel->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(CareerLevel $careerLevel, FormBuilder $formBuilder, Request $request)
    {
        event(new BeforeEditContentEvent($request, $careerLevel));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $careerLevel->name]));

        return $formBuilder->create(CareerLevelForm::class, ['model' => $careerLevel])->renderForm();
    }

    public function update(CareerLevel $careerLevel, CareerLevelRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            CareerLevel::query()->where('id', '!=', $careerLevel->getKey())->update(['is_default' => 0]);
        }

        $careerLevel->fill($request->input());
        $careerLevel->save();

        event(new UpdatedContentEvent(CAREER_LEVEL_MODULE_SCREEN_NAME, $request, $careerLevel));

        return $response
            ->setPreviousUrl(route('career-levels.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(CareerLevel $careerLevel, Request $request, BaseHttpResponse $response)
    {
        try {
            $careerLevel->delete();

            event(new DeletedContentEvent(CAREER_LEVEL_MODULE_SCREEN_NAME, $request, $careerLevel));

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
            $careerLevel = CareerLevel::query()->findOrFail($id);
            $careerLevel->delete();
            event(new DeletedContentEvent(CAREER_LEVEL_MODULE_SCREEN_NAME, $request, $careerLevel));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
