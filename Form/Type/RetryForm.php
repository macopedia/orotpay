<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class RetryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tpay', HiddenType::class, [
            'required' => true,
            'empty_data' => 1,
        ]);
    }
}
