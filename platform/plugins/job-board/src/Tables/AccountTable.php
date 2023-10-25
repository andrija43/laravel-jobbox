<?php

namespace Botble\JobBoard\Tables;

use Botble\Base\Facades\Html;
use Botble\JobBoard\Enums\AccountTypeEnum;
use Botble\JobBoard\Models\Account;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\EnumColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class AccountTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Account::class)
            ->addActions([
                EditAction::make()->route('accounts.edit'),
                DeleteAction::make()->route('accounts.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('first_name', function (Account $item) {
                if (! $this->hasPermission('accounts.edit')) {
                    return $item->name;
                }

                return Html::link(route('accounts.edit', $item->getKey()), $item->name);
            })
            ->editColumn('phone', function (Account $item) {
                return $item->phone ?: '&mdash;';
            })
            ->editColumn('updated_at', function (Account $item) {
                $html = '';
                foreach ($item->companies as $key => $company) {
                    if (! $company->id) {
                        continue;
                    }

                    $html .= Html::link(route('companies.edit', $company->id), $company->name);
                    if ($key < count($item->companies) - 1) {
                        $html .= ', ';
                    }
                }

                return $html ?: '&mdash;';
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'type',
                'updated_at',
                'created_at',
            ])
            ->with('companies');

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            Column::make('first_name')
                ->title(trans('core/base::tables.name'))
                ->alignLeft(),
            Column::make('email')
                ->title(trans('core/base::tables.email'))
                ->alignLeft(),
            Column::make('phone')
                ->title(trans('plugins/job-board::job-application.tables.phone'))
                ->alignLeft(),
            EnumColumn::make('type')
                ->title(trans('plugins/job-board::account.type'))
                ->alignLeft(),
            Column::make('updated_at')
                ->title(__('Company'))
                ->alignLeft(),
            CreatedAtColumn::make(),
        ];
    }

    public function buttons(): array
    {
        $buttons = $this->addCreateButton(route('accounts.create'), 'accounts.create');

        if ($this->hasPermission('accounts.import')) {
            $buttons['import'] = [
                'link' => route('accounts.import'),
                'text' => '<i class="fas fa-cloud-upload-alt"></i> ' . trans('plugins/job-board::account.import.name'),
                'class' => 'btn-warning',
            ];
        }

        if ($this->hasPermission('accounts.export')) {
            $buttons['export'] = [
                'link' => route('accounts.export'),
                'text' => '<i class="fas fa-cloud-download-alt"></i> ' . trans(
                    'plugins/job-board::account.export.name'
                ),
                'class' => 'btn-warning',
            ];
        }

        return $buttons;
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('accounts.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'first_name' => [
                'title' => __('First name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'last_name' => [
                'title' => __('Last name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'email' => [
                'title' => trans('core/base::tables.email'),
                'type' => 'text',
                'validate' => 'required|max:120|email',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
            'type' => [
                'title' => __('Type'),
                'type' => 'select',
                'choices' => AccountTypeEnum::labels(),
                'validate' => 'required|in:' . implode(',', AccountTypeEnum::values()),
            ],
        ];
    }
}
