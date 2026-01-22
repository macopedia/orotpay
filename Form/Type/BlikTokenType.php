<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class BlikTokenType extends AbstractType
{
    private const string NAME = 'oro_tpay_blik_token';
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
                'token',
                TelType::class,
                [
                    'mapped' => false,
                    'required' => true,
                    'label' => 'macopedia.tpay.frontend.labels.blik.token',
                    'attr' => [
                        'data-blik-token' => true,
                        'data-error-msg' => $this->translator->trans('macopedia.tpay.frontend.errors.required'),
                        'placeholder' => false,
                        'minlength' => 6,
                        'maxlength' => 6,
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
            'label' => 'macopedia.tpay.methods.blik',
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
