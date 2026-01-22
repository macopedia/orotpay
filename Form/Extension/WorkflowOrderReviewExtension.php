<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Form\Extension;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowTransitionType;
use Override;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class WorkflowOrderReviewExtension extends AbstractTypeExtension
{
    protected const string APPLICABLE_WORKFLOW = 'b2b_flow_checkout';
    protected const string APPLICABLE_STEP = 'order_review';
    protected const string ADDITIONAL_DATA_FIELD_NAME = 'additional_data';

    #[Override]
    public static function getExtendedTypes(): iterable
    {
        return [WorkflowTransitionType::class];
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$this->isApplicable($options)) {
            return;
        }

        if (!$builder->has(self::ADDITIONAL_DATA_FIELD_NAME)) {
            $builder->add(self::ADDITIONAL_DATA_FIELD_NAME, HiddenType::class);
        }
    }

    protected function isApplicable(array $options): bool
    {
        return isset($options['workflow_item']) &&
            $options['workflow_item'] instanceof WorkflowItem &&
            $options['workflow_item']->getWorkflowName() === self::APPLICABLE_WORKFLOW &&
            $options['workflow_item']->getCurrentStep()?->getName() === self::APPLICABLE_STEP;
    }
}
