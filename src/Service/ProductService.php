<?php

declare(strict_types=1);

namespace TmpApp\Service;

use TmpApp\Repository\ProductRepository;

class ProductService
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getNameById(int $id): string
    {
        return $this->productRepository->getNameById($id);
    }
}
