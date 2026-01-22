<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class VisaMobileType extends AbstractType
{
    private const string NAME = 'oro_tpay_visa_mobile_phone';

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
                'phone',
                TelType::class,
                [
                    'mapped' => false,
                    'required' => true,
                    'label' => 'macopedia.tpay.frontend.labels.visa_mobile.phone',
                    'attr' => [
                        'data-visa-mobile-phone' => true,
                        'data-error-msg' => $this->translator->trans('macopedia.tpay.frontend.errors.required'),
                        'placeholder' => false,
                        'minlength' => 7,
                        'maxlength' => 15,
                    ]
                ],
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'macopedia.tpay.methods.visa_mobile',
            'csrf_protection' => false,
        ]);
    }

    public function getName(): string
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
}
