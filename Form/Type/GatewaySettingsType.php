<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Form\Type;

use Macopedia\Bundle\TpayBundle\Entity\GatewaySettings;
use Macopedia\Bundle\TpayBundle\Method\Config\Factory\TpayConfigFactoryInterface;
use Macopedia\Bundle\TpayBundle\Tpay\GatewayFactoryInterface;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use RuntimeException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class GatewaySettingsType extends AbstractType
{
    public const string BLOCK_PREFIX = 'gateway_settings';

    public function __construct(
        protected $logger,
        protected TranslatorInterface $translator,
        protected TpayConfigFactoryInterface $configFactory,
        protected GatewayFactoryInterface $gatewayFactory
    ) {
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'client_id',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank(allowNull: false),
                    ],
                    'empty_data' => '',
                    'label' => 'macopedia.tpay.settings.gateway_configuration.client_id',
                ],
            )
            ->add(
                'client_secret',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank(allowNull: false),
                    ],
                    'empty_data' => '',
                    'label' => 'macopedia.tpay.settings.gateway_configuration.client_secret',
                ],
            )
            ->add(
                'merchant_id',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank(allowNull: false),
                    ],
                    'empty_data' => '',
                    'label' => 'macopedia.tpay.settings.gateway_configuration.merchant_id',
                ],
            )
            ->add(
                'notification_security_code',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank(allowNull: false),
                    ],
                    'empty_data' => '',
                    'label' => 'macopedia.tpay.settings.gateway_configuration.notification_security_code',
                ],
            )
            ->add(
                'merchant_rsa_key',
                TextareaType::class,
                [
                    'constraints' => [
                    ],
                    'required' => false,
                    'empty_data' => '',
                    'label' => 'macopedia.tpay.settings.gateway_configuration.merchant_rsa_key',
                ],
            )
            ->add(
                'google_merchant_id',
                TextType::class,
                [
                    'constraints' => [],
                    'empty_data' => '',
                    'required' => false,
                    'label' => 'macopedia.tpay.settings.gateway_configuration.google_merchant_id',
                ],
            )
            ->add(
                'apple_merchant_id',
                TextType::class,
                [
                    'constraints' => [],
                    'empty_data' => '',
                    'required' => false,
                    'label' => 'macopedia.tpay.settings.gateway_configuration.apple_merchant_id',
                ],
            )
            ->add(
                'redirectHiddenInCheckout',
                ChoiceType::class,
                [
                    'choices' => [
                        'macopedia.tpay.settings.gateway_configuration.yes_label' => true,
                        'macopedia.tpay.settings.gateway_configuration.no_label' => false,
                    ],
                    'label' => 'macopedia.tpay.settings.gateway_configuration.redirect_hidden_in_checkout',
                ],
            )
            ->add(
                'production_mode',
                ChoiceType::class,
                [
                    'choices' => [
                        'macopedia.tpay.settings.gateway_configuration.yes_label' => true,
                        'macopedia.tpay.settings.gateway_configuration.no_label' => false,
                    ],
                    'label' => 'macopedia.tpay.settings.gateway_configuration.production_mode',
                ],
            )
            ->add(
                'labels',
                LocalizedFallbackValueCollectionType::class,
                [
                    'label' => 'macopedia.tpay.settings.labels.label',
                    'required' => true,
                    'entry_options' => ['constraints' => [new NotBlank()]],
                ]
            )
            ->add(
                'shortLabels',
                LocalizedFallbackValueCollectionType::class,
                [
                    'label' => 'macopedia.tpay.settings.short_labels.label',
                    'required' => true,
                    'entry_options' => ['constraints' => [new NotBlank()]],
                ]
            )
            ->add(
                'blikLabels',
                LocalizedFallbackValueCollectionType::class,
                [
                    'label' => 'macopedia.tpay.settings.blik.label',
                    'required' => true,
                    'entry_options' => ['constraints' => [new NotBlank()]],
                ]
            )
            ->add(
                'cards_labels',
                LocalizedFallbackValueCollectionType::class,
                [
                    'label' => 'macopedia.tpay.settings.cards.label',
                    'required' => true,
                    'entry_options' => ['constraints' => [new NotBlank()]],
                ]
            )
            ->add(
                'payByLinkLabels',
                LocalizedFallbackValueCollectionType::class,
                [
                    'label' => 'macopedia.tpay.settings.pay_by_links.label',
                    'required' => true,
                    'entry_options' => ['constraints' => [new NotBlank()]],
                ]
            )
            ->add(
                'visaMobileLabels',
                LocalizedFallbackValueCollectionType::class,
                [
                    'label' => 'macopedia.tpay.settings.visa_mobile.label',
                    'required' => true,
                    'entry_options' => ['constraints' => [new NotBlank()]],
                ]
            )
            ->add(
                'pragmaPayLabels',
                LocalizedFallbackValueCollectionType::class,
                [
                    'label' => 'macopedia.tpay.settings.pragma_pay.label',
                    'required' => true,
                    'entry_options' => ['constraints' => [new NotBlank()]],
                ]
            )
            ->add(
                'applePayLabels',
                LocalizedFallbackValueCollectionType::class,
                [
                    'label' => 'macopedia.tpay.settings.apple_pay.label',
                    'required' => true,
                    'entry_options' => ['constraints' => [new NotBlank()]],
                ]
            )
            ->add(
                'googlePayLabels',
                LocalizedFallbackValueCollectionType::class,
                [
                    'label' => 'macopedia.tpay.settings.google_pay.label',
                    'required' => true,
                    'entry_options' => ['constraints' => [new NotBlank()]],
                ]
            )
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                try {
                    $response = $this->gatewayFactory
                        ->create($this->configFactory->create($event->getData()))
                        ->transactions()
                        ->getBankGroups(true);

                    if (($response['result'] ?? '') !== 'success') {
                        throw new RuntimeException();
                    }
                } catch (Throwable $e) {
                    $this->logger->error($e->getMessage(), ['exception' => $e]);
                    $error = new FormError($this->translator->trans('macopedia.tpay.settings.validation.wrong_credentials'));

                    $event->getForm()->get('client_id')->addError($error);
                    $event->getForm()->get('client_secret')->addError($error);
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => GatewaySettings::class,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return self::BLOCK_PREFIX;
    }
}
