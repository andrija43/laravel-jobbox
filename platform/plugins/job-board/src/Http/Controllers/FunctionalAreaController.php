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
use Botble\JobBoard\Forms\FunctionalAreaForm;
use Botble\JobBoard\Http\Requests\FunctionalAreaRequest;
use Botble\JobBoard\Models\FunctionalArea;
use Botble\JobBoard\Tables\FunctionalAreaTable;
use Exception;
use Illuminate\Http\Request;

class FunctionalAreaController extends BaseController
{
    public function index(FunctionalAreaTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::functional-area.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::functional-area.create'));

        return $formBuilder->create(FunctionalAreaForm::class)->renderForm();
    }

    public function store(FunctionalAreaRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            FunctionalArea::query()->where('id', '>', 0)->update(['is_default' => 0]);
        }

        $functionalArea = FunctionalArea::query()->create($request->input());

        event(new CreatedContentEvent(FUNCTIONAL_AREA_MODULE_SCREEN_NAME, $request, $functionalArea));

        return $response
            ->setPreviousUrl(route('functional-areas.index'))
            ->setNextUrl(route('functional-areas.edit', $functionalArea->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(FunctionalArea $functionalArea, FormBuilder $formBuilder, Request $request)
    {
        event(new BeforeEditContentEvent($request, $functionalArea));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $functionalArea->name]));

        return $formBuilder->create(FunctionalAreaForm::class, ['model' => $functionalArea])->renderForm();
    }

    public function update(FunctionalArea $functionalArea, FunctionalAreaRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            FunctionalArea::query()->where('id', '!=', $functionalArea->getKey())->update(['is_default' => 0]);
        }

        $functionalArea->fill($request->input());
        $functionalArea->save();

        event(new UpdatedContentEvent(FUNCTIONAL_AREA_MODULE_SCREEN_NAME, $request, $functionalArea));

        return $response
            ->setPreviousUrl(route('functional-areas.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(FunctionalArea $functionalArea, Request $request, BaseHttpResponse $response)
    {
        try {
            $functionalArea->delete();

            event(new DeletedContentEvent(FUNCTIONAL_AREA_MODULE_SCREEN_NAME, $request, $functionalArea));

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
            $functionalArea = FunctionalArea::query()->findOrFail($id);
            $functionalArea->delete();
            event(new DeletedContentEvent(FUNCTIONAL_AREA_MODULE_SCREEN_NAME, $request, $functionalArea));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
