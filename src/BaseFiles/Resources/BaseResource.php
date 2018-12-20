<?php

namespace DumpNamespace;

use App\Models\BaseUser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

abstract class BaseResource extends JsonResource
{
    /**
     * Field to check if is a collection
     * @var bool
     */
    private $isCollection;

    /**
     * Field to check if needs to send JWT token with the response
     * @var bool
     */
    protected $withToken = false;

    /**
     * Field to send a message with the response
     * @var string
     */
    protected $message;

    /**
     * BaseResource constructor.
     *
     * @param $resource Collection|LengthAwarePaginator|Model|Model[]
     */
    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->isCollection = ($resource instanceof Collection || $resource instanceof LengthAwarePaginator);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public final function toArray($request)
    {
        return $this->isCollection ? $this->toCollection($this->resource) : $this->toResource($this->resource);
    }

    public function with($request)
    {
        $response = [
            'success' => true,
        ];

        if ($this->resource instanceof LengthAwarePaginator) {
            $response = $this->extractPaginator($response);
        }

        if ($this->withToken && !$this->isCollection && $this->resource instanceof BaseUser) {
            $response['token'] = \JWTAuth::fromUser($this->resource);
        }

        if ($this->message) {
            $response['message'] = $this->message;
        }


        return $response;
    }

    /**
     * Function to map the collection and return the ToItemOfCollection
     *
     * @param Collection|LengthAwarePaginator $collection
     *
     * @return array
     */
    public function toCollection($collection): array
    {
        return $collection->map(function ($resource) {
            return $this->toItemOfCollection($resource);
        })->toArray();
    }

    /**
     * Function do map the resource in collection
     *
     * @param Model $resource
     *
     * @return array
     */
    public function toItemOfCollection($resource): array
    {
        return $this->toResource($resource);
    }

    /**
     * Function to map the resource case is not a collection
     *
     * @param Model $resource
     *
     * @return array
     */
    abstract public function toResource($resource): array;

    /**
     * @param string $message
     *
     * @return static
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return static
     */
    public function withToken()
    {
        $this->withToken = true;
        return $this;
    }

    /**
     * Function do extract paginator from
     *
     * @param $response
     *
     * @return array
     */
    public function extractPaginator($response): array
    {
        $paginator = (object)$this->resource->toArray();

        $response['links'] = [
            'first_page_url' => $paginator->first_page_url,
            'last_page_url' => $paginator->last_page_url,
            'next_page_url' => $paginator->next_page_url,
            'prev_page_url' => $paginator->prev_page_url,
        ];

        $response['meta'] = [
            'current_page' => $paginator->current_page,
            'from' => $paginator->from,
            'last_page' => $paginator->last_page,
            'path' => $paginator->path,
            'per_page' => $paginator->per_page,
            'to' => $paginator->to,
            'total' => $paginator->total,
        ];

        return $response;
    }

    protected function getValueOrNull($value, $default = null, $function = null)
    {
        if (blank($value)) {
            return $default;
        }
        if ($function && method_exists($value, $function)) {
            return $value->$function();
        }
        return $value;
    }

    /**
     * @param $model
     *
     * @return static
     */
    public static function makeResource($model)
    {
        return new static($model);
    }
}
