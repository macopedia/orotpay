<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Macopedia\Bundle\TpayBundle\Entity\Repository\GatewaySettingsRepository;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Symfony\Component\HttpFoundation\ParameterBag;

#[ORM\Entity(repositoryClass: GatewaySettingsRepository::class)]
class GatewaySettings extends Transport
{
    public const string CLIENT_ID = 'client_id';
    public const string CLIENT_SECRET = 'client_secret';
    public const string MERCHANT_ID = 'merchant_id';
    public const string GOOGLE_MERCHANT_ID = 'google_merchant_id';
    public const string APPLE_MERCHANT_ID = 'apple_merchant_id';
    public const string NOTIFICATION_SECURITY_CODE = 'notification_security_code';
    public const string PRODUCTION_MODE = 'production_mode';
    public const string REDIRECT_HIDDEN_IN_CHECKOUT = 'redirect_hidden_in_checkout';
    public const string LABELS = 'labels';
    public const string SHORT_LABELS = 'short_labels';
    public const string MERCHANT_RSA_KEY = 'merchant_rsa_key';
    public const string LABELS_BLIK = 'labels_blik';
    public const string LABELS_CARDS = 'labels_cards';
    public const string LABELS_PAY_BY_LINK = 'labels_pbl';
    public const string LABELS_PRAGMA_PAY = 'labels_pragma_pay';
    public const string LABELS_VISA_MOBILE = 'labels_visa_mobile';
    public const string LABELS_APPLE_PAY = 'labels_apple_pay';
    public const string LABELS_GOOGLE_PAY = 'labels_google_pay';

    /**
     * @var Collection|LocalizedFallbackValue[]
     */
    #[ORM\ManyToMany(targetEntity: LocalizedFallbackValue::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'oro_tpay_label')]
    #[ORM\JoinColumn(name: 'transport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'localized_value_id', referencedColumnName: 'id', onDelete: 'CASCADE', unique: true)]
    private ?Collection $labels;

    /**
     * @var Collection|LocalizedFallbackValue[]
     */
    #[ORM\ManyToMany(targetEntity: LocalizedFallbackValue::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'oro_tpay_short_label')]
    #[ORM\JoinColumn(name: 'transport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'localized_value_id', referencedColumnName: 'id', onDelete: 'CASCADE', unique: true)]
    private ?Collection $shortLabels;

    /**
     * @var Collection|LocalizedFallbackValue[]
     */
    #[ORM\ManyToMany(targetEntity: LocalizedFallbackValue::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'oro_tpay_blik_label')]
    #[ORM\JoinColumn(name: 'transport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'localized_value_id', referencedColumnName: 'id', onDelete: 'CASCADE', unique: true)]
    private ?Collection $blikLabels;

    /**
     * @var Collection|LocalizedFallbackValue[]
     */
    #[ORM\ManyToMany(targetEntity: LocalizedFallbackValue::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'oro_tpay_cards_label')]
    #[ORM\JoinColumn(name: 'transport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'localized_value_id', referencedColumnName: 'id', onDelete: 'CASCADE', unique: true)]
    private ?Collection $cardsLabels;

    /**
     * @var Collection|LocalizedFallbackValue[]
     */
    #[ORM\ManyToMany(targetEntity: LocalizedFallbackValue::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'oro_tpay_pbl_label')]
    #[ORM\JoinColumn(name: 'transport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'localized_value_id', referencedColumnName: 'id', onDelete: 'CASCADE', unique: true)]
    private ?Collection $payByLinkLabels;

    /**
     * @var Collection|LocalizedFallbackValue[]
     */
    #[ORM\ManyToMany(targetEntity: LocalizedFallbackValue::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'oro_tpay_visa_mobile_label')]
    #[ORM\JoinColumn(name: 'transport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'localized_value_id', referencedColumnName: 'id', onDelete: 'CASCADE', unique: true)]
    private ?Collection $visaMobileLabels;

    /**
     * @var Collection|LocalizedFallbackValue[]
     */
    #[ORM\ManyToMany(targetEntity: LocalizedFallbackValue::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'oro_tpay_pragma_pay_label')]
    #[ORM\JoinColumn(name: 'transport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'localized_value_id', referencedColumnName: 'id', onDelete: 'CASCADE', unique: true)]
    private ?Collection $pragmaPayLabels;

    /**
     * @var Collection|LocalizedFallbackValue[]
     */
    #[ORM\ManyToMany(targetEntity: LocalizedFallbackValue::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'oro_tpay_apple_pay_label')]
    #[ORM\JoinColumn(name: 'transport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'localized_value_id', referencedColumnName: 'id', onDelete: 'CASCADE', unique: true)]
    private ?Collection $applePayLabels;

    /**
     * @var Collection|LocalizedFallbackValue[]
     */
    #[ORM\ManyToMany(targetEntity: LocalizedFallbackValue::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'oro_tpay_google_pay_label')]
    #[ORM\JoinColumn(name: 'transport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'localized_value_id', referencedColumnName: 'id', onDelete: 'CASCADE', unique: true)]
    private ?Collection $googlePayLabels;

    #[ORM\Column(name: 'tpay_client_id', type: 'crypted_string', length: 255, nullable: false)]
    protected string $clientId = '';

    #[ORM\Column(name: 'tpay_client_secret', type: 'crypted_string', length: 255, nullable: false)]
    protected string $clientSecret = '';

    #[ORM\Column(name: 'tpay_merchant_id', type: 'crypted_string', length: 255, nullable: false)]
    protected string $merchantId = '';

    #[ORM\Column(name: 'tpay_google_merchant_id', type: 'crypted_string', length: 255, nullable: false)]
    protected string $googleMerchantId = '';

    #[ORM\Column(name: 'tpay_apple_merchant_id', type: 'crypted_string', length: 255, nullable: false)]
    protected string $appleMerchantId = '';

    #[ORM\Column(name: 'tpay_merchant_rsa_key', type: 'crypted_text', nullable: true)]
    protected ?string $merchantRsaKey = null;

    #[ORM\Column(name: 'tpay_notification_security_code', type: 'crypted_string', length: 255, nullable: false)]
    protected string $notificationSecurityCode = '';

    #[ORM\Column(name: 'tpay_production_mode', type: Types::BOOLEAN, nullable: false, options: ['default' => true])]
    protected bool $productionMode = true;

    #[ORM\Column(name: 'tpay_redirect_hidden_in_checkout', type: Types::BOOLEAN, nullable: false, options: ['default' => true])]
    protected bool $redirectHiddenInCheckout = true;

    private ?ParameterBag $settings = null;

    public function __construct()
    {
        $this->labels = new ArrayCollection();
        $this->shortLabels = new ArrayCollection();

        $defaultRedirectLabel = new LocalizedFallbackValue();
        $defaultRedirectLabel->setString('Płatność online z przekierowaniem (Tpay)');
        $this->labels = new ArrayCollection([$defaultRedirectLabel]);

        $defaultShortLabel = new LocalizedFallbackValue();
        $defaultShortLabel->setString('Tpay - płatność online');
        $this->shortLabels = new ArrayCollection([$defaultShortLabel]);

        $defaultBlikLabel = new LocalizedFallbackValue();
        $defaultBlikLabel->setString('BLIK');
        $this->blikLabels = new ArrayCollection([$defaultBlikLabel]);

        $defaultCardsLabel = new LocalizedFallbackValue();
        $defaultCardsLabel->setString('Karta płatnicza');
        $this->cardsLabels = new ArrayCollection([$defaultCardsLabel]);

        $defaultPayByLinkLabel = new LocalizedFallbackValue();
        $defaultPayByLinkLabel->setString('Płatność online (Tpay)');
        $this->payByLinkLabels = new ArrayCollection([$defaultPayByLinkLabel]);

        $defaultVisaMobileLabel = new LocalizedFallbackValue();
        $defaultVisaMobileLabel->setString('Visa Mobile');
        $this->visaMobileLabels = new ArrayCollection([$defaultVisaMobileLabel]);

        $defaultPragmaPayLabel = new LocalizedFallbackValue();
        $defaultPragmaPayLabel->setString('PragmaPay');
        $this->pragmaPayLabels = new ArrayCollection([$defaultPragmaPayLabel]);

        $defaultApplePayLabel = new LocalizedFallbackValue();
        $defaultApplePayLabel->setString('Apple Pay');
        $this->applePayLabels = new ArrayCollection([$defaultApplePayLabel]);

        $defaultGooglePayLabel = new LocalizedFallbackValue();
        $defaultGooglePayLabel->setString('Google Pay');
        $this->googlePayLabels = new ArrayCollection([$defaultGooglePayLabel]);
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    public function getNotificationSecurityCode(): string
    {
        return $this->notificationSecurityCode;
    }

    public function setNotificationSecurityCode(string $notificationSecurityCode): void
    {
        $this->notificationSecurityCode = $notificationSecurityCode;
    }

    public function isProductionMode(): bool
    {
        return $this->productionMode;
    }

    public function setProductionMode(bool $productionMode): void
    {
        $this->productionMode = $productionMode;
    }

    /**
     * @return Collection|LocalizedFallbackValue[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    public function addLabel(LocalizedFallbackValue $label): static
    {
        if (!$this->labels->contains($label)) {
            $this->labels->add($label);
        }

        return $this;
    }

    public function removeLabel(LocalizedFallbackValue $label): static
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
        }

        return $this;
    }

    /**
     * @return Collection|LocalizedFallbackValue[]
     */
    public function getShortLabels()
    {
        return $this->shortLabels;
    }

    public function addShortLabel(LocalizedFallbackValue $label): static
    {
        if (!$this->shortLabels->contains($label)) {
            $this->shortLabels->add($label);
        }

        return $this;
    }

    public function removeShortLabel(LocalizedFallbackValue $label): static
    {
        if ($this->shortLabels->contains($label)) {
            $this->shortLabels->removeElement($label);
        }

        return $this;
    }

    public function getMerchantRsaKey(): string
    {
        return $this->merchantRsaKey;
    }

    public function setMerchantRsaKey(string $merchantRsaKey): void
    {
        $this->merchantRsaKey = $merchantRsaKey;
    }

    public function getGoogleMerchantId(): string
    {
        return $this->googleMerchantId;
    }

    public function setGoogleMerchantId(string $googleMerchantId): void
    {
        $this->googleMerchantId = $googleMerchantId;
    }

    public function getAppleMerchantId(): string
    {
        return $this->appleMerchantId;
    }

    public function setAppleMerchantId(string $appleMerchantId): void
    {
        $this->appleMerchantId = $appleMerchantId;
    }

    public function getBlikLabels(): ?Collection
    {
        return $this->blikLabels;
    }

    public function addBlikLabel(LocalizedFallbackValue $label): static
    {
        if (!$this->blikLabels->contains($label)) {
            $this->blikLabels->add($label);
        }

        return $this;
    }

    public function removeBlikLabel(LocalizedFallbackValue $label): static
    {
        if ($this->blikLabels->contains($label)) {
            $this->blikLabels->removeElement($label);
        }

        return $this;
    }

    public function getCardsLabels(): ?Collection
    {
        return $this->cardsLabels;
    }

    public function addCardsLabel(LocalizedFallbackValue $label): static
    {
        if (!$this->cardsLabels->contains($label)) {
            $this->cardsLabels->add($label);
        }

        return $this;
    }

    public function removeCardsLabel(LocalizedFallbackValue $label): static
    {
        if ($this->cardsLabels->contains($label)) {
            $this->cardsLabels->removeElement($label);
        }

        return $this;
    }

    public function getPayByLinkLabels(): ?Collection
    {
        return $this->payByLinkLabels;
    }

    public function addPayByLinkLabel(LocalizedFallbackValue $label): static
    {
        if (!$this->payByLinkLabels->contains($label)) {
            $this->payByLinkLabels->add($label);
        }

        return $this;
    }

    public function removePayByLinkLabel(LocalizedFallbackValue $label): static
    {
        if ($this->payByLinkLabels->contains($label)) {
            $this->payByLinkLabels->removeElement($label);
        }

        return $this;
    }

    public function getVisaMobileLabels(): ?Collection
    {
        return $this->visaMobileLabels;
    }

    public function addVisaMobileLabel(LocalizedFallbackValue $label): static
    {
        if (!$this->visaMobileLabels->contains($label)) {
            $this->visaMobileLabels->add($label);
        }

        return $this;
    }

    public function removeVisaMobileLabel(LocalizedFallbackValue $label): static
    {
        if ($this->visaMobileLabels->contains($label)) {
            $this->visaMobileLabels->removeElement($label);
        }

        return $this;
    }

    public function getPragmaPayLabels(): ?Collection
    {
        return $this->pragmaPayLabels;
    }

    public function addPragmaPayLabel(LocalizedFallbackValue $label): static
    {
        if (!$this->pragmaPayLabels->contains($label)) {
            $this->pragmaPayLabels->add($label);
        }

        return $this;
    }

    public function removePragmaPayLabel(LocalizedFallbackValue $label): static
    {
        if ($this->pragmaPayLabels->contains($label)) {
            $this->pragmaPayLabels->removeElement($label);
        }

        return $this;
    }

    public function getApplePayLabels(): ?Collection
    {
        return $this->applePayLabels;
    }

    public function addApplePayLabel(LocalizedFallbackValue $label): static
    {
        if (!$this->applePayLabels->contains($label)) {
            $this->applePayLabels->add($label);
        }

        return $this;
    }

    public function removeApplePayLabel(LocalizedFallbackValue $label): static
    {
        if ($this->applePayLabels->contains($label)) {
            $this->applePayLabels->removeElement($label);
        }

        return $this;
    }

    public function getGooglePayLabels(): ?Collection
    {
        return $this->googlePayLabels;
    }

    public function addGooglePayLabel(LocalizedFallbackValue $label): static
    {
        if (!$this->googlePayLabels->contains($label)) {
            $this->googlePayLabels->add($label);
        }

        return $this;
    }

    public function removeGooglePayLabel(LocalizedFallbackValue $label): static
    {
        if ($this->googlePayLabels->contains($label)) {
            $this->googlePayLabels->removeElement($label);
        }

        return $this;
    }

    public function isRedirectHiddenInCheckout(): bool
    {
        return $this->redirectHiddenInCheckout;
    }

    public function setRedirectHiddenInCheckout(bool $redirectHiddenInCheckout): void
    {
        $this->redirectHiddenInCheckout = $redirectHiddenInCheckout;
    }

    public function getSettingsBag(): ParameterBag
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag(
                [
                    self::LABELS => $this->getLabels(),
                    self::SHORT_LABELS => $this->getShortLabels(),
                    self::LABELS_BLIK => $this->getBlikLabels(),
                    self::LABELS_CARDS => $this->getCardsLabels(),
                    self::LABELS_PAY_BY_LINK => $this->getPayByLinkLabels(),
                    self::LABELS_PRAGMA_PAY => $this->getPragmaPayLabels(),
                    self::LABELS_VISA_MOBILE => $this->getVisaMobileLabels(),
                    self::LABELS_APPLE_PAY => $this->getApplePayLabels(),
                    self::LABELS_GOOGLE_PAY => $this->getGooglePayLabels(),
                    self::CLIENT_ID => $this->getClientId(),
                    self::CLIENT_SECRET => $this->getClientSecret(),
                    self::MERCHANT_ID => $this->getMerchantId(),
                    self::MERCHANT_RSA_KEY => $this->getMerchantRsaKey(),
                    self::GOOGLE_MERCHANT_ID => $this->getGoogleMerchantId(),
                    self::APPLE_MERCHANT_ID => $this->getAppleMerchantId(),
                    self::NOTIFICATION_SECURITY_CODE => $this->getNotificationSecurityCode(),
                    self::PRODUCTION_MODE => $this->isProductionMode(),
                    self::REDIRECT_HIDDEN_IN_CHECKOUT => $this->isRedirectHiddenInCheckout(),
                ]
            );
        }

        return $this->settings;
    }
}
