<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

use function date;
use function range;
use function sprintf;

final class CreditCardType extends AbstractType
{
    private const int MAX_VALIDITY_IN_YEARS = 10;
    private const string NAME = 'oro_tpay_credit_card';

    public function __construct(protected TranslatorInterface $translator)
    {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'number',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'macopedia.tpay.frontend.labels.card.number',
                    'required' => true,
                    'attr' => [
                        'data-card-number' => true,
                        'data-error-msg' => $this->translator->trans('macopedia.tpay.frontend.errors.required'),
                        'autocomplete' => 'cc-number',
                        'placeholder' => false,
                    ]
                ],
            )
            ->add(
                'expiration_date_month',
                ChoiceType::class,
                [
                    'mapped' => false,
                    'label' => 'macopedia.tpay.frontend.labels.card.expiration_date.month',
                    'placeholder' => 'macopedia.tpay.frontend.labels.card.expiration_date.month',
                    'choices' => $this->getMonthsRange(),
                    'attr' => [
                        'data-expiration-date-month' => true,
                        'data-error-msg' => $this->translator->trans('macopedia.tpay.frontend.errors.required'),
                    ],
                    'required' => true,
                ],
            )
            ->add(
                'expiration_date_year',
                ChoiceType::class,
                [
                    'mapped' => false,
                    'label' => 'macopedia.tpay.frontend.labels.card.expiration_date.year',
                    'placeholder' => 'macopedia.tpay.frontend.labels.card.expiration_date.year',
                    'choices' => $this->getCardValidYearsRange(),
                    'required' => true,
                    'attr' => [
                        'data-expiration-date-year' => true,
                        'data-error-msg' => $this->translator->trans('macopedia.tpay.frontend.errors.required'),
                    ]
                ],
            )
            ->add(
                'cvv',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'macopedia.tpay.frontend.labels.card.cvv',
                    'required' => true,
                    'attr' => [
                        'data-card-cvv' => true,
                        'data-error-msg' => $this->translator->trans('macopedia.tpay.frontend.errors.required'),
                        'maxlength' => 3,
                    ],
                ],
            )
            ->add(
                'save_for_later',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'macopedia.tpay.frontend.labels.card.save_for_later',
                    'mapped' => false,
                    'data' => true,
                    'attr' => [
                        'data-save-for-later' => true,
                    ],
                ]
            )
            ->add(
                'expiration_date',
                HiddenType::class,
                [
                    'required' => true,
                    'label' => 'macopedia.tpay.frontend.labels.card.expiration_date.full',
                    'attr' => ['data-expiration-date' => true]
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'macopedia.tpay.methods.credit_card',
            'csrf_protection' => false,
        ]);
    }

    private function getCardValidYearsRange(): array
    {
        $result = [];
        $currentYear = (int)date('Y');

        foreach (range($currentYear, $currentYear + self::MAX_VALIDITY_IN_YEARS) as $year) {
            $result[$year] = $year;
        }

        return $result;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    /**
     * @return array<string, string>
     */
    private function getMonthsRange(): array
    {
        $months = [];

        foreach (range(1, 12) as $value) {
            $month = sprintf('%02d', $value);
            $months[$month] = $month;
        }

        return $months;
    }
}
