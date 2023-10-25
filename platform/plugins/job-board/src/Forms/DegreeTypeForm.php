<?php

namespace Botble\JobBoard\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FormAbstract;
use Botble\JobBoard\Http\Requests\DegreeTypeRequest;
use Botble\JobBoard\Models\DegreeLevel;
use Botble\JobBoard\Models\DegreeType;

class DegreeTypeForm extends FormAbstract
{
    public function buildForm(): void
    {
        $degreeLevels = DegreeLevel::query()->pluck('name', 'id')->all();

        $this
            ->setupModel(new DegreeType())
            ->setValidatorClass(DegreeTypeRequest::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('degree_level_id', 'customSelect', [
                'label' => trans('plugins/job-board::degree-type.degree-level'),
                'required' => true,
                'attr' => [
                    'class' => 'form-control select-search-full',
                ],
                'choices' => $degreeLevels,
            ])
            ->add('order', 'number', [
                'label' => trans('core/base::forms.order'),
                'attr' => [
                    'placeholder' => trans('core/base::forms.order_by_placeholder'),
                ],
                'default_value' => 0,
            ])
            ->add('is_default', 'onOff', [
                'label' => trans('core/base::forms.is_default'),
                'default_value' => false,
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'required' => true,
                'choices' => BaseStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
