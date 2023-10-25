<?php

namespace Botble\JobBoard\Forms\Fronts;

use Botble\Base\Forms\FormAbstract;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Forms\Fields\CustomEditorField;
use Botble\JobBoard\Forms\Fields\CustomImageField;
use Botble\JobBoard\Http\Requests\CompanyRequest;
use Botble\JobBoard\Models\Company;

class CompanyForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new Company())
            ->setValidatorClass(CompanyRequest::class)
            ->withCustomFields()
            ->setFormOption('enctype', 'multipart/form-data')
            ->addCustomField('customEditor', CustomEditorField::class)
            ->addCustomField('customImage', CustomImageField::class)
            ->setFormOption('template', JobBoardHelper::viewPath('dashboard.forms.base'))
            ->add('name', 'text', [
                'label' => __('Company Name'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('description', 'textarea', [
                'label' => trans('core/base::forms.description'),
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('core/base::forms.description_placeholder'),
                    'data-counter' => 350,
                ],
            ])
            ->add('content', 'customEditor', [
                'label' => trans('core/base::forms.content'),
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('core/base::forms.description_placeholder'),
                ],
            ])
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('ceo', 'text', [
                'label' => __('Company CEO / President'),
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'placeholder' => __('Company CEO / President'),
                    'data-counter' => 120,
                ],
            ])
            ->add('email', 'email', [
                'label' => __('Email'),
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'placeholder' => __('Ex: contact@your-company.com'),
                    'data-counter' => 120,
                ],
            ])
            ->add('phone', 'text', [
                'label' => __('Phone'),
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'placeholder' => __('Phone number, will be public'),
                    'data-counter' => 30,
                ],
            ])
            ->add('website', 'text', [
                'label' => __('Website'),
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'placeholder' => __('https://'),
                    'data-counter' => 120,
                ],
            ])
            ->add('rowClose1', 'html', [
                'html' => '</div>',
            ])
            ->add('rowOpen2', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('year_founded', 'number', [
                'label' => __('Year founded'),
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'placeholder' => __('Ex: 1987'),
                    'data-counter' => 10,
                ],
            ])
            ->add('number_of_offices', 'number', [
                'label' => __('Number of offices'),
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'placeholder' => __('Ex: 3'),
                    'data-counter' => 10,
                ],
            ])
            ->add('number_of_employees', 'number', [
                'label' => __('Number of employees'),
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'placeholder' => __('Ex: 100-250'),
                    'data-counter' => 10,
                ],
            ])
            ->add('annual_revenue', 'text', [
                'label' => __('Annual revenue'),
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'placeholder' => __('Ex: 2M'),
                    'data-counter' => 10,
                ],
            ])
            ->add('rowClose2', 'html', [
                'html' => '</div>',
            ]);

        if (is_plugin_active('location')) {
            $this->add('location', 'selectLocation', [
                'wrapper' => [
                    'class' => 'form-group mb-0 col-sm-4',
                ],
                'wrapperClassName' => 'row g-1',
            ]);
        }

        $this
            ->add('rowOpen3', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('address', 'text', [
                'label' => __('Address'),
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'attr' => [
                    'placeholder' => __('Address'),
                    'data-counter' => 120,
                ],
            ])
            ->add('postal_code', 'text', [
                'label' => __('Postal code'),
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'attr' => [
                    'placeholder' => __('Postal code'),
                    'data-counter' => 20,
                ],
            ])
            ->add('rowClose3', 'html', [
                'html' => '</div>',
            ])
            ->add('rowOpen4', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('latitude', 'text', [
                'label' => __('Latitude'),
                'wrapper' => [
                    'class' => 'form-group mb-3 col-md-6',
                ],
                'attr' => [
                    'placeholder' => 'Ex: 1.462260',
                    'data-counter' => 25,
                ],
                'help_block' => [
                    'tag' => 'a',
                    'text' => __('Go here to get Latitude from address.'),
                    'attr' => [
                        'href' => 'https://www.latlong.net/convert-address-to-lat-long.html',
                        'target' => '_blank',
                        'rel' => 'nofollow',
                    ],
                ],
            ])
            ->add('longitude', 'text', [
                'label' => __('Longitude'),
                'wrapper' => [
                    'class' => 'form-group mb-3 col-md-6',
                ],
                'attr' => [
                    'placeholder' => 'Ex: 103.812530',
                    'data-counter' => 25,
                ],
                'help_block' => [
                    'tag' => 'a',
                    'text' => __('Go here to get Longitude from address.'),
                    'attr' => [
                        'href' => 'https://www.latlong.net/convert-address-to-lat-long.html',
                        'target' => '_blank',
                        'rel' => 'nofollow',
                    ],
                ],
            ])
            ->add('rowClose4', 'html', [
                'html' => '</div>',
            ])
            ->add('logo', 'customImage', [
                'label' => __('Logo (~500x500)'),
            ])
            ->add('cover_image', 'customImage', [
                'label' => __('Cover Image (~1920x300)'),
            ])
            ->setBreakFieldPoint('logo')
            ->addMetaBoxes([
                'social_links' => [
                    'title' => __('Social links'),
                    'content' => view(
                        JobBoardHelper::viewPath('dashboard.forms.social-links'),
                        ['company' => $this->getModel()]
                    )->render(),
                ],
            ]);
    }
}
