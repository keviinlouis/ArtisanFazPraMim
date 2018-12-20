<?php

namespace DumpNamespace;

use Illuminate\Http\Resources\Json\JsonResource;

class ValidationResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var \Illuminate\Validation\ValidationException
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $response = [
            'success' => false,
            'message' => $this->resource->validator->errors()->first(),
        ];

        if (env('APP_ENV') != 'production') {
            $exception = $this->resource;
            $response['url'] = $request->path();
            $response['method'] = $request->getMethod();
        }

        return $response;
    }

    /**
     * Customize the outgoing response for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     * @return void
     */
    public function withResponse($request, $response): void
    {
        $response->setStatusCode(\Illuminate\Http\Response::HTTP_BAD_REQUEST);
    }
}
