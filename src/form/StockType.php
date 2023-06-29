<?php

namespace App\form;
use App\Entity\Stock;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $selectedStocks = $options['data']['stocks'] ?? [];
//var_dump($selectedStocks); exit;
        foreach ($options['stocks'] as $stock) {
            $builder->add('stock_' . $stock->getId(), CheckboxType::class, [
                'label' => $stock->getLibelle(),
                'required' => false,
                'data' => in_array($stock->getId(), $selectedStocks),
            ]);
        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('stocks');
        $resolver->setAllowedTypes('stocks', 'iterable');
    }
}