<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiCollection extends ResourceCollection
{
    protected string $resourceClass;

    /**
     * Create a new ApiCollection instance.
     *
     * @param  mixed  $resource
     * @param  string  $resourceClass  The resource class to wrap each item with.
     */
    public function __construct($resource, string $resourceClass)
    {
        parent::__construct($resource);
        $this->resourceClass = $resourceClass;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Handle paginated resources
        if ($this->resource instanceof LengthAwarePaginator) {
            return [
                'current_page'     => $this->resource->currentPage(),
                'data'             => ($this->resourceClass)::collection($this->resource->getCollection())->toArray($request),
                'first_page_url'   => $this->resource->url(1),
                'from'             => $this->resource->firstItem(),
                'last_page'        => $this->resource->lastPage(),
                'last_page_url'    => $this->resource->url($this->resource->lastPage()),
                'next_page_url'    => $this->resource->nextPageUrl(),
                'path'             => $this->resource->path(),
                'per_page'         => $this->resource->perPage(),
                'prev_page_url'    => $this->resource->previousPageUrl(),
                'to'               => $this->resource->lastItem(),
                'total'            => $this->resource->total(),
            ];
        }

        // Handle non-paginated collections
        return ($this->resourceClass)::collection($this->resource)->toArray($request);
    }

    /**
     * Static helper for cleaner controller usage.
     */
    public static function for($resource, string $resourceClass)
    {
        return new static($resource, $resourceClass);
    }
}
