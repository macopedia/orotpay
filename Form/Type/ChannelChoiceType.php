<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ChannelChoiceType extends AbstractType
{
    private const string NAME = 'oro_tpay_channel_pbl';

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'channelId',
                ChoiceType::class,
                [
                    'mapped' => false,
                    'expanded' => true,
                    'multiple' => false,
                    'required' => true,
                    'choices' => $options['channels'],
                    'label' => 'macopedia.tpay.frontend.labels.blik.token',
                    'attr' => [
                        'data-channelId' => true,
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
            'label' => 'macopedia.tpay.methods.pbl',
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
