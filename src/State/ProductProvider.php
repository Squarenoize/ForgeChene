<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Product;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

class ProductProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: CollectionProvider::class)] private ProviderInterface $collectionProvider,
        #[Autowire(service: ItemProvider::class)] private ProviderInterface $itemProvider,
        private Packages $packages,
        private RequestStack $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $result = $this->collectionProvider->provide($operation, $uriVariables, $context);
            foreach ($result as $product) {
                $this->hydrate($product);
            }

            return $result;
        }

        $product = $this->itemProvider->provide($operation, $uriVariables, $context);
        if ($product instanceof Product) {
            $this->hydrate($product);
        }

        return $product;
    }

    private function hydrate(Product $product): void
    {
        if (!$product->getPhotoPath()) {
            return;
        }

        $path = $this->packages->getUrl($product->getPhotoPath());
        $host = $this->requestStack->getCurrentRequest()?->getSchemeAndHttpHost() ?? '';
        $product->setPhotoUrl(str_starts_with($path, 'http') ? $path : $host.$path);
    }
}
