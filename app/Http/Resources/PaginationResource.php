<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PaginationResource
 *
 * @package App\Http\Resources
 */
class PaginationResource extends JsonResource
{
    /**
     * @var array
     */
    private array $pagination;

    /**
     * @param $resource
     */
    public function __construct($resource)
    {
        $this->pagination = $this->paginate($resource);
        $resource = $resource->getCollection();
        parent::__construct($resource);
    }

    /**
     * @param $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return $this->pagination;
    }

    /**
     * @param $resource
     *
     * @return array
     */
    public function paginate($resource): array
    {
        return [
            'total'          => $resource->total(),
            'count'          => $resource->count(),
            'per_page'       => $resource->perPage(),
            'current_page'   => $resource->currentPage(),
            'last_page'      => $resource->lastPage(),
            'from'           => $resource->firstItem(),
            'to'             => $resource->lastItem(),
            'first_page_url' => $resource->url(1),
            'next_page_url'  => $resource->nextPageUrl(),
            'prev_page_url'  => $resource->previousPageUrl(),
            'last_page_url'  => $resource->url($resource->lastPage()),
        ];
    }
}
