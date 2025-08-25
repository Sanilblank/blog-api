<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class BaseRepository
 *
 * @package App\Repositories
 */
abstract class BaseRepository
{
    /**
     * @var Model
     */
    protected Model $model;

    /**
     * Constructor
     *
     * @param Model $model Eloquent model for the repository
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Return all records from the database
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Get a query builder for the given request, with and where conditions
     *
     * @param array $request
     * @param array $with
     * @param array $where
     *
     * @return Builder
     */
    public function index(array $request = [], array $with = [], array $where = []): Builder
    {
        return $this->model
            ->filter($request)
            ->with($with)
            ->where($where)
            ->orderBy('id', 'desc');
    }

    /**
     * Return a single record from the database by id
     *
     * @param int $id ID of the record
     *
     * @return Model|null The record or null if not found
     */
    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Create a new record in the database
     *
     * @param array $data Array of data to be saved
     *
     * @return Model The newly created record
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a record in the database
     *
     * @param Model $model The record to be updated
     * @param array $data Array of data to be saved
     *
     * @return Model The updated record
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model;
    }

    /**
     * Delete a record from the database
     *
     * @param Model $model The record to be deleted
     *
     * @return bool True if the record was deleted, false otherwise
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }
}
