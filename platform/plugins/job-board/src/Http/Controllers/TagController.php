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
use Botble\JobBoard\Forms\TagForm;
use Botble\JobBoard\Http\Requests\TagRequest;
use Botble\JobBoard\Models\Tag;
use Botble\JobBoard\Tables\TagTable;
use Exception;
use Illuminate\Http\Request;

class TagController extends BaseController
{
    public function index(TagTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::tag.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::tag.create'));

        return $formBuilder->create(TagForm::class)->renderForm();
    }

    public function store(TagRequest $request, BaseHttpResponse $response)
    {
        $tag = Tag::query()->create($request->input());

        event(new CreatedContentEvent(JOB_BOARD_TAG_MODULE_SCREEN_NAME, $request, $tag));

        return $response
            ->setPreviousUrl(route('job-board.tag.index'))
            ->setNextUrl(route('job-board.tag.edit', $tag->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Tag $tag, FormBuilder $formBuilder, Request $request)
    {
        event(new BeforeEditContentEvent($request, $tag));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $tag->name]));

        return $formBuilder->create(TagForm::class, ['model' => $tag])->renderForm();
    }

    public function update(Tag $tag, TagRequest $request, BaseHttpResponse $response)
    {
        $tag->fill($request->input());
        $tag->save();

        event(new UpdatedContentEvent(JOB_BOARD_TAG_MODULE_SCREEN_NAME, $request, $tag));

        return $response
            ->setPreviousUrl(route('job-board.tag.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Tag $tag, Request $request, BaseHttpResponse $response)
    {
        try {
            $tag->delete();

            event(new DeletedContentEvent(JOB_BOARD_TAG_MODULE_SCREEN_NAME, $request, $tag));

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
            $tag = Tag::query()->findOrFail($id);
            $tag->delete();
            event(new DeletedContentEvent(JOB_BOARD_TAG_MODULE_SCREEN_NAME, $request, $tag));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }

    public function getAllTags()
    {
        return Tag::query()->pluck('name')->all();
    }
}
