<?php

namespace Botble\Team\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Team\Forms\TeamForm;
use Botble\Team\Http\Requests\TeamRequest;
use Botble\Team\Models\Team;
use Botble\Team\Tables\TeamTable;
use Exception;
use Illuminate\Http\Request;

class TeamController extends BaseController
{
    public function index(TeamTable $table)
    {
        PageTitle::setTitle(trans('plugins/team::team.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/team::team.create'));

        return $formBuilder->create(TeamForm::class)->renderForm();
    }

    public function store(TeamRequest $request, BaseHttpResponse $response)
    {
        $team = Team::query()->create($request->validated());

        event(new CreatedContentEvent(TEAM_MODULE_SCREEN_NAME, $request, $team));

        return $response
            ->setPreviousUrl(route('team.index'))
            ->setNextUrl(route('team.edit', $team->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Team $team, FormBuilder $formBuilder, Request $request)
    {
        event(new BeforeEditContentEvent($request, $team));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $team->name]));

        return $formBuilder->create(TeamForm::class, ['model' => $team])->renderForm();
    }

    public function update(Team $team, TeamRequest $request, BaseHttpResponse $response)
    {
        $team->update($request->validated());

        event(new UpdatedContentEvent(TEAM_MODULE_SCREEN_NAME, $request, $team));

        return $response
            ->setPreviousUrl(route('team.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Team $team, Request $request, BaseHttpResponse $response)
    {
        try {
            $team->delete();

            event(new DeletedContentEvent(TEAM_MODULE_SCREEN_NAME, $request, $team));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
