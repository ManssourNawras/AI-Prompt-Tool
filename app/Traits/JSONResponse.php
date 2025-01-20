<?php


namespace App\Traits;

trait JSONResponse
{
    private $resource;

    public function getResource()
    {
        return $this->resource;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    public function collection($collection, $status = true, $errors = null)
    {
        $resource = $this->getResource();

        return $resource::collection($collection)
            ->additional(
                [
                    'status' => $status,
                    'total' => $collection->count()>0?$collection->total():0,
                    'errors' => $errors,
                ]
            );
    }

    public function resource($collection)
    {
        $resourceInstance = new $this->resource($collection);
        $resourceInstance->additional([]);
        return $resourceInstance;
    }
}

