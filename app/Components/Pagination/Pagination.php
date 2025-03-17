<?php

declare(strict_types=1);

namespace App\Components;

use Nette\Http\Request;
use Nette\Utils\Paginator;

class Pagination extends BaseControl
{
    private Paginator $paginator;

    /** @var array<string,string> $queryParams */
    private array $queryParams;

    public function __construct()
    {
        $this->paginator = new Paginator();
    }

    private function buildUrl(int $page): string
    {
        $params = http_build_query(array_merge($this->queryParams, ['page' => $page]));
        return '?' . $params;
    }

    public function render(string $template = 'Pagination'): void
    {
        $this->template->setFile(__DIR__ . "/$template.latte");
        $this->template->paginator = $this->paginator;
        $this->template->buildUrl = \Closure::fromCallable([$this, 'buildUrl']);
        $this->template->render();
    }

    public function setItemsPerPage(int $itemsPerPage): void
    {
        $this->paginator->setItemsPerPage($itemsPerPage);
    }

    public function setTotalItems(?int $totalItems = null): void
    {
        $this->paginator->setItemCount($totalItems);
    }

    public function setCurrentPage(int $currentPage): void
    {
        $this->paginator->setPage($currentPage);
    }

    public function setQueryParams(Request $httpRequest): void
    {
        $this->queryParams = $httpRequest->getQuery();
        unset($this->queryParams['page']);
    }
}
