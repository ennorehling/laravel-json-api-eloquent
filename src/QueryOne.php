<?php
/*
 * Copyright 2021 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Eloquent;

use Illuminate\Database\Eloquent\Model;
use LaravelJsonApi\Contracts\Store\QueryOneBuilder as QueryOneBuilderContract;
use LaravelJsonApi\Core\Query\QueryParameters;
use LaravelJsonApi\Eloquent\Contracts\Driver;

class QueryOne implements QueryOneBuilderContract
{

    use HasQueryParameters;

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * @var Driver
     */
    private Driver $driver;

    /**
     * @var Model|null
     */
    private ?Model $model;

    /**
     * @var string
     */
    private string $resourceId;

    /**
     * QueryOne constructor.
     *
     * @param Schema $schema
     * @param Driver $driver
     * @param Model|null $model
     * @param string $resourceId
     */
    public function __construct(
        Schema $schema,
        Driver $driver,
        ?Model $model,
        string $resourceId
    ) {
        $this->schema = $schema;
        $this->driver = $driver;
        $this->model = $model;
        $this->resourceId = $resourceId;
        $this->queryParameters = new QueryParameters();
    }

    /**
     * @return JsonApiBuilder
     */
    public function query(): JsonApiBuilder
    {
        $query = new JsonApiBuilder(
            $this->schema,
            $this->driver->query(),
        );

        return $query->withQueryParameters(
            $this->queryParameters
        );
    }

    /**
     * @inheritDoc
     */
    public function filter(?array $filters): QueryOneBuilderContract
    {
        $this->queryParameters->setFilters($filters);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function first(): ?object
    {
        if ($this->model && empty($this->queryParameters->filter())) {
            $this->schema->loader()
                ->forModel($this->model)
                ->loadMissing($this->queryParameters->includePaths());

            return $this->model;
        }

        return $this
            ->query()
            ->whereResourceId($this->resourceId)
            ->first();
    }

}
